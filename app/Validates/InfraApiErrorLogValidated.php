<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class InfraApiErrorLogValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'trace_id' => 'required|string|max:64', # 链路追踪编号
            'user_id' => 'nullable|integer', # 用户编号
            'user_type' => 'nullable|boolean', # 用户类型：10=会员， 20=管理员
            'application_name' => 'required|string|max:50', # 应用名
            'request_method' => 'required|string|max:16', # 请求方法名
            'request_url' => 'required|string|max:255', # 请求地址
            'request_params' => 'required|string|max:8000', # 请求参数
            'user_ip' => 'required|string|max:50', # 用户 IP
            'user_agent' => 'required|string|max:512', # 浏览器 UA
            'exception_time' => 'required|date_format:Y-m-d H:i:s', # 异常发生时间
            'exception_name' => 'nullable|string|max:128', # 异常名
            'exception_message' => 'required|string|max:65535', # 异常导致的消息
            'exception_root_cause_message' => 'required|string|max:65535', # 异常导致的根消息
            'exception_stack_trace' => 'required|string|max:65535', # 异常的栈轨迹
            'exception_class_name' => 'required|string|max:512', # 异常发生的类全名
            'exception_file_name' => 'required|string|max:512', # 异常发生的类文件
            'exception_method_name' => 'required|string|max:512', # 异常发生的方法名
            'exception_line_number' => 'required|integer', # 异常发生的方法所在行
            'process_status' => 'required|boolean', # 处理状态：10：未处理，10：已处理，10：已忽略
            'process_time' => 'nullable|date_format:Y-m-d H:i:s', # 处理时间
            'process_user_id' => 'nullable|integer', # 处理用户编号
            'tenant_id' => 'nullable|integer', # 租户编号
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
            'trace_id' => __('validated.300000'), # 链路追踪编号
            'user_id' => __('validated.300001'), # 用户编号
            'user_type' => __('validated.300002'), # 用户类型
            'application_name' => __('validated.300003'), # 应用名
            'request_method' => __('validated.300004'), # 请求方法名
            'request_url' => __('validated.300005'), # 请求地址
            'request_params' => __('validated.300006'), # 请求参数
            'user_ip' => __('validated.300008'), # 用户 IP
            'user_agent' => __('validated.300009'), # 浏览器 UA
            'exception_time' => __('validated.300021'), # 异常发生时间
            'exception_name' => __('validated.300022'), # 异常名
            'exception_message' => __('validated.300023'), # 异常导致的消息
            'exception_root_cause_message' => __('validated.300024'), # 异常导致的根消息
            'exception_stack_trace' => __('validated.300025'), # 异常的栈轨迹
            'exception_class_name' => __('validated.300026'), # 异常发生的类全名
            'exception_file_name' => __('validated.300027'), # 异常发生的类文件
            'exception_method_name' => __('validated.300028'), # 异常发生的方法名
            'exception_line_number' => __('validated.300029'), # 异常发生的方法所在行
            'process_status' => __('validated.300030'), # 处理状态
            'process_time' => __('validated.300031'), # 处理时间
            'process_user_id' => __('validated.300032'), # 处理用户编号
            'tenant_id' => __('validated.300018'), # 租户编号
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
        ];
    }
}