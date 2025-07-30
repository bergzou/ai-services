<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemSmsChannelValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'snowflake_id' => 'required|string|max:64', # 雪花Id
            'signature' => 'required|string|max:12', # 短信签名
            'code' => 'required|string|max:63', # 渠道编码
            'status' => 'required|boolean', # 开启状态
            'remark' => 'nullable|string|max:255', # 备注
            'api_key' => 'required|string|max:128', # 短信 API 的账号
            'api_secret' => 'nullable|string|max:128', # 短信 API 的秘钥
            'callback_url' => 'nullable|string|max:255', # 短信发送回调 URL
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
            'signature' => __('validated.300203'), # 短信签名
            'code' => __('validated.300204'), # 渠道编码
            'status' => __('validated.300141'), # 开启状态
            'remark' => __('validated.300054'), # 备注
            'api_key' => __('validated.300205'), # 短信 API 的账号
            'api_secret' => __('validated.300206'), # 短信 API 的秘钥
            'callback_url' => __('validated.300207'), # 短信发送回调 URL
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
        ];
    }
}