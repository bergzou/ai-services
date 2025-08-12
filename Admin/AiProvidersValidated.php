<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class AiProvidersValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'id' => 'required', # 自增ID
            'snowflake_id' => 'required', # 雪花ID
            'name' => 'required', # 服务商名称
            'code' => 'required', # 服务商编码（唯一）
            'base_uri' => 'required', # 基础 API 地址
            'api_key' => 'nullable', # 默认 API Key
            'status' => 'required', # 状态：1=启用，0=禁用
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
            'id' => __('validated.300309'), # 自增ID
            'snowflake_id' => __('validated.300310'), # 雪花ID
            'name' => __('validated.300304'), # 服务商名称
            'code' => __('validated.300319'), # 服务商编码（唯一）
            'base_uri' => __('validated.300320'), # 基础 API 地址
            'api_key' => __('validated.300321'), # 默认 API Key
            'status' => __('validated.300111'), # 状态
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
        return [
            'name',
            'code',
            'base_uri',
            'api_key',
            'status',
        ];
    }

    /**
     * 更新参数
     * @return array
     */
    public function updateParams(): array
    {
        return [
            'snowflake_id',
            'name',
            'code',
            'base_uri',
            'api_key',
            'status',
        ];
    }

    /**
     * 删除参数
     * @return array
     */
    public function deleteParams(): array
    {
        return [
            'snowflake_id',
        ];
    }

    /**
     * 详情参数
     * @return array
     */
    public function detailParams(): array
    {
        return [
            'snowflake_id',
        ];
    }
}