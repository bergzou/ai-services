<?php

namespace App\Validates;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

/**
 * 验证服务基类：提供通用参数验证功能
 * 子类需实现具体验证规则（rules）、错误消息（messages）和属性别名（customAttributes）
 * 支持分组验证、动态参数过滤和错误处理
 */
abstract class BaseValidated
{
    protected string $group = '';       // 当前验证分组（用于区分不同业务场景）
    protected array $params = [];       // 待验证的原始参数数组
    protected string $groupParamsFun = '';  // 分组参数方法名（用于获取当前分组的参数规则）

    /**
     * 必须由子类实现的验证规则方法
     * @return array 验证规则数组（键为参数名，值为验证规则字符串/数组）
     * @example ['username' => 'required|string|max:20']
     */
    abstract function rules(): array;

    /**
     * 必须由子类实现的错误消息方法
     * @return array 错误消息数组（键为 "参数.规则"，值为自定义消息）
     * @example ['username.required' => '用户名不能为空']
     */
    abstract function messages(): array;

    /**
     * 必须由子类实现的属性别名方法
     * @return array 属性别名数组（键为参数名，值为更友好的属性名）
     * @example ['username' => '用户名']
     */
    abstract function customAttributes(): array;

    /**
     * 构造函数：初始化验证参数和分组
     * @param mixed $params 待验证的参数（通常为请求参数数组）
     * @param string $group 当前验证分组（对应具体业务场景，如 "create"、"update"）
     */
    public function __construct($params, string $group)
    {
        $this->params = $params;          // 初始化待验证参数
        $this->group = $group;            // 初始化验证分组
        $this->groupParamsFun = $this->group . 'Params';  // 生成默认分组参数方法名（如 "createParams"）

        // 兼容直接使用分组名作为方法名的情况（如分组为 "create"，则尝试调用 "create()" 方法）
        if (!method_exists($this, $this->groupParamsFun) && method_exists($this, $group)) {
            $this->groupParamsFun = $group;
        }
        return $this;
    }

    /**
     * 执行验证并返回错误信息（或抛出异常）
     * @param bool $isThrow 是否抛出异常（默认不抛出）
     * @param bool $isAll 是否返回所有错误（默认返回第一条错误）
     * @return string|array 错误信息（字符串或数组）
     * @throws BusinessException 验证失败且 $isThrow 为 true 时抛出
     */
    public function isRunFail(bool $isThrow = false, bool $isAll = false)
    {
        $params = $this->params;  // 待验证参数
        $group = $this->group;    // 当前验证分组

        if (empty($group)) return [];  // 无分组时直接返回空数组

        // 动态调用分组对应的验证规则（通过 __call 魔术方法实现）
        $rules = $this->$group();  # 执行__call方法
        if (empty($rules)) {
            throw new BusinessException(Lang::get('rules function name error'));  // 规则不存在时抛异常
        }

        // 处理关联数组格式的规则（兼容两种规则定义方式）
        $rules = $this->isAssoc($rules) ? $rules : $rules[$group];

        // 创建验证器实例（使用自定义消息和属性别名）
        $validator = Validator::make($params, $rules, $this->messages(), $this->customAttributes());
        $messages = [];

        if ($validator->fails()) {
            // 根据 $isAll 参数决定返回所有错误或第一条错误
            if ($isAll) {
                $messages = $validator->errors()->all();  // 获取所有错误信息数组
                $messages = implode(',', $messages);      // 合并为字符串
            } else {
                $messages = $validator->errors()->first();  // 获取第一条错误信息
            }

            // 验证失败且要求抛出异常时，抛出业务异常
            if ($isThrow) {
                throw new BusinessException($messages);
            }
        }

        return $messages;
    }

    /**
     * 获取过滤后的请求参数（仅保留需要验证的参数）
     * @return array 过滤后的参数数组（与验证规则相关的参数）
     */
    public function getRequestParams()
    {
        $paramsFun = $this->groupParamsFun;  // 分组参数方法名

        // 若分组参数方法不存在，返回原始参数
        if (!method_exists($this, $paramsFun)) {
            return $this->params;
        }

        // 通过分组参数方法获取需要验证的参数键名，过滤原始参数
        return array_intersect_key($this->params, $this->$paramsFun());
    }

    /**
     * 魔术方法：动态获取分组对应的验证规则
     * @param string $name 方法名（即验证分组名）
     * @param array $arguments 方法参数（未使用）
     * @return array 分组对应的验证规则数组
     */
    public function __call($name, $arguments)
    {
        $paramsFun = $this->groupParamsFun;  // 分组参数方法名

        // 若分组参数方法不存在，返回空规则
        if (!method_exists($this, $paramsFun)) {
            return [];
        }

        $intersectRules = [];  // 最终交集规则数组
        $rules = $this->rules();  // 获取所有验证规则

        // 遍历分组参数方法返回的参数键名，生成交集规则
        foreach ($this->$paramsFun() as $key => $value) {
            if (isset($rules[$key])) {
                // 参数键名与规则键名匹配时，使用规则（若 $value 非空则覆盖）
                $intersectRules[$key] = !empty($value) ? $value : $rules[$key];
            } elseif (isset($rules[$value])) {
                // 参数值与规则键名匹配时，直接使用规则
                $intersectRules[$value] = $rules[$value];
            }
        }

        return $intersectRules;
    }



    /**
     * 判断数组是否为关联数组（键为字符串）
     * @param mixed $array 待判断的数组
     * @return bool true（关联数组）| false（索引数组）
     */
    public function isAssoc($array): bool
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }
}