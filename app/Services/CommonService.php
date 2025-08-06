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
        } elseif (is_array($data)) {
            foreach ($data as &$item) {
                // 递归调用自身处理数组中的每个元素
                $item = self::convertToArray($item);
            }
        }else{
            $data = [];
        }

        return $data;
    }


    /**
     * 递归过滤数组，仅保留允许的键及其子数组中的允许键
     * @param array $data 待过滤的原始数组（支持嵌套结构）
     * @param array $allowedKeys 允许保留的键名数组（白名单）
     * @return array 过滤后的新数组（仅包含允许的键及其符合条件的子数组内容）
     */
    public static function filterRecursive(array $params, array $allowedKeys): array
    {
        $result = [];

        foreach ($params as $key => $value) {
            $keyAllowed = in_array($key, $allowedKeys, true);

            if (is_array($value)) {
                // 检测是否为索引数组（所有键均为数字）
                $isIndexArray = !array_filter(array_keys($value), 'is_string');

                if ($isIndexArray) {
                    // 索引数组：递归处理每个元素（如果元素是数组）
                    $filtered = [];
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            $filtered[] = self::filterRecursive($item, $allowedKeys);
                        } else {
                            $filtered[] = $item;
                        }
                    }
                } else {
                    // 关联数组：递归过滤整个数组
                    $filtered = self::filterRecursive($value, $allowedKeys);
                }

                // 保留条件：过滤后非空 或 当前键在允许列表中
                if (!empty($filtered) || $keyAllowed) {
                    $result[$key] = $filtered;
                }
            } elseif ($keyAllowed) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

}