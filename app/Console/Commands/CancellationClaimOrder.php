<?php

namespace App\Console\Commands;

use App\Enums\EnumReturnedClaimOrder;
use App\Libraries\Common;
use App\Libraries\LibSnowflake;
use App\Models\ReturnedClaimLogModel;
use App\Models\ReturnedClaimOrderModel;
use App\Services\CommonService;
use App\Services\Queue\QueueDetailConfigService;
use App\Services\Queue\QueueDetailService;
use App\Services\ReturnedClaimOrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 认领单自动作废命令
 *
 * 功能：自动处理超过7天未认领的订单，将其状态标记为已作废
 * 触发方式：通过Laravel任务调度定期执行（需在Kernel中注册）
 */

class CancellationClaimOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CancellationClaimOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '作废-超期处置剩余天数-认领单';


    /**
     * 执行命令核心逻辑
     *
     * @return array
     *
     * 主要流程：
     * 1. 开启数据库事务
     * 2. 获取一周前的时间节点
     * 3. 查询所有待处理且创建时间超过7天的认领单
     * 4. 遍历处理每个认领单：
     *    a. 更新认领单状态为已作废
     *    b. 推送状态变更到消息队列
     *    c. 记录系统操作日志
     * 5. 提交事务
     * 6. 异常时回滚事务并抛出错误
     * @throws \Exception
     */
    public function handle(): array
    {
        try {
            DB::beginTransaction();
            // 计算时间节点：当前时间往前推7天
            $timestamp = strtotime('-1 week');
            $newAt = date('Y-m-d H:i:s', $timestamp);

            // 查询待处理且超期的认领单（状态为待认领，创建时间早于7天前）
            $claimOrder = ReturnedClaimOrderModel::query()
                ->where('claim_status', EnumReturnedClaimOrder::CLAIM_STATUS_PENDING)
                ->where('created_at', '<', $newAt)
                ->get()->toArray();
            if (empty($claimOrder)) return [];

            
            // 转换数据结构为数组格式
            $claimOrder = CommonService::convertToArray($claimOrder);

            $libSnowflake = new LibSnowflake(Common::getWorkerId());
            foreach ($claimOrder as $item) {

                $updateData = [
                    'claim_status' => EnumReturnedClaimOrder::CLAIM_STATUS_ABANDONED,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'abandoned_at' => date('Y-m-d H:i:s'),
                    'handling_method' => EnumReturnedClaimOrder::HANDLING_METHOD_ABANDONED,
                ];
                // 更新认领单状态为已作废
                ReturnedClaimOrderModel::query()
                    ->where('id', $item['id'])
                    ->update($updateData);

                // 准备推送队列数据（320表示作废操作类型）
                $pushData = [
                    'claim_order_code' => $item['claim_order_code'],
                    'claim_status' => $item['claim_status']
                ];
                $syncData = QueueDetailConfigService::pushReturnedOrderToWmsReturned($pushData, $item['region_code'], 320);
                QueueDetailService::writeLocalQueueTask($syncData, '', false, false);

                // 记录系统操作日志
                $logData = [
                    'claim_order_code' => $item['claim_order_code'],
                    'claim_log_id' => $libSnowflake->next(),
                    'content' => '超期处置剩余天数-系统自动作废',
                    'opeator_name' => 'System',
                    'opeator_uid' => 'System',
                    'operation_at' => date('Y-m-d H:i:s'),
                    'log_type' => 1, // 1表示系统自动操作
                ];
                ReturnedClaimLogModel::query()->insert($logData);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('操作失败' . $e->getMessage());
        }

        return [];
    }

}
