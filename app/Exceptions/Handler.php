<?php

namespace App\Exceptions;

use App\Libraries\Logger;
use App\Libraries\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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

    public function render($request, Throwable $e)
    {
        if ($this->shouldReport($e)) {
            if ($e instanceof BusinessException) {
                // 自定义异常处理类
                Logger::business($e->getMessage());
                return Response::fail($e->getMessage());
            }

            //如果是生产模式,则不打印异常信息
            if (config('app.env') === 'production') {
                Logger::throwable($e->getFile(), $e->getLine(),$e->getMessage());
                return Response::fail('服务异常');
            }

            // 默认异常处理类
            Logger::throwable($e->getFile(), $e->getLine(), $e->getMessage());

        }
        return parent::render($request, $e);
    }
}
