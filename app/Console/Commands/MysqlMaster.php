<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Common\QueueTaskModel;
use App\Models\Common\QueueDetailModel;
use App\Libraries\Predis;
class MysqlMaster extends Command
{
    /**
     * The name and signature of the console command.
     * /usr/bin/php /www/wwwroot/artisan command:MysqlMaster all
     * @var string
     */
    protected $signature = 'command:MysqlMaster {arg1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '多进程跑Mysql队列的主程序';

    private function getSystemPrefix(){
        $redisPrefix = getenv('SERVICE_CODE') ? getenv('SERVICE_CODE') : $prefix;
        echo "系统缓存前缀:".$redisPrefix;
        echo PHP_EOL;
        return $redisPrefix;
    }

    private function getQueueListKey(){
        return $this->getSystemPrefix().":queue_list_key";
    }

    private function getCacheKeyPrefix(){
        return $this->getSystemPrefix().":customer_cron";
    }

    private function getRedisTaskPrefix(){
        return $this->getSystemPrefix().":redis_tasks_";
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $group = [];
            $cron_groups = [];
            $sparkPath = str_replace(['app\\','app/'],'',app_path().DIRECTORY_SEPARATOR);
            $queueName = $this->argument('arg1');
            $redis = $this->getRedis();
            $this->redis = $redis;
            if($queueName != 'all'){
                $model = new QueueTaskModel();
                $info = $model->where('process_name',$queueName)->where('type',1)->get()->toArray();
                if(empty($info)){
                    die("查找不到Mysql队列:".$queueName);
                }
                $cron_groups = $info;
            }else{
                $cron_groups  = $redis->get($this->getQueueListKey());
                if(!is_array($cron_groups)){
                    $cron_groups = json_decode($cron_groups,true);
                }
            }
      
            if(is_array($cron_groups) && !empty($cron_groups)){
                $detailModel = new QueueDetailModel();
                foreach ($cron_groups as $group){
                    if($group['type'] == 0){
                        continue;
                    }
                    $group_id         = $group['id'] ?? '';
                    $max_process_num  = $group['max_process_num'] ?? 0;
                    $current_task_num = $this->_getTasknumsByGroup($group_id);
                    $total_task_num   = $this->_getTotalTaskNums();
                    $process_name = $group['process_name'] ?? '';
                    $task_code = $group['task_code'] ?? '';
                    if(empty($task_code)){
                        continue;
                    }

                    $sparkPath = str_replace(['app\\','app/'],'',app_path().DIRECTORY_SEPARATOR);
                    $command = '/usr/bin/php '.$sparkPath.'artisan command:MysqlProcess';

                    $total_msg_count = $detailModel->where('task_code',$task_code)->where('task_status',0)->count();
                    $redisKey = $this->getRedisTaskPrefix().$task_code;
                    $reday_msg_count = $this->redis->lLen($redisKey);   ##待消费总数
                    
                    if($reday_msg_count <100){
                        $limit = 100 - $reday_msg_count;
                        $tasks = $detailModel->where('task_code',$task_code)->where('task_status',0)->orderBy('id')->limit($limit)->get();
                        if($tasks){
                            $tasks = $tasks->toArray();
                            foreach($tasks as $_value){
                                $this->redis->lpush($redisKey,$_value['id']);
                                $detailModel->where('id',$_value['id'])->update(['task_status' => 1,'complete_time' => date("Y-m-d H:i:s"),'err_msg' => '']);
                            }
                        }
                    }

                    $reday_msg_count = $this->redis->lLen($redisKey);   ##待消费总数
                    echo $process_name.'：现有任务总数:'.$total_msg_count.PHP_EOL;
                    echo $process_name.'：待消费任务总数:'.$reday_msg_count.PHP_EOL;
     
                    if($reday_msg_count < $max_process_num){
                        $current_task_num = $this->_getTasknumsByGroup($group_id);
                        $all_task_num     = $current_task_num + $reday_msg_count;
                        if($all_task_num <= $max_process_num){
                            $max_process_num = $all_task_num;
                        }
                    }
                    $allow_all = getenv('MAX_CRON_NUM') ? getenv('MAX_CRON_NUM') : 100;

                    if(($total_task_num < $allow_all) &&  ($current_task_num < $max_process_num)){
                        for($i =0; $i < $max_process_num;$i++){
                            if($i < $max_process_num){
                                $current_task_num = $this->_getTasknumsByGroup($group_id);
                                $total_task_num = $this->_getTotalTaskNums();
                                if($total_task_num >= $allow_all || ($current_task_num > $max_process_num)) {
                                    continue;
                                }
                                $reday_msg_count = $this->redis->lLen($redisKey);   ##待消费总数
                                if($reday_msg_count <= 0){
                                    continue;
                                }
                                //更新为执行中
                                $child = new \Swoole\Process(function (\Swoole\Process $process) use ($sparkPath,$task_code) {
                                    $process->exec('/usr/bin/php',[$sparkPath.'artisan','command:MysqlProcess',$task_code]);
                                });
                                $child_id = $child->start();
                                $redis->set($this->_getKey('cron_group_key_' . $child_id), $group_id);
                                $redis->set($this->_getKey('cron_child_task_'.$group_id.'_'.$child_id),time());
                                $redis->sadd($this->_getKey($group_id),$child_id);
                            }
                        }
    
    
                        //退出任务
                        while ($res = \Swoole\Process::wait()) {
                            if ($res) {
                                $group_id = $redis->get($this->_getKey('cron_group_key_'.$res['pid']));
                                echo $group_id.PHP_EOL;
                                $redis->del($this->_getKey('cron_group_key_'.$res['pid']));
                                $redis->del($this->_getKey('cron_child_task_'.$group_id.'_'.$res['pid']));
                                $redis->del($this->_getKey('cron_key_' . $res['pid']));
                                $redis->srem($this->_getKey($group_id), $res['pid']);
                            } else {
                                break;
                            }
                        }
    
                    }


                }
                        
            }    
    


    }catch(\Exception $e){
        echo $e->getMessage();
    }

}

    public function getRedis(){
        static $redis = null;
        if(is_null($redis)){
            $redis = new Predis();
        }
        return $redis;
    }


    //计算总的进程数
    private function _getTotalTaskNums(){
        $total_num = 0;
        $redis = $this->getRedis();
        $queue_list  = $redis->get($this->getQueueListKey());
        if(!is_array($queue_list)){
            $queue_list = json_decode($queue_list,true);
        }
        if(is_array($queue_list) && !empty($queue_list)){
            foreach ($queue_list as $queue_row){
                $child_list = $redis->sMembers($this->_getKey($queue_row['id']));
                $total_num += count($child_list);
            }
        }
        return $total_num;
    }

    //计算出每个队列的进程数
    private function _getTasknumsByGroup($group_id){
        $key = $this->_getKey($group_id);
        $child_list = $this->redis->sMembers($key);
        return count($child_list);
    }

    private function _getKey($group_id){
        return $this->getCacheKeyPrefix().$group_id;
    }

}
