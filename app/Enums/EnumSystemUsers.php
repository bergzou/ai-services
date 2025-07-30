<?php

namespace App\Enums;

class EnumSystemUsers
{

    # 帐号状态
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


    # 会员等级
    const LEVEL_10 = 10; // 普通会员
    const LEVEL_20 = 20; // 黄金会员
    const LEVEL_30 = 30; // 铂金会员
    const LEVEL_40 = 40; // 砖石会员
    const LEVEL_50 = 50; // 终生会员

    /**
     * 获取level映射
     * @return array|string
     */
    public static function getLevelMap( $value = null)
    {
        $map = [
            self::LEVEL_10 => __('enums.100027'),
            self::LEVEL_20 => __('enums.100028'),
            self::LEVEL_30 => __('enums.100029'),
            self::LEVEL_40 => __('enums.100030'),
            self::LEVEL_50 => __('enums.100031'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
