<?php

namespace App\Services;

use App\Client\IcsClient;
use App\Client\IcsWmsClient;
use App\Client\ProductClient;
use App\Client\WarehouseBaseClient;
use App\Client\WmsUserClient;
use App\Exceptions\BusinessException;
use App\Libraries\Common;


trait TraitCommonService
{

    //获取仓库code对应的仓库信息
    public function getListWarehouseNameMap($list): array
    {

        $warehouseCodeArr = array_column($list, 'warehouse_code');
        $warehouseCodeArr = array_filter(array_unique($warehouseCodeArr));

        $warehouseMapData = [];
        if ($warehouseCodeArr) {
            $warehouseInfoRes = (new IcsWmsClient())->getWarehouses(['warehouse_code' => $warehouseCodeArr]);
            $warehouseMapData = $warehouseInfoRes['data'] ?? [];
            $warehouseMapData = array_column($warehouseMapData, null, 'warehouse_code');
        }
        return $warehouseMapData;
    }

    //组装仓库名称
    public function setRowWarehouseName(&$row, $warehouseInfo)
    {

        //兼容一维和二维
        if (!isset($warehouseInfo['warehouse_name_zh']) && isset($row['warehouse_code'])) {
            $warehouseInfo = $warehouseInfo[$row['warehouse_code']] ?? [];
        }
        $row['warehouse_name'] = $row['warehouse_code_name'] = empty($warehouseInfo) ? '' : ($warehouseInfo['warehouse_name_zh'] . '(' . $warehouseInfo['warehouse_code'] . ')');

    }

    //组装仓库名称 - list
    public function setListRowWarehouseName($row, $warehouseListInfo)
    {

        $warehouse_code_name = '';
        if (isset($row['warehouse_code'])) {
            $warehouse_code      = $row['warehouse_code'];
            $warehouseInfo       = $warehouseListInfo[$warehouse_code] ?? [];
            $warehouse_code_name = empty($warehouseInfo) ? '' : ($warehouseInfo['warehouse_name_zh'] . '(' . $warehouseInfo['warehouse_code'] . ')');
        }
        return $warehouse_code_name;
    }

    public function getSellerSkuSellerCode($list)
    {

        $skuArr = array_unique(
            array_filter(
                explode(',', implode(',', array_column($list, 'sku')))
            )
        );

        $sellerCodeArr = array_unique(
            array_filter(
                explode(',', implode(',', array_column($list, 'seller_code')))
            )
        );
        $resData = [];
        if ($skuArr) {
            $res     = ProductClient::staticModel(true)->getSellerSkuSellerCode(['sku' => $skuArr,'seller_code'=>$sellerCodeArr]);
            $resData = $res['data'] ?? [];
            $resData = array_column($resData, null, 'sku');
        }

        return $resData;
    }


    public function getProductSkuListInfoMap($list,$sku_key='sku')
    {

//        $skuArr = array_unique(array_filter(array_column($list,'sku')));

        $skuArr = array_unique(
            array_filter(
                explode(',', implode(',', array_column($list, $sku_key)))
            )
        );

        $resData = [];
        if ($skuArr) {
            $res     = ProductClient::staticModel(true)->getProductList(['sku' => $skuArr]);
            $resData = $res['data'] ?? [];
            $resData = array_column($resData, null, 'sku');
        }

        return $resData;
    }

    /**
     *
     * @param array $skuPlatformMap [sku => [平台1，平台2]]
     * @return array
     */
    public function getProductSkuListInfoMapV2(array $skuPlatformMap)
    {

        $reqData = [
            'tenant_code' => $this->reqTenantCode,
            'sku_platform_map' => $skuPlatformMap
        ];

        if (empty($this->reqTenantCode) || empty($skuPlatformMap)){
            return [];
        }

        $res     = ProductClient::staticModel(true)->getProductListV2($reqData);
        $resData = $res['data'] ?? [];
        return array_column($resData, null, 'sku');

    }




    //组装产品信息
    public function setProductListInfo(&$row, $SkuMap, $appendFields = ['name_en', 'name_cn', 'main_image'],$sku_key='sku')
    {

        $skuInfo = $SkuMap[$row[$sku_key]] ?? [];

        foreach ($appendFields as $fk => $fv) {
            $apKey = $fv;
            if (is_string($fk)) $apKey = $fk;

            if ($apKey == 'main_image'){
                $main_image = $skuInfo[$fv] ?? '';
                $skuInfo[$fv] = Common::getImageUrl($main_image);
            }

            $row[$apKey] = $skuInfo[$fv] ?? '';
        }
        return $row;
    }

    public function getWarehouseNameMap($list)
    {

        $warehouseCodeArr = array_column($list, 'warehouse_code');
        $warehouseCodeArr = array_filter(array_unique($warehouseCodeArr));

        $warehouseMapData = [];
        if ($warehouseCodeArr) {
            $warehouseInfoRes = WarehouseBaseClient::staticModel(true)->getWarehouseRow(['warehouse_code' => $warehouseCodeArr]);

            $warehouseMapData = $warehouseInfoRes['data'] ?? [];
            $warehouseMapData = array_column($warehouseMapData, null, 'warehouse_code');
        }
        return $warehouseMapData;
    }


    // 获取容器详情并判断容器状态
    public function getPackInfoAndCheckStatus($input){

        if (empty($input['pack_bar_code'])) throw new \RuntimeException(lang('Errors.10710267'));

        //获取容器，判断容器状态
        $packInfoRes = WarehouseBaseClient::staticModel()->getPackWork($input);
        if ($packInfoRes['code'] != 200) throw new \RuntimeException($packInfoRes['msg']);

        $packInfo = $packInfoRes['data'] ?? [];
        if (empty($packInfo)) throw new \RuntimeException(lang('Errors.10710266'));

        //判断容器是否被占用
        if ( $packInfo['work_status'] != 2 ) throw new \RuntimeException(lang('Errors.10710264'));
        if ( $packInfo['pack_status'] != 1 ) throw new \RuntimeException(lang('Errors.10710265'));

        return $packInfo;
    }

    /**
     * 根据卖家代码获取对应详情
     * @param $input
     */
    public function getCustomerCompanyRow($input){

        if (empty($input['seller_code'])) throw new \RuntimeException(lang('Errors.10710111'));

        $res = WarehouseBaseClient::staticModel()->getCustomerCompanyRow($input);

        if ($res['code'] != 200) throw new \RuntimeException(lang('Errors.10710112'));
        if (empty($res['data'])) throw new \RuntimeException(lang('Errors.10710112'));

        return $res['data'];
    }


    //检测库位是否存在
    public function checkWarehousePositionInfo($params){

        $codeArr = $params['code'] ?? [];
        if (empty($codeArr)) throw new \RuntimeException('position code is not empty');
        $list = WarehouseBaseClient::staticModel()->getWarehousePositionCodeInfo($params);

        if ($list['code'] != 200){
            $msg = $list['msg'] ?? ($list['message'] ?? lang('Errors.10000010'));
            throw new \RuntimeException($msg);
        }

        $data =  $list['data'] ?? [];
        if (empty($data)) throw new \RuntimeException(lang('Errors.10500208',[implode(',',$codeArr)]));

        //对比不存在的库位
        $dataCodeArr = array_column($data,'code');
        $diffCode = array_diff($codeArr,$dataCodeArr);
        if ($diffCode) throw new \RuntimeException(lang('Errors.10500208',[implode(',',$diffCode)]));

        return $data;
    }


    //获取容器作业详情
    public function getPackBarWorkInfo($input){

        $pack_info = WarehouseBaseClient::staticModel()->getPackBarWorkInfo($input);
        if ($pack_info['code'] != 200) throw new \RuntimeException($pack_info['msg']);

        $pack_row = $pack_info['data'] ?? [];
        if (empty($pack_row)) throw new \RuntimeException(lang('Errors.10500127'));

        return $pack_row;
    }


    /**
     * @param $row
     * @param $skuKey
     * @param $product
     * @param $skuSeller
     * @param string[] $appendFields
     * @return mixed
     */
    public function setProductMap( &$row, $skuKey ,$product, $appendFields = ['name_en', 'name_cn', 'main_image' ,'seller_sku'] )
    {
        $productData = [];  $row['seller_sku'] = '';

        if (!empty($product)) {
            foreach ($product as $value) {
                $value['seller_sku'] = '';
                if (!empty($value['sku_map'])) $value['seller_sku'] = array_column($value['sku_map'], 'seller_sku');
                switch ($skuKey) {
                    case 'sku':
                        $productData[$value[$skuKey]] = $value;
                        break;
                    case 'new_sku':
                        if ($row['new_sku'] == $value['sku']) {
                            $productData[$row['new_sku']] = $value;
                        }
                        break;
                }

            }
        }

        foreach ($appendFields as $fk => $fv) {
            $apKey = $fv;
            if (is_string($fk)) $apKey = $fk;
            if ($apKey == 'main_image'){
                $main_image = $productData[$row[$skuKey]][$apKey] ?? '';
                $productData[$row[$skuKey]][$apKey] = Common::getImageUrl($main_image);
            }
            $row[$apKey] = $productData[$row[$skuKey]][$apKey] ?? '';
        }


        return $row;
    }

    /**
     * 组装平台仓条码
     * @param $sellerSku
     * @param string $sku
     * @return string
     */
    public function  getSellerSkuSet($sellerSku , $sku = ''){

        $sellerSkuSet = '';
        if (!empty($sellerSku)){

            if (is_array($sellerSku)){
                $sellerSkuSet = implode(',',$sellerSku);
            }else{
                $sellerSkuSet = $sellerSku;
            }
        }
        return $sellerSkuSet;
    }




    /** SKU 组合查询
     * @param $builder
     * @param $table
     * @param $searchCode
     * @param $request
     * @param array $searchArr
     * @return mixed
     */
    public function skuCombinationSearch($builder , $table , $searchCode ,$request ,$searchArr = ['sku','customer_sku','seller_sku']){

        $searchCodeArr = ['空'];
        $addedChangeDetail = [];
        $db = Common::getAdapt();
        $productClient = (new ProductClient());
        foreach ($searchArr as $value){

            if (isset($request[$value]) && $request[$value] != ''){
                if ($value == 'seller_sku'){
                    $productData = $productClient->getSkuBySellerSku(['seller_sku' =>  $request['seller_sku'] , 'tenant_code' => $this->reqTenantCode]);
                    if (isset($productData['code']) && !empty($productData)
                        && $productData['code'] == 200 && !empty($productData['data'])){
                        $addedChangeDetail = $db->table($table)->select([$searchCode])
                            ->whereIn('sku',$productData['data'])
                            ->get()->getResultArray();
                        if ($addedChangeDetail) $searchCodeArr = array_unique(array_column($addedChangeDetail,$searchCode));
                    }
                }else{
                    $addedChangeDetail = $db->table($table)->select([$searchCode])
                        ->where($value,$request[$value])
                        ->get()->getResultArray();
                }
                if ($addedChangeDetail) $searchCodeArr = array_unique(array_column($addedChangeDetail,$searchCode));
                $builder = $builder->whereIn($searchCode , $searchCodeArr);
            }

        }
        return $builder;
    }


    /**
     * @param $sellerCode
     * @return string
     * @throws BusinessException
     */

    public function getReturnedOrderCode($sellerCode): string
    {

        $companyParams = [
            'seller_code_arr' => [$sellerCode],
            'select' => ['short_code','seller_code']
        ];
        $companyAllData = (new WmsUserClient())->getCompanyAll($companyParams);

        $companyAll = $companyAllData['data']['list'] ?? [];
        if (empty($companyAll)) throw new BusinessException("获取卖家简码失败");
        $companyMap = array_column($companyAll,'short_code','seller_code');
        $shortCode = $companyMap[$sellerCode] ?? '';
        if (empty($shortCode)) throw new BusinessException('该卖家简码不存在');
        // 生成退件单号，例如：R202403210001
        return $this->getCode('rt_oms_returned_order_code', $this->getPrefix('RT'.$shortCode,date("y")) , 7);
    }


}