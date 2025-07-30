<?php

namespace App\Enums;

class EnumSystemDictType
{

    # 状态
    const STATUS_1 = 1; // 启用
    const STATUS_2 = 2; // 停用

    /**
     * 获取status映射
     * @return array|string
     */
    public static function getStatusMap( $value = null)
    {
        $map = [
            self::STATUS_1 => __('enums.100005'),
            self::STATUS_2 => __('enums.100006'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
