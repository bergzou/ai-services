<?php

namespace App\Services;

/**
 * 通用服务类
 *
 * 继承自基础服务类BaseService，提供通用的数据处理方法
 */
class CommonService extends BaseService
{

    /**
     * 将对象/嵌套结构转换为纯数组
     * @param mixed $data 待转换的数据（支持对象或数组类型）
     * @return mixed 转换后的数组（原始类型非对象/数组时返回原值）
     */
    public static function convertToArray($data)
    {
        // 处理对象类型：通过JSON序列化反序列化将对象转为关联数组
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }
        // 处理数组类型：递归遍历每个元素进行转换，确保深层嵌套结构被处理
        elseif (is_array($data)) {
            foreach ($data as &$item) {
                // 递归调用自身处理数组中的每个元素
                $item = self::convertToArray($item);
            }
        }

        return $data;
    }
}