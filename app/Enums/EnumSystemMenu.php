<?php

namespace App\Enums;

class EnumSystemMenu
{

    # 菜单类型
    const TYPE_1 = 1; // 目录
    const TYPE_2 = 2; // 菜单
    const TYPE_3 = 3; // 按钮

    /**
     * 获取type映射
     * @return array|string
     */
    public static function getTypeMap( $value = null)
    {
        $map = [
            self::TYPE_1 => __('enums.100007'),
            self::TYPE_2 => __('enums.100008'),
            self::TYPE_3 => __('enums.100009'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }


    # 菜单状态
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


    # 是否可见
    const VISIBLE_1 = 1; // 显示
    const VISIBLE_2 = 2; // 隐藏

    /**
     * 获取visible映射
     * @return array|string
     */
    public static function getVisibleMap( $value = null)
    {
        $map = [
            self::VISIBLE_1 => __('enums.100010'),
            self::VISIBLE_2 => __('enums.100011'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }


    # 是否缓存
    const KEEP_ALIVE_1 = 1; // 缓存
    const KEEP_ALIVE_2 = 2; // 不缓存

    /**
     * 获取keep_alive映射
     * @return array|string
     */
    public static function getKeepAliveMap( $value = null)
    {
        $map = [
            self::KEEP_ALIVE_1 => __('enums.100012'),
            self::KEEP_ALIVE_2 => __('enums.100013'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }


    # 是否总是显示
    const ALWAYS_SHOW_1 = 1; // 总是
    const ALWAYS_SHOW_2 = 2; // 不是

    /**
     * 获取always_show映射
     * @return array|string
     */
    public static function getAlwaysShowMap( $value = null)
    {
        $map = [
            self::ALWAYS_SHOW_1 => __('enums.100014'),
            self::ALWAYS_SHOW_2 => __('enums.100015'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
