<?php

namespace App\Enums;

class EnumAiProviders
{

    # 状态
    const STATUS_1 = 1; // 启用0=禁用

    /**
     * 获取status映射
     * @return array|string
     */
    public static function getStatusMap( $value = null)
    {
        $map = [
            self::STATUS_1 => __('enums.100000'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
