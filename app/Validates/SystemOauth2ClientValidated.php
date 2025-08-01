<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemOauth2ClientValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'id' => 'required', # 编号
            'snowflake_id' => 'required', # 雪花Id
            'client_id' => 'required', # 客户端编号
            'secret' => 'required', # 客户端密钥
            'name' => 'required', # 应用名
            'logo' => 'required', # 应用图标
            'description' => 'nullable', # 应用描述
            'status' => 'required', # 状态
            'access_token_validity_seconds' => 'required', # 访问令牌的有效期
            'refresh_token_validity_seconds' => 'required', # 刷新令牌的有效期
            'redirect_uris' => 'required', # 可重定向的 URI 地址
            'authorized_grant_types' => 'required', # 授权类型
            'scopes' => 'nullable', # 授权范围
            'auto_approve_scopes' => 'nullable', # 自动通过的授权范围
            'authorities' => 'nullable', # 权限
            'resource_ids' => 'nullable', # 资源
            'additional_information' => 'nullable', # 附加信息
            'created_by' => 'required', # 创建人名称
            'updated_by' => 'required', # 更新人名称
            'is_deleted' => 'required', # 是否删除
            'deleted_by' => 'nullable', # 删除人名称
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
            'id' => __('validated.300280'), # 编号
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'client_id' => __('validated.300169'), # 客户端编号
            'secret' => __('validated.300173'), # 客户端密钥
            'name' => __('validated.300003'), # 应用名
            'logo' => __('validated.300174'), # 应用图标
            'description' => __('validated.300175'), # 应用描述
            'status' => __('validated.300111'), # 状态
            'access_token_validity_seconds' => __('validated.300176'), # 访问令牌的有效期
            'refresh_token_validity_seconds' => __('validated.300177'), # 刷新令牌的有效期
            'redirect_uris' => __('validated.300178'), # 可重定向的 URI 地址
            'authorized_grant_types' => __('validated.300179'), # 授权类型
            'scopes' => __('validated.300170'), # 授权范围
            'auto_approve_scopes' => __('validated.300180'), # 自动通过的授权范围
            'authorities' => __('validated.300181'), # 权限
            'resource_ids' => __('validated.300182'), # 资源
            'additional_information' => __('validated.300183'), # 附加信息
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
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