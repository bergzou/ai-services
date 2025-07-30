<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemSocialUserValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'type' => 'required|boolean', # 社交平台的类型
            'openid' => 'required|string|max:32', # 社交 openid
            'token' => 'nullable|string|max:256', # 社交 token
            'raw_token_info' => 'required|string|max:1024', # 原始 Token 数据，一般是 JSON 格式
            'nickname' => 'required|string|max:32', # 用户昵称
            'avatar' => 'nullable|string|max:255', # 用户头像
            'raw_user_info' => 'required|string|max:1024', # 原始用户数据，一般是 JSON 格式
            'code' => 'required|string|max:256', # 最后一次的认证 code
            'state' => 'nullable|string|max:256', # 最后一次的认证 state
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
            'is_deleted' => 'nullable|boolean', # 是否删除
            'deleted_by' => 'nullable|string|max:255', # 删除人名称
            'tenant_id' => 'nullable|integer', # 租户编号
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
            'type' => __('validated.300230'), # 社交平台的类型
            'openid' => __('validated.300232'), # 社交 openid
            'token' => __('validated.300233'), # 社交 token
            'raw_token_info' => __('validated.300234'), # 原始 Token 数据，一般是 JSON 格式
            'nickname' => __('validated.300235'), # 用户昵称
            'avatar' => __('validated.300236'), # 用户头像
            'raw_user_info' => __('validated.300237'), # 原始用户数据，一般是 JSON 格式
            'code' => __('validated.300238'), # 最后一次的认证 code
            'state' => __('validated.300239'), # 最后一次的认证 state
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
            'tenant_id' => __('validated.300018'), # 租户编号
        ];
    }
}