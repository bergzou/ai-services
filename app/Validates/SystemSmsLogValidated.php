<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemSmsLogValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'id' => 'required', # 编号
            'snowflake_id' => 'required', # 雪花Id
            'channel_id' => 'required', # 短信渠道编号
            'channel_code' => 'required', # 短信渠道编码
            'template_id' => 'required', # 模板编号
            'template_code' => 'required', # 模板编码
            'template_type' => 'required', # 短信类型
            'template_content' => 'required', # 短信内容
            'template_params' => 'required', # 短信参数
            'api_template_id' => 'required', # 短信 API 的模板编号
            'mobile' => 'required', # 手机号
            'user_id' => 'nullable', # 用户编号
            'user_type' => 'nullable', # 用户类型
            'send_status' => 'required', # 发送状态
            'send_time' => 'nullable', # 发送时间
            'api_send_code' => 'nullable', # 短信 API 发送结果的编码
            'api_send_msg' => 'nullable', # 短信 API 发送失败的提示
            'api_request_id' => 'nullable', # 短信 API 发送返回的唯一请求 ID
            'api_serial_no' => 'nullable', # 短信 API 发送返回的序号
            'receive_status' => 'required', # 接收状态
            'receive_time' => 'nullable', # 接收时间
            'api_receive_code' => 'nullable', # API 接收结果的编码
            'api_receive_msg' => 'nullable', # API 接收结果的说明
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
            'id' => __('validated.300280'), # 编号
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'channel_id' => __('validated.300216'), # 短信渠道编号
            'channel_code' => __('validated.300217'), # 短信渠道编码
            'template_id' => __('validated.300125'), # 模板编号
            'template_code' => __('validated.300126'), # 模板编码
            'template_type' => __('validated.300218'), # 短信类型
            'template_content' => __('validated.300219'), # 短信内容
            'template_params' => __('validated.300220'), # 短信参数
            'api_template_id' => __('validated.300221'), # 短信 API 的模板编号
            'mobile' => __('validated.300208'), # 手机号
            'user_id' => __('validated.300001'), # 用户编号
            'user_type' => __('validated.300002'), # 用户类型
            'send_status' => __('validated.300131'), # 发送状态
            'send_time' => __('validated.300132'), # 发送时间
            'api_send_code' => __('validated.300222'), # 短信 API 发送结果的编码
            'api_send_msg' => __('validated.300223'), # 短信 API 发送失败的提示
            'api_request_id' => __('validated.300224'), # 短信 API 发送返回的唯一请求 ID
            'api_serial_no' => __('validated.300225'), # 短信 API 发送返回的序号
            'receive_status' => __('validated.300226'), # 接收状态
            'receive_time' => __('validated.300227'), # 接收时间
            'api_receive_code' => __('validated.300228'), # API 接收结果的编码
            'api_receive_msg' => __('validated.300229'), # API 接收结果的说明
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