<?php

namespace App\Enums;

class EnumInfraApiAccessLog
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


    # 操作分类
    const OPERATE_TYPE_10 = 10; // 查询
    const OPERATE_TYPE_20 = 20; // 新增
    const OPERATE_TYPE_30 = 30; // 修改
    const OPERATE_TYPE_40 = 40; // 删除
    const OPERATE_TYPE_50 = 50; // 导出
    const OPERATE_TYPE_60 = 60; // 导入
    const OPERATE_TYPE_70 = 70; // 其它

    /**
     * 获取operate_type映射
     * @return array|string
     */
    public static function getOperateTypeMap( $value = null)
    {
        $map = [
            self::OPERATE_TYPE_10 => __('enums.100032'),
            self::OPERATE_TYPE_20 => __('enums.100033'),
            self::OPERATE_TYPE_30 => __('enums.100034'),
            self::OPERATE_TYPE_40 => __('enums.100035'),
            self::OPERATE_TYPE_50 => __('enums.100036'),
            self::OPERATE_TYPE_60 => __('enums.100037'),
            self::OPERATE_TYPE_70 => __('enums.100003'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
