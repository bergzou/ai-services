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


class RunDetail extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan command:RunDetail xxx
     * @var string
     */
    protected $signature = 'command:RunDetail {arg1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '多进程跑MYSQL';


    public function handle()
    {
        $task_id = $this->argument('arg1');

        if (is_numeric($task_id)){
            $this->run_detail($task_id);
        }else{
            $this->run_mq($task_id);
        }

    }


    protected function run_detail($task_id){

        $model = new QueueDetailModel();
        $task_info = $model->where('id',$task_id)->first();
        if(empty($task_info)){
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
        }
    }


    public function run_mq($queue_code)
    {

        while(true){
            if(empty($queue_code)){
                posix_kill(posix_getpid(),15);
                break;
            }

            $task_model = new QueueTaskModel();
            $logModel = new QueueTaskLogModel();

            $task_info = $task_model->where('task_code',$queue_code)->first();
            if(empty($task_info)){
                break;
            }
            $task_info = $task_info->toArray();
            $task_id = $task_info['id'];
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
                    $log_id = $logModel->insert($error_data);
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
