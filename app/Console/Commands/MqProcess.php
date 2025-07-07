<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Libraries\RabbitMQ;
use App\Models\Common\QueueTaskModel;
use App\Models\Common\QueueTaskLogModel;
use App\Exceptions\BusinessException;


class MqProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:MqProcess {arg1} {arg2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '多进程跑MQ的主程序';


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
        $queue_code = $this->argument('arg1');
        $task_id = $this->argument('arg2');
        while(true){
            if(empty($queue_code) || empty($task_id)){
                posix_kill(posix_getpid(),15);
                break;
            }

            $task_model = new QueueTaskModel();
            $logModel = new QueueTaskLogModel();

            $task_info = $task_model->where('id',$task_id)->first();
            if(empty($task_info)){
                break;
            }
            $task_info = $task_info->toArray();
            $amqp = RabbitMQ::getInstance();
            $message = $amqp->getMessage($queue_code);
            if(!$message) {
                $amqp->ack($message); //应答完成
                posix_kill(posix_getpid(),15);
                break;
            }

            $msg_info = $message->getBody();


            try{
                $error_info = $logModel->where('md5_msg',md5($msg_info))->select(['id','try_num','task_id'])->first();
                if(empty($error_info)){
                    $params = $msg_info ? json_decode($msg_info,true) : [];
                    $msgBody = isset($params['reqBody']) ? $params['reqBody'] : [];
                    $msgData = isset($msgBody['data']) ? $msgBody['data'] : [];
                    $error_data = [
                        'service' => getenv('SERVICE_CODE'), 'md5_msg' => md5($msg_info), 'msg' => $msg_info, 'task_id' => $task_id,
                        'queue' => $queue_code,'try_num' => 0,
                        'create_time' => date("Y-m-d H:i:s"), 'error_msg' => '', 'last_try_time' => date("Y-m-d H:i:s"),
                        'key_code' => isset($msgData[$task_info['key_code']]) ? $msgData[$task_info['key_code']] : '',
                        'task_status' => 0,
                    ];
                    $log_id = $logModel->insertGetId($error_data);
                }else{
                    $error_info = $error_info->toArray();
                    $log_id = $error_info['id'];
                }

            }catch(BusinessException $e){
                break;
            }catch(\Exception $e){
                echo $e->getMessage();
            }

            if(empty($log_id)){
                break;
            }
            $error_info = $logModel->where('id',$log_id)->select(['id','try_num','task_id'])->first();
            if($error_info){
                $error_info = $error_info->toArray();
                $try_num = $error_info['try_num'] + 1;
            }else{
                break;
            }

            $isFalse = false;
            try{
                $servicePath = $task_info['service_path'] ?? '';
                $service = new $servicePath();
                $funcName = $task_info['func_name'] ?? '';
                if(method_exists($service,$funcName)){
                    $params = $msg_info ? json_decode($msg_info,true) : [];
                    $status = $service->$funcName($params,$task_info);
                    $status = $status > 0 ? 2 : 10;
                }else{
                    throw new BusinessException($servicePath."无".$funcName."方法");
                }

                $logModel->where('id',$log_id)->update(['task_status' => $status,'last_try_time' => date("Y-m-d H:i:s"),'error_msg' => '']);

            }catch(BusinessException $e){
                $err_msg = $e->getMessage();
                if($log_id){
                    $this->updateLogMsg($log_id,$logModel,$err_msg,$try_num);
                }else{
                    echo $err_msg;
                }
                $isFalse = true;
            }catch(\Throwable $e){
                $err_msg = $e->getMessage();
                if($log_id){
                    $this->updateLogMsg($log_id,$logModel,$err_msg,$try_num);
                }else{
                    echo $err_msg;
                }
            }
            if(!is_null($message)){
                $amqp->ack($message); //应答完成
            }
            $reday_msg_count = $amqp->getMessageCount($queue_code);
            echo "执行成功，剩余".$reday_msg_count.PHP_EOL;
            if($reday_msg_count < 1){
                posix_kill(posix_getpid(),15);
                break; //干掉并退出
            }
            $rand = rand(2,5)*1000;
            usleep($rand);
        }
    }

    public function updateLogMsg($log_id,QueueTaskLogModel $logModel,$err_msg,$try_num){
        $logModel->where('id',$log_id)->update(['task_status' => 3,'last_try_time' => date("Y-m-d H:i:s"),'error_msg' => $err_msg,'try_num' => $try_num]);


    }

}
