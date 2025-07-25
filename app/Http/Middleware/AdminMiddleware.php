<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Web通用中间件：处理HTTP请求的通用逻辑（如输入数据清理）
 * 作用于Web端请求，对输入数据进行预处理后传递给后续处理流程
 */
class AdminMiddleware
{
    /**
     * 中间件核心处理方法（Laravel中间件标准入口）
     * @param Request $request 当前HTTP请求对象
     * @param Closure $next 传递请求到下一个中间件的闭包
     * @return mixed 处理后的请求响应
     */
    public function handle(Request $request, Closure $next)
    {
        // 获取原始请求头（当前未使用，可能为后续扩展保留）
        $headers = apache_request_headers();

        // 获取所有请求输入（包括GET/POST/JSON等参数）
        $input = $request->all();

        // 递归遍历输入数据，对每个值执行trim操作（去除前后空格）
        // 作用：防止用户输入中的多余空格影响业务逻辑（如用户名、密码等字段）
        array_walk_recursive($input, function (&$item, $key) {
            $item = trim($item);
        });

        // 将清理后的输入数据合并回请求对象（覆盖原始输入）
        $request->merge($input);

        // 将处理后的请求传递给下一个中间件/控制器
        return $next($request);
    }
}