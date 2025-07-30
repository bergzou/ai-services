<?php

namespace App\Enums;

class EnumSystemNotifyMessage
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
}
