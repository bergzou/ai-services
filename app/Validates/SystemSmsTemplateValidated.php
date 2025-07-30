<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemSmsTemplateValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'type' => 'required|boolean', # 模板类型
            'status' => 'required|boolean', # 开启状态
            'code' => 'required|string|max:63', # 模板编码
            'name' => 'required|string|max:63', # 模板名称
            'content' => 'required|string|max:255', # 模板内容
            'params' => 'required|string|max:255', # 参数数组
            'remark' => 'nullable|string|max:255', # 备注
            'api_template_id' => 'required|string|max:63', # 短信 API 的模板编号
            'channel_id' => 'required|integer', # 短信渠道编号
            'channel_code' => 'required|string|max:63', # 短信渠道编码
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
            'type' => __('validated.300060'), # 模板类型
            'status' => __('validated.300141'), # 开启状态
            'code' => __('validated.300126'), # 模板编码
            'name' => __('validated.300135'), # 模板名称
            'content' => __('validated.300139'), # 模板内容
            'params' => __('validated.300140'), # 参数数组
            'remark' => __('validated.300054'), # 备注
            'api_template_id' => __('validated.300221'), # 短信 API 的模板编号
            'channel_id' => __('validated.300216'), # 短信渠道编号
            'channel_code' => __('validated.300217'), # 短信渠道编码
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
        ];
    }
}