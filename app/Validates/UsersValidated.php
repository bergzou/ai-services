<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class UsersValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'uuid' => 'required|string|max:255', # UUID
            'name' => 'required|string|max:200', # 用户名
            'password' => 'required|string|max:500', # 加密密码
            'email' => 'required|string|max:100', # 邮箱
            'mobile' => 'nullable|string|max:200', # 手机号
            'points' => 'nullable|integer', # 会员积分
            'level' => 'required|integer', # 会员等级：10=普通会员,20=黄金会员,30=铂金会员,40=钻石会员,50=终身会员
            'status' => 'nullable|integer', # 状态：0=禁用,1=启用,2=未激活
            'avatar' => 'nullable|string|max:255', # 头像路径
            'created_by' => 'nullable|string|max:255', # 创建人名称
            'updated_by' => 'nullable|string|max:255', # 更新人名称
            'updated_by' => 'nullable|string|max:255', # 更新人名称
            'updated_by' => 'nullable|string|max:255', # 更新人名称
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
            'uuid' => __('validated.300027'), # UUID
            'name' => __('validated.300000'), # 用户名
            'password' => __('validated.300001'), # 加密密码
            'email' => __('validated.300002'), # 邮箱
            'mobile' => __('validated.300003'), # 手机号
            'points' => __('validated.300004'), # 会员积分
            'level' => __('validated.300028'), # 会员等级
            'status' => __('validated.300006'), # 状态
            'avatar' => __('validated.300007'), # 头像路径
            'created_by' => __('validated.300008'), # 创建人名称
            'updated_by' => __('validated.300009'), # 更新人名称
        ];
    }

    public function addParams(): array
    {
        return ['name','password','mobile'];
    }

    public function registerByUsernameParams(): array
    {
        return ['name','password'];
    }

}