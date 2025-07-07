<?php

namespace App\Enums;
class EnumBase
{

    const SYSTEM = 'system';

    //启用、禁用状态枚举值
    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 2;

    public static function getStatusMap()
    {
        return [
            self::STATUS_ENABLE  => '启用',
            self::STATUS_DISABLE => '禁用',
        ];
    }

    /**
     * @功能：获取枚举值名称
     * @作者：wdl
     * @param $enumName
     * @param $enumValue
     * @return mixed|string
     */
    public static function getValue($enumName, $enumValue)
    {
        if (method_exists(static::class,$enumName)){
            $enumMapFunction = $enumName;
        }else{
            $enumMapFunction = 'get'.$enumName.'Map';
        }
        if(!method_exists(static::class,$enumMapFunction)) {
            return '';
        }
        $map = static::$enumMapFunction();
        return $map[$enumValue] ?? '';
    }


    public static function getEnumKeys($mapFun){

        return implode(',', array_keys(static::$mapFun()));
    }

    //获取枚举类型对应的key
    public static function __callStatic($name,$arguments){

        $argType = current($arguments);
        $mapFun = $name.'Map';

        if (!method_exists(static::class,$mapFun)){
            return in_array($argType,['flip']) ? [] : '';
        }
        $map = static::$mapFun();


        if ($argType == 'keys') return implode(',', array_keys($map)); #返回由枚举key组成的字符串
        if ($argType == 'names') return implode(',', $map); #返回由枚举值组成的字符串
        if ($argType == 'flip') return array_flip($map); #返回对应枚举的交换数组

        return $map[$argType] ?? ''; #返回枚举key对应的值
    }


    const ACCOUNT_MODULE = 'ACCOUNT';//账号
    const AUDIT_CONFIG_MODULE = 'AUDIT_CONFIG';//审批配置
    const WAREHOUSE_MODULE = 'WAREHOUSE';//仓库
    const COMMON_MODULE = 'COMMON';//公共


}
