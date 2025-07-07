<?php

namespace App\Service\Queue;

use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Libraries\LibSnowflake;
use App\Models\Common\QueueDetailModel;
use App\Service\BaseService;
use Illuminate\Support\Facades\Lang;

class QueueDetailService extends BaseService
{
    /**
     * 消息前缀
     * @var string
     */
    protected string $msgPrefix;

    /**
     * 下游系统的请求数据
     * @var
     */
    protected $downSystemReqData;

    /**
     * 下游系统对应的服务
     * @var string
     */
    protected string $serviceId;

    /**
     * 下游系统的路由地址
     * @var string
     */
    protected string $reqUri;

    /**
     * 上游系统推送到队列中心的服务(类)
     * @var string
     */
    protected string $upSystemPushToQueCenterService;

    /**
     * 上游系统推送到队列中心的方法
     * @var string
     */
    protected string $upSystemPushToQueCenterMethod;

    /**
     * 消息描述
     * @var string
     */
    protected string $desc;

    /**
     * 任务code
     * @var string
     */
    protected string $taskCode;

    /**
     * 操作类型
     * @var
     */
    protected $operateType;

    protected $queue_name = '';
    protected $exchange_name = '';
    protected $exchange_type = '';
    protected $router_key = '';
    protected $code = '';

    public $isOnlyReqBodyData = false;
    public $isOnlyData = false;

    /**
     * 设置数据
     * @param array $task
     * @return $this
     * @throws ValidateException
     */
    public function setTaskData(array $task)
    {
        $this->msgPrefix                      = $task['msgPrefix'] ?? '';
        $this->downSystemReqData              = $task['downSystemReqData'] ?? '';
        $this->serviceId                      = $task['serviceId'] ?? '';
        $this->reqUri                         = $task['reqUri'] ?? '';
        $this->upSystemPushToQueCenterService = $task['upSystemPushToQueCenterService'] ?? '';
        $this->upSystemPushToQueCenterMethod  = $task['upSystemPushToQueCenterMethod'] ?? '';
        $this->desc                           = $task['desc'] ?? '';
        $this->taskCode                       = $task['taskCode'] ?? '';
        $this->operateType                    = $task['operateType'];
        $this->exchange_name                  = $task['exchange_name'] ?? '';
        $this->exchange_type                  = $task['exchange_type'] ?? '';
        $this->router_key                     = $task['router_key'] ?? '';
        $this->queue_name                     = $task['queue_name'] ?? '';
        $this->code                           = $task['code'] ?? '';

        if (
            empty($this->msgPrefix) || empty($this->downSystemReqData) || empty($this->serviceId) ||
            empty($this->reqUri) || empty($this->upSystemPushToQueCenterService) ||
            empty($this->upSystemPushToQueCenterMethod) || empty($this->desc) || empty($this->taskCode)
        ) {
            throw new BusinessException("{$this->desc}:新增队列任务明细参数缺失");
        }

        return $this;
    }

    /**
     * @param $data
     * @return array
     */
    public function buildQueueTask($data): array
    {
        return [

            'task_name'     => $data['task_name'],
            'service_path'  => $data['service_path'],
            'func_name'     => $data['func_name'],
            'task_code'     => $data['task_code'],
            'param'         => $data['param'],
            'code'          => empty($data['code']) ? $this->code : $data['code'],
            'queue_name'    => empty($data['queue_name']) ? $this->queue_name : $data['queue_name'],
            'exchange_name' => empty($data['exchange_name']) ? $this->exchange_name : $data['exchange_name'],
            'exchange_type' => empty($data['exchange_type']) ? $this->exchange_type : $data['exchange_type'],
            'router_key'    => empty($data['router_key']) ? $this->router_key : $data['router_key'],
            'created_at'    => $data['date'],
        ];
    }

    /**
     * 构建任务数据
     * @return array
     */
    public function buildPushQueueCenterData(): array
    {
        $now = date('Y-m-d H:i:s');
        $id = (new LibSnowflake(Common::getWorkerId()))->next();
        $data = [
            'msgId'       => $this->msgPrefix . $id,
            'data'        => $this->downSystemReqData,
            'requestAt'   => $now,
            'language'    => 'zh-CN',
            'operateType' => $this->operateType
        ];

        $receiveData = [
            'serviceId'   => $this->serviceId,
            'reqUri'      => $this->reqUri,
            'reqBody'     => $data,
            'description' => $this->desc,
        ];

        if ($this->isOnlyReqBodyData){
            $receiveData = $data;
        }

        if ($this->isOnlyData){
            $receiveData = $this->downSystemReqData;
        }

        return [
            'task_name'     => $this->desc,
            'service_path'  => $this->upSystemPushToQueCenterService,
            'func_name'     => $this->upSystemPushToQueCenterMethod,
            'task_code'     => $this->taskCode,
            'param'         => json_encode($receiveData, true),
            'date'          => $now,
            'queue_name'    => $this->queue_name,
            'exchange_name' => $this->exchange_name,
            'exchange_type' => $this->exchange_type,
            'router_key'    => $this->router_key,
            'code'          => $this->code,
        ];

    }



    //写本地队列任务表

    /**
     * @throws BusinessException
     */
    public static function writeLocalQueueTask($data, $code = '', $isOnlyReqBodyData = false, $isOnlyData = false)
    {
        if (empty($data)) return [];
        if (!empty($code)) $data['code'] = $code;
        $queueDetailService = (new QueueDetailService())->setTaskData($data);

        $queueDetailService->isOnlyReqBodyData = $isOnlyReqBodyData;
        $queueDetailService->isOnlyData        = $isOnlyData;

        $queueData       = $queueDetailService->buildQueueTask($queueDetailService->buildPushQueueCenterData());

        $insertQueueData = QueueDetailModel::query()->insert($queueData);
        if (!$insertQueueData) {
            throw new BusinessException("新增QueueDetail失败");
        }
    }


}
