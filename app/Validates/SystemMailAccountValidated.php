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
            'id' => 'required', # 主键
            'snowflake_id' => 'required', # 雪花Id
            'mail' => 'required', # 邮箱
            'username' => 'required', # 用户名
            'password' => 'required', # 密码
            'host' => 'required', # SMTP 服务器域名
            'port' => 'required', # SMTP 服务器端口
            'ssl_enable' => 'required', # 是否开启 SSL
            'starttls_enable' => 'required', # 是否开启 STARTTLS
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
            'id' => __('validated.300289'), # 主键
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