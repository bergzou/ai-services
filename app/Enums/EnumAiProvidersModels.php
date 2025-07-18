<?php

namespace App\Enums;

class EnumAiProvidersModels
{

    # 状态
    const STATUS_0 = 0; // 禁用
    const STATUS_1 = 1; // 启用

    /**
     * 获取status映射
     * @return array|string
     */
    public static function getStatusMap( $value = null)
    {
        $map = [
            self::STATUS_0 => __('enums.100007'),
            self::STATUS_1 => __('enums.100008'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
