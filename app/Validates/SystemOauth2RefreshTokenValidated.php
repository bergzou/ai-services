<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemOauth2RefreshTokenValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'user_id' => 'required|integer', # 用户编号
            'refresh_token' => 'required|string|max:32', # 刷新令牌
            'user_type' => 'required|integer', # 用户类型
            'client_id' => 'required|string|max:255', # 客户端编号
            'scopes' => 'nullable|string|max:255', # 授权范围
            'expires_time' => 'required|date_format:Y-m-d H:i:s', # 过期时间
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
            'is_deleted' => 'required|integer', # 是否删除
            'deleted_by' => 'nullable|string|max:255', # 删除人名称
            'tenant_id' => 'required|integer', # 租户编号
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
            'user_id' => __('validated.300001'), # 用户编号
            'refresh_token' => __('validated.300168'), # 刷新令牌
            'user_type' => __('validated.300002'), # 用户类型
            'client_id' => __('validated.300169'), # 客户端编号
            'scopes' => __('validated.300170'), # 授权范围
            'expires_time' => __('validated.300171'), # 过期时间
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
            'tenant_id' => __('validated.300018'), # 租户编号
        ];
    }
}