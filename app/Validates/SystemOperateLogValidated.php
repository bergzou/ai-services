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
            'id' => 'required', # 日志主键
            'snowflake_id' => 'required', # 雪花Id
            'trace_id' => 'required', # 链路追踪编号
            'user_id' => 'required', # 用户编号
            'user_type' => 'required', # 用户类型
            'type' => 'required', # 操作模块类型
            'sub_type' => 'required', # 操作名
            'biz_id' => 'required', # 操作数据模块编号
            'action' => 'required', # 操作内容
            'success' => 'required', # 操作结果
            'extra' => 'required', # 拓展字段
            'request_method' => 'nullable', # 请求方法名
            'request_url' => 'nullable', # 请求地址
            'user_ip' => 'nullable', # 用户 IP
            'user_agent' => 'nullable', # 浏览器 UA
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
            'id' => __('validated.300279'), # 日志主键
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