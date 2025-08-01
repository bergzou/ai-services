<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemLoginLogValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'id' => 'required', # 访问ID
            'snowflake_id' => 'required', # 雪花Id
            'log_type' => 'required', # 日志类型
            'trace_id' => 'required', # 链路追踪编号
            'user_id' => 'required', # 用户编号
            'user_type' => 'required', # 用户类型：10=会员， 20=管理员
            'username' => 'required', # 用户账号
            'result' => 'required', # 登陆结果
            'user_ip' => 'required', # 用户 IP
            'user_agent' => 'required', # 浏览器 UA
            'tenant_id' => 'required', # 租户编号
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
            'id' => __('validated.300288'), # 访问ID
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'log_type' => __('validated.300115'), # 日志类型
            'trace_id' => __('validated.300000'), # 链路追踪编号
            'user_id' => __('validated.300001'), # 用户编号
            'user_type' => __('validated.300002'), # 用户类型
            'username' => __('validated.300116'), # 用户账号
            'result' => __('validated.300117'), # 登陆结果
            'user_ip' => __('validated.300008'), # 用户 IP
            'user_agent' => __('validated.300009'), # 浏览器 UA
            'tenant_id' => __('validated.300018'), # 租户编号
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