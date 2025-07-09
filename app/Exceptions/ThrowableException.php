<?php

namespace App\Exceptions;

use App\Libraries\Response;

use App\Logging\ExceptionLogger;
use Exception;


class ThrowableException extends Exception
{

    public function handle($e): void
    {
        // 创建业务异常日志记录器实例，调用其handle方法记录异常信息（如写入日志文件）
        (new ExceptionLogger())->handle($e);

    }

}
