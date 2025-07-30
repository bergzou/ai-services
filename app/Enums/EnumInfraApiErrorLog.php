<?php

namespace App\Enums;

class EnumInfraApiErrorLog
{

    # 用户类型
    const USER_TYPE_10 = 10; // 会员
    const USER_TYPE_20 = 20; // 管理员

    /**
     * 获取user_type映射
     * @return array|string
     */
    public static function getUserTypeMap( $value = null)
    {
        $map = [
            self::USER_TYPE_10 => __('enums.100000'),
            self::USER_TYPE_20 => __('enums.100001'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }


    # 处理状态
    const PROCESS_STATUS_10 = 10; // 未处理10：已处理10：已忽略

    /**
     * 获取process_status映射
     * @return array|string
     */
    public static function getProcessStatusMap( $value = null)
    {
        $map = [
            self::PROCESS_STATUS_10 => __('enums.100004'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
