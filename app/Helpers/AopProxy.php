<?php

namespace App\Helpers;

use App\Logging\AopLogger;
use Exception;
use Throwable;

/**
 * AOP代理工厂类，用于创建具有日志记录功能的动态代理
 */
class AopProxy
{
    /**
     * 创建代理实例
     *
     * 添加泛型类型声明使 PhpStorm 能识别返回的具体类型
     *
     * @template T of object
     * @param class-string<T> $className 需要被代理的类名
     * @return T 返回与输入类相同类型的代理对象
     */
    public static function make(string $className): object
    {
        return new class($className) {
            private $instance;  // 被代理的原始实例
            private  $className; // 原始类名
            private bool $enabled;

            /**
             * @param class-string<T> $className
             */
            public function __construct(string $className)
            {
                $this->className = $className;
                $this->instance = new $className;
                $this->enabled = config('logging.aop_enabled', false);
            }

            /**
             * 代理方法调用
             * @param string $method 被调用的方法名
             * @param array $arguments 方法参数
             * @return mixed 原方法的执行结果
             *
             * @throws Exception 原方法可能抛出的异常
             */
            public function __call(string $method, array $arguments)
            {
                try {
                    // 记录方法执行前
                    if ($this->enabled)  AopLogger::logBefore($this->instance, $method, $arguments);

                    // 执行原方法
                    $result = call_user_func_array([$this->instance, $method], $arguments);

                    // 记录方法执行后
                    if ($this->enabled)  AopLogger::logAfter($this->instance, $method, $result);

                    return $result;
                } catch (Throwable $e) {
                    // 记录业务异常信息
                    if ($this->enabled)   AopLogger::logException($this->instance, $method, $e);
                    // 重新抛出异常，保持原有行为
                    throw $e;
                }
            }
        };
    }
}