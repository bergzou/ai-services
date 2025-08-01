<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemNotifyMessageValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'id' => 'required', # 用户ID
            'snowflake_id' => 'required', # 雪花Id
            'user_id' => 'required', # 用户id
            'user_type' => 'required', # 用户类型：10=会员， 20=管理员
            'template_id' => 'required', # 模版编号
            'template_code' => 'required', # 模板编码
            'template_nickname' => 'required', # 模版发送人名称
            'template_content' => 'required', # 模版内容
            'template_type' => 'required', # 模版类型
            'template_params' => 'required', # 模版参数
            'read_status' => 'required', # 是否已读
            'read_time' => 'nullable', # 阅读时间
            'created_by' => 'required', # 创建人名称
            'updated_by' => 'required', # 更新人名称
            'tenant_id' => 'required', # 租户编号
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
            'id' => __('validated.300253'), # 用户ID
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'user_id' => __('validated.300157'), # 用户id
            'user_type' => __('validated.300002'), # 用户类型
            'template_id' => __('validated.300158'), # 模版编号
            'template_code' => __('validated.300126'), # 模板编码
            'template_nickname' => __('validated.300127'), # 模版发送人名称
            'template_content' => __('validated.300159'), # 模版内容
            'template_type' => __('validated.300160'), # 模版类型
            'template_params' => __('validated.300161'), # 模版参数
            'read_status' => __('validated.300162'), # 是否已读
            'read_time' => __('validated.300163'), # 阅读时间
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'tenant_id' => __('validated.300018'), # 租户编号
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