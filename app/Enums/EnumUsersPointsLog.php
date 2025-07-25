<?php

namespace App\Enums;

class EnumUsersPointsLog
{

    # 积分来源
    const SOURCE_TYPE_10 = 10; // 充值会员

    /**
     * 获取source_type映射
     * @return array|string
     */
    public static function getSourceTypeMap( $value = null)
    {
        $map = [
            self::SOURCE_TYPE_10 => __('enums.100015'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
