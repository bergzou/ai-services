<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class AiProvidersModelsValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255', # 产品名称
            'slug' => 'required|string|max:255', # 唯一标识
            'endpoint' => 'required|string|max:500', # API端点
            'api_key' => 'required|string|max:255', # APi key
            'status' => 'nullable|integer', # 状态：0：禁用 1：启用
            'created_by' => 'required|string|max:50', # 创建人名称
            'updated_by' => 'nullable|string|max:50', # 更新人名称
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
            'name' => __('validated.300021'), # 产品名称
            'slug' => __('validated.300022'), # 唯一标识
            'endpoint' => __('validated.300025'), # API端点
            'api_key' => __('validated.300024'), # APi key
            'status' => __('validated.300006'), # 状态
            'created_by' => __('validated.300008'), # 创建人名称
            'updated_by' => __('validated.300009'), # 更新人名称
        ];
    }
}