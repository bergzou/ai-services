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
            'id' => 'required', # 主键(自增策略)
            'snowflake_id' => 'required', # 雪花Id
            'type' => 'required', # 社交平台的类型
            'openid' => 'required', # 社交 openid
            'token' => 'nullable', # 社交 token
            'raw_token_info' => 'required', # 原始 Token 数据，一般是 JSON 格式
            'nickname' => 'required', # 用户昵称
            'avatar' => 'nullable', # 用户头像
            'raw_user_info' => 'required', # 原始用户数据，一般是 JSON 格式
            'code' => 'required', # 最后一次的认证 code
            'state' => 'nullable', # 最后一次的认证 state
            'created_by' => 'required', # 创建人名称
            'updated_by' => 'required', # 更新人名称
            'is_deleted' => 'required', # 是否删除
            'deleted_by' => 'nullable', # 删除人名称
            'tenant_id' => 'required', # 租户编号
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
            'id' => __('validated.300292'), # 主键(自增策略)
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

    /**
     * 新增参数
     * @return array
     */
    public function addParams(): array
    {
        return [];
    }

    /**
     * 更新参数
     * @return array
     */
    public function updateParams(): array
    {
        return [];
    }

    /**
     * 删除参数
     * @return array
     */
    public function deleteParams(): array
    {
        return [];
    }

    /**
     * 详情参数
     * @return array
     */
    public function detailParams(): array
    {
        return [];
    }
}