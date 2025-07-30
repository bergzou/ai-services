<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemTenantValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'name' => 'required|string|max:30', # 租户名
            'contact_user_id' => 'nullable|integer', # 联系人的用户编号
            'contact_name' => 'required|string|max:30', # 联系人
            'contact_mobile' => 'nullable|string|max:500', # 联系手机
            'status' => 'nullable|integer', # 租户状态：1=正常， 2=停用
            'website' => 'nullable|string|max:256', # 绑定域名
            'package_id' => 'required|integer', # 租户套餐编号
            'expire_time' => 'required|date_format:Y-m-d H:i:s', # 过期时间
            'account_count' => 'required|integer', # 账号数量
            'creator' => 'nullable|string|max:64', # 创建者
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
            'is_deleted' => 'nullable|boolean', # 是否删除
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
            'name' => __('validated.300241'), # 租户名
            'contact_user_id' => __('validated.300242'), # 联系人的用户编号
            'contact_name' => __('validated.300243'), # 联系人
            'contact_mobile' => __('validated.300244'), # 联系手机
            'status' => __('validated.300245'), # 租户状态
            'website' => __('validated.300246'), # 绑定域名
            'package_id' => __('validated.300247'), # 租户套餐编号
            'expire_time' => __('validated.300171'), # 过期时间
            'account_count' => __('validated.300248'), # 账号数量
            'creator' => __('validated.300249'), # 创建者
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
        ];
    }
}