<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Libraries\RabbitMQ;
use App\Models\Common\QueueTaskModel;
use App\Models\Common\QueueTaskLogModel;
use App\Models\Common\QueueDetailModel;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;
use App\Libraries\Predis;

class RunCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:RunCron {arg1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时刷缓存和监控任务';


    public function handle()
    {
        $type = $this->argument('arg1');
        if($type == 1){
            $this->cacheQueList();
        }elseif($type == 2){
            $this->push();
        }elseif($type == 3){
            $this->check();
        }elseif($type == 4){
            $this->createTask();
        }elseif($type ==5){
            $this->clearKey();
        }
        // $service = new CreatePreOrdersService();
        // $service->createPreOrders();
    }

    //缓存队列
    public function cacheQueList(){
        $queListModel = new QueueTaskModel();
        $redis = new Predis();
        $groups = $queListModel->where('status',1)->get();
        if($groups){
            $groups = $groups->toArray();
        }
        $array_column = array_column($groups, 'level');
        array_multisort($array_column,SORT_DESC,$groups);
        if($groups){
            $redis->set($this->getQueueListKey(),json_encode($groups));
        }
        $data = $redis->get($this->getQueueListKey());
        $data = $data ? json_decode($data,true) : [];
        print_r($data);
        return $data;

    }


    public function check(){
        $redis = new Predis();
        $data = $redis->get($this->getQueueListKey());
        $cron_groups = $data ? json_decode($data,true) : [];
        if(is_array($cron_groups) && !empty($cron_groups)){
            foreach ($cron_groups as $group){
                $child_list = $redis->sMembers($this->_getKey($group['id']));
                print_r($child_list);
                if(is_array($child_list) && $child_list){
                    $group_id = $group['id'];
                    foreach ($child_list as $child_id){
                        $is_active = shell_exec("ps -ef |grep $child_id |grep -v grep");
                        if(!$is_active){ //如果进程不存在
                            $redis->del($this->_getKey('cron_group_key_'.$child_id));
                            $redis->del( $this->_getKey('cron_child_task_'.$group_id.'_'.$child_id));
                            $redis->del( $this->_getKey('cron_key_' . $child_id));
                            $redis->srem($this->_getKey($group_id), $child_id);
                        }
                    }
                }
            }

        }
    }

    private function _getKey($group_id){
        return $this->getCacheKeyPrefix().$group_id;
    }

    private function getSystemPrefix(){
        $prefix = rand(1,9999999);
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
   
   
}
