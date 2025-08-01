<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemSocialClientValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'name' => 'required|string|max:255', # 应用名
            'social_type' => 'required|integer', # 社交平台的类型：10=后台， 20=微信， 21=微信公众平台， 22=微信小程序， 30=支付宝， 31=钉钉， 50=Gitee
            'user_type' => 'required|integer', # 用户类型：10：会员， 20：管理员
            'client_id' => 'required|string|max:255', # 客户端编号
            'client_secret' => 'required|string|max:255', # 客户端密钥
            'agent_id' => 'nullable|string|max:255', # 代理编号
            'status' => 'required|integer', # 状态
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
            'name' => __('validated.300003'), # 应用名
            'social_type' => __('validated.300230'), # 社交平台的类型
            'user_type' => __('validated.300002'), # 用户类型
            'client_id' => __('validated.300169'), # 客户端编号
            'client_secret' => __('validated.300173'), # 客户端密钥
            'agent_id' => __('validated.300231'), # 代理编号
            'status' => __('validated.300111'), # 状态
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
            'tenant_id' => __('validated.300018'), # 租户编号
        ];
    }
}