<?php

namespace App\Logging;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class ExceptionLogger
{
    // 日志格式常量
    const BORDER_CHAR_H = '═';
    const BORDER_CHAR_V = '║';
    const BORDER_CHAR_C = '╬';
    const MAX_LINE_WIDTH = 120;

    // 日志级别图标
    const ICON_ERROR = '⛔';
    const ICON_WARNING = '⚠️';
    const ICON_INFO = 'ℹ️';
    const ICON_TRACE = '↳';

    protected static  bool $enabled = false;


    public function __construct()
    {
        self::$enabled = config('logging.exception_enabled', false);
    }


    /**
     * 记录异常日志
     */
    public static function handle(Throwable $exception): void
    {


        if (!self::$enabled) return;

        Log::channel('exception')->error(self::formatException($exception));
    }

    /**
     * 格式化异常日志
     */
    protected static function formatException(Throwable $e): string
    {
        $request = Request::instance();
        $requestId = Str::uuid();
        $timestamp = now()->format('Y-m-d H:i:s.v');

        // 构建日志头部
        $logContent = self::buildHeader($e, $timestamp, $requestId);

        // 添加请求信息
        $logContent .= self::buildRequestInfo($request);

        // 添加异常基本信息
        $logContent .= self::buildExceptionInfo($e);

        // 添加简化堆栈
        $logContent .= self::buildStacktrace($e);

        // 添加日志尾部
        $logContent .= self::buildFooter();

        return self::wrapInBorder($logContent);
    }

    /**
     * 构建日志头部
     */
    private static function buildHeader(Throwable $e, string $timestamp, string $requestId): string
    {
        $header = self::BORDER_CHAR_V . " EXCEPTION LOG " . str_repeat(self::BORDER_CHAR_H, 103) . "\n";
        $header .= self::BORDER_CHAR_V . sprintf(
                " %s %-40s %s %-20s %s %-36s %s\n",
                self::ICON_ERROR,
                '[' . get_class($e) . ']',
                self::ICON_INFO,
                $timestamp,
                self::ICON_INFO,
                "ID: {$requestId}",
                self::BORDER_CHAR_V
            );

        return $header . self::BORDER_CHAR_V . str_repeat(self::BORDER_CHAR_H, 118) . self::BORDER_CHAR_V . "\n";
    }

    /**
     * 构建请求信息
     */
    private static function buildRequestInfo($request): string
    {
        if (!$request) return '';

        return self::formatBlock([
            'Request URL'    => $request->method() . ' ' . $request->fullUrl(),
            'Client IP'      => $request->ip(),
            'User Agent'     => $request->userAgent(),
            'Request Params' => json_encode($request->except(['password', 'token', 'api_key']))
        ], 'REQUEST INFO', self::ICON_INFO);
    }

    /**
     * 构建异常基本信息
     */
    private static function buildExceptionInfo(Throwable $e): string
    {
        return self::formatBlock([
            'Exception Code' => $e->getCode(),
            'Error Message'  => $e->getMessage(),
            'Location'       => self::shortenPath($e->getFile()) . ':' . $e->getLine(),
            'Environment'    => config('app.env'),
        ], 'EXCEPTION INFO', self::ICON_ERROR);
    }

    /**
     * 构建堆栈跟踪
     */
    private static function buildStacktrace(Throwable $e): string
    {
        $content = self::BORDER_CHAR_V . " STACK TRACE " . str_repeat(self::BORDER_CHAR_H, 105) . self::BORDER_CHAR_V . "\n";

        $stack = self::simplifyTrace($e->getTrace());
        foreach ($stack as $index => $frame) {
            $line = sprintf(
                "%s #%02d %s%s%s()",
                self::BORDER_CHAR_V,
                $index,
                $frame['class'] ?? '',
                $frame['type'] ?? '',
                $frame['function'] ?? ''
            );

            $fileLine = sprintf(
                " in %s:%d",
                self::shortenPath($frame['file'] ?? ''),
                $frame['line'] ?? 0
            );

            $content .= self::formatLine($line . $fileLine);
        }

        return $content;
    }

    /**
     * 构建日志尾部
     */
    private static function buildFooter(): string
    {
        return self::BORDER_CHAR_V . "\n" .
            self::BORDER_CHAR_V . " END OF LOG " . str_repeat(self::BORDER_CHAR_H, 106) . self::BORDER_CHAR_V . "\n";
    }

    /**
     * 简化堆栈跟踪
     */
    private static function simplifyTrace(array $trace): array
    {
        $simplified = [];
        $appPath = base_path();
        $maxDepth = 8;
        $currentDepth = 0;

        foreach ($trace as $frame) {
            if ($currentDepth >= $maxDepth) break;

            // 跳过vendor目录和内部函数
            if (isset($frame['file']) && str_contains($frame['file'], '/vendor/')) continue;
            if (isset($frame['function']) && in_array($frame['function'], ['call_user_func', 'call_user_func_array'])) continue;

            // 简化文件路径
            if (isset($frame['file'])) {
                $frame['file'] = self::shortenPath($frame['file']);
            }

            $simplified[] = $frame;
            $currentDepth++;
        }

        return $simplified;
    }

    /**
     * 缩短文件路径
     */
    private static function shortenPath(string $path): string
    {
        $base = base_path();
        $shortened = str_replace($base, '', $path);

        // 处理隐藏路径（如/home/user -> ~）
        if (str_starts_with($shortened, '/home/')) {
            $parts = explode('/', $shortened);
            if (count($parts) > 3) {
                $shortened = '/~/' . implode('/', array_slice($parts, 3));
            }
        }

        return ltrim($shortened, '/') ?: $path;
    }

    /**
     * 格式化信息块
     */
    private static function formatBlock(array $data, string $title, string $icon): string
    {
        $block = self::BORDER_CHAR_V . " {$icon} {$title} " . str_repeat(self::BORDER_CHAR_H, 112 - mb_strlen($title)) . self::BORDER_CHAR_V . "\n";

        foreach ($data as $key => $value) {
            $line = sprintf("%-15s: %s", $key, $value);
            $block .= self::formatLine($line);
        }

        return $block;
    }

    /**
     * 格式化单行日志
     */
    private static function formatLine(string $line): string
    {
        $line = self::BORDER_CHAR_V . " " . $line;
        $padding = self::MAX_LINE_WIDTH - mb_strlen($line) + 2;

        if ($padding > 0) {
            $line .= str_repeat(' ', $padding);
        }

        return $line . self::BORDER_CHAR_V . "\n";
    }

    /**
     * 添加边框
     */
    private static function wrapInBorder(string $content): string
    {
        $lines = explode("\n", trim($content));
        $maxWidth = self::MAX_LINE_WIDTH;

        // 构建顶部边框
        $topBorder = self::BORDER_CHAR_C . str_repeat(self::BORDER_CHAR_H, $maxWidth) . self::BORDER_CHAR_C . "\n";

        // 构建底部边框
        $bottomBorder = self::BORDER_CHAR_C . str_repeat(self::BORDER_CHAR_H, $maxWidth) . self::BORDER_CHAR_C;

        return $topBorder . implode("\n", $lines) . "\n" . $bottomBorder;
    }
}