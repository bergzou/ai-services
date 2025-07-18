<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class UsersPointsLogValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|string|max:255', # 用户ID
            'points_change' => 'required|integer', # 积分变动值
            'current_points' => 'required|integer', # 变动后积分
            'source_type' => 'nullable|integer', # 来源类型
            'source_id' => 'nullable|string|max:50', # 来源ID
            'description' => 'required|string|max:255', # 变动描述
            'created_by' => 'required|string|max:50', # 操作人名称
            'updated_by' => 'nullable|string|max:50', # 更新人名称
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
            'user_id' => __('validated.300014'), # 用户ID
            'points_change' => __('validated.300015'), # 积分变动值
            'current_points' => __('validated.300016'), # 变动后积分
            'source_type' => __('validated.300017'), # 来源类型
            'source_id' => __('validated.300018'), # 来源ID
            'description' => __('validated.300019'), # 变动描述
            'created_by' => __('validated.300020'), # 操作人名称
            'updated_by' => __('validated.300009'), # 更新人名称
        ];
    }
}