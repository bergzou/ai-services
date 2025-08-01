<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class InfraApiAccessLogValidated extends BaseValidated implements ValidatesInterface
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
            'user_id' => 'required|integer', # 用户编号
            'user_type' => 'required|integer', # 用户类型：10=会员， 20=管理员
            'application_name' => 'required|string|max:50', # 应用名
            'request_method' => 'required|string|max:16', # 请求方法名
            'request_url' => 'required|string|max:255', # 请求地址
            'request_params' => 'nullable|string|max:65535', # 请求参数
            'response_body' => 'nullable|string|max:65535', # 响应结果
            'user_ip' => 'required|string|max:50', # 用户 IP
            'user_agent' => 'required|string|max:512', # 浏览器 UA
            'operate_module' => 'nullable|string|max:50', # 操作模块
            'operate_name' => 'nullable|string|max:50', # 操作名
            'operate_type' => 'nullable|integer', # 操作分类：10=查询， 20=新增， 30=修改， 40=删除， 50=导出， 60=导入， 70=其它
            'begin_time' => 'required|date_format:Y-m-d H:i:s', # 开始请求时间
            'end_time' => 'required|date_format:Y-m-d H:i:s', # 结束请求时间
            'duration' => 'required|integer', # 执行时长
            'result_code' => 'required|integer', # 结果码
            'result_msg' => 'nullable|string|max:512', # 结果提示
            'tenant_id' => 'required|integer', # 租户编号
            'created_by' => 'required|string|max:255', # 创建人名称
            'updated_by' => 'required|string|max:255', # 更新人名称
            'is_deleted' => 'required|integer', # 是否删除
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
            'trace_id' => __('validated.300000'), # 链路追踪编号
            'user_id' => __('validated.300001'), # 用户编号
            'user_type' => __('validated.300002'), # 用户类型
            'application_name' => __('validated.300003'), # 应用名
            'request_method' => __('validated.300004'), # 请求方法名
            'request_url' => __('validated.300005'), # 请求地址
            'request_params' => __('validated.300006'), # 请求参数
            'response_body' => __('validated.300007'), # 响应结果
            'user_ip' => __('validated.300008'), # 用户 IP
            'user_agent' => __('validated.300009'), # 浏览器 UA
            'operate_module' => __('validated.300010'), # 操作模块
            'operate_name' => __('validated.300011'), # 操作名
            'operate_type' => __('validated.300012'), # 操作分类
            'begin_time' => __('validated.300013'), # 开始请求时间
            'end_time' => __('validated.300014'), # 结束请求时间
            'duration' => __('validated.300015'), # 执行时长
            'result_code' => __('validated.300016'), # 结果码
            'result_msg' => __('validated.300017'), # 结果提示
            'tenant_id' => __('validated.300018'), # 租户编号
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
        ];
    }
}