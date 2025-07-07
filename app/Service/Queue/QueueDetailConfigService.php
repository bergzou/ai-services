<?php

namespace App\Service\Queue;

use App\Enums\Returned\EnumReturnedOrder;
use App\Exceptions\BusinessException;
use App\Service\Returned\ReturnedOrderAuditService;

class QueueDetailConfigService
{

    

    public static function pushReturnedOrderToWmsReturned($syncData, $regionCode, $operateType): array
    {
        $serviceId = strtolower($regionCode . '_' . getenv('RETURNED_SERVICE_ID'));
        return [
            'msgPrefix'                      => 'oms_syncReturnedOrder_',
            'downSystemReqData'              => $syncData,
            'serviceId'                      => $serviceId,
            'code'                           => $regionCode,
            'reqUri'                         => '/internal/returnedOperate/returnSave',
            'upSystemPushToQueCenterService' => QueueDetailSyncService::class,
            'upSystemPushToQueCenterMethod'  => 'pushQueueDetailToWmsReturned',
            'desc'                           => '推送退件单',
            'taskCode'                       => 'push_returned_order',
            'operateType'                    => $operateType,
        ];
    }


    //推送数据到MQ
    public static function pushReturnedOrderPassToIcs($syncData): array
    {
        return [
            'msgPrefix'                      => 'Returned_pass_',
            'downSystemReqData'              => $syncData,
            'serviceId'                      => 'mq',
            'reqUri'                         => 'mq',
            'upSystemPushToQueCenterService' => QueueDetailSyncService::class,
            'upSystemPushToQueCenterMethod'  => 'pushQueueDetailToMq',
            'desc'                           => '退件单确认通过-添加在途',
            'taskCode'                       => 'push_returned_order',
            'operateType'                    => 200,
            'exchange_type'                  => 'fanout',
            'exchange_name'                  => 'WMS_OVERSEA_PUSH_STOCK_DATA',
            'router_key'                     => 'WMS_OVERSEA_PUSH_STOCK_DATA',
            'queue_name'                     => 'WMS_OVERSEA_PUSH_STOCK_DATA',
        ];
    }


    public static function pushReturnedOrderPassToPlatform($syncData): array
    {
        return [
            'msgPrefix'                      => 'Returned_pass_',
            'downSystemReqData'              => $syncData,
            'serviceId'                      => 'mq',
            'reqUri'                         => 'mq',
            'upSystemPushToQueCenterService' => QueueDetailSyncService::class,
            'upSystemPushToQueCenterMethod'  => 'pushQueueDetailToMq',
            'desc'                           => '退件单-推送ERP',
            'taskCode'                       => 'push_returned_order',
            'operateType'                    => 200,
            'exchange_type'                  => 'fanout',
            'exchange_name'                  => 'WMS_OVERSEA_RETURNED_CODE_SYNC',
            'router_key'                     => 'WMS_OVERSEA_RETURNED_CODE_SYNC',
            'queue_name'                     => 'WMS_OVERSEA_RETURNED_CODE_SYNC',
        ];
    }



    public static function pushReturnedOrderToOutbound($syncData): array
    {
        $serviceId = env('OMS_OUTBOUND_SERVICE_ID','omsoutbound');
        return [
            'msgPrefix'                      => 'oms_syncReturnedOrder_',
            'downSystemReqData'              => $syncData,
            'serviceId'                      => $serviceId,
            'reqUri'                         => '/internal/outbound/order/rmsNoticeReturnInfo',
            'upSystemPushToQueCenterService' => QueueDetailSyncService::class,
            'upSystemPushToQueCenterMethod'  => 'pushReturnedOrderToOutbound',
            'desc'                           => '推送门户出库系统',
            'taskCode'                       => 'push_oms_outbound',
            'operateType'                    => 200,
        ];
    }
}
