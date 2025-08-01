<?php

namespace App\Exceptions;

use App\Libraries\Response;
use App\Logging\ExceptionLogger;
use Exception;
use Illuminate\Http\JsonResponse;

class BusinessException extends Exception
{

    /**
     * 处理业务异常（记录日志并返回响应）
     * @param BusinessException $e 捕获到的业务异常实例
     */
    public function handle(BusinessException $e): void
    {
        // 创建业务异常日志记录器实例，调用其handle方法记录异常信息（如写入日志文件）
        (new ExceptionLogger())->handle($e);

    }
}
