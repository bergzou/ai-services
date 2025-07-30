<?php

namespace App\Enums;

class EnumSystemRole
{

    # 数据范围（1
    const DATA_SCOPE_2 = 2; // 自定数据权限
    const DATA_SCOPE_3 = 3; // 本部门数据权限
    const DATA_SCOPE_4 = 4; // 本部门及以下数据权限）

    /**
     * 获取data_scope映射
     * @return array|string
     */
    public static function getDataScopeMap( $value = null)
    {
        $map = [
            self::DATA_SCOPE_2 => __('enums.100016'),
            self::DATA_SCOPE_3 => __('enums.100017'),
            self::DATA_SCOPE_4 => __('enums.100018'),
        ];

        if ($value !== null) {
            return $map[$value] ?? '';
        }
        
        return $map;
    }
}
