<?php

namespace App\Enums;

class EnumSystemSocialClient
{

    # 社交平台的类型
    const SOCIAL_TYPE_10 = 10; // 后台
    const SOCIAL_TYPE_20 = 20; // 微信
    const SOCIAL_TYPE_21 = 21; // 微信公众平台
    const SOCIAL_TYPE_22 = 22; // 微信小程序
    const SOCIAL_TYPE_30 = 30; // 支付宝
    const SOCIAL_TYPE_31 = 31; // 钉钉
    const SOCIAL_TYPE_50 = 50; // Gitee

    /**
     * 获取social_type映射
     * @return array|string
     */
    public static function getSocialTypeMap( $value = null)
    {
        $map = [
            self::SOCIAL_TYPE_10 => __('enums.100019'),
            self::SOCIAL_TYPE_20 => __('enums.100020'),
            self::SOCIAL_TYPE_21 => __('enums.100021'),
            self::SOCIAL_TYPE_22 => __('enums.100022'),
            self::SOCIAL_TYPE_30 => __('enums.100023'),
            self::SOCIAL_TYPE_31 => __('enums.100024'),
            self::SOCIAL_TYPE_50 => __('enums.100025'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }


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
