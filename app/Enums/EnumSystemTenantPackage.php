<?php

namespace App\Enums;

class EnumSystemTenantPackage
{

    # 套餐状态
    const STATUS_1 = 1; // 正常
    const STATUS_2 = 2; // 停用

    /**
     * 获取status映射
     * @return array|string
     */
    public static function getStatusMap( $value = null)
    {
        $map = [
            self::STATUS_1 => __('enums.100026'),
            self::STATUS_2 => __('enums.100006'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
