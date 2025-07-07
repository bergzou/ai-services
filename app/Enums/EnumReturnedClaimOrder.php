<?php

namespace App\Enums;

class EnumReturnedClaimOrder
{
    // 认领状态
    const CLAIM_STATUS_PENDING = 10; // 待认领
    const CLAIM_STATUS_CLAIMED = 2;  // 已认领
    const CLAIM_STATUS_ABANDONED = 3; // 已弃货

    // 认领类型
    const CLAIM_TYPE_MINE = 1;      // 我的退件
    const CLAIM_TYPE_UNKNOWN = 2;    // 未知退件



    /**
     * 获取认领状态映射
     */
    public static function getClaimStatusMap(): array
    {
        return [
            self::CLAIM_STATUS_PENDING => '待认领',
            self::CLAIM_STATUS_CLAIMED => '已认领',
            self::CLAIM_STATUS_ABANDONED => '已弃货'
        ];
    }

    /**
     * 获取认领类型映射
     */
    public static function getClaimTypeMap(): array
    {
        return [
            self::CLAIM_TYPE_MINE => '我的退件',
            self::CLAIM_TYPE_UNKNOWN => '未知退件'
        ];
    }


    // 处理方式
    const HANDLING_METHOD_RESTOCK = 1; // 重新上架
    const HANDLING_METHOD_DESTROY = 2; // 销毁
    const HANDLING_METHOD_ABANDONED = 4; // 无人认领弃货
    public static function getHandlingMethodMap(): array
    {
        return [
            self::HANDLING_METHOD_RESTOCK => '重新上架',
            self::HANDLING_METHOD_DESTROY => '销毁',
            self::HANDLING_METHOD_ABANDONED => '无人认领弃货',
        ];
    }
}
