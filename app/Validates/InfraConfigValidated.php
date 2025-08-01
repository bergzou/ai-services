<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class InfraConfigValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'category' => 'required|string|max:50', # 参数分组
            'type' => 'required|integer', # 参数类型
            'name' => 'required|string|max:100', # 参数名称
            'config_key' => 'required|string|max:100', # 参数键名
            'value' => 'required|string|max:500', # 参数键值
            'visible' => 'required|integer', # 是否可见
            'remark' => 'nullable|string|max:500', # 备注
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
            'is_deleted' => 'required|integer', # 是否删除
            'deleted_by' => 'nullable|string|max:255', # 删除人名称
        ];
    }

    /**
     * 定义验证错误消息数组
     * @return array 键为'字段名.规则名'（如 'name.required'），值为自定义错误提示信息
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * 定义字段自定义别名数组（用于错误消息中显示友好名称）
     * @return array 键为字段名，值为业务友好的字段显示名称（如 'name' => '用户姓名'）
     * */
    public function customAttributes(): array
    {
        return [
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'category' => __('validated.300068'), # 参数分组
            'type' => __('validated.300069'), # 参数类型
            'name' => __('validated.300070'), # 参数名称
            'config_key' => __('validated.300071'), # 参数键名
            'value' => __('validated.300072'), # 参数键值
            'visible' => __('validated.300073'), # 是否可见
            'remark' => __('validated.300054'), # 备注
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
        ];
    }
}