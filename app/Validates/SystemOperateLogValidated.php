<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemOperateLogValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'trace_id' => 'nullable|string|max:64', # 链路追踪编号
            'user_id' => 'required|integer', # 用户编号
            'user_type' => 'nullable|boolean', # 用户类型
            'type' => 'required|string|max:50', # 操作模块类型
            'sub_type' => 'required|string|max:50', # 操作名
            'biz_id' => 'required|integer', # 操作数据模块编号
            'action' => 'nullable|string|max:2000', # 操作内容
            'success' => 'nullable|boolean', # 操作结果
            'extra' => 'nullable|string|max:2000', # 拓展字段
            'request_method' => 'nullable|string|max:16', # 请求方法名
            'request_url' => 'nullable|string|max:255', # 请求地址
            'user_ip' => 'nullable|string|max:50', # 用户 IP
            'user_agent' => 'nullable|string|max:512', # 浏览器 UA
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
            'trace_id' => __('validated.300000'), # 链路追踪编号
            'user_id' => __('validated.300001'), # 用户编号
            'user_type' => __('validated.300002'), # 用户类型
            'type' => __('validated.300187'), # 操作模块类型
            'sub_type' => __('validated.300011'), # 操作名
            'biz_id' => __('validated.300188'), # 操作数据模块编号
            'action' => __('validated.300189'), # 操作内容
            'success' => __('validated.300190'), # 操作结果
            'extra' => __('validated.300191'), # 拓展字段
            'request_method' => __('validated.300004'), # 请求方法名
            'request_url' => __('validated.300005'), # 请求地址
            'user_ip' => __('validated.300008'), # 用户 IP
            'user_agent' => __('validated.300009'), # 浏览器 UA
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
            'tenant_id' => __('validated.300018'), # 租户编号
        ];
    }
}