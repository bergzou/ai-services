<?php
namespace App\Service;

use App\Client\IcsClient;
use App\Client\OmsOutboundClient;
use App\Client\WmsUserClient;
use App\Enums\EnumReturnedClaimOrder;
use App\Enums\EnumReturnedOrder;
use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Service\BaseService;
use App\Service\CommonService;
use App\Models\ReturnedOrderModel;
use App\Models\ReturnedDetailModel;
use App\Models\ReturnedOperateModel;
use App\Models\ReturnedLogModel;
use App\Models\ReturnedOrderBoxDetailModel;
use App\Models\ReturnedOrderBoxModel;
use App\Libraries\LibSnowflake;
use App\Client\ProductClient;
use App\Models\ReturnedClaimDetailModel;
use App\Models\ReturnedDetailAttachModel;
use App\Service\Queue\QueueDetailConfigService;
use App\Service\Queue\QueueDetailService;
use App\Validates\ReturnedOrderValidation;
use App\Validates\ReturnedDetailValidation;
use App\Validates\ReturnedOrderBoxValidation;
use App\Models\ReturnedClaimOrderModel;
use http\Exception\BadConversionException;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\IFTTTHandler;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;

class ReturnedOrderLogicService extends BaseService
{
    use TraitCommonService;

    public function getDetail($requestData)
    {
        $fields = [
            'returned_order_id', 'returned_order_code', 'tracking_number', 'returned_reference_no', 'manage_name',
            'manage_code', 'returned_sign', 'warehouse_code', 'returned_type', 'handling_method', 'returned_illustrate',
            'outbound_order_code', 'seller_order_code', 'expected_delivery_time', 'claim_order_code', 'returned_status', 'outbound_warehouse_code',
            'outbound_warehouse_name', 'warehouse_name', 'document_type', 'create_type','receiving_at','completion_at','seller_code',
        ];
        $returnedOrder = ReturnedOrderModel::query()->select($fields)->where('returned_order_code', $requestData['returned_order_code'])->first();
        if (empty($returnedOrder)) throw new BusinessException('退件单不存在');
        $returnedOrder = CommonService::convertToArray($returnedOrder);
        $returnedOrder['returned_status_name'] = EnumReturnedOrder::getReturnedStatusMap()[$returnedOrder['returned_status']] ?? '';
        $returnedOrder['returned_type_name'] = EnumReturnedOrder::getReturnedTypeMap()[$returnedOrder['returned_type']] ?? '';
        $returnedOrder['handling_method_name'] = EnumReturnedOrder::getHandlingMethodMap()[$returnedOrder['handling_method']] ?? '';
        $returnedOrder['create_type_name'] = EnumReturnedOrder::getCreateTypeMap()[$returnedOrder['create_type']] ?? '';
        $returnedOrder['document_type_name'] = '无';
        if ($returnedOrder['returned_sign'] == EnumReturnedOrder::RETURNED_SIGN_1){
            $returnedOrder['document_type_name'] = EnumReturnedOrder::getDocumentTypeMap()[$returnedOrder['document_type']] ?? '';
        }



        $warehouseMap = $this->getListWarehouseNameMap([$returnedOrder]);
        $this->setRowWarehouseName($returnedOrder, $warehouseMap);

        $fields = [
            'returned_detail_id', 'sku', 'customer_sku', 'forecast_quantity', 'receive_quantity', 'receive_defective_quantity', 'putaway_quantity',
        ];

        $returnedOrderDetail = ReturnedDetailModel::query($fields)->where('returned_order_code', $returnedOrder['returned_order_code'])->get()->toArray();
        if (!empty($returnedOrderDetail)) $returnedOrderDetail = CommonService::convertToArray($returnedOrderDetail);

        $attach = ReturnedDetailAttachModel::query()->where('returned_order_code', $returnedOrder['returned_order_code'])->get()->toArray();
        $returnedOrder['attach_list'] = $attach;
        $attachData = [];
        if (!empty($attach)) {
            $attach = CommonService::convertToArray($attach);
            foreach ($attach as $key => &$value) {
                $attachData[$value['sku']][] = $value;
            }
        }

        if (!empty($returnedOrderDetail)) {

            $productParams = [
                'sku' => array_column($returnedOrderDetail,'sku'),
                'tenant_code' => 'xhs',
            ];
            $productClient = (new ProductClient())->getProductAndMapInfoV2($productParams);
            $productMap = $productClient['data'] ?? [];
            if (!empty($productMap)) $productMap = array_column($productMap,'seller_sku','sku');

            foreach ($returnedOrderDetail as $key => &$value) {
                $value = $this->setProductMap($value, 'sku', $productClient['data'] ?? []);
                $value['receiv_good_quantity'] = $value['receive_quantity'] - $value['receive_defective_quantity'];
                $value['attach_list'] = $attachData[$value['sku']] ?? [];
                $value['seller_sku'] = $productMap[$value['sku']] ?? '';
                $value['seller_sku'] = CommonService::conversionSellerSku($value['seller_sku']);
                $value['new_seller_sku'] = $value['seller_sku'];
            }
        }

        $returnedOrder['detail_list'] = $returnedOrderDetail;


        $boxDetail = ReturnedOrderBoxDetailModel::query()->select()->where('returned_order_code', $returnedOrder['returned_order_code'])->get()->toArray();
        $boxDetailData = [];
        if (!empty($boxDetail)) {
            $boxDetail = CommonService::convertToArray($boxDetail);

            $productParams = [
                'sku' => array_column($boxDetail,'sku'),
                'tenant_code' => 'xhs',
            ];
            $productClient = (new ProductClient())->getProductAndMapInfoV2($productParams);
            $productMap = $productClient['data'] ?? [];
            if (!empty($productMap)) $productMap = array_column($productMap,'seller_sku','sku');


            foreach ($boxDetail as $key => &$value) {
                $value = $this->setProductMap($value, 'sku', $productClient['data'] ?? []);
                $value['returned_quantity'] = $value['packing_quantity'];
                $boxDetailData[$value['returned_box_id']][] = $value;

                $value['seller_sku'] = $productMap[$value['sku']] ?? '';
                $value['seller_sku'] = CommonService::conversionSellerSku($value['seller_sku']);
            }
        }
        $boxs = ReturnedOrderBoxModel::query()->where('returned_order_code', $returnedOrder['returned_order_code'])->get()->toArray();
        if (!empty($boxs)) {
            $boxs = CommonService::convertToArray($boxs);
            $attach = ReturnedDetailAttachModel::query()->where('returned_order_code', $returnedOrder['returned_order_code'])->where('box_code', array_column($boxs, 'box_code'))->get()->toArray();
            $attachData = [];
            if (!empty($attach)) {
                $attach = CommonService::convertToArray($attach);
                foreach ($attach as $key => &$value) {
                    $attachData[$value['box_code']][] = $value;
                }
            }
            foreach ($boxs as $key => &$value) {
                $value['box_detail'] = $boxDetailData[$value['returned_box_id']] ?? [];
                $picList = $attachData[$value['box_code']] ?? [];
                $value['pic_list'] = [];
                if (!empty($picList)) {
                    $value['pic_list'] = array_column($picList, 'attach_url');
                }

            }
        }
        $returnedOrder['box_list'] = $boxs;

        return $returnedOrder;
    }


    /**
     * @throws BusinessException
     */
    public function pushOverseasWarehouse($operateType, $returnedOrderCode, $operateUserId = '', $operateUserName = '')
    {

        $pushData = [];
        $returnedOrder = ReturnedOrderModel::query()->where('returned_order_code', $returnedOrderCode)->first();
        if (empty($returnedOrder)) throw new BusinessException('退件单不存在');
        $returnedOrder = CommonService::convertToArray($returnedOrder);
        $returnedOrder['return_sign'] = $returnedOrder['returned_sign'];
        $returnedOrder['return_illustrate'] = $returnedOrder['returned_illustrate'];

        switch ($operateType) {
            case EnumReturnedOrder::WAREHOUSE_OPERATE_TYPE_200:

                $pushData = $returnedOrder;

                $fields = [
                    'customer_sku', 'destruction_quantity', 'forecast_quantity', 'id', 'identification_mark', 'returned_detail_id', 'returned_order_code', 'seller_code', 'sku'
                ];
                $returnedDetail = ReturnedDetailModel::query()->select($fields)->where('returned_order_code', $returnedOrder['returned_order_code'])->get()->toArray();
                if (!empty($returnedDetail)) {
                    $returnedDetail = CommonService::convertToArray($returnedDetail);
                    foreach ($returnedDetail as $key => &$value) {
                        $img_list = ReturnedDetailAttachModel::query()->where('returned_order_code', $returnedOrder['returned_order_code'])->where('sku',$value['sku'])->get()->toArray();
                        $value['img_list'] = $img_list;
                    }
                }
                $pushData['order_detail'] = $returnedDetail;

                break;
            case EnumReturnedOrder::WAREHOUSE_OPERATE_TYPE_240:

                $pushData['region_code'] = $returnedOrder['region_code'];
                $pushData['returned_order_code'] = $returnedOrder['returned_order_code'];
                $pushData['returned_status'] = EnumReturnedOrder::RETURNED_STATUS_60;
                $pushData['updator_name'] = $returnedOrder['updator_name'];
                $pushData['updator_uid'] = $returnedOrder['updator_uid'];

                break;
            case EnumReturnedOrder::WAREHOUSE_OPERATE_TYPE_260:

                $pushData = $returnedOrder;
                $pushData['attach'] = [];

                $returnedOrderBoxDetail = ReturnedOrderBoxDetailModel::query()->select()->where('returned_order_code', $returnedOrder['returned_order_code'])->get()->toArray();
                $returnedOrderBoxDetailData = [];
                if (!empty($returnedOrderBoxDetail)) {
                    $returnedOrderBoxDetail = CommonService::convertToArray($returnedOrderBoxDetail);
                    foreach ($returnedOrderBoxDetail as $value){
                        $returnedOrderBoxDetailData[$value['box_code']][] = $value;
                    }

                }
                $fields = [
                    'box_code', 'outer_box_height', 'outer_box_length', 'outer_box_width', 'outer_box_weight',
                    'tracking_number', 'remarks'
                ];

                $orderBox = ReturnedOrderBoxModel::query()->select($fields)->where('returned_order_code', $returnedOrder['returned_order_code'])->get()->toArray();
                if (!empty($orderBox)) {
                    $orderBox = CommonService::convertToArray($orderBox);
                    foreach ($orderBox as $key => &$value) {
                        $value['box_detail'] = $returnedOrderBoxDetailData[$value['box_code']] ?? [];
                    }
                }
                $pushData['box'] = $orderBox;

                break;
            case EnumReturnedOrder::WAREHOUSE_OPERATE_TYPE_310:

                $pushData['returned_order_code'] = $returnedOrder['returned_order_code'];
                $pushData['warehouse_code'] = $returnedOrder['warehouse_code'];
                $pushData['updator_uid'] = $returnedOrder['updator_uid'];
                $pushData['updator_name'] = $returnedOrder['updator_name'];
                $pushData['returned_status'] = $returnedOrder['tracking_number'];
                $pushData['tenant_code'] = $returnedOrder['tenant_code'];
                $pushData['seller_code'] = $returnedOrder['seller_code'];

                $pushData['region_code'] = $returnedOrder['region_code'];
                $pushData['receiving_at'] = $returnedOrder['receiving_at'];
                $pushData['manage_name'] = $returnedOrder['manage_name'];
                $pushData['manage_code'] = $returnedOrder['manage_code'];
                $pushData['id'] = $returnedOrder['id'];
                $pushData['handling_method'] = $returnedOrder['handling_method'];
                if (empty($returnedOrder['claim_order_code'])) throw new BusinessException('认领单号不能为空');
                $returnedClaimOrder = ReturnedClaimOrderModel::query()->select()->where('claim_order_code', $returnedOrder['claim_order_code'])->first();
                if (empty($returnedClaimOrder)) throw new BusinessException('认领单号不存在');
                $returnedClaimOrder = CommonService::convertToArray($returnedClaimOrder);
                $pushData['claim_at'] = $returnedClaimOrder['claim_at'];
                $pushData['claim_order_code'] = $returnedClaimOrder['claim_order_code'];
                $pushData['claim_order_id'] = $returnedClaimOrder['claim_order_id'];
                $pushData['claim_status'] = $returnedClaimOrder['claim_status'];
                $pushData['claim_type'] = $returnedClaimOrder['claim_type'];
                $pushData['returned_desc'] = $returnedClaimOrder['returned_desc'];

                $fields = [
                    'claim_order_code', 'customer_sku', 'identification_mark', 'new_customer_sku', 'new_sku',
                    'receive_defective_quantity', 'receive_quantity', 'sku','seller_sku'
                ];
                $returnedClaimDetail = ReturnedClaimDetailModel::query()->select($fields)->where('claim_order_code', $returnedOrder['claim_order_code'])->get()->toArray();
                if (!empty($returnedDetail)) {
                    $returnedClaimDetail = CommonService::convertToArray($returnedClaimDetail);
                    foreach ($returnedClaimDetail as $key => &$value) {
                        $img_list = [];
                        $value['img_list'] = $img_list;
                    }
                }
                $pushData['order_detail'] = $returnedClaimDetail;
                break;

            case EnumReturnedOrder::WAREHOUSE_OPERATE_TYPE_320:

                break;

        }


        $syncData = QueueDetailConfigService::pushReturnedOrderToWmsReturned($pushData, $returnedOrder['region_code'], $operateType);
        QueueDetailService::writeLocalQueueTask($syncData, '', false, false);

    }

    /**
     * @throws BusinessException
     */
    public function pushOverseasPlatform($returnedOrderCode){
        $pushData = ['returned_order_code' => $returnedOrderCode];
        $returnedOrder = DB::table('returned_order')->where('returned_order_code',$returnedOrderCode)->first();
        if (!empty($returnedOrder) && $returnedOrder->create_type = EnumReturnedOrder::CREATE_TYPE_3){
            $syncData = QueueDetailConfigService::pushReturnedOrderPassToPlatform($pushData);
            QueueDetailService::writeLocalQueueTask($syncData, '', false, false);
        }
    }

    /**
     *  检查参数
     * @throws BusinessException
     */
    public function checkParams($requestData)
    {

        $returnedOrderValidation = new ReturnedOrderValidation($requestData, 'add');
        $messages = $returnedOrderValidation->isRunFail();
        if (!empty($messages)) throw new BusinessException($messages);

        $outboundOrder = [];
        if ($requestData['returned_sign'] == EnumReturnedOrder::RETURNED_SIGN_1) {

            if (empty($requestData['outbound_order_code'])) throw new BusinessException('芯宏发货退件出库单号必填');

            $omsOutboundData = (new OmsOutboundClient())->getOutboundOrder(['outbound_order_code' => $requestData['outbound_order_code']]);
            $outboundOrder = $omsOutboundData['data'] ?? [];
            if (empty($outboundOrder)) throw new BusinessException('出库单不存在');

            switch ($requestData['handling_method']) {

                case EnumReturnedOrder::HANDLING_METHOD_1:
                case EnumReturnedOrder::HANDLING_METHOD_2:


                    if ($outboundOrder['document_type'] == EnumReturnedOrder::DOCUMENT_TYPE_2) {
                        if (empty($requestData['boxs'])) throw new BusinessException('箱子信息必填');
                        foreach ($requestData['boxs'] as $key => &$value) {

                            $returnedOrderBoxValidation = new ReturnedOrderBoxValidation($value, 'add');
                            if ($returnedOrderBoxValidation->isRunFail()) throw new BusinessException($returnedOrderBoxValidation->isRunFail());

                            foreach ($value['box_detail'] as $k => $v) {

                                $detailData[] = [
                                    'sku' => $v['sku'],
                                    'customer_sku' => $v['customer_sku'],
                                    'sku_weight' => $v['sku_weight'],
                                    'sku_length' => $v['sku_length'],
                                    'sku_width' => $v['sku_width'],
                                    'sku_height' => $v['sku_height'],
                                    'returned_quantity' => $v['returned_quantity'],
                                    'forecast_quantity' => $v['forecast_quantity'],
                                ];
                            }
                        }
                        $detailData = CommonService::mergeArrayByKey($detailData);
                        $requestData['detail'] = $detailData;

                    } else {

                        $realQtyData = array_column($outboundOrder['detail'], 'shipment_quantity', 'sku');
                        if (empty($requestData['detail'])) throw new BusinessException('退件单详情必填');
                        foreach ($requestData['detail'] as $key => &$value) {
                            $returnedDetailValidation = new ReturnedDetailValidation($value, 'add');
                            if ($returnedDetailValidation->isRunFail()) throw new BusinessException($returnedDetailValidation->isRunFail());

                            $forecastQuantity = ReturnedOrderModel::query()->select(DB::raw("SUM( forecast_quantity ) as forecast_quantity"))
                                ->where('outbound_order_code', $outboundOrder['outbound_order_code'])
                                ->where('sku', $value['sku'])
                                ->where('returned_status','<>',EnumReturnedOrder::RETURNED_STATUS_60)
                                ->leftJoin('returned_detail', 'returned_detail.returned_order_code', '=', 'returned_order.returned_order_code')->first();
                            $forecastQuantity = CommonService::convertToArray($forecastQuantity);
                            $forecastQuantity = $forecastQuantity['forecast_quantity'] ?? 0;
                            $realQty = $realQtyData[$value['sku']] ?? 0;
                            if (($realQty - $forecastQuantity) < $value['forecast_quantity']) throw new BusinessException('该出库单的SKU可退数量不足' . $value['customer_sku'] . ':' . ($realQty - $forecastQuantity));

                        }
                    }
                    break;
                case EnumReturnedOrder::HANDLING_METHOD_3:


                    if (empty($requestData['boxs'])) throw new BusinessException('箱子信息必填');
                    foreach ($requestData['boxs'] as $key => &$value) {



                        $returnedOrderBox = ReturnedOrderModel::query()->select(['box_code'])
                            ->where('outbound_order_code', $outboundOrder['outbound_order_code'])
                            ->where('box_code', 'RT'.$value['box_code'])
                            ->where('returned_status','<>',EnumReturnedOrder::RETURNED_STATUS_60)
                            ->leftJoin('returned_order_box', 'returned_order_box.returned_order_code', '=', 'returned_order.returned_order_code')->first();

                        if (!empty($returnedOrderBox)) throw new BusinessException('该出库单的箱子已退' . $value['box_code']);
                        $returnedOrderBoxValidation = new ReturnedOrderBoxValidation($value, 'add');
                        if ($returnedOrderBoxValidation->isRunFail()) throw new BusinessException($returnedOrderBoxValidation->isRunFail());

                        $value['box_code'] = 'RT'.$value['box_code'];

                    }

                    break;
                default:
                    throw new BusinessException('处理方式错误');
            }
        } else if ($requestData['returned_sign'] == EnumReturnedOrder::RETURNED_SIGN_2) {

            if (empty($requestData['detail'])) throw new BusinessException('退件单详情必填');
            foreach ($requestData['detail'] as $key => &$value) {

                $value['returned_quantity'] = $value['returned_quantity'] ?? 0 ;
                if (empty($value['returned_quantity'])) $value['returned_quantity']  = $value['forecast_quantity'] ?? 0;
                $value['actual_received_quantity'] =  $value['returned_quantity'];


                $returnedDetailValidation = new ReturnedDetailValidation($value, 'add');
                if ($returnedDetailValidation->isRunFail()) throw new BusinessException($returnedDetailValidation->isRunFail());
            }

        } else {
            throw new BusinessException('退件标识错误');
        }


        $requestData['region_code'] = $outboundOrder['region_code'] ?? '';
        $icsClient = (new IcsClient())->getWarehouseCode(['warehouse_code' => [$requestData['warehouse_code']]]);
        $warehouseData = $icsClient['data']['list'] ?? [];
        if (empty($warehouseData)) throw new BusinessException('仓库不存在');
        $warehouseData = array_column($warehouseData, 'region_code', 'warehouse_code');
        $requestData['region_code'] = $warehouseData[$requestData['warehouse_code']] ?? '';


        return array($outboundOrder, $requestData);
    }


    /**
     * @throws BusinessException
     * @throws \Exception
     */
    public function addReturnedOrder($requestData, $outboundOrder, $documentType, $userInfo): array
    {

        $snowflake = new LibSnowflake(Common::getWorkerId());
        $returnedOrderCode = $requestData['returned_order_code'] ?? '';
        if (empty($returnedOrderCode)){
            $returnedOrderCode = $this->getReturnedOrderCode($requestData['seller_code']);
            if (empty($returnedOrderCode)) throw new BusinessException('退件单号获取失败');
        }


        $returnedOrder = [
            'returned_order_id' => $snowflake->next(),
            'returned_order_code' => $returnedOrderCode,
            'tracking_number' => $requestData['tracking_number'] ?? '',
            'returned_reference_no' => $requestData['returned_reference_no'] ?? '',
            'manage_name' => $requestData['manage_name'],
            'manage_code' => $requestData['manage_code'],
            'returned_sign' => $requestData['returned_sign'],
            'warehouse_code' => $requestData['warehouse_code'],
            'returned_type' => $requestData['returned_type'],
            'handling_method' => $requestData['handling_method'],
            'returned_illustrate' => $requestData['returned_illustrate'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'submit_at' => date('Y-m-d H:i:s'),
            'receiving_at' => $requestData['receiving_at'] ?? '',
            'outbound_order_code' => $requestData['outbound_order_code'] ?? '',
            'seller_order_code' => $requestData['seller_order_code'] ?? '',
            'expected_delivery_time' => $requestData['expected_delivery_time'] ?? '',
            'region_code' => $requestData['region_code'],
            'seller_code' => $requestData['seller_code'],
            'tenant_code' => $userInfo['tenant_code'],
            'claim_order_code' => $requestData['claim_order_code'] ?? '',
            'returned_status' => $requestData['returned_status'],
            'outbound_warehouse_code' => $requestData['outbound_warehouse_code'] ?? '',
            'outbound_warehouse_name' => $requestData['outbound_warehouse_name'] ?? '',
            'document_type' => $documentType,
            'create_type' => $requestData['create_type'] ?? EnumReturnedOrder::CREATE_TYPE_1,
            'updator_uid' => $userInfo['user_id'],
            'updator_name' => $userInfo['user_name'],
        ];
        $result = ReturnedOrderModel::query()->insert($returnedOrder);
        if (empty($result)) throw new BusinessException('退件单创建失败');

        return $returnedOrder;
    }


    /**
     * @throws BusinessException
     */
    public function updateReturnedOrder($requestData, $outboundOrder, $documentType, $userInfo): array
    {

        $returnedOrder = ReturnedOrderModel::query()->where('returned_order_code',$requestData['returned_order_code'])->first();
        $returnedOrder = CommonService::convertToArray($returnedOrder);

        $returnedOrderUpdateData = [
            'tracking_number' => $requestData['tracking_number'],
            'returned_reference_no' => $requestData['returned_reference_no'] ?? '',
            'manage_name' => $requestData['manage_name'],
            'manage_code' => $requestData['manage_code'],
            'returned_sign' => $requestData['returned_sign'],
            'warehouse_code' => $requestData['warehouse_code'],
            'returned_type' => $requestData['returned_type'],
            'handling_method' => $requestData['handling_method'],
            'returned_illustrate' => $requestData['returned_illustrate'] ?? '',
            'submit_at' => date('Y-m-d H:i:s'),
            'receiving_at' => $requestData['receiving_at'] ?? '',
            'outbound_order_code' => $requestData['outbound_order_code'] ?? '',
            'seller_order_code' => $requestData['seller_order_code'] ?? '',
            'expected_delivery_time' => $requestData['expected_delivery_time'] ?? '',
            'returned_status' => $requestData['returned_status'],
            'outbound_warehouse_code' => $requestData['outbound_warehouse_code'] ?? '',
            'outbound_warehouse_name' => $requestData['outbound_warehouse_name'] ?? '',
            'document_type' => $documentType,
            'create_type' => $requestData['create_type'] ?? EnumReturnedOrder::CREATE_TYPE_1,
            'updator_uid' => $userInfo['user_id'] ?? '',
            'updator_name' => $userInfo['user_name'] ?? '',
        ];
        $result = DB::table('returned_order')->where('returned_order_code',$requestData['returned_order_code'])->update($returnedOrderUpdateData);
        if (empty($result)) throw new BusinessException('退件单更新');

        return $returnedOrder;
    }


    /**
     * @throws BusinessException
     */
    public function addReturnedOrderBox($returnedOrder, $outboundOrder, $requestData): array
    {

        $snowflake = new LibSnowflake(Common::getWorkerId());
        $orderBoxData = $orderBoxDetailData = $orderDetail = [];

        foreach ($requestData['boxs'] as $key => $value) {

            $skuTypes = 0;
            $skuPieces = 0;
            $returnedBoxId = $snowflake->next();
            foreach ($value['box_detail'] as $key => $vv) {

                $orderBoxDetailData[] = [
                    'returned_order_code' => $returnedOrder['returned_order_code'],
                    'returned_order_box_detail_id' => $snowflake->next(),
                    'sku' => $vv['sku'],
                    'customer_sku' => $vv['customer_sku'],
                    'sku_weight' => $vv['sku_weight'],
                    'sku_length' => $vv['sku_length'],
                    'sku_width' => $vv['sku_width'],
                    'sku_height' => $vv['sku_height'],
                    'shipment_quantity' => $vv['forecast_quantity'] ??$vv['returned_quantity'] ?? 0,
                    'box_code' => $value['box_code'],
                    'returned_box_id' => $returnedBoxId,
                    'seller_code' => $requestData['seller_code'],
                    'packing_quantity' =>  $vv['forecast_quantity'] ??$vv['returned_quantity'] ?? 0,
                ];
                $skuTypes++;
                $skuPieces +=  $vv['forecast_quantity'] ?? $vv['returned_quantity'] ?? 0;
                $orderDetail[] = [
                    'returned_detail_id' => $snowflake->next(),
                    'sku' => $vv['sku'],
                    'customer_sku' => $vv['customer_sku'],
                    'forecast_quantity' =>  $vv['forecast_quantity'] ??$vv['returned_quantity'] ?? 0,
                    'returned_order_code' => $returnedOrder['returned_order_code'],
                    'seller_code' => $requestData['seller_code'],
                ];

            }


            $orderBoxData[] = [
                'returned_box_id' => $returnedBoxId,
                'returned_order_code' => $returnedOrder['returned_order_code'],
                'box_code' => $value['box_code'],
                'tracking_number' => $requestData['tracking_number'] ?? '',
                'outer_box_length' => $value['outer_box_length'],
                'outer_box_width' => $value['outer_box_width'],
                'outer_box_height' => $value['outer_box_height'],
                'outer_box_weight' => $value['outer_box_weight'],
                'sku_types' => $skuTypes,
                'sku_pieces' => $skuPieces,
                'actual_outer_box_length' => $value['actual_outer_box_length'] ?? '',
                'actual_outer_box_width' => $value['actual_outer_box_width'] ?? '',
                'actual_outer_box_height' => $value['actual_outer_box_height'] ?? '',
                'actual_outer_box_weight' => $value['actual_outer_box_weight'] ?? '',
                'remarks' => $value['remarks'] ?? '',
            ];

        }

        $result = ReturnedOrderBoxModel::query()->insert($orderBoxData);
        if (empty($result)) throw new BusinessException('退件单创建失败-箱子信息');

        $result = ReturnedOrderBoxDetailModel::query()->insert($orderBoxDetailData);
        if (empty($result)) throw new BusinessException('退件单创建失败-箱子明细信息');

        $result = ReturnedDetailModel::query()->insert($orderDetail);
        if (empty($result)) throw new BusinessException('退件单创建失败-退件明细信息');


        return $orderBoxDetailData;
    }

    public function addReturnedOrderDetail($returnedOrder, $outboundOrder, $requestData)
    {
        $snowflake = new LibSnowflake(Common::getWorkerId());
        $orderDetail = [];

        foreach ($requestData['detail'] as $key => $value) {
            $orderDetail[] = [
                'returned_detail_id' => $snowflake->next(),
                'sku' => $value['sku'],
                'customer_sku' => $value['customer_sku'],
                'actual_received_quantity' => $value['actual_received_quantity'] ??  $value['returned_quantity'] ?? 0,
                'returned_quantity' => $value['returned_quantity'] ?? 0,
                'forecast_quantity' => $value['forecast_quantity'] ??$value['returned_quantity'] ?? 0,
                'receive_quantity' => $value['receive_quantity'] ?? 0,
                'receive_defective_quantity' => $value['receive_defective_quantity'] ?? 0,
                'returned_order_code' => $returnedOrder['returned_order_code'],
                'seller_code' => $requestData['seller_code'],
            ];
        }
        $result = ReturnedDetailModel::query()->insert($orderDetail);
        if (empty($result)) throw new BusinessException('退件单创建失败-退件明细信息');

        return $orderDetail;


    }

    public function addReturnedOrderLog($returnedOrder, $userInfo, $content)
    {
        $snowflake = new LibSnowflake(Common::getWorkerId());


        $orderLog = [
            'returned_order_code' => $returnedOrder['returned_order_code'],
            'content' => $content,
            'opeator_name' => $userInfo['user_name'],
            'opeator_uid' => $userInfo['user_id'],
            'operation_at' => date('Y-m-d H:i:s'),
            'returned_log_id' => $snowflake->next(),
            'log_type' => 1,
            'seller_code' => $returnedOrder['seller_code'],
        ];

        $result = ReturnedLogModel::query()->insert($orderLog);
        if (empty($result)) throw new BusinessException('退件单创建失败-退件日志信息');
        return [];

    }

    public function addReturnedOrderOperate($returnedOrder, $userInfo)
    {

        $contentNum = [];
        switch ($returnedOrder['returned_status']) {
            case EnumReturnedOrder::RETURNED_STATUS_10:
                $contentNum  = [EnumReturnedOrder::RETURNED_STATUS_10]; 
                break;
            case EnumReturnedOrder::RETURNED_STATUS_20:
                $contentNum  = [EnumReturnedOrder::RETURNED_STATUS_10,EnumReturnedOrder::RETURNED_STATUS_20]; 
                break;
            case EnumReturnedOrder::RETURNED_STATUS_30:
                $contentNum  = [EnumReturnedOrder::RETURNED_STATUS_10,EnumReturnedOrder::RETURNED_STATUS_20,EnumReturnedOrder::RETURNED_STATUS_30]; 
                break;
            case EnumReturnedOrder::RETURNED_STATUS_40:
                $contentNum  = [EnumReturnedOrder::RETURNED_STATUS_10,EnumReturnedOrder::RETURNED_STATUS_20,EnumReturnedOrder::RETURNED_STATUS_30,EnumReturnedOrder::RETURNED_STATUS_40]; 
                break;
            case EnumReturnedOrder::RETURNED_STATUS_50:
                $contentNum  = [EnumReturnedOrder::RETURNED_STATUS_10,EnumReturnedOrder::RETURNED_STATUS_20,EnumReturnedOrder::RETURNED_STATUS_30,EnumReturnedOrder::RETURNED_STATUS_40,EnumReturnedOrder::RETURNED_STATUS_50]; 
                break;
            default:
                throw new BusinessException('退件状态错误') ;
        };

        foreach ($contentNum as $key => $value) {
            $operate = ReturnedOperateModel::query()->select('id')->where('returned_order_code', $returnedOrder['returned_order_code'])
                                ->where('content_num',$value)->first(); 
            if (empty($operate)){
                $orderOperate = [
                    'returned_order_code' => $returnedOrder['returned_order_code'],
                    'content' => EnumReturnedOrder::getReturnedStatusMap()[$value] ?? '',
                    'operate_name' => $userInfo['user_name'],
                    'operate_at' => date('Y-m-d H:i:s'),
                    'content_num' => $returnedOrder['returned_status'],
                ];
                $result = ReturnedOperateModel::query()->insert($orderOperate);
                if (empty($result)) throw new BusinessException('退件单创建失败-退件操作信息');
            }                   
        }
      
        return [];
    }


    /**
     * 添加附件
     * @throws BusinessException
     */
    public function addReturnedOrderAttach($returnedOrder,$attach){
        
        $snowflake = new LibSnowflake(Common::getWorkerId());
        $attachData = [];
        foreach ($attach as $key => $value) {
            $attachData[] = [
                'returned_attach_detail_id' => $snowflake->next(),
                'attach_url' => $value['attach_url'],
                'attach_name' => $value['attach_name'],
                'sku' => $value['sku'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'returned_order_code' => $returnedOrder['returned_order_code'],
                'claim_order_code' => $returnedOrder['claim_order_code']?? '',
                'box_code' => $value['box_code']?? '',

            ]; 
        }
        $result = ReturnedDetailAttachModel::query()->insert($attachData);
        if (empty($result)) throw new BusinessException('退件单创建失败-退件附件信息');
        return [];
    }

    /**
     * @param $returnedOrder
     * @throws BusinessException
     */
    public function addTransit($returnedOrder,$orderDetail) {

        // 增加在途数量
        $transitData = [
            'associated_order_number' => $returnedOrder['returned_order_code'],
            'document_type' => 3,
            'manage_code' => $returnedOrder['manage_code'],
            'manage_name' => $returnedOrder['manage_name'] ?? '',
            'operation_type' => 17,
            'region_code' => $returnedOrder['region_code'],
            'warehouse_code' => $returnedOrder['warehouse_code'],
            'seller_code' => $returnedOrder['seller_code'],
            'tenant_code' => $returnedOrder['tenant_code'],
        ];

        $productDetail = [];
        switch ($returnedOrder['document_type']) {
            case EnumReturnedOrder::DOCUMENT_TYPE_1:  // 代发退件单
                if (empty($orderDetail)) throw new \Exception('添加在途-详情不能为空');
                foreach ($orderDetail as $key => $value) {
                    $productDetail[] = [
                        'box_code' => '',
                        'customer_sku' => $value['customer_sku'],
                        'provider_box_code' => '',
                        'quantity' => $value['forecast_quantity'],
                        'reference_code' => '',
                        'seller_sku_set' => '',
                        'shipping_method' => 1,
                        'sku' => $value['sku'],
                    ];
                }

                break;
            case    EnumReturnedOrder::DOCUMENT_TYPE_2:  // 整箱中转退件单
                if (empty($orderDetail)) throw new \Exception('添加在途-箱详情不能为空');
                foreach ($orderDetail as $key => $value) {
                    $productDetail[] = [
                        'box_code' => $value['box_code'],
                        'customer_sku' => $value['customer_sku'],
                        'provider_box_code' => '',
                        'quantity' => $value['shipment_quantity'] ?? $value['packing_quantity'] ?? 0,
                        'reference_code' => '',
                        'seller_sku_set' => '',
                        'shipping_method' => 2,
                        'sku' => $value['sku'],
                    ];
                }
                break;
        }
        $transitData['product_detail'] = $productDetail;
        $syncData = QueueDetailConfigService::pushReturnedOrderPassToIcs($transitData);
        QueueDetailService::writeLocalQueueTask($syncData);
        return [];
    }

    /**
     * 
     */
    public function addReturnedClaimOrder($requestData,$userInfo){

        $libSnowflake = new LibSnowflake(Common::getWorkerId());

        $returnedClaimOrder['claim_order_code'] = $requestData['claim_order_code'];
        $returnedClaimOrder['tracking_number'] = $requestData['tracking_number'];
        $returnedClaimOrder['returned_desc'] = $requestData['returned_desc'] ?? "";
        $returnedClaimOrder['seller_code'] = $requestData['seller_code'] ?? '';
        $returnedClaimOrder['claim_status'] = EnumReturnedClaimOrder::CLAIM_STATUS_PENDING;
        $returnedClaimOrder['claim_type'] = EnumReturnedClaimOrder::CLAIM_TYPE_UNKNOWN;
        $returnedClaimOrder['warehouse_code'] = $requestData['warehouse_code'];
        $returnedClaimOrder['region_code'] = $requestData['region_code'];
        $returnedClaimOrder['created_at'] = date('Y-m-d H:i:s');
        $returnedClaimOrder['receiving_at'] = date("Y-m-d H:i:s");
        $returnedClaimOrder['tenant_code'] = $requestData['tenant_code'];
        $returnedClaimOrder['claim_order_id'] = $libSnowflake->next();

        
        $result = ReturnedClaimOrderModel::query()->insert($returnedClaimOrder);
        if (empty($result)) throw new BusinessException('退件单创建失败-认领单信息');
    
        return $returnedClaimOrder;
    }




    public function addReturnedClaimOrderDetail( $returnedClaimOrder ,$requestData ,$userInfo)
    {


        $libSnowflake = new LibSnowflake(Common::getWorkerId());
        $returnedClaimDetail = [];
        $returnedAttach = [];
        if (empty($requestData['detail_list'])) throw new BusinessException('退件单创建失败-认领单详情信息');
        foreach ($requestData['detail_list'] as $key => $value) {
            $returnedClaimDetail[] = [
                'sku' => $value['sku'],
                'customer_sku' => $value['customer_sku'] ?? '',
                'receive_quantity' => $value['receive_quantity'],
                'receive_defective_quantity' => $value['receive_defective_quantity'],
                'claim_order_code' => $returnedClaimOrder['claim_order_code'],
                'identification_mark' => $value['identification_mark'],
                'created_at' => date('Y-m-d H:i:s'),
                'seller_sku' => $value['seller_sku'] ?? '',
            ];

            if (!empty($value['img_list'])) {
                foreach ($value['img_list'] as $k => $v) {
                    $returnedAttach[] = [
                        'returned_attach_detail_id' => $libSnowflake->next(),
                        'attach_url' => $v['attach_url'],
                        'attach_name' => $v['attach_name'],
                        'sku' => $value['sku'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'claim_order_code' => $returnedClaimOrder['claim_order_code'],
                    ];
                }
            }
        }

        if (!empty($returnedClaimDetail)) {
            $result = ReturnedClaimDetailModel::query()->insert($returnedClaimDetail);
            if (empty($result)) throw new BusinessException('退件单创建失败-认领单详情信息');
        }

        if (!empty($returnedAttach)) {
            $result = ReturnedDetailAttachModel::query()->insert($returnedAttach);
            if (empty($result)) throw new BusinessException('退件单创建失败-认领单详情信息');
        }

        return $returnedClaimDetail;
    }
}