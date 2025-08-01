<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class InfraJobLogValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'job_id' => 'required|integer', # 任务编号
            'handler_name' => 'required|string|max:64', # 处理器的名字
            'handler_param' => 'nullable|string|max:255', # 处理器的参数
            'execute_index' => 'required|integer', # 第几次执行
            'begin_time' => 'required|date_format:Y-m-d H:i:s', # 开始执行时间
            'end_time' => 'nullable|date_format:Y-m-d H:i:s', # 结束执行时间
            'duration' => 'nullable|integer', # 执行时长
            'status' => 'required|integer', # 任务状态
            'result' => 'nullable|string|max:4000', # 结果数据
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
            'job_id' => __('validated.300096'), # 任务编号
            'handler_name' => __('validated.300090'), # 处理器的名字
            'handler_param' => __('validated.300091'), # 处理器的参数
            'execute_index' => __('validated.300097'), # 第几次执行
            'begin_time' => __('validated.300098'), # 开始执行时间
            'end_time' => __('validated.300099'), # 结束执行时间
            'duration' => __('validated.300015'), # 执行时长
            'status' => __('validated.300089'), # 任务状态
            'result' => __('validated.300100'), # 结果数据
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
        ];
    }
}