<?php
namespace App\Service;

use App\Client\IcsClient;
use App\Client\ProductClient;
use App\Client\RoleClient;
use App\Client\UicClient;
use App\Enums\EnumReturnedOrder;
use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Models\ReturnedDetailAttachModel;
use App\Service\BaseService;
use App\Service\CommonService;
use App\Models\ReturnedOrderModel;
use App\Models\ReturnedDetailModel;
use App\Models\ReturnedOperateModel;
use App\Models\ReturnedLogModel;
use App\Models\ReturnedOrderBoxDetailModel;
use App\Models\ReturnedOrderBoxModel;
use App\Client\FileClient;
use App\Client\OmsOutboundClient;
use App\Libraries\LibSnowflake;
use App\Service\Queue\QueueDetailConfigService;
use App\Service\Queue\QueueDetailService;
use App\Service\Queue\QueueDetailSyncService;
use Couchbase\Role;
use Exception;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use App\Validates\ReturnedOrderValidation;
use App\Validates\ReturnedDetailValidation;
use App\Validates\ReturnedOrderBoxValidation;


use function Psy\debug;
use function Symfony\Component\String\s;

class ReturnedOrderService extends BaseService
{

    use  TraitCommonService;


    /**
     * 获取退件单列表
     * @param array $requestData
     * @return array
     * @throws BusinessException
     */
    public function getList(array $requestData): array
    {

        $fields = [
            "*"
        ];

        if (isset($requestData['returned_status']) && $requestData['returned_status'] == 0) {
            unset($requestData['returned_status']); //删除returned_status字段
        }
        if (isset($requestData['region_warehouse_code_list']) && $requestData['region_warehouse_code_list'] != []) {
            $requestData['warehouse_code'] = array_column($requestData['region_warehouse_code_list'], 1); //删除region_warehouse_code_list字段
            $requestData['region_code'] = array_column($requestData['region_warehouse_code_list'], 0); //删除region_warehouse_code_list字段
        }

        $size = $requestData['size'] ?? 10;
        $current = $requestData['current'] ?? 1; //页数
        $whereMap = [
            'id_than' => ['field' => 'id', 'search' => 'where', 'operator' => '<'],
            'created_at' => ['field' => 'created_at', 'search' => 'whereBetween'],
            'submit_at' => ['field' => 'submit_at', 'search' => 'whereBetween'],
            'receiving_at' => ['field' => 'receiving_at', 'search' => 'whereBetween'],
            'completion_at' => ['field' => 'completion_at', 'search' => 'whereBetween'],
            'returned_order_code' => ['field' => 'returned_order_code'],
            'returned_reference_no' => ['field' => 'returned_reference_no'],
            'tracking_number' => ['field' => 'tracking_number'],
            'outbound_order_code' => ['field' => 'outbound_order_code'],
            'returned_status' => ['field' => 'returned_status'],
            'manage_code_list' => ['field' => 'manage_code'],
            'manage_name' => ['field' => 'manage_name'],
            'returned_type' => ['field' => 'returned_type'],
            'handling_method' => ['field' => 'handling_method'],
            'warehouse_code' => ['field' => 'warehouse_code'],
            'seller_code' => ['field' => 'seller_code'],
            'create_type' => ['field' => 'create_type'],
            'updator_name' => ['field' => 'updator_name', 'search' => 'like'],
        ];


        $returnOrderModel = new ReturnedOrderModel();

        $requestData = $returnOrderModel->transWhere($requestData);

        $where = $returnOrderModel->convertConditions($requestData, $whereMap);


        $modelData = $returnOrderModel->getQueryByCondition($where, $fields, ['created_at' => 'desc', 'id' => 'desc'], true, $current, $size);
        if (!empty($modelData['list']) && $modelData['total'] > 0) {


            $returnedDetail = ReturnedDetailModel::query()->select(
                DB::raw('SUM(forecast_quantity) as forecast_quantity'),
                DB::raw('SUM(receive_quantity) as receive_quantity'),
                DB::raw('SUM(receive_defective_quantity) as receive_defective_quantity'),
                DB::raw('SUM(putaway_quantity) as putaway_quantity'),'returned_order_code'
            )->whereIn('returned_order_code',array_column($modelData['list'],'returned_order_code'))->groupBy('returned_order_code')->get()->toArray();
            $returnedDetailMap = [];
            if (!empty($returnedDetail)) $returnedDetailMap = array_column($returnedDetail,null,'returned_order_code');

            $warehouseMap = $this->getListWarehouseNameMap($modelData['list']);


            $roleData = (new RoleClient())->getSellerCodeAgent(['seller_code_set' => array_column($modelData['list'],'seller_code')]);
            $sellerCodeMap = $roleData['data'] ?? [];

            foreach ($modelData['list'] as $key => &$value) {

                $value = CommonService::convertToArray($value);
                $value['returned_status_name'] = EnumReturnedOrder::getReturnedStatusMap()[$value['returned_status']] ?? '';
                $value['returned_type_name'] = EnumReturnedOrder::getReturnedTypeMap()[$value['returned_type']] ?? '';
                $value['handling_method_name'] = EnumReturnedOrder::getHandlingMethodMap()[$value['handling_method']] ?? '';
                $value['create_type_name'] = EnumReturnedOrder::getCreateTypeMap()[$value['create_type']] ?? '';
                $value['document_type_name'] = EnumReturnedOrder::getDocumentTypeMap()[$value['document_type']] ?? '';
                $this->setRowWarehouseName($value, $warehouseMap);

                $value['forecast_quantity'] = (int)$returnedDetailMap[$value['returned_order_code']]['forecast_quantity'] ?? 0;
                $value['receive_quantity'] = (int)$returnedDetailMap[$value['returned_order_code']]['receive_quantity'] ?? 0;
                $value['destruction_quantity'] = (int)$returnedDetailMap[$value['returned_order_code']]['receive_defective_quantity'] ?? 0;
                $value['putaway_quantity'] = (int)$returnedDetailMap[$value['returned_order_code']]['putaway_quantity'] ?? 0;
                $value['receive_quantity'] = (int)($value['receive_quantity'] -   $value['destruction_quantity']);
                $value['seller_code_agent'] = $sellerCodeMap[$value['seller_code']] ?? '';

            }
        }
        return $modelData;
    }

    /**
     * @throws BusinessException
     */
    public function getListCount($requestData): array
    {

        if (isset($requestData['returned_status']) && $requestData['returned_status'] == 0) {
            unset($requestData['returned_status']); //删除returned_status字段
        }
        if (isset($requestData['region_warehouse_code_list']) && $requestData['region_warehouse_code_list'] != []) {
            $requestData['warehouse_code'] = array_column($requestData['region_warehouse_code_list'], 1); //删除region_warehouse_code_list字段
            $requestData['region_code'] = array_column($requestData['region_warehouse_code_list'], 0); //删除region_warehouse_code_list字段
        }


        $whereMap = [
            'id_than' => ['field' => 'id', 'search' => 'where', 'operator' => '<'],
            'created_at' => ['field' => 'created_at', 'search' => 'whereBetween'],
            'submit_at' => ['field' => 'submit_at', 'search' => 'whereBetween'],
            'receiving_at' => ['field' => 'receiving_at', 'search' => 'whereBetween'],
            'completion_at' => ['field' => 'completion_at', 'search' => 'whereBetween'],
            'returned_order_code' => ['field' => 'returned_order_code'],
            'returned_reference_no' => ['field' => 'returned_reference_no'],
            'tracking_number' => ['field' => 'tracking_number'],
            'outbound_order_code' => ['field' => 'outbound_order_code'],
            'returned_status' => ['field' => 'returned_status'],
            'manage_code_list' => ['field' => 'manage_code'],
            'manage_name' => ['field' => 'manage_name'],
            'returned_type' => ['field' => 'returned_type'],
            'handling_method' => ['field' => 'handling_method'],
            'warehouse_code' => ['field' => 'warehouse_code'],
            'seller_code' => ['field' => 'seller_code'],
            'create_type' => ['field' => 'create_type'],
            'updator_name' => ['field' => 'updator_name', 'search' => 'like'],
        ];


        $returnOrderModel = new ReturnedOrderModel();

        $requestData = $returnOrderModel->transWhere($requestData);

        $where = $returnOrderModel->convertConditions($requestData, $whereMap);

        $query = $returnOrderModel->getWhereData($where, ReturnedOrderModel::query());

        $statusMap = EnumReturnedOrder::getReturnedStatusMap();
        $responseData = [];
        $newQuery = clone $query; // 克隆查询对象
        $count = $newQuery->count(); // 执行查询并获取计数
        $responseData[] = [
            'status' => 0,
            'name' => '全部',
            'count_val' => $count ?? 0,
        ];

        foreach ($statusMap as $key => $value) {
            $newQuery = clone $query; // 克隆查询对象
            $count = $newQuery->where('returned_status', $key)->count(); // 执行查询并获取计数
            $responseData[] = [
                'status' => $key,
                'name' => $value,
                'count_val' => $count ?? 0,
            ];
            unset($newQuery); // 释放查询对象
        }

        return $responseData;
    }

    /**
     * 获取退件单详情
     * @param array $requestData
     * @return array
     *
     * @throws BusinessException
     */
    public function getDetail($requestData): array
    {

        if (empty($requestData['returned_order_code'])) {
            throw new BusinessException('退件单号不能为空');
        }
        $returnedOrderLogicService = new ReturnedOrderLogicService();
        $returnedOrder = $returnedOrderLogicService->getDetail($requestData);
        return $returnedOrder;
    }


    public function getOperateList($requestData)
    {
        if (empty($requestData['returned_order_code'])) {
            throw new BusinessException('退件单号不能为空');
        }
        $operate = ReturnedOperateModel::query()->where('returned_order_code', $requestData['returned_order_code'])->orderBy('id','desc')->get()->toArray();
        return $operate;
    }

    /**
     * 获取退件单日志
     * @param array $requestData
     * @return array
     */

    public function getLog($requestData)
    {
        if (empty($requestData['returned_order_code'])) {
            throw new BusinessException('退件单号不能为空');
        }
        $size = $requestData['size'] ?? 10;
        $current = $requestData['current'] ?? 1; //页数
        $whereMap = [
            'returned_order_code' => ['field' => 'returned_order_code'],
        ];

        $returnedLogModel = new ReturnedLogModel();
        $where = $returnedLogModel->convertConditions($requestData, $whereMap);

        $modelData = $returnedLogModel->getQueryByCondition($where, "*", ['id' => 'desc'], true, $current, $size);

        return $modelData;
    }

    /**
     * 添加退件单
     * @throws BusinessException
     * @throws Exception
     */
    public function add($requestData, $userInfo = []): array
    {

        try {

            DB::beginTransaction();

            $returnedOrderLogicService = new ReturnedOrderLogicService();


            // 校验
            list($outboundOrder, $requestData) = $returnedOrderLogicService->checkParams($requestData);


            switch ($requestData['handling_method']) {
                case EnumReturnedOrder::HANDLING_METHOD_1:
                case EnumReturnedOrder::HANDLING_METHOD_2:
                    // 添加退件单
                    $returnedOrder = $returnedOrderLogicService->addReturnedOrder($requestData, $outboundOrder, EnumReturnedOrder::DOCUMENT_TYPE_1, $userInfo);
                    // 添加退件单详情
                    $returnedOrderLogicService->addReturnedOrderDetail($returnedOrder, $outboundOrder, $requestData);
                    break;

                case EnumReturnedOrder::HANDLING_METHOD_3:
                    // 添加退件单
                    $returnedOrder = $returnedOrderLogicService->addReturnedOrder($requestData, $outboundOrder, EnumReturnedOrder::DOCUMENT_TYPE_2, $userInfo);

                    // 添加退件单详情-箱
                    $returnedOrderLogicService->addReturnedOrderBox($returnedOrder, $outboundOrder, $requestData);
                    break;
                default:
                    throw new BusinessException('处理方式错误');
            }

            // 添加附件
            if (isset($requestData['attach_list']) && $requestData['attach_list']){
                $returnedOrderLogicService->addReturnedOrderAttach($returnedOrder, $requestData['attach_list']);
            }



            // 添加日志
            $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '创建退件单');

            if ($returnedOrder['returned_status'] != EnumReturnedOrder::RETURNED_STATUS_10){
                $returnedOrderLogicService->addReturnedOrderOperate(['returned_order_code'=>$returnedOrder['returned_order_code'],'returned_status' => EnumReturnedOrder::RETURNED_STATUS_10 ], $userInfo);
            }

            // 添加操作日志
            $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);

            DB::commit();

        } catch (BusinessException $e) {
            DB::rollBack();
            throw new BusinessException($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }


        if ($returnedOrder['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_20){
            if ($returnedOrder['document_type'] == EnumReturnedOrder::DOCUMENT_TYPE_1){
                $returnedOrderLogicService->pushOverseasWarehouse(200, $returnedOrder['returned_order_code']);
            }else{
                $returnedOrderLogicService->pushOverseasWarehouse(260, $returnedOrder['returned_order_code']);
            }
            $returnedOrderLogicService->pushOverseasPlatform( $returnedOrder['returned_order_code']);
        }


        return [];
    }


    /**
     * @throws BusinessException
     * @throws Exception
     */
    public function edit($requestData, $userInfo = []): array
    {

        try {

            DB::beginTransaction();

            $returnedOrderLogicService = new ReturnedOrderLogicService();


            if (empty($requestData['returned_order_code'])) throw new BusinessException('退件单号不能为空');

            $order = ReturnedOrderModel::query()->where('returned_order_code',$requestData['returned_order_code'])->first();
            if (empty($order))  throw new BusinessException('退件单号不存在');

            // 先清除详情信息在添加
            ReturnedDetailModel::query()->where('returned_order_code',$requestData['returned_order_code'])->delete();
            ReturnedOrderBoxModel::query()->where('returned_order_code',$requestData['returned_order_code'])->delete();
            ReturnedOrderBoxDetailModel::query()->where('returned_order_code',$requestData['returned_order_code'])->delete();
            ReturnedOrderBoxDetailModel::query()->where('returned_order_code',$requestData['returned_order_code'])->delete();
            ReturnedDetailAttachModel::query()->where('returned_order_code',$requestData['returned_order_code'])->delete();


            // 校验
            list($outboundOrder, $requestData) = $returnedOrderLogicService->checkParams($requestData);



            switch ($requestData['handling_method']) {
                case EnumReturnedOrder::HANDLING_METHOD_1:
                case EnumReturnedOrder::HANDLING_METHOD_2:
                    // 添加退件单
                    $returnedOrder = $returnedOrderLogicService->updateReturnedOrder($requestData, $outboundOrder, EnumReturnedOrder::DOCUMENT_TYPE_1, $userInfo);
                    // 添加退件单详情
                    $returnedOrderLogicService->addReturnedOrderDetail($returnedOrder, $outboundOrder, $requestData);
                    break;

                case EnumReturnedOrder::HANDLING_METHOD_3:
                    // 添加退件单
                    $returnedOrder = $returnedOrderLogicService->updateReturnedOrder($requestData, $outboundOrder, EnumReturnedOrder::DOCUMENT_TYPE_2, $userInfo);

                    // 添加退件单详情-箱
                    $returnedOrderLogicService->addReturnedOrderBox($returnedOrder, $outboundOrder, $requestData);
                    break;
                default:
                    throw new BusinessException('处理方式错误');
            }

            // 添加附件
            if (isset($requestData['attach_list']) && $requestData['attach_list']){
                $returnedOrderLogicService->addReturnedOrderAttach($returnedOrder, $requestData['attach_list']);
            }



            // 添加日志
            $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '编辑退件单');

            if ($returnedOrder['returned_status'] != EnumReturnedOrder::RETURNED_STATUS_10){
                $returnedOrderLogicService->addReturnedOrderOperate(['returned_order_code'=>$returnedOrder['returned_order_code'],'returned_status' => EnumReturnedOrder::RETURNED_STATUS_10 ], $userInfo);
            }

            // 添加操作日志
            $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);


            DB::commit();

        } catch (BusinessException $e) {
            DB::rollBack();
            throw new BusinessException($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }



        if ($requestData['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_20){
            if ($returnedOrder['document_type'] == EnumReturnedOrder::DOCUMENT_TYPE_1){
                $returnedOrderLogicService->pushOverseasWarehouse(200, $returnedOrder['returned_order_code']);
            }else{
                $returnedOrderLogicService->pushOverseasWarehouse(260, $returnedOrder['returned_order_code']);
            }
            $returnedOrderLogicService->pushOverseasPlatform( $returnedOrder['returned_order_code']);
        }


        return [];
    }

    public function export($requestData, $userInfo = []): array
    {

        $exportRow = [
            'returned_order_code' => '退件单号',
            'returned_type_name' => '退件类型',
            'manage_name' => '项目',
            'seller_code' => '卖家代码',
            'warehouse_name' => '退件仓库',
            'returned_sign_name' => '退件标识',
            'create_type_name' => '创建类型',
            'outbound_order_code' => '出库单号',
            'seller_order_code' => '卖家订单号',
            'outbound_warehouse_name' => '发货仓库',
            'returned_reference_no' => '退件参考号',
            'returned_status_name' => '状态',
            'document_type_name' => '原单据类型',
            'handling_method_name' => '处理方式',
            'tracking_number' => '跟踪号',
            'expected_delivery_time' => '预计到货时间',
            'returned_illustrate' => '退件说明',
            'customer_sku' => 'CKSU',
            'sku_pieces_qty' => '商品名称（中英）',
            'name_en' => '商品名称（英）',
            'name_cn' => '商品名称（中）',
            'forecast_quantity' => '预报退件数量',
            'receive_good_quantity' => '实收良品数量',
            'receive_defective_quantity' => '不良品数量',
            'putaway_quantity' => '上架数量',
        ];
        $exportArr = [];
        foreach ($exportRow as $key => $val) {
            $exportArr[] = [
                'key' => $key,
                'desc' => $val
            ];

        }
        $createData = [
            'maxPage' => 1000,
            'serviceId' => env('SERVICE_ID'),
            'moduleName' => '退件单列表',
            'dataUrl' => '/internal/order/getExportOrder',
            'reqBody' => empty($requestData) ? '{}' : json_encode($requestData, true),
            'fileName' => '退件单列表_' . Common::getUuid(),
            'userId' => $userInfo['user_id'] ?? '',
            'userName' => $userInfo['user_name'] ?? '',
            'rowMap' => $exportArr,
            'pageSize' => 1000
        ];
        (new FileClient())->createPageExport($createData);
        return [];
    }

    /**
     * 获取退件单列表
     * @param array $requestData
     * @return array
     */
    public function getExportOrder(array $requestData): array
    {

        if (isset($requestData['returned_status']) && $requestData['returned_status'] == 0) {
            unset($requestData['returned_status']); //删除returned_status字段
        }
        if (isset($requestData['region_warehouse_code_list']) && $requestData['region_warehouse_code_list'] != []) {
            $requestData['warehouse_code'] = array_column($requestData['region_warehouse_code_list'], 1); //删除region_warehouse_code_list字段
            $requestData['region_code'] = array_column($requestData['region_warehouse_code_list'], 0); //删除region_warehouse_code_list字段
        }


        $whereMap = [
            'id_than' => ['field' => 'returned_order.id', 'search' => 'where', 'operator' => '<'],
            'created_at' => ['field' => 'returned_order.created_at', 'search' => 'whereBetween'],
            'submit_at' => ['field' => 'returned_order.submit_at', 'search' => 'whereBetween'],
            'receiving_at' => ['field' => 'returned_order.receiving_at', 'search' => 'whereBetween'],
            'completion_at' => ['field' => 'returned_order.completion_at', 'search' => 'whereBetween'],
            'returned_order_code' => ['field' => 'returned_order.returned_order_code'],
            'returned_reference_no' => ['field' => 'returned_order.returned_reference_no'],
            'tracking_number' => ['field' => 'returned_order.tracking_number'],
            'outbound_order_code' => ['field' => 'returned_order.outbound_order_code'],
            'returned_status' => ['field' => 'returned_order.returned_status'],
            'manage_code_list' => ['field' => 'returned_order.manage_code'],
            'returned_type' => ['field' => 'returned_order.returned_type'],
            'handling_method' => ['field' => 'returned_order.handling_method'],
            'warehouse_code' => ['field' => 'returned_order.warehouse_code'],
            'seller_code' => ['field' => 'returned_order.seller_code'],
            'create_type' => ['field' => 'returned_order.create_type'],
            'updator_name' => ['field' => 'returned_order.updator_name', 'search' => 'like'],
        ];

        $size = $requestData['size'] ?? 10;
        $current = $requestData['current'] ?? 1; //页数

        $returnOrderModel = new ReturnedOrderModel();

        $requestData = $returnOrderModel->transWhere($requestData);

        $where = $returnOrderModel->convertConditions($requestData, $whereMap);


        $query = $returnOrderModel->getWhereData($where, ReturnedOrderModel::query());
        $query = $query->join('returned_detail','returned_detail.returned_order_code','=','returned_order.returned_order_code');

        $list = $query->orderBy('returned_order.id','desc')->offset( ($current - 1) * $size )->limit($size)->get()->toArray();
        if (!empty( $list )){

            $productClient = (new ProductClient())->getProductsBySkusNew(['sku' => array_column($list, 'sku')]);


            $warehouseMap = $this->getListWarehouseNameMap($list);

            foreach ($list as $key => &$value) {
                $value = CommonService::convertToArray($value);
                $value['returned_status_name'] = EnumReturnedOrder::getReturnedStatusMap()[$value['returned_status']] ?? '';
                $value['returned_type_name'] = EnumReturnedOrder::getReturnedTypeMap()[$value['returned_type']] ?? '';
                $value['handling_method_name'] = EnumReturnedOrder::getHandlingMethodMap()[$value['handling_method']] ?? '';
                $value['create_type_name'] = EnumReturnedOrder::getCreateTypeMap()[$value['create_type']] ?? '';
                $value['document_type_name'] = EnumReturnedOrder::getDocumentTypeMap()[$value['document_type']] ?? '';
                $value['returned_sign_name'] = EnumReturnedOrder::getReturnedSignMap()[$value['returned_sign']] ?? '';
                $value['receive_good_quantity'] = $value['receive_quantity'] - $value['receive_defective_quantity'];
                $value = $this->setProductMap($value, 'sku', $productClient['data'] ?? []);

                $this->setRowWarehouseName($value, $warehouseMap);
            }
        }


        return $list;
    }


    /**
     * @throws BusinessException
     * @throws Exception
     */
    public function cancel($requestData, $userInfo = [])
    {

        if (!isset($requestData['return_order_code']) || !is_array($requestData['return_order_code']) || $requestData['return_order_code'] == []) {
            throw new BusinessException('退件单号不能为空');
        }

        try {
            DB::beginTransaction();
            $libSnowflake = new LibSnowflake(Common::getWorkerId());

            $returnedOrderLogicService = new ReturnedOrderLogicService();

            foreach ($requestData['return_order_code'] as $key => $value) {
                $returnedOrder = ReturnedOrderModel::query()->where('returned_order_code', $value)->first();

                if (empty($returnedOrder)) throw new BusinessException('退件单号不存在' . $value);
                $returnedOrder = CommonService::convertToArray($returnedOrder);
                if (!in_array($returnedOrder['returned_status'], [EnumReturnedOrder::RETURNED_STATUS_10, EnumReturnedOrder::RETURNED_STATUS_20, EnumReturnedOrder::RETURNED_STATUS_30])) {
                    throw new BusinessException('退件单号状态非草稿、待确认、待签收禁止作废' . $value);
                }

                $updateData = [
                    'returned_status' => EnumReturnedOrder::RETURNED_STATUS_60,
                    'updator_name' => $userInfo['user_name'] ?? '',
                    'updator_uid' => $userInfo['user_id'] ?? '',
                ];

                DB::table('returned_order')->where('returned_order_code', $value)->update($updateData);

                $logData = [
                    'returned_order_code' => $value,
                    'content' => '作废退件单',
                    'opeator_name' => $userInfo['user_name'] ?? '',
                    'opeator_uid' => $userInfo['user_id'] ?? '',
                    'operation_at' => date('Y-m-d H:i:s'),
                    'returned_log_id' => $libSnowflake->next(),
                    'log_type' => 1,
                    'seller_code' => $requestData['seller_code'] ?? '',
                ];
                ReturnedLogModel::query()->insert($logData);

                $returnedOrderLogicService->pushOverseasWarehouse(EnumReturnedOrder::WAREHOUSE_OPERATE_TYPE_240, $returnedOrder['returned_order_code']);

                $returnedOrderLogicService->pushOverseasPlatform( $returnedOrder['returned_order_code']);

            }

            DB::commit();

        } catch (BusinessException $e) {
            DB::rollBack();
            throw new BusinessException($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }


        return [];
    }

    /**
     * 云仓同步数据
     * @param $requestData
     * @return array
     * @throws BusinessException
     * @throws Exception
     */
    public function sync($params): array
    {

        $userInfo = [
            'user_id' => 'System',
            'tenant_code' => 'xhs',
            'user_name' => 'System',
        ];
        $requestData = $params['data'] ?? [];
        $returnedOrderLogicService = new ReturnedOrderLogicService();

        $libSnowflake = new LibSnowflake(Common::getWorkerId());
        try {
            DB::beginTransaction();
            switch ($params['operateType']) {

                case EnumReturnedOrder::ORDER_SAVE :  // 新增退件成功

                    break;

                case EnumReturnedOrder::CONFIRM_RETURNED_ORDER :  // 确认退件单

                    if (empty($requestData['returned_order_code_list'])) throw new BusinessException('退件单号不能为空');
                    $returnedOrderModel = new ReturnedOrderModel();

                    foreach ($requestData['returned_order_code_list'] as $key => $value) {

                        $returnedOrder = $returnedOrderModel->query()->where('returned_order_code', $value)->first();
                        if (empty($returnedOrder)) throw new BusinessException('退件单号不存在');
                        $returnedOrder = CommonService::convertToArray($returnedOrder);
                        if ($returnedOrder['returned_status'] != EnumReturnedOrder::RETURNED_STATUS_20) throw new BusinessException('退件单状态非待确认');

                        $returnedOrderUpdate = [
                            'returned_order_code' => $returnedOrder['returned_order_code'],
                            'returned_status' => EnumReturnedOrder::RETURNED_STATUS_30,
                        ];
                        $result = DB::table('returned_order')->where('returned_order_code', $returnedOrder['returned_order_code'])->update($returnedOrderUpdate);
                        if (!$result) throw new BusinessException('修改退件单状态失败');


                        // 添加在途
                        if ($returnedOrder['document_type'] == EnumReturnedOrder::DOCUMENT_TYPE_1){
                            $orderDetail = ReturnedDetailModel::query()->where('returned_order_code', $returnedOrder['returned_order_code'])->get()->toArray(); 
                        }else{
                            $orderDetail = ReturnedOrderBoxDetailModel::query()->where('returned_order_code', $returnedOrder['returned_order_code'])->get()->toArray();  
                        }
                        $orderDetail = CommonService::convertToArray($orderDetail);
                        $returnedOrderLogicService->addTransit($returnedOrder, $orderDetail); 




                        // 添加日志
                        $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '仓库确认');

                        // 添加操作日志
                        $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);

                        $returnedOrderLogicService->pushOverseasPlatform( $returnedOrder['returned_order_code']);

                    }


                    break;
                case EnumReturnedOrder::ORDER_UPDATE_TYPE :  // 代发收货

                    $returnedOrderModel = new ReturnedOrderModel();
                    if (empty($requestData['returned_order_code'])) throw new BusinessException('退件单号不能为空');
                    $returnedOrder = $returnedOrderModel->query()->where('returned_order_code', $requestData['returned_order_code'])->first();
                    if (empty($returnedOrder)) throw new BusinessException('退件单号不存在');
                    $returnedOrder = CommonService::convertToArray($returnedOrder);

                    $returnedOrderUpdate = [
                        'returned_order_code' => $requestData['returned_order_code'],
                        'returned_status' => $requestData['returned_status'] ?? EnumReturnedOrder::RETURNED_STATUS_40,
                        'updator_uid' => $requestData['updator_uid'] ?? '',
                        'updator_name' => $requestData['updator_name'] ?? '',
                        'receiving_at' => date("Y-m-d H:i:s")
                    ];

                    $result = DB::table('returned_order')->where('returned_order_code', $returnedOrder['returned_order_code'])->update($returnedOrderUpdate);
                    if (!$result) throw new BusinessException('修改退件单状态失败');


                    $returnedOrderDetail = $requestData['order_detail'] ?? [];
                    if (!empty($returnedOrderDetail)) {
                        foreach ($returnedOrderDetail as $key => $value) {
                            if (empty($value['sku'])) throw new BusinessException('退件单详情SKU不能为空');
                            if (isset($value['receive_quantity']) && $value['receive_quantity'] > 0) {

                                DB::table('returned_detail')->where('returned_order_code', $returnedOrder['returned_order_code'])->where('sku', $value['sku'])->update(['receive_quantity' => DB::raw("COALESCE(receive_quantity, 0) + {$value['receive_quantity']} ")]);
                            }

                            if (isset($value['receive_defective_quantity']) && $value['receive_defective_quantity'] > 0) {

                                DB::table('returned_detail')->where('returned_order_code', $returnedOrder['returned_order_code'])->where('sku', $value['sku'])->update(['receive_defective_quantity' => DB::raw("COALESCE(receive_defective_quantity, 0) + {$value['receive_defective_quantity']} ")]);

                            }


                        }
                    }


                    if ($returnedOrder['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_30) {
                        // 添加日志
                        $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '仓库签收');

                        // 添加操作日志
                        $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);
                    }

                    if ($returnedOrder['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_40) {
                        // 添加日志
                        $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '仓库收货');

                        // 添加操作日志
                        $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);
                    }

                    if(isset($requestData['attach']) && !empty($requestData['attach'])){
                        // 添加附件
                        $returnedOrderLogicService->addReturnedOrderAttach($returnedOrder, $requestData['attach']);
                    }

                    $returnedOrderLogicService->pushOverseasPlatform( $returnedOrder['returned_order_code']);

                    break;
                case EnumReturnedOrder::PROXY_BOX_RECEIVE_INSERT :  // 代发退件单-新增+收货

                    $requestData['receiving_at'] = date("Y-m-d H:i:s");
                    $requestData['returned_status'] = EnumReturnedOrder::RETURNED_STATUS_40;
                    $requestData['returned_sign'] = EnumReturnedOrder::RETURNED_SIGN_1;
                    $requestData['create_type'] = EnumReturnedOrder::CREATE_TYPE_2;


                    $returnedOrderValidation = new ReturnedOrderValidation($requestData, 'syncAdd');
                    if ($returnedOrderValidation->isRunFail()) throw new BusinessException($returnedOrderValidation->isRunFail());
                    if (!empty($messages)) throw new BusinessException($messages);
                    if (empty($requestData['detail'])) throw new BusinessException('退件单详情不能为空');
                    foreach ($requestData['detail'] as $key => &$value) {
                        $returnedDetailValidation = new ReturnedDetailValidation($value, 'add');
                        if ($returnedDetailValidation->isRunFail()) throw new BusinessException($returnedDetailValidation->isRunFail());
                    }
                    $returnedOrder = $returnedOrderLogicService->addReturnedOrder($requestData, [], EnumReturnedOrder::DOCUMENT_TYPE_1, $userInfo);
                    $orderDetail = $returnedOrderLogicService->addReturnedOrderDetail($returnedOrder, [], $requestData);
                    $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '创建退件单');// 添加操作日志
                    $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);


                    if ($returnedOrder['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_30) {
                        // 添加日志
                        $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '仓库签收');

                        // 添加操作日志
                        $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);
                    }

                    if ($returnedOrder['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_40) {
                        // 添加日志
                        $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '仓库收货');

                        // 添加操作日志
                        $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);
                    }

                    if(isset($requestData['attach']) && !empty($requestData['attach'])){
                        // 添加附件
                        $returnedOrderLogicService->addReturnedOrderAttach($returnedOrder, $requestData['attach']);
                    }

                    // 添加在途
                    $orderDetail = CommonService::convertToArray($orderDetail);
                    $returnedOrderLogicService->addTransit($returnedOrder, $orderDetail); 

                    break;

                case EnumReturnedOrder::FULL_BOX_RECEIVE_SYNC :  // 整箱收货

                    $requestData['receiving_at'] = date("Y-m-d H:i:s");
                    $requestData['returned_status'] = EnumReturnedOrder::RETURNED_STATUS_40;
                    $requestData['returned_sign'] = EnumReturnedOrder::RETURNED_SIGN_1;
                    $requestData['create_type'] = EnumReturnedOrder::CREATE_TYPE_2;


                    $returnedOrderModel = new ReturnedOrderModel();
                    if (empty($requestData['returned_order_code'])) throw new BusinessException('退件单号不能为空');
                    $returnedOrder = $returnedOrderModel->query()->where('returned_order_code', $requestData['returned_order_code'])->first();
                    if (empty($returnedOrder)) throw new BusinessException('退件单号不存在');
                    $returnedOrder = CommonService::convertToArray($returnedOrder);

                    $returnedOrderUpdate = [
                        'returned_order_code' => $requestData['returned_order_code'],
                        'returned_status' => $requestData['returned_status'] ?? EnumReturnedOrder::RETURNED_STATUS_40,
                        'updator_uid' => $requestData['updator_uid'] ?? '',
                        'updator_name' => $requestData['updator_name'] ?? '',
                        'receiving_at' => date("Y-m-d H:i:s")
                    ];

                    $result = DB::table('returned_order')->where('returned_order_code', $returnedOrder['returned_order_code'])->update($returnedOrderUpdate);
                    if (!$result) throw new BusinessException('修改退件单状态失败');


                    $returnedOrderBox = $requestData['box'] ?? [];
                    if (!empty($returnedOrderBox)) {
                        foreach ($returnedOrderBox as $key => $value) {
                            $boxs = ReturnedOrderBoxModel::query()->where('returned_order_code', $returnedOrder['returned_order_code'])->where('box_code', $value['box_code'])->first();
                            if (empty($boxs)) throw new BusinessException('整箱退件单详情不存在');
                            foreach ($value['box_detail'] as $key => $val) {
                                DB::table('returned_detail')->where('returned_order_code', $returnedOrder['returned_order_code'])->where('sku', $val['sku'])->increment('receive_quantity', $val['packing_quantity']);
                            }
                        }
                    }


                    if ($returnedOrder['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_30) {
                        // 添加日志
                        $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '仓库签收');

                        // 添加操作日志
                        $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);
                    }

                    if ($returnedOrder['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_40) {
                        // 添加日志
                        $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '仓库收货');

                        // 添加操作日志
                        $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);
                    }

                    if(isset($requestData['attach']) && !empty($requestData['attach'])){
                        // 添加附件
                        $returnedOrderLogicService->addReturnedOrderAttach($returnedOrder, $requestData['attach']);
                    }

                    $returnedOrderLogicService->pushOverseasPlatform( $returnedOrder['returned_order_code']);

                    break;
                case EnumReturnedOrder::FULL_BOX_RECEIVE_INSERT :  // 整箱退件单-新增+收货

                    $requestData['receiving_at'] = date("Y-m-d H:i:s");
                    $requestData['returned_status'] = EnumReturnedOrder::RETURNED_STATUS_40;
                    $requestData['returned_sign'] = EnumReturnedOrder::RETURNED_SIGN_1;
                    $requestData['create_type'] = EnumReturnedOrder::CREATE_TYPE_2;


                    $returnedOrderValidation = new ReturnedOrderValidation($requestData, 'syncAdd');
                    if ($returnedOrderValidation->isRunFail()) throw new BusinessException($returnedOrderValidation->isRunFail());
                    if (!empty($messages)) throw new BusinessException($messages);
                    if (empty($requestData['box'])) throw new BusinessException('退件单详情不能为空');

                    $requestData['boxs'] = $requestData['box'];
                    foreach ($requestData['boxs'] as $key => &$value) {
                        foreach ($value['box_detail'] as &$val){
                            $val['returned_quantity'] = $val['packing_quantity'] ?? 0;
                        }
                        $returnedDetailValidation = new ReturnedOrderBoxValidation($value, 'add');
                        if ($returnedDetailValidation->isRunFail()) throw new BusinessException($returnedDetailValidation->isRunFail());

                    }

                    $returnedOrder = $returnedOrderLogicService->addReturnedOrder($requestData, [], EnumReturnedOrder::DOCUMENT_TYPE_2, $userInfo);
                    $orderDetail = $returnedOrderLogicService->addReturnedOrderBox($returnedOrder, [], $requestData);

                    $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '创建退件单');// 添加操作日志
                    $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);


                    if ($returnedOrder['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_30) {
                        // 添加日志
                        $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '仓库签收');

                        // 添加操作日志
                        $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);
                    }

                    if ($returnedOrder['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_40) {
                        // 添加日志
                        $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '仓库收货');

                        // 添加操作日志
                        $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);
                    }

                    if(isset($requestData['attach']) && !empty($requestData['attach'])){
                        // 添加附件
                        $returnedOrderLogicService->addReturnedOrderAttach($returnedOrder, $requestData['attach']);
                    }

                     // 添加在途
                     $orderDetail = CommonService::convertToArray($orderDetail);

                     $returnedOrderLogicService->addTransit($returnedOrder, $orderDetail); 

                    break;
                case EnumReturnedOrder::ORDER_UPDATE :  // 修改草稿退件单

                    break;
                case EnumReturnedOrder::SYNC_FULL_BOX_RETURNED :  // 同步签收退件单

                    break;
                case EnumReturnedOrder::SYNC_PUTAWAY_BATCH :  // 同步上架批次
                    if (empty($requestData['returned_order_code'])) throw new BusinessException('退件单号不能为空');
                    $returnedOrder = ReturnedOrderModel::query()->where('returned_order_code', $requestData['returned_order_code'])->first();
                    if (empty($returnedOrder)) throw new BusinessException('退件单号不存在');
                    $returnedOrder = CommonService::convertToArray($returnedOrder);

                    if (empty($requestData['product_detail'])) throw new BusinessException('退件单详情不能为空');
                    foreach ($requestData['product_detail'] as $key => $value) {
                        if (empty($value['sku'])) throw new BusinessException('退件单详情SKU不能为空');
                        DB::table('returned_detail')->where('returned_order_code', $returnedOrder['returned_order_code'])->where('sku', $value['sku'])->increment('putaway_quantity', $value['putaway_quantity']);
                    }


                    // 推送到出库单
                    if (!empty($returnedOrder['outbound_order_code'])){
                        $pushData = [
                            'returned_order_code' => $returnedOrder['returned_order_code'],
                            'return_code' => $returnedOrder['returned_order_code'],
                            'outbound_order_code' => $returnedOrder['outbound_order_code'],
                        ];
                        $syncData = QueueDetailConfigService::pushReturnedOrderToOutbound($pushData);
                        QueueDetailService::writeLocalQueueTask($syncData, $returnedOrder['returned_order_code']);
                    }



                    break;
                case EnumReturnedOrder::SYNC_PUTAWAY_INFO :  // 退件单上架完成

                    $returnedOrderModel = new ReturnedOrderModel();
                    if (empty($requestData['returned_order_code'])) throw new BusinessException('退件单号不能为空');
                    $returnedOrder = $returnedOrderModel->query()->where('returned_order_code', $requestData['returned_order_code'])->first();
                    if (empty($returnedOrder)) throw new BusinessException('退件单号不存在');
                    $returnedOrder = CommonService::convertToArray($returnedOrder);

                    $returnedOrderUpdate = [
                        'returned_order_code' => $requestData['returned_order_code'],
                        'returned_status' => $requestData['returned_status'] ?? EnumReturnedOrder::RETURNED_STATUS_50,
                        'updator_uid' => $requestData['updator_uid'] ?? '',
                        'updator_name' => $requestData['updator_name'] ?? '',
                        'completion_at' => date("Y-m-d H:i:s"),
                    ];

                    $result = DB::table('returned_order')->where('returned_order_code', $returnedOrder['returned_order_code'])->update($returnedOrderUpdate);
                    if (!$result) throw new BusinessException('修改退件单状态失败');



                    // 添加日志
                    $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '仓库上架完成');

                    // 添加操作日志
                    $returnedOrderLogicService->addReturnedOrderOperate($returnedOrderUpdate, $userInfo);

                    $returnedOrderLogicService->pushOverseasPlatform( $returnedOrder['returned_order_code']);

                    break;
                case EnumReturnedOrder::CLAIM_SAVE :  // 新增认领单
                    
                    $returnedClaimOrder = $returnedOrderLogicService->addReturnedClaimOrder($requestData, $userInfo);
                    $returnedOrderLogicService->addReturnedClaimOrderDetail($returnedClaimOrder,$requestData, $userInfo);

                    break;
                case EnumReturnedOrder::CLAIM_UPDATE_TYPE :  // 修改认领单

                    break;
                default:
                    throw new BusinessException('操作类型错误');
            }

            DB::commit();
        } catch (BusinessException $exception) {
            DB::rollBack();
            throw new BusinessException($exception->getMessage());
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }


        return [];

    }

    /**
     * 获取出库单
     * @param $requestData
     * @return mixed
     * @throws BusinessException
     * @throws Exception
     */

    public function getOutboundOrder($requestData){

        if (empty($requestData['outbound_order_code'])) throw new BusinessException('退件单号不能为空');
        if (empty($requestData['manage_code'])) throw new BusinessException('项目不能为空');
        $omsOutboundClientData = (new OmsOutboundClient())->getOutboundOrder($requestData);
        $outboundOrder = $omsOutboundClientData['data']?? [];
        if (empty($outboundOrder)) throw new BusinessException('出库单不存在');

        if ($requestData['manage_code'] != $outboundOrder['manage_code'])  throw new BusinessException('出库单不存在');
        if ($outboundOrder['outbound_order_status'] != 110 ) throw new BusinessException('出库单状态非已完成');



        

        if (!empty($outboundOrder['detail'])){

            $productParams = [
                'sku' => array_column($outboundOrder['detail'],'sku'),
                'tenant_code' => 'xhs',
            ];
            $productClient = (new ProductClient())->getProductAndMapInfoV2($productParams);
            $productMap = $productClient['data'] ?? [];
            if (!empty($productMap)) $productMap = array_column($productMap,null,'sku');

            foreach ($outboundOrder['detail'] as &$detail){
                $detail['name_en'] = $productMap[$detail['sku']]['name_en']?? '';
                $detail['name_cn'] = $productMap[$detail['sku']]['name_cn']?? '';
                $detail['main_image'] = $productMap[$detail['sku']]['main_image']?? '';
                $detail['seller_sku'] = $productMap[$detail['sku']]['seller_sku']?? '';
                $detail['seller_sku'] = CommonService::conversionSellerSku($detail['seller_sku']);
                $detail['new_seller_sku'] = $detail['seller_sku'];

            }
        }

        if (!empty($outboundOrder['box_list'])){
            foreach ($outboundOrder['box_list'] as &$box){
                $box['box_detail'] = $box['product_list'];

                foreach ($box['box_detail'] as &$v){
                    $v['returned_quantity'] = $v['real_qty'];
                }

            }
        }

        return $outboundOrder;
    }


    /**
     * @throws BusinessException
     */
    public function generateOrderCode($requestData){
        if (empty($requestData['seller_code'])) throw new BusinessException('卖家代码不能为空');
        $returnedOrderCode = $this->getReturnedOrderCode($requestData['seller_code']);
        if (empty($returnedOrderCode)) throw new BusinessException('获取单号失败');
        return ['returned_order_code' => $returnedOrderCode];
    }


    /**
     * @throws BusinessException
     * @throws Exception
     */
    public function save($requestData, $userInfo = []): array
    {


        if (empty($requestData['seller_order_code'])) throw new BusinessException('卖家订单号必填');
        $returnedOrderOld = ReturnedOrderModel::query()->where('seller_order_code',$requestData['seller_order_code'])
            ->where('create_type',EnumReturnedOrder::CREATE_TYPE_3)->first();

        if (!isset($requestData['returned_order_code']) || $requestData['returned_order_code'] == ''){
            if (!empty($returnedOrderOld)) return CommonService::convertToArray($returnedOrderOld);
        }


        try {

            DB::beginTransaction();

            $returnedOrderLogicService = new ReturnedOrderLogicService();


            if (empty($requestData['warehouse_code'])) throw new BusinessException('仓库编码必填');
            $warehouseData = (new IcsClient())->getWarehouseCode(['warehouse_code' => [$requestData['warehouse_code']]]);
            $warehouseData = $warehouseData['data']['list'] ?? [];
            $warehouseMap = array_column($warehouseData,'region_code','warehouse_code');
            $requestData['region_code'] = $warehouseMap[$requestData['warehouse_code']] ?? '';

            if (empty($requestData['seller_code'])) throw new BusinessException('卖家代码必填');
            if (empty($requestData['region_code'])) throw new BusinessException('区域仓编码必填');
            if (empty($requestData['tenant_code'])) throw new BusinessException('租户编码必填');
            $requestData['create_type'] = EnumReturnedOrder::CREATE_TYPE_3;

            if (isset($requestData['returned_order_code']) && $requestData['returned_order_code'] != '') {
                $order = ReturnedOrderModel::query()->where('returned_order_code', $requestData['returned_order_code'])->first();
                if (empty($order)) throw new BusinessException('退件单号不存在');

                // 先清除详情信息在添加
                ReturnedDetailModel::query()->where('returned_order_code', $requestData['returned_order_code'])->delete();
                ReturnedOrderBoxModel::query()->where('returned_order_code', $requestData['returned_order_code'])->delete();
                ReturnedOrderBoxDetailModel::query()->where('returned_order_code', $requestData['returned_order_code'])->delete();
                ReturnedOrderBoxDetailModel::query()->where('returned_order_code', $requestData['returned_order_code'])->delete();
                ReturnedDetailAttachModel::query()->where('returned_order_code', $requestData['returned_order_code'])->delete();
            }


            // 校验
            list($outboundOrder, $requestData) = $returnedOrderLogicService->checkParams($requestData);


            switch ($requestData['handling_method']) {
                case EnumReturnedOrder::HANDLING_METHOD_1:
                case EnumReturnedOrder::HANDLING_METHOD_2:
                    // 添加退件单
                    if (isset($requestData['returned_order_code']) && $requestData['returned_order_code'] != '') {
                        $returnedOrder = $returnedOrderLogicService->updateReturnedOrder($requestData, $outboundOrder, EnumReturnedOrder::DOCUMENT_TYPE_1, $userInfo);

                    } else {
                        $returnedOrder = $returnedOrderLogicService->addReturnedOrder($requestData, $outboundOrder, EnumReturnedOrder::DOCUMENT_TYPE_1, $userInfo);

                    }
                    // 添加退件单详情
                    $returnedOrderLogicService->addReturnedOrderDetail($returnedOrder, $outboundOrder, $requestData);
                    break;

                case EnumReturnedOrder::HANDLING_METHOD_3:
                    // 添加退件单
                    if (isset($requestData['returned_order_code']) && $requestData['returned_order_code'] != '') {
                        $returnedOrder = $returnedOrderLogicService->updateReturnedOrder($requestData, $outboundOrder, EnumReturnedOrder::DOCUMENT_TYPE_2, $userInfo);

                    } else {
                        $returnedOrder = $returnedOrderLogicService->addReturnedOrder($requestData, $outboundOrder, EnumReturnedOrder::DOCUMENT_TYPE_2, $userInfo);
                    }
                    // 添加退件单详情-箱
                    $returnedOrderLogicService->addReturnedOrderBox($returnedOrder, $outboundOrder, $requestData);
                    break;
                default:
                    throw new BusinessException('处理方式错误');
            }



            // 添加附件
            if (isset($requestData['attach_list']) && $requestData['attach_list']) {
                $returnedOrderLogicService->addReturnedOrderAttach($returnedOrder, $requestData['attach_list']);
            }


            // 添加日志
            $returnedOrderLogicService->addReturnedOrderLog($returnedOrder, $userInfo, '编辑退件单');

            if ($returnedOrder['returned_status'] != EnumReturnedOrder::RETURNED_STATUS_10) {
                $returnedOrderLogicService->addReturnedOrderOperate(['returned_order_code' => $returnedOrder['returned_order_code'], 'returned_status' => EnumReturnedOrder::RETURNED_STATUS_10], $userInfo);
            }

            // 添加操作日志
            $returnedOrderLogicService->addReturnedOrderOperate($returnedOrder, $userInfo);


            DB::commit();

        } catch (BusinessException $e) {
            DB::rollBack();
            throw new BusinessException($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }


        if ($returnedOrder['returned_status'] == EnumReturnedOrder::RETURNED_STATUS_20) {
            if ($returnedOrder['document_type'] == EnumReturnedOrder::DOCUMENT_TYPE_1) {
                $returnedOrderLogicService->pushOverseasWarehouse(200, $returnedOrder['returned_order_code']);
            } else {
                $returnedOrderLogicService->pushOverseasWarehouse(260, $returnedOrder['returned_order_code']);
            }
        }


        return $returnedOrder;
    }


    /**
     * @throws BusinessException
     */
    public function selectData($requestData , $type, $userInfo)
    {


        $responseData = [];
        $createType = EnumReturnedOrder::getCreateTypeMap();
        foreach ($createType as $key => $value) {
            $responseData['create_type'][] = [
                'key' => $key,
                'desc' => $value,
            ];
        }
        $documentType = EnumReturnedOrder::getDocumentTypeMap();
        foreach ($documentType as $key => $value) {
            $responseData['document_Type'][] = [
                'key' => $key,
                'desc' => $value,
            ];
        }

        $handlingMethod = EnumReturnedOrder::getHandlingMethodMap();
        foreach ($handlingMethod as $key => $value) {
            if ($key == EnumReturnedOrder::HANDLING_METHOD_4) continue;
            
            $responseData['handling_method_list'][] = [
                'key' => $key,
                'desc' => $value,
            ];
        }

        $returnedType = EnumReturnedOrder::getReturnedTypeMap();
        foreach ($returnedType as $key => $value) {
            $responseData['returned_type_list'][] = [
                'key' => $key,
                'desc' => $value,
            ];
        }

        $manage = $warehouseList = $warehouseCodeSet = [];
        $uicClient = new UicClient();
        switch ($type) {
            case 'oms':
                if (empty($requestData['seller_code'])) throw new BusinessException('卖家代码不能为空');
                $manageList = $uicClient->userTopWarehouse(['userId' => $userInfo['user_id']]);
                break;
            case 'middle';
                $manageList = $uicClient->innerWarehouse();
                break;
        }


        $manageList = $manageList['data'] ?? [];
        if (!empty($manageList)) {
            foreach ($manageList as $value) {
                $manageRow = [
                    'key' => $value['manageCode'],
                    'desc' => $value['name'],
                ];
                $warehouseItemList = [];
                if (!empty($value['warehouseList'])) {
                    $warehouseList = array_merge(  $warehouseList ,$value['warehouseList'] );
                    foreach ($value['warehouseList'] as $item) {
                        $warehouseItem = [
                            'warehouse_code' => $item['warehouseCode'],
                            'warehouse_name' => $item['warehouseName'],
                            'region_code' => $item['regionCode'],
                            'region_name' => $item['regionName'],
                        ];
                        $warehouseCodeSet[$item['warehouseCode']] = true;
                        $warehouseItemList[] = $warehouseItem;
                    }
                }
                $manageRow['warehouse_list'] = $warehouseItemList;
                $manage[] = $manageRow;
            }
        }
        $responseData['manage'] = $manage;



        $region = [];
        $icsClient = new IcsClient();
        $queryDTOMap = [];
        if (!empty($warehouseList)){
            $warehouseCodes = array_keys($warehouseCodeSet);
            $queryWarehouse = $icsClient->getWarehouseCode(['warehouse_code'=>$warehouseCodes]);
            $queryWarehouse = $queryWarehouse['data']['list'] ?? [];
            $regionMap = [];
            if (!empty($queryWarehouse)){

                foreach ($queryWarehouse as $dto) {

                    $queryDTOMap[$dto['warehouse_code']] = $dto;
                    $regionCode = $dto['region_code'];
                    if (!isset($regionMap[$regionCode])) {
                        $regionMap[$regionCode] = [];
                    }
                    $regionMap[$regionCode][] = $dto;
                }
            }

            foreach ($regionMap as $regionCode => $dtos) {

                $regionItem = [
                    'key' => $regionCode,
                    'desc' => $dtos[0]['region_name_zh'],
                ];


                $warehouseItemList = [];
                foreach ($dtos as $dto) {
                    if (!isset($warehouseCodeSet[$dto['warehouse_code']])) {
                        continue;
                    }
                    $warehouseItem = [
                        'warehouse_code' => $dto['warehouse_code'],
                        'warehouse_name' => $dto['warehouse_name_zh'],
                        'region_code' => $dto['region_code'],
                        'region_name' => $dto['region_name_zh'],
                        'country' => $dto['site_code'],

                    ];
                    $warehouseItemList[] = $warehouseItem;
                }

                $regionItem['warehouse_list'] = $warehouseItemList;
                $region[] = $regionItem;
            }

        }

        $responseData['region'] = $region;

        return $responseData;
    }
}