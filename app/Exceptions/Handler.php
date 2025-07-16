<?php

namespace App\Exceptions;

use App\Libraries\Logger;
use App\Libraries\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }


    /**
     * 渲染异常响应（Laravel 异常处理核心方法）
     * @param $request
     * @param Throwable $e
     * @return JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        // 判断当前异常是否需要被报告（根据 $dontReport 数组过滤不需要报告的异常类型）
        if ($this->shouldReport($e)) {
            // 分支处理：根据异常类型调用对应的自定义处理器
            if ($e instanceof BusinessException) {
                // 其他异常（非业务异常）：创建 ThrowableException 实例并调用 handle 方法处理（默认异常处理逻辑）
                (new BusinessException())->handle($e);
                if(env('APP_ENV','local') == 'production'){
                    return Response::fail($e->getCode(), $e->getMessage());
                }
            } else {
                // 其他异常（非业务异常）：创建 ThrowableException 实例并调用 handle 方法处理（默认异常处理逻辑）
                (new ThrowableException())->handle($e);
                if(env('APP_ENV','local') == 'production'){
                    return Response::error();
                }
            }
        }

        // 调用父类方法生成默认异常响应（Laravel 内置的异常响应逻辑）
        return parent::render($request, $e);
    }


    /**
     * 控制异常是否需要被报告（如记录日志或发送到监控服务）
     * @param Throwable $e 捕获到的异常实例
     * @return void
     * @throws Throwable
     */
    public function report(Throwable $e)
    {
        // 检查当前异常是否需要被报告（基于 $dontReport 数组判断）
        // 若 shouldReport 返回 true（需要报告），则跳过后续报告逻辑（直接返回）
        if ($this->shouldReport($e))  return;

        // 调用父类的 report 方法执行默认报告逻辑（如写入日志文件）
        parent::report($e);
    }
}
