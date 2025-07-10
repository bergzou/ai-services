<?php
namespace App\Services;

use App\Client\ProductClient;
use App\Client\RoleClient;
use App\Enums\EnumReturnedOrder;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Oms\ReturnedClaimOrderController;
use App\Libraries\Common;
use App\Libraries\LibSnowflake;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Models\ReturnedOrderModel;
use App\Models\ReturnedLogModel;
use App\Client\FileClient;
use App\Models\ReturnedClaimLogModel;
use App\Enums\EnumReturnedClaimOrder;
use App\Models\ReturnedClaimDetailModel;
use App\Models\ReturnedClaimOrderModel;
use App\Validates\ReturnedClaimOrderValidation;
use App\Validates\ReturnedOrderValidation;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\TraitCommonService;
use App\Models\ReturnedDetailAttachModel;

class ReturnedClaimOrderService extends BaseService
{

    use TraitCommonService;
    /**
     * 获取退件单列表·
     * @param array $requestData
     * @return array
     */
    public function getList(array $requestData): array
    {


         if (isset($requestData['claim_status']) &&  $requestData['claim_status'] == 0) {
            unset($requestData['claim_status']); //删除returned_status字段
         }

        if (isset($requestData['region_warehouse_code_list']) && $requestData['region_warehouse_code_list'] != []) {
            $requestData['warehouse_code'] = array_column($requestData['region_warehouse_code_list'], 1); //删除region_warehouse_code_list字段
            $requestData['region_code'] = array_column($requestData['region_warehouse_code_list'], 0); //删除region_warehouse_code_list字段
        }

        $size = $requestData['size'] ?? 10;
        $current = $requestData['current'] ?? 1; //页数
        $whereMap = [
            'id_than' => ['field' => 'id', 'search' => 'where', 'operator' => '<'],
            'receiving_at' => ['field' => 'receiving_at', 'search' => 'whereBetween'],
            'claim_at' => ['field' => 'claim_at', 'search' => 'whereBetween'],
            'tracking_number' => ['field' => 'tracking_number'],
            'claim_order_code' => ['field' => 'claim_order_code'],
            'returned_order_code' => ['field' => 'returned_order_code'],
            'manage_code' => ['field' => 'manage_code'],
            'manage_name' => ['field' => 'manage_name'],
            'handling_method' => ['field' => 'handling_method'],
            'claim_type' => ['field' => 'claim_type'],
            'warehouse_code' => ['field' => 'warehouse_code'],
            'claim_status' => ['field' => 'claim_status'],
        ];



        $ReturnedClaimOrderModel = new ReturnedClaimOrderModel();

        $requestData = $ReturnedClaimOrderModel->transWhere($requestData);


        $where = $ReturnedClaimOrderModel->convertConditions($requestData, $whereMap);
        $query = $ReturnedClaimOrderModel->getWhereData($where, ReturnedClaimOrderModel::query());

        if (isset($requestData['seller_code']) && $requestData['seller_code'] != ""){
            $query = $query->where(function ($query) use ($requestData){
                if (is_array($requestData['seller_code'])){
                    $query->orWhereIn('seller_code' , $requestData['seller_code'])
                        ->orWhere('claim_status',EnumReturnedClaimOrder::CLAIM_STATUS_PENDING);
                }else{
                    $query->orWhere('seller_code' , $requestData['seller_code'])
                        ->orWhere('claim_status',EnumReturnedClaimOrder::CLAIM_STATUS_PENDING);
                }

            });
        }

        $newQuery = clone $query; // 克隆查询对象
        $total = $newQuery->count(); // 执行查询并获取计数


        $list = $query->orderBy('id','desc')->get()->toArray();

        if (!empty($list)) {

            $warehouseMap       = $this->getListWarehouseNameMap($list);

            $roleData = (new RoleClient())->getSellerCodeAgent(['seller_code_set' => array_column($list,'seller_code')]);
            $sellerCodeMap = $roleData['data'] ?? [];

            $newAt = date('Y-m-d H:i:s');

            foreach ($list as $key => &$value) {
                $value = CommonService::convertToArray($value);
                $value['claim_type_name'] = EnumReturnedClaimOrder::getClaimTypeMap()[$value['claim_type']]?? '';
                $value['claim_status_name'] = EnumReturnedClaimOrder::getClaimStatusMap()[$value['claim_status']]?? '';
                $value['handling_method_name'] = EnumReturnedOrder::getHandlingMethodMap()[$value['handling_method']] ?? '';
                $value['days'] = $this->calculateRemainingDays($value['created_at'],$newAt);
                $this->setRowWarehouseName($value, $warehouseMap);

                $value['seller_code_agent'] = $sellerCodeMap[$value['seller_code']] ?? '';

            }
        }

        return [
            'total' => $total,
            'size' => $size,
            'current' => $current,
            'list' => $list
        ];
    }


    /**
     * 计算剩余有效天数
     * @param mixed $createdAt 创建时间（支持时间戳或日期字符串）
     * @param mixed $newAt 当前/参考时间（支持时间戳或日期字符串）
     * @return int 剩余天数（当已过天数>=7天时返回0）
     *
     * */
    function calculateRemainingDays($createdAt, $newAt): int
    {
        // 转换为时间戳（兼容字符串和时间戳格式）
        $createTime = is_numeric($createdAt) ? (int)$createdAt : strtotime($createdAt);
        $currentTime = is_numeric($newAt) ? (int)$newAt : strtotime($newAt);

        // 计算时间差（秒）
        $diffSeconds = $currentTime - $createTime;

        // 计算已过天数
        $elapsedDays = floor($diffSeconds / 86400); // 86400秒=1天

        // 计算剩余天数（7天减去已过天数）
        $remainingDays = max(7 - $elapsedDays, 0);

        // 超过7天或已过天数>=7时返回0
        return ($elapsedDays >= 7) ? 0 : (int)$remainingDays;
    }



    public function getListCount($requestData): array
    {

      

        if (isset($requestData['claim_status']) &&  $requestData['claim_status'] == 0) {
            unset($requestData['claim_status']); //删除returned_status字段
         }
         if (isset($requestData['region_warehouse_code_list']) &&  $requestData['region_warehouse_code_list'] != []) {
             $requestData['warehouse_code']  = array_column($requestData['region_warehouse_code_list'],1); //删除region_warehouse_code_list字段
             $requestData['region_code']  = array_column($requestData['region_warehouse_code_list'],0); //删除region_warehouse_code_list字段
         }



        $whereMap = [
            'id_than' => ['field' => 'id', 'search' => 'where', 'operator' => '<'],
            'receiving_at' => ['field' => 'receiving_at', 'search' => 'whereBetween'],
            'claim_at' => ['field' => 'receiving_at', 'search' => 'whereBetween'],
            'tracking_number' => ['field' => 'tracking_number'],
            'claim_order_code' => ['field' => 'claim_order_code'],
            'returned_order_code' => ['field' => 'returned_order_code'],
            'manage_code' => ['field' => 'manage_code'],
            'manage_name' => ['field' => 'manage_name'],
            'handling_method' => ['field' => 'handling_method'],
            'claim_type' => ['field' => 'claim_type'],
            'warehouse_code' => ['field' => 'warehouse_code'],
            'claim_status' => ['field' => 'claim_status'],
        ];



        $ReturnedClaimOrderModel = new ReturnedClaimOrderModel();

        $requestData = $ReturnedClaimOrderModel->transWhere($requestData);

        $where = $ReturnedClaimOrderModel->convertConditions($requestData, $whereMap);


        $query = $ReturnedClaimOrderModel->getWhereData($where, ReturnedClaimOrderModel::query());


        if (isset($requestData['seller_code']) && $requestData['seller_code'] != ""){
            $query = $query->where(function ($query) use ($requestData){
                if (is_array($requestData['seller_code'])){
                    $query->orWhereIn('seller_code' , $requestData['seller_code'])
                        ->orWhere('claim_status',EnumReturnedClaimOrder::CLAIM_STATUS_PENDING);
                }else{
                    $query->orWhere('seller_code' , $requestData['seller_code'])
                        ->orWhere('claim_status',EnumReturnedClaimOrder::CLAIM_STATUS_PENDING);
                }

            });
        }



        $statusMap = EnumReturnedClaimOrder::getClaimStatusMap();
        $responseData = [];
        $newQuery = clone $query; // 克隆查询对象
        $count = $newQuery->count(); // 执行查询并获取计数
        $responseData[] = [
            'claim_status' => 0,
            'claim_status_name' => '全部',
            'count' => $count ?? 0,
        ];

        foreach ($statusMap as $key => $value) {
            $newQuery = clone $query; // 克隆查询对象
            $count = $newQuery->where('claim_status', $key)->count(); // 执行查询并获取计数
            $responseData[] = [
                'claim_status' => $key,
                'claim_status_name' => $value,
                'count' => $count ?? 0,
            ];
            unset($newQuery); // 释放查询对象
        }

        return $responseData;
    }

    /**
     * 获取退件单详情
     * @param array $requestData
     * @return array
     **/
    public function getDetail($requestData)
    {

        if (empty($requestData['claim_order_code'])) {
            throw new BusinessException('认领单号不能为空');
        }


        $returnedClaimOrder = ReturnedClaimOrderModel::query()->where('claim_order_code', $requestData['claim_order_code'])->first();
        if (empty($returnedClaimOrder)) throw new BusinessException('认领单不存在');
        $returnedClaimOrder = CommonService::convertToArray($returnedClaimOrder);

        $returnedClaimOrder['claim_type_name'] = EnumReturnedClaimOrder::getClaimTypeMap()[$returnedClaimOrder['claim_type']]?? '';
        $returnedClaimOrder['claim_status_name'] = EnumReturnedClaimOrder::getClaimStatusMap()[$returnedClaimOrder['claim_status']]?? '';
        $returnedClaimOrder['handling_method_name'] = EnumReturnedOrder::getHandlingMethodMap()[$returnedClaimOrder['handling_method']]?? '';
        $this->setRowWarehouseName($returnedClaimOrder, $this->getListWarehouseNameMap([$returnedClaimOrder]));
        
        $claimDetailList = ReturnedClaimDetailModel::query()->where('claim_order_code', $requestData['claim_order_code'])->get()->toArray();    
        if (!empty($claimDetailList)) {
            $claimDetailList = CommonService::convertToArray($claimDetailList);
            $attach = ReturnedDetailAttachModel::query()->where('claim_order_code', $requestData['claim_order_code'])->get()->toArray();
           
            $attachData = [];
            if (!empty($attach)) {
                $attach = CommonService::convertToArray($attach); 
                foreach ($attach as $key => &$value) {
                    $attachData[$value['sku']][] = $value;
                }  
            }

            $skuArr = array_column($claimDetailList,'new_sku');
            $result = (new ProductClient())->getProductsBySkus($skuArr,['sku','main_image','name_en','name_cn']);
            $productArr = $result['data'] ?? [];
            $productMap = array_column($productArr,null,'sku');


            foreach ($claimDetailList as $key => &$value) {
                $value['img_list'] = $attachData[$value['sku']]?? [];
                $value['receive_good_quantity'] =  $value['receive_quantity'] - $value['receive_defective_quantity'];
                $value['name_en'] = $productMap[$value['new_sku']]['name_en']?? '';
                $value['name_cn'] = $productMap[$value['new_sku']]['name_cn']?? '';

            }


        }
        $returnedClaimOrder['detail'] = $claimDetailList;
        $returnedClaimOrder['claim_detail_list'] = $claimDetailList;

        return $returnedClaimOrder;
    }





    /**
     * 获取退件单日志
     * @param array $requestData
     * @return array
     */

    public function getLog($requestData)
    {
        if (empty($requestData['claim_order_code'])) {
            throw new BusinessException('认领单号不能为空');
        }
        $size = $requestData['size'] ?? 10;
        $current = $requestData['current'] ?? 1; //页数
        $whereMap = [
            'claim_order_code' => ['field' => 'claim_order_code'],
        ];

        $returnedClaimLogModel = new ReturnedClaimLogModel();
        $where = $returnedClaimLogModel->convertConditions($requestData, $whereMap);

        $modelData = $returnedClaimLogModel->getQueryByCondition($where, "*", ['id' => 'desc'], true, $current, $size);

        return $modelData;
    }


    /**
     * @throws BusinessException
     */
    public function claim($requestData)
    {

        $returnedOrderValidation = new ReturnedClaimOrderValidation($requestData, 'add');
        $messages = $returnedOrderValidation->isRunFail();
        if (!empty($messages)) throw new BusinessException($messages);

        foreach ($requestData['detail'] as &$value){
            if (empty($value['new_sku'])) $value['new_sku'] = $value['sku'];
            if (empty($value['new_sku'])) $value['new_customer_sku'] = $value['customer_sku'];
        }
        unset($value);


        $returnedClaimOrder = ReturnedClaimOrderModel::query()->where('claim_order_code', $requestData['claim_order_code'])->first();
        if (empty($returnedClaimOrder)) throw new BusinessException('认领单不存在');
        $returnedClaimOrder = CommonService::convertToArray($returnedClaimOrder);
        if (empty($requestData['detail']))  throw new BusinessException('认领单详情不存在');


        if ($returnedClaimOrder['claim_status'] != EnumReturnedClaimOrder::CLAIM_STATUS_PENDING) throw new BusinessException('该认领单已处理');

        try{

            DB::beginTransaction();
            
            $skuArr = array_column($requestData['detail'],'new_sku');
           
            $result = (new ProductClient())->getProductsBySkus($skuArr,['sku','customer_sku','package_weight','package_length','package_width','        "package_height"']);
            $productArr = $result['data'] ?? [];
            $productMap = array_column($productArr,null,'sku');


            $returnedOrderDetailParams = [];
            foreach ($requestData['detail'] as $key => $value) {

                $returnedClaimDetail = ReturnedClaimDetailModel::query()->where('claim_order_code', $requestData['claim_order_code'])->where('sku', $value['sku'])->first();
                if (empty($returnedClaimDetail)) throw new BusinessException('认领单详情不存在');
                $returnedClaimDetail = CommonService::convertToArray($returnedClaimDetail);
                $skuData = $productMap[$value['new_sku']]?? [];
                if (empty($skuData)) throw new BusinessException('SKU不存在');

                $returnedClaimDetailUpdate = [
                    'new_sku' => $value['new_sku'], 
                    'customer_sku' => $skuData['customer_sku'],
                    'new_customer_sku' => $skuData['customer_sku'],
                    'seller_sku' => $value['new_seller_sku'] ?? '',
                ];
           
                $result = DB::table('returned_claim_detail')->where('id', $returnedClaimDetail['id'])->update($returnedClaimDetailUpdate);
                if ($result === false) throw new BusinessException('认领单详情更新失败');

                $returnedOrderDetailParams[] = [
                    'sku' => $value['new_sku'],
                    'customer_sku' => $skuData['customer_sku'],
                    'seller_sku' => $value['new_seller_sku'] ?? '',
                    'sku_weight' => $skuData['package_weight']?? 0,
                    'sku_length' => $skuData['package_length']?? 0,
                    'sku_width' => $skuData['package_width']?? 0,
                    'sku_height' => $skuData['package_height']?? 0,
                    'actual_received_quantity' => $returnedClaimDetail['receive_quantity'],
                    'returned_quantity' => $returnedClaimDetail['receive_quantity'],
                ];


                $returnedDetailAttach = ReturnedDetailAttachModel::query()->where('claim_order_code', $requestData['claim_order_code'])->where('sku',$returnedClaimDetail['sku'])->get()->toArray();
                $returnedDetailAttachData = [];
                if (!empty($returnedDetailAttach)) {
                    $returnedDetailAttach = CommonService::convertToArray($returnedDetailAttach);

                    foreach ($returnedDetailAttach as $key => $val) {
                        
                        $returnedDetailAttachData[] = [
                            'sku' => $value['new_sku'],
                            'attach_url' => $val['attach_url'],
                            'attach_name' => $val['attach_name'],
                        ];
                    } 
                }
            }

            $returnedOrderParams = [
                'manage_code' => $requestData['manage_code'],
                'manage_name' => $requestData['manage_name'],
                'warehouse_code' => $returnedClaimOrder['warehouse_code'],
                'tracking_number' => $returnedClaimOrder['tracking_number'],
                'claim_order_code' => $returnedClaimOrder['claim_order_code'],
                'region_code' => $returnedClaimOrder['region_code'],
                'seller_code' => $requestData['seller_code'],
                'returned_sign' => EnumReturnedOrder::RETURNED_SIGN_1,
                'returned_type' => EnumReturnedOrder::RETURNED_TYPE_3,
                'receiving_at' => date('Y-m-d H:i:s'),
                'returned_illustrate' => $requestData['returned_illustrate'],
                'handling_method' => $requestData['handling_method'],
                'returned_status' => EnumReturnedOrder::RETURNED_STATUS_40,
                'create_type' =>  EnumReturnedOrder::CREATE_TYPE_2,
            ];

            $returnedOrderParams['detail'] = $returnedOrderDetailParams;

            $returnedOrderLogicService = new ReturnedOrderLogicService();
            $returnedOrder = $returnedOrderLogicService->addReturnedOrder($returnedOrderParams, [],EnumReturnedOrder::DOCUMENT_TYPE_1 ,$this->userInfo);
            $orderDetail = $returnedOrderLogicService->addReturnedOrderDetail($returnedOrder,[], $returnedOrderParams);
            if (!empty($returnedDetailAttachData)) {
                $returnedOrderLogicService->addReturnedOrderAttach($returnedOrder, [], $returnedDetailAttachData);
            }
            // 添加日志
            $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $this->userInfo, '创建退件单');
            // 添加操作日志
            $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $this->userInfo);



            $returnedClaimOrderUpdate = [
                'claim_status' => EnumReturnedClaimOrder::CLAIM_STATUS_CLAIMED, 
                'handling_method' => $requestData['handling_method'],
                'manage_code' => $requestData['manage_code'],
                'manage_name' => $requestData['manage_name'],
                'seller_code' => $requestData['seller_code'],
                'claim_at' => date('Y-m-d H:i:s'),
                'returned_order_code' => $returnedOrder['returned_order_code'],
            ];
         
            $result = DB::table('returned_claim_order')->where('id', $returnedClaimOrder['id'])->update($returnedClaimOrderUpdate);
            if ($result === false) throw new BusinessException('认领单更新失败');

            $returnedOrderLogicService->addTransit($returnedOrder, $orderDetail);


            $libSnowflake =  new LibSnowflake(Common::getWorkerId());
            $logData = [
                'claim_order_code' => $returnedClaimOrder['claim_order_code'],
                'claim_log_id' => $libSnowflake->next(),
                'content' => '提交认领单',
                'opeator_name' => $this->userInfo['user_name'] ?? '',
                'opeator_uid' => $this->userInfo['user_uid'] ?? '',
                'operation_at' => date('Y-m-d H:i:s'),
                'log_type' => 1,
                'seller_code' => $requestData['seller_code'],
            ];

            $result = DB::table('returned_claim_log')->insert($logData);
            if (!$result) throw new Exception('创建认领日志失败');

            DB::commit();
        }catch (BusinessException $e) {
            DB::rollBack();
            throw new BusinessException($e->getMessage());
        }catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }


        if ($returnedOrder['document_type'] == EnumReturnedOrder::DOCUMENT_TYPE_1){
            $returnedOrderLogicService->pushOverseasWarehouse(200, $returnedOrder['returned_order_code']);
        }else{
            $returnedOrderLogicService->pushOverseasWarehouse(260, $returnedOrder['returned_order_code']);
        }


        // 推送认领单
        $returnedOrderLogicService->pushOverseasWarehouse(310, $returnedOrder['returned_order_code']);

        return [];
    }
}