<?php
namespace App\Console\Commands;
use App\Libraries\Logger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Libraries\RabbitMQ;
use App\Models\Common\QueueTaskModel;
use App\Models\Common\QueueTaskLogModel;
use App\Models\Common\QueueDetailModel;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;
use App\Libraries\Predis;


class MysqlProcess extends Command
{
    /**
     * The name and signature of the console command.
     * /usr/bin/php /www/wwwroot/artisan command:MysqlProcess shipping_forecast
     * @var string
     */
    protected $signature = 'command:MysqlProcess {arg1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '多进程跑MYSQL';

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

    public function handle()
    {
        $task_code = $this->argument('arg1');
        while(true){
            if(empty($task_code)){
                posix_kill(posix_getpid(),15);
                return ;
            }
            $task_id = $this->run_task($task_code);
            echo "当前取得的任务id:".$task_id.PHP_EOL;
            if(empty($task_id)){
                posix_kill(posix_getpid(),15);
                return ;
            }

            $model = new QueueDetailModel();
            DB::beginTransaction();
            try{
                $info = $model->forUpdateByPk($task_id,'task_status');
                if($info && $info->task_status == 1){
                    $rows = $model->where('id',$task_id)->where(['task_status' => 1])->update(['task_status' => 5]);
                    if($rows <= 0){
                        throw new BusinessException("更新任务状态失败");
                    }
                }else{
                    throw new BusinessException("任务状态不允许执行");
                }
                DB::commit();
            }catch(BusinessException $e){
                DB::rollBack();
                echo $e->getMessage();
                return ;
            }catch(\Exception $e){
                echo $e->getMessage();
            }

            $task_info = $model->where('id',$task_id)->where('task_code',$task_code)->first();
            if(empty($task_info)){
                posix_kill(posix_getpid(),15);
                return ;
            }
            $task_info = $task_info->toArray();
            $try_num = $task_info['try_num'];
            try{
                $servicePath = $task_info['service_path'] ?? '';
                $param = $task_info['param'] ?? '';
                $service = new $servicePath();
                $funcName = $task_info['func_name'] ?? '';
                if(method_exists($service,$funcName)){
                    $param = $param ? json_decode($param,true) : [];
                    if ($funcName == 'pushQueueDetailToMq'){
                        $param = $task_info;
                    }
                    $status = $service->$funcName($param);
                }else{
                    throw new BusinessException($servicePath."无".$funcName."方法");
                }
                $model->where('id',$task_id)->update(['task_status' => 2,'complete_time' => date("Y-m-d H:i:s"),'err_msg' => '']);
            }catch(BusinessException $e){
                $try_num += 1;
                $model->where('id',$task_id)->update(['task_status' => 3,'try_num' => $try_num,'complete_time' => date("Y-m-d H:i:s"),'err_msg' => $e->getMessage().",已重试:".$try_num."次"]);
            }catch(\Throwable $e){
                $try_num += 1;
                $errMsg = 'file:'.$e->getFile().',line:'.$e->getLine().',message:'.$e->getMessage();
                $model->where('id',$task_id)->update(['task_status' => 3,'try_num' => $try_num, 'complete_time' => date("Y-m-d H:i:s"), 'err_msg' => $errMsg.",已重试:".$try_num."次"]);
            }

            $rand = rand(2,5)*1000;
            usleep($rand);
        }
    }

    public function run_task($task_code){
        $redis = $this->getRedis();
        $redisKey = $this->getRedisTaskPrefix().$task_code;
        $reday_msg_count = $redis->lLen($redisKey);   ##待消费总数
        echo "当前任务队列{$redisKey}剩余数量:".$reday_msg_count.PHP_EOL;
        if($reday_msg_count){
            $task_id = $redis->rpop($redisKey);
            return $task_id;
        }else{
            posix_kill(posix_getpid(),15);
            return 0;
        }
    }

    public function getRedis(){
        static $redis = null;
        if(is_null($redis)){
            $redis = new Predis();
        }
        return $redis;
    }

}
