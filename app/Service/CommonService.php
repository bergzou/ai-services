<?php

namespace App\Service;

use App\Client\PmsClient;
use App\Client\WmsUserClient;
use App\Models\Tms\CountryModel;
use App\Models\Tms\CurrencyModel;


class CommonService extends BaseService
{

    /**
     * 将对象转换为数组
     *
     * @param object|array $data 要进行转换的对象或数组
     * @return array 转换后的关联数组
     */
    public static function convertToArray($data)
    {
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        } elseif (is_array($data)) {
            foreach ($data as &$item) {
                $item = self::convertToArray($item);
            }
        }

        return $data;
    }

    /**
     * 动态合并数组，根据指定字段分组并累加目标字段
     * @param array $data 原始数组
     * @param string $groupKey 分组依据的字段名（例如'sku'）
     * @param string $sumField 需要累加的字段名（例如'returned_quantity'）
     * @return array 合并后的新数组
     */
    public static function mergeArrayByKey(array $data, string $groupKey = 'sku', string $sumField = 'returned_quantity'): array
    {
        $merged = [];
        foreach ($data as $item) {
            // 提取分组键和累加值
            $key = $item[$groupKey];
            $value = (int)$item[$sumField]; // 按需调整类型（如float）

            if (isset($merged[$key])) {
                // 累加目标字段
                $merged[$key][$sumField] += $value;
            } else {
                // 创建新条目并初始化
                $newEntry = $item;
                $newEntry[$sumField] = $value; // 确保类型一致
                $merged[$key] = $newEntry;
            }
        }

        // 移除临时键名并恢复原始类型
        $result = [];
        foreach ($merged as $entry) {
            $entry[$sumField] = (string)$entry[$sumField]; // 按需保持原类型
            $result[] = $entry;
        }

        return $result;
    }

    public static function conversionSellerSku($sellerSku)
    {

        $items = explode(',', $sellerSku);
        $result = [];

        foreach ($items as $item) {
            $parts = explode('@*@', trim($item));
            if (count($parts) >= 1 && !empty($parts[0])) {
                $result[] = $parts[0];
            }
        }

        return implode(',', $result);

    }
}


