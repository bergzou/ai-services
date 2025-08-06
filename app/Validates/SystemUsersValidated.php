<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemUsersValidated extends BaseValidated implements ValidatesInterface
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
            'username' => 'required|alpha_num|between:4,16', # 用户账号
            'password' => 'required|between:4,16', # 密码
            'nickname' => 'required', # 用户昵称
            'remark' => 'nullable', # 备注
            'dept_id' => 'nullable', # 部门ID
            'post_ids' => 'nullable', # 岗位编号数组
            'email' => 'nullable', # 用户邮箱
            'mobile' => 'nullable', # 手机号码
            'sex' => 'nullable', # 用户性别
            'avatar' => 'nullable', # 头像地址
            'status' => 'required', # 帐号状态： 1=正常， 2=停用
            'login_ip' => 'nullable', # 最后登录IP
            'login_date' => 'nullable', # 最后登录时间
            'created_by' => 'required', # 创建人名称
            'updated_by' => 'required', # 更新人名称
            'is_deleted' => 'required', # 是否删除
            'deleted_by' => 'nullable', # 删除人名称
            'tenant_id' => 'required', # 租户编号
            'level' => 'required', # 会员等级：10=普通会员， 20=黄金会员， 30=铂金会员， 40=砖石会员， 50=终生会员
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
            'username' => __('validated.300116'), # 用户账号
            'password' => __('validated.300076'), # 密码
            'nickname' => __('validated.300235'), # 用户昵称
            'remark' => __('validated.300054'), # 备注
            'dept_id' => __('validated.300257'), # 部门ID
            'post_ids' => __('validated.300258'), # 岗位编号数组
            'email' => __('validated.300259'), # 用户邮箱
            'mobile' => __('validated.300260'), # 手机号码
            'sex' => __('validated.300261'), # 用户性别
            'avatar' => __('validated.300262'), # 头像地址
            'status' => __('validated.300263'), # 帐号状态
            'login_ip' => __('validated.300264'), # 最后登录IP
            'login_date' => __('validated.300265'), # 最后登录时间
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
            'tenant_id' => __('validated.300018'), # 租户编号
            'level' => __('validated.300266'), # 会员等级
        ];
    }

    /**
     * 新增参数
     * @return array
     */
    public function addParams(): array
    {
        return ['nickname', 'dept_id', 'mobile', 'email', 'username', 'password', 'post_ids', 'remark', 'status', 'role_ids'];
    }

    /**
     * 更新参数
     * @return array
     */
    public function updateParams(): array
    {
        return ['snowflake_id','nickname', 'dept_id', 'mobile', 'email', 'username', 'post_ids', 'remark', 'status', 'role_ids'];
    }

    /**
     * 删除参数
     * @return array
     */
    public function deleteParams(): array
    {
        return ['snowflake_id'];
    }

    /**
     * 详情参数
     * @return array
     */
    public function detailParams(): array
    {
        return ['snowflake_id'];
    }
}