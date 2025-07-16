<?php

namespace App\Interfaces;

/**
 * 验证接口：定义验证类需要实现的基础方法规范
 * 所有需要实现验证逻辑的类应通过实现此接口来保证方法一致性
 */
interface ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如 'required|max:64'）
     */
    public function rules(): array;

    /**
     * 定义验证错误消息数组
     * @return array 键为'字段名.规则名'（如 'name.required'），值为自定义错误提示信息
     */
    public function messages(): array;

    /**
     * 定义字段自定义别名数组（用于错误消息中显示友好名称）
     * @return array 键为字段名，值为业务友好的字段显示名称（如 'name' => '用户姓名'）
     */
    public function customAttributes(): array;
}