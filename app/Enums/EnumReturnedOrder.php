<?php

namespace App\Enums;
class EnumReturnedOrder
{
    // 退件标识 1:芯宏发货退件 2：非芯宏发货退件
    const RETURNED_SIGN_1 = 1;
    const RETURNED_SIGN_2 = 2;
    


    public static function getReturnedSignMap(){
        return [
            self::RETURNED_SIGN_1 => '芯宏发货退件',
            self::RETURNED_SIGN_2 => '非芯宏发货退件', 
        ];
    }
    
    // 创建类型: 1：客户创建 2：仓库创建
    const   CREATE_TYPE_1 = 1;
    const   CREATE_TYPE_2 = 2;
    const   CREATE_TYPE_3 = 3;
    public static function getCreateTypeMap(){
        return [
            self::CREATE_TYPE_1 => '客户创建',
            self::CREATE_TYPE_2 => '仓库创建',
            self::CREATE_TYPE_3 => 'Api下单',
        ];
    }


     // 退件类型 1：买家退件 2：物流退件 3：退件认领
     const RETURNED_TYPE_1 = 1;
     const RETURNED_TYPE_2 = 2;
     const RETURNED_TYPE_3 = 3;
     const RETURNED_TYPE_4 = 4;

    public static function getReturnedTypeMap(){
        return [
            self::RETURNED_TYPE_1 => '买家退件',
            self::RETURNED_TYPE_2 => '物流退件',
            self::RETURNED_TYPE_3 => '退件认领',
            self::RETURNED_TYPE_4 => '平台仓退件',
        ];
    }


      // 处理方式 1：重新上架 2:销毁
      const HANDLING_METHOD_1 = 1;
      const HANDLING_METHOD_2 = 2;
      const HANDLING_METHOD_3 = 3;
      const HANDLING_METHOD_4 = 4;



    
    public static function getHandlingMethodMap(){
        return [
            self::HANDLING_METHOD_1 => '拆包裹/箱上架',
            self::HANDLING_METHOD_2 => '转不良品上架',
            self::HANDLING_METHOD_3 => '整箱上架',
            self::HANDLING_METHOD_4 => '无人认领弃货',
        ];
    }
    

      
    // 退件状态 10：草稿 20:待确认 30:待签收 40：处理中 50：已完成 60：已取消
    const RETURNED_STATUS_10 = 10;
    const RETURNED_STATUS_20 = 20;
    const RETURNED_STATUS_30 = 30;
    const RETURNED_STATUS_40 = 40;
    const RETURNED_STATUS_50 = 50;
    const RETURNED_STATUS_60 = 60;
    
    public static function getReturnedStatusMap(){
        return [
            self::RETURNED_STATUS_10 => '草稿',
            self::RETURNED_STATUS_20 => '待确认',
            self::RETURNED_STATUS_30 => '待签收',
            self::RETURNED_STATUS_40 => '处理中',
            self::RETURNED_STATUS_50 => '已完成',
            self::RETURNED_STATUS_60 => '已取消'
        ];
    }


    // `document_type` int(4) NOT NULL DEFAULT '1' COMMENT '单据类型: 1：代发退件单 2：整箱中转退件单',
    const DOCUMENT_TYPE_1 = 1;
    const DOCUMENT_TYPE_2 = 2;
    public static function getDocumentTypeMap(){
        return [
            self::DOCUMENT_TYPE_1 => '代发退件单',
            self::DOCUMENT_TYPE_2 => '整箱中转退件单',
        ];
    }
    
    const WAREHOUSE_OPERATE_TYPE_200 = 200;
    const WAREHOUSE_OPERATE_TYPE_240 = 240;
    const WAREHOUSE_OPERATE_TYPE_260 = 260;
    const WAREHOUSE_OPERATE_TYPE_310 = 310;
    const WAREHOUSE_OPERATE_TYPE_320 = 320;

    public static function getWarehouseOperateTypeMap(){
        return [
            self::WAREHOUSE_OPERATE_TYPE_200 => '代发退件单创建编辑',
            self::WAREHOUSE_OPERATE_TYPE_240 => '退件单作废',
            self::WAREHOUSE_OPERATE_TYPE_260 => '整箱中转退件单创建编辑',
            self::WAREHOUSE_OPERATE_TYPE_310 => '认领单-认领',
            self::WAREHOUSE_OPERATE_TYPE_320 => '认领单-自动弃货', 
        ]; 
    }




    const ORDER_SAVE = 200;
    const CONFIRM_RETURNED_ORDER = 205;
    const ORDER_UPDATE_TYPE = 210;
    const PROXY_BOX_RECEIVE_INSERT = 211;
    const FULL_BOX_RECEIVE_SYNC = 220;
    const FULL_BOX_RECEIVE_INSERT = 221;
    const ORDER_UPDATE = 250;
    const SYNC_FULL_BOX_RETURNED = 260;
    const RETURNED_SYNC_SIGN = 263;
    const SYNC_PUTAWAY_BATCH = 265;
    const SYNC_PUTAWAY_INFO = 270;
    const CLAIM_SAVE = 300;
    const CLAIM_UPDATE_TYPE = 310;

    public static function getOperateTypeMap(){

        return [
            self::ORDER_SAVE => '新增退件单',
            self::CONFIRM_RETURNED_ORDER => '确认退件单',
            self::ORDER_UPDATE_TYPE => '修改提交以后状态的退件单',
            self::PROXY_BOX_RECEIVE_INSERT => '代发退件单-新增+收货',
            self::FULL_BOX_RECEIVE_SYNC => '接收新的整箱退件单',
            self::FULL_BOX_RECEIVE_INSERT => '整箱退件单-新增+收货',
            self::ORDER_UPDATE => '修改草稿退件单',
            self::SYNC_FULL_BOX_RETURNED => '同步签收退件单',
            self::SYNC_PUTAWAY_BATCH => '批次上架同步',
            self::SYNC_PUTAWAY_INFO => '退件单上架完成',
            self::CLAIM_SAVE => '新增认领单',
            self::CLAIM_UPDATE_TYPE => '修改认领单', 
        ];
    }

}
