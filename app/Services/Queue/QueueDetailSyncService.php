<?php

namespace App\Services\Queue;

use App\Client\BaseClient;
use App\Client\IcsClient;
use App\Client\OmsOutboundClient;
use App\Client\WarehouseBaseClient;
use App\Client\WmsInboundClient;
use App\Client\WmsReturnedClient;
use App\Exceptions\BusinessException;
use App\Libraries\RabbitMQ;

class QueueDetailSyncService
{


    /**
     * @throws BusinessException
     */
    public function pushQueueDetailToWmsReturned($params)
    {
        if (isset($params['service_path'])) {
            $param  = $params['param'] ?? '';
            $params = $param ? json_decode($param, true) : [];
        }
        $serviceId  = getenv('RETURNED_SERVICE_ID', '');
        $regionCode = str_replace('_' . $serviceId, '', $params['serviceId']);
        $reqUri     = $params['reqUri'];
        $reqBody    = $params['reqBody'];
        $client     = new WmsReturnedClient($regionCode);
        $res        = $client->sendClient($client->host . $reqUri, 'POST', json_encode($reqBody));
        if ($res['code'] != 200) {
            $errorMsg = $res['msg'] ?? json_encode($res);
            throw new BusinessException($errorMsg);
        }
        return $res;
    }

    /**
     * @throws BusinessException
     * @throws \Exception
     */
    public function pushQueueDetailToMq($detail): bool
    {
        $param        = $detail['param'] ?? '';
        $params       = $param ? json_decode($param, true) : [];
        $queue        = $detail['queue_name'] ?? '';
        $exchangeName = $detail['exchange_name'] ?? '';
        $exchangeType = $detail['exchange_type'] ?? 'topic';
        $routerKey    = $detail['router_key'] ?? '';

        if (empty($queue) || empty($exchangeName) || empty($routerKey)) {
            throw new BusinessException("推送队列不能为空");
        }

        $reqBody = $params['reqBody'] ?? $params;
        $mq      = RabbitMQ::getInstance();
        return $mq->push($queue, $exchangeName, $routerKey, json_encode($reqBody), $exchangeType);
    }



    /**
     * 推送退货单到出库系统
     *
     * @param array $params 包含以下参数的数组:
     *              - reqUri: 请求路径
     *              - reqBody: 请求体数据
     *              - service_path: [可选] 服务路径标识，存在时会解析param字段
     *              - param: [当service_path存在时] JSON格式的参数字符串
     * @return array 接口响应结果
     * @throws BusinessException 当接口返回码非200时抛出异常
     */
    public function pushReturnedOrderToOutbound($params): array
    {
        // 处理带服务路径的参数结构
        if (isset($params['service_path'])) {
            $param  = $params['param'] ?? '';
            $params = $param ? json_decode($param, true) : [];
        }

        // 提取请求参数
        $reqUri     = $params['reqUri'];

        // 处理请求体数据
        switch ($params['reqUri']){
            case '/internal/outbound/order/rmsNoticeReturnInfo':
                $reqBody    =  $params['reqBody']['data'] ?? [];
                break;
            default:
                $reqBody    =  $params['reqBody'] ?? [];
                break;
        }

        // 初始化出库系统客户端
        $client     = new OmsOutboundClient();

        // throw new BusinessException($client->host.$reqUri);
        // 发送POST请求到目标系统
        $res        = $client->sendClient($client->host.$reqUri, 'POST', json_encode($reqBody));

        // 处理异常响应
        if ($res['code'] != 200) {
            $errorMsg = $res['msg'] ?? json_encode($res);
            throw new BusinessException($errorMsg);

        }
        return $res;
    }


}
