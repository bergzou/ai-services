<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemMailAccountValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'mail' => 'required|string|max:255', # 邮箱
            'username' => 'required|string|max:255', # 用户名
            'password' => 'required|string|max:255', # 密码
            'host' => 'required|string|max:255', # SMTP 服务器域名
            'port' => 'required|integer', # SMTP 服务器端口
            'ssl_enable' => 'nullable|boolean', # 是否开启 SSL
            'starttls_enable' => 'nullable|boolean', # 是否开启 STARTTLS
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
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
            'mail' => __('validated.300106'), # 邮箱
            'username' => __('validated.300075'), # 用户名
            'password' => __('validated.300076'), # 密码
            'host' => __('validated.300118'), # SMTP 服务器域名
            'port' => __('validated.300119'), # SMTP 服务器端口
            'ssl_enable' => __('validated.300120'), # 是否开启 SSL
            'starttls_enable' => __('validated.300121'), # 是否开启 STARTTLS
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
        ];
    }
}