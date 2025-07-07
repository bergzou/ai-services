<?php
namespace App\Service\Queue;
use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Libraries\LibSnowflake;
use App\Models\Common\MqLogModel;
use App\Validates\MqLogValidation;

class MqLogService
{

    public function addMqLog($data)
    {
        $mqData    = [
            'body'          => $data['body'] ?? '',
            'msg_id'        => $data['msg_id'] ?? '',
            'mq_log_id'     => $data['mq_log_id'],
            'queue_name'    => $data['queue_name'] ?? '',
            'exchange_name' => $data['exchange_name'] ?? '',
            'created_at'    => date('Y-m-d H:i:s')
        ];
        MqLogModel::query()->insert($mqData);
        return true;
    }


    public function validateMqLog($data)
    {
        $validation = new MqLogValidation($data, 'request');
        $validation->isRunFail(true);
        $isExists = MqLogModel::query()->select('msg_id')->where('msg_id', $data['msgId'])->exists();
        if ($isExists) {
            throw new BusinessException('已经消费过,无需消费', 300);
        }
        return true;
    }

    public function saveValidateMqLog($data)
    {
        $LibSnowflake = new LibSnowflake(Common::getWorkerId());
        $mqInfo       = [
            'msg_id'    => $data['msgId'],
            'mq_log_id' => $LibSnowflake->next(),
        ];
        return $this->addMqLog($mqInfo);
    }


    /**
     * 根据队列数据和任务数据，记录消费日志
     * @param $mqData
     * @param $Taskdata
     * @throws \ReflectionException
     */
    public static function addMqLogByTask($mqData,$Taskdata){

        $LibSnowflake  = new LibSnowflake(Common::getWorkerId());
        $data = $mqData['data'] ?? [];
        $mq_data = [
            'mq_log_id' => $LibSnowflake->next(),
            'body' => json_encode($data),
            'msg_id' => $mqData['msgId'] ??'',
            'queue_name' => $Taskdata['queue_name']??'',
            'exchange_name' => $Taskdata['exchange_name']??'',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $mqLogModel = new MqLogModel();
        $insertResult = $mqLogModel->insert($mq_data);

        return true;
    }


}
