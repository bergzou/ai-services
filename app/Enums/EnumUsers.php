<?php

namespace App\Enums;

class EnumUsers
{

    # 会员等级
    const LEVEL_10 = 10; // 普通会员
    const LEVEL_20 = 20; // 黄金会员
    const LEVEL_30 = 30; // 铂金会员
    const LEVEL_40 = 40; // 钻石会员
    const LEVEL_50 = 50; // 终身会员

    /**
     * 获取level映射
     * @return array|string
     */
    public static function getLevelMap( $value = null)
    {
        $map = [
            self::LEVEL_10 => __('enums.100010'),
            self::LEVEL_20 => __('enums.100011'),
            self::LEVEL_30 => __('enums.100012'),
            self::LEVEL_40 => __('enums.100013'),
            self::LEVEL_50 => __('enums.100014'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }


    # 状态
    const STATUS_0 = 0; // 禁用
    const STATUS_1 = 1; // 启用
    const STATUS_2 = 2; // 未激活

    /**
     * 获取status映射
     * @return array|string
     */
    public static function getStatusMap( $value = null)
    {
        $map = [
            self::STATUS_0 => __('enums.100007'),
            self::STATUS_1 => __('enums.100008'),
            self::STATUS_2 => __('enums.100009'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
