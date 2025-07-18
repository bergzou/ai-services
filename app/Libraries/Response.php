<?php

namespace App\Libraries;
use Illuminate\Http\JsonResponse;

/**
 * 统一响应生成类：封装业务接口的标准JSON响应格式
 * 包含成功、失败、错误、分页等常见场景的响应生成方法
 */
class Response
{
    /**
     * 生成成功响应（业务逻辑正常时使用）
     * @param array $data 响应携带的数据（默认空数组）
     * @param string $message 提示信息（默认空字符串）
     * @param int $code 业务状态码（默认200，表示成功）
     * @param int $status HTTP状态码（默认200，表示请求成功）
     * @param array $headers 响应头（默认空数组）
     * @return JsonResponse 标准格式的JSON响应对象
     * @example Response::success(['user' => 'trae'], '获取用户成功')
     */
    public static function success(array $data = [], string $message = '', int $code = 0 , int $status = 200 , array $headers = []): JsonResponse
    {
        if (empty($message))  $message = __('common.200000');
        if (empty($code))  $code = '200000';

        return response()->json([
            'code' => $code,
            'msg'  => $message,
            'data' => $data
        ], $status, $headers, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 生成失败响应（业务逻辑异常时使用，如用户输入错误）
     * @param string $code 业务错误码（支持非数字，如 'USER_NOT_FOUND'）
     * @param string $message 错误描述信息（如 '用户不存在'）
     * @param array $data 附加数据（如错误详情，默认空数组）
     * @param int $status HTTP状态码（默认200，业务错误通常不改变HTTP状态）
     * @param array $headers 响应头（默认空数组）
     * @return JsonResponse 标准格式的JSON响应对象
     * @example Response::fail('4001', '密码错误', ['retry' => 3])
     */
    public static function fail( string $code , string $message , array $data = [], int $status = 200 , array $headers = []): JsonResponse
    {
        if (empty($message))  $message = __('common.400000');
        if (empty($code))  $code = '400000';

        return response()->json([
            'code' => $code,
            'msg'  => $message,
            'data' => $data
        ], $status, $headers, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 生成系统错误响应（代码异常/服务故障时使用）
     * @param int $code 错误码（默认500，表示服务器内部错误）
     * @param string $message 错误信息（默认'系统内部错误'）
     * @param array $data 附加数据（如错误堆栈，默认空数组）
     * @param int $status HTTP状态码（默认200，保持与前端统一处理）
     * @param array $headers 响应头（默认空数组）
     * @return JsonResponse 标准格式的JSON响应对象
     * @example Response::error(503, '服务暂时不可用')
     */
    public static function error( int $code = 0 , string $message = '' , array $data = [], int $status = 200 , array $headers = []): JsonResponse
    {
        if (empty($message))  $message = __('common.500000');
        if (empty($code))  $code = '500000';

        return response()->json([
            'code' => $code,
            'msg' => $message,
            'data' => $data
        ], $status, $headers, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 生成分页响应（列表分页场景使用）
     * @param mixed $paginate Laravel分页对象（需包含items()/total()等方法）
     * @param int $code 业务状态码（默认200，表示成功）
     * @param string $message 提示信息（默认空字符串）
     * @param int $status HTTP状态码（默认200，表示请求成功）
     * @param array $headers 响应头（默认空数组）
     * @return JsonResponse 包含分页信息的标准JSON响应对象
     * @example Response::paginate($users->paginate(10), 200, '获取用户列表成功')
     */
    public static function paginate($paginate, int $code = 200 , string $message = '',  int $status = 200 , array $headers = []): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'msg'  => $message,
            'data' => [
                'list'    => $paginate->items(),    // 当前页数据列表
                'total'   => $paginate->total(),    // 总记录数
                'size'    => $paginate->perPage(),  // 每页显示数量
                'current' => $paginate->currentPage(), // 当前页码
            ]
        ], $status, $headers, JSON_UNESCAPED_UNICODE);
    }
}