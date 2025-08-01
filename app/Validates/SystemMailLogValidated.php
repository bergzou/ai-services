<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemMailLogValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'user_id' => 'nullable|integer', # 用户编号
            'user_type' => 'nullable|integer', # 用户类型
            'to_mail' => 'required|string|max:255', # 接收邮箱地址
            'account_id' => 'required|integer', # 邮箱账号编号
            'from_mail' => 'required|string|max:255', # 发送邮箱地址
            'template_id' => 'required|integer', # 模板编号
            'template_code' => 'required|string|max:63', # 模板编码
            'template_nickname' => 'nullable|string|max:255', # 模版发送人名称
            'template_title' => 'required|string|max:255', # 邮件标题
            'template_content' => 'required|string|max:65535', # 邮件内容
            'template_params' => 'required|string|max:255', # 邮件参数
            'send_status' => 'required|integer', # 发送状态
            'send_time' => 'nullable|date_format:Y-m-d H:i:s', # 发送时间
            'send_message_id' => 'nullable|string|max:255', # 发送返回的消息 ID
            'send_exception' => 'nullable|string|max:65535', # 发送异常
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
            'user_id' => __('validated.300001'), # 用户编号
            'user_type' => __('validated.300002'), # 用户类型
            'to_mail' => __('validated.300122'), # 接收邮箱地址
            'account_id' => __('validated.300123'), # 邮箱账号编号
            'from_mail' => __('validated.300124'), # 发送邮箱地址
            'template_id' => __('validated.300125'), # 模板编号
            'template_code' => __('validated.300126'), # 模板编码
            'template_nickname' => __('validated.300127'), # 模版发送人名称
            'template_title' => __('validated.300128'), # 邮件标题
            'template_content' => __('validated.300129'), # 邮件内容
            'template_params' => __('validated.300130'), # 邮件参数
            'send_status' => __('validated.300131'), # 发送状态
            'send_time' => __('validated.300132'), # 发送时间
            'send_message_id' => __('validated.300133'), # 发送返回的消息 ID
            'send_exception' => __('validated.300134'), # 发送异常
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
        ];
    }
}