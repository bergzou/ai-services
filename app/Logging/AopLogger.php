<?php

namespace App\Logging;

/**
 * AOP日志记录器，用于记录方法调用链、耗时统计和异常追踪
 *
 * 功能特性：
 * 1. 自动记录方法入参/出参
 * 2. 耗时统计与性能预警
 * 3. 异常调用栈追踪
 * 4. 结构化日志输出
 * 5. 手动日志记录
 */

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;
use Throwable;

class AopLogger
{
    private static $logStack = [];
    private static $depth = 0;
    private static $requestId;
    private static $manualLogs = [];
    private static $inProgress = [];

    // 配置常量
    const MAX_LINE_WIDTH = 200;  // 单行最大字符数
    // 优化点1：使用更美观的分割符号
    const BORDER_CHAR_H = '═';   // 水平边框字符
    const BORDER_CHAR_V = '║';   // 垂直边框字符
    const BORDER_CHAR_C = '╬';   // 角落连接字符

    // 日志级别表情符号
    const EMOJI_INFO = '📝';
    const EMOJI_DEBUG = '🐞';
    const EMOJI_WARNING = '⚠️';
    const EMOJI_ERROR = '❌';

    // 耗时阈值配置（毫秒）
    const DURATION_WARNING = 100; // >100ms 显示警告
    const DURATION_NOTICE = 50;   // >50ms 显示注意

    /**
     * 记录方法调用前日志
     * @param object $object 被调用的对象实例
     * @param string $method 调用的方法名称
     * @param array $arguments 方法调用参数
     */
    public static function logBefore($object, string $method, array $arguments)
    {
        $key = spl_object_hash($object) . $method;
        if (isset(self::$inProgress[$key])) {
            return;
        }
        self::$inProgress[$key] = true;

        if (empty(self::$requestId)) {
            self::$requestId = (string) Str::uuid();
        }

        $className = get_class($object);
        $fullMethod = "{$className}::{$method}";

        self::$depth++;
        $startTime = microtime(true);
        $startFormatted = self::getMicrotime();

        self::$logStack[] = [
            'type' => 'start',
            'method' => $fullMethod,
            'params' => $arguments,
            'time' => $startFormatted,
            'depth' => self::$depth,
            'start' => $startTime
        ];
    }

    /**
     * 记录方法调用后日志
     * @param object $object 被调用的对象实例
     * @param string $method 调用的方法名称
     * @param mixed $result 方法返回结果
     */
    public static function logAfter($object, string $method, $result)
    {
        $key = spl_object_hash($object) . $method;
        unset(self::$inProgress[$key]);

        $className = get_class($object);
        $fullMethod = "{$className}::{$method}";

        $endTime = microtime(true);
        $endFormatted = self::getMicrotime();

        // 查找对应的开始记录
        $startRecord = null;
        foreach (array_reverse(self::$logStack) as $record) {
            if ($record['type'] === 'start' && $record['method'] === $fullMethod) {
                $startRecord = $record;
                break;
            }
        }

        $rawDuration = $startRecord
            ? ($endTime - $startRecord['start']) * 1000
            : 0;

        self::$logStack[] = [
            'type' => 'end',
            'method' => $fullMethod,
            'result' => self::formatResult($result),
            'time' => $endFormatted,
            'depth' => self::$depth,
            'duration' => number_format($rawDuration, 2) . 'ms',
            'raw_duration' => $rawDuration
        ];

        self::$depth--;

        // 如果是顶层方法，写入日志
        if (self::$depth === 0) {
            self::writeLog();
            self::$logStack = [];
            self::$manualLogs = [];
            self::$requestId = null;
        }
    }

    /**
     * 手动添加业务日志
     * @param string $message 日志消息
     * @param mixed $data 关联数据
     * @param string $level 日志级别（info|debug|warning|error）
     */
    public static function manualLog(string $message, $data = null, string $level = 'info')
    {
        if (empty(self::$requestId)) {
            self::$requestId = (string) Str::uuid();
        }

        self::$manualLogs[] = [
            'type' => 'manual',
            'message' => $message,
            'data' => $data,
            'time' => self::getMicrotime(),
            'depth' => self::$depth,
            'level' => $level
        ];
    }

    /**
     * 格式化方法返回结果
     * @param mixed $result 原始返回结果
     * @return array|string 格式化后的结果
     * */
    private static function formatResult($result)
    {
        if (is_object($result)) {
            if (method_exists($result, 'toArray')) {
                return $result->toArray();
            }
            return get_class($result);
        }

        if (is_array($result)) {
            return $result;
        }

        return $result;
    }

    /**
     * 获取毫秒级时间
     */
    private static function getMicrotime(): string
    {
        $microtime = microtime(true);
        $seconds = floor($microtime);
        $milliseconds = sprintf("%03d", ($microtime - $seconds) * 1000);
        return date('Y-m-d H:i:s', $seconds) . '.' . $milliseconds;
    }

    /**
     * 写入日志
     */
    private static function writeLog()
    {
        $request = Request::instance();
        if (!$request) return;

        // 生成日志内容
        $logContent = self::generateLogContent();

        // 添加网格边框
        $finalLogContent = self::addGridBorder($logContent);

        // 写入日志
        Log::channel('aop')->info($finalLogContent);
    }

    /**
     * 生成结构化日志内容
     * @return string 格式化后的日志内容，包含以下部分：
     *                - 请求基本信息（路由、时间、参数）
     *                - 执行流程（方法调用链、参数、结果、耗时）
     *                - 手动日志记录
     *                - 异常信息（如有）
     *                - 响应摘要（总耗时、最长耗时等）
     */
    private static function generateLogContent(): string
    {
        $request = Request::instance();

        // 计算动态宽度
        $headerWidth = 118;

        // 优化点1：使用新的分割符号
        $logContent = self::BORDER_CHAR_V . " REQUEST ROUTE " . str_repeat(self::BORDER_CHAR_H, $headerWidth - 15) . self::BORDER_CHAR_V . "\n";

        // 修复点：避免负数的 str_repeat()
        $url = $request->method() . ' ' . $request->fullUrl();
        $urlLine = sprintf(" %-20s %s", 'URL:', $url);
        $urlPadding = max(0, $headerWidth - mb_strlen($urlLine) - 2); // 确保非负
        $logContent .= self::BORDER_CHAR_V . $urlLine . str_repeat(' ', $urlPadding) . self::BORDER_CHAR_V . "\n";

        $time = self::getMicrotime();
        $timeLine = sprintf(" %-20s %s", 'Time:', $time);
        $timePadding = max(0, $headerWidth - mb_strlen($timeLine) - 2); // 确保非负
        $logContent .= self::BORDER_CHAR_V . $timeLine . str_repeat(' ', $timePadding) . self::BORDER_CHAR_V . "\n";

        $params = json_encode(array_merge(
            $request->all(),
            ['request_id' => self::$requestId]
        ));
        $paramsLine = sprintf(" %-20s %s", 'Parameters:', $params);
        $paramsPadding = max(0, $headerWidth - mb_strlen($paramsLine) - 2); // 确保非负
        $logContent .= self::BORDER_CHAR_V . $paramsLine . str_repeat(' ', $paramsPadding) . self::BORDER_CHAR_V . "\n";

        $logContent .= self::BORDER_CHAR_V . str_repeat(self::BORDER_CHAR_H, $headerWidth) . self::BORDER_CHAR_V . "\n";
        $logContent .= self::BORDER_CHAR_V . " EXECUTION FLOW " . str_repeat(self::BORDER_CHAR_H, $headerWidth - 15) . self::BORDER_CHAR_V . "\n";

        // 合并自动日志和手动日志（按时间排序）
        $allLogs = array_merge(self::$logStack, self::$manualLogs);
        usort($allLogs, function ($a, $b) {
            return strcmp($a['time'], $b['time']);
        });

        foreach ($allLogs as $entry) {
            $indent = str_repeat('    ', $entry['depth']);
            switch ($entry['type']) {
                case 'start':
                    $logContent .= self::BORDER_CHAR_V . " " . sprintf(
                            "🚀 %s【%s】",
                            $indent,
                            $entry['method']
                        ) . str_repeat(' ', $headerWidth - mb_strlen($indent . $entry['method']) - 3) . self::BORDER_CHAR_V . "\n";

                    $paramsLine = "{$indent}↳ Params: " . json_encode($entry['params']);
                    $logContent .= self::formatLine($paramsLine);

                    $timeLine = "{$indent}↳ Time: {$entry['time']}";
                    $logContent .= self::formatLine($timeLine);
                    break;
                case 'end':
                    $resultLine = "{$indent}↳ Result: " . json_encode($entry['result']);
                    $logContent .= self::formatLine($resultLine);

                    $duration = $entry['duration'];
                    $timeFormatted = $entry['time'];

                    // 根据耗时设置样式标记
                    $durationMark = '';
                    if ($entry['raw_duration'] > self::DURATION_WARNING) {
                        $durationMark = self::EMOJI_WARNING . " ";
                        $timeFormatted = ">>> {$timeFormatted} <<<";
                    } elseif ($entry['raw_duration'] > self::DURATION_NOTICE) {
                        $durationMark = "❗ ";
                        $timeFormatted = ">> {$timeFormatted} <<";
                    }

                    $timeLine = "{$indent}↳ End: {$timeFormatted} ({$durationMark}{$duration})";
                    $logContent .= self::formatLine($timeLine);
                    break;
                case 'manual':
                    // 获取对应的表情符号
                    $levelMark = '';
                    switch ($entry['level']) {
                        case 'warning':
                            $levelMark = self::EMOJI_WARNING . ' ';
                            break;
                        case 'error':
                            $levelMark = self::EMOJI_ERROR . ' ';
                            break;
                        case 'debug':
                            $levelMark = self::EMOJI_DEBUG . ' ';
                            break;
                        default:
                            $levelMark = self::EMOJI_INFO . ' ';
                    }

                    // 手动日志基础行
                    $messageLine = "{$indent}{$levelMark}Manual: 【{$entry['message']}】";

                    // 添加关联数据（如果存在）
                    if ($entry['data'] !== null) {
                        $dataStr = json_encode($entry['data']);
                        $messageLine .= " Data: {$dataStr}";
                    }
                    $logContent .= self::formatLine($messageLine);
                    break;
                case 'exception':
                case 'business_exception':

                    $logContent .= self::formatLine(
                        "{$indent}🚫 Exception【{$entry['method']}】"
                    );

                    $logContent .= self::formatLine(
                        "{$indent}↳ Message: {$entry['exception']['message']}"
                    );

                    // 如果有业务数据
                    if (!empty($entry['exception']['data'])) {
                        $dataStr = json_encode($entry['exception']['data']);
                        $logContent .= self::formatLine(
                            "{$indent}↳ Data: {$dataStr}"
                        );
                    }

                    // 优化点2：使用更简洁的堆栈格式
                    $logContent .= self::formatLine(
                        "{$indent}↳ Location: " . self::shortenPath($entry['exception']['file']) .
                        ":{$entry['exception']['line']}"
                    );

                    $logContent .= self::formatLine(
                        "{$indent}↳ Time: {$entry['time']} ({$entry['duration']})"
                    );
                    break;
            }
        }

        // 添加摘要信息
        $summary = self::generateSummary();
        $logContent .= self::BORDER_CHAR_V . str_repeat(self::BORDER_CHAR_H, $headerWidth) . self::BORDER_CHAR_V . "\n";
        $logContent .= self::BORDER_CHAR_V . " RESPONSE SUMMARY " . str_repeat(self::BORDER_CHAR_H, $headerWidth - 17) . self::BORDER_CHAR_V . "\n";
        $logContent .= self::formatLine(" Total time: {$summary['total_time']}ms");
        $logContent .= self::formatLine(" Longest duration: {$summary['longest_duration']}");
        $logContent .= self::formatLine(" Request ID: {$summary['request_id']}");
        $logContent .= self::formatLine(" Manual logs: {$summary['manual_logs']}");
        $logContent .= self::BORDER_CHAR_V . " End time: {$summary['end_time']} " . str_repeat(' ', $headerWidth - mb_strlen(" End time: {$summary['end_time']} ") - 1) . self::BORDER_CHAR_V . "\n";
        $logContent .= self::BORDER_CHAR_V . str_repeat(self::BORDER_CHAR_H, $headerWidth) . self::BORDER_CHAR_V . "\n";
        return $logContent;
    }

    /**
     * 异常日志分发器：根据异常类型调用对应的日志记录方法
     * @param object $object 发生异常的对象实例
     * @param string $method 异常发生的方法名称
     * @param Throwable $e 捕获到的异常实例
     * @note 注意：当前代码存在递归调用风险（else分支调用自身）
     */
    public static function logException(object $object, string $method, Throwable $e){
        // 检查是否为业务异常（BusinessException 类型）
        if ($e instanceof BusinessException){
            // 调用业务异常日志记录方法
            self::logBusinessException($object, $method, $e);
        }else{
            // 非业务异常：此处存在逻辑错误，会递归调用自身（方法名与调用名相同）
            // 预期应为调用系统异常日志记录方法（如 self::logSystemException）
            self::logThrowableException($object, $method, $e);
        }
    }

    /**
     * 记录业务异常日志
     * @param object $object 发生异常的对象实例
     * @param string $method 异常发生的方法名称
     * @param BusinessException $e 业务异常实例
     */
    public static function logBusinessException($object, string $method, BusinessException $e)
    {
        $key = spl_object_hash($object) . $method;
        unset(self::$inProgress[$key]);

        if (empty(self::$requestId)) {
            self::$requestId = (string) Str::uuid();
        }

        $className = get_class($object);
        $fullMethod = "{$className}::{$method}";

        $endTime = microtime(true);
        $endFormatted = self::getMicrotime();

        // 查找对应的开始记录
        $startRecord = null;
        foreach (array_reverse(self::$logStack) as $record) {
            if ($record['type'] === 'start' && $record['method'] === $fullMethod) {
                $startRecord = $record;
                break;
            }
        }

        $rawDuration = $startRecord
            ? ($endTime - $startRecord['start']) * 1000
            : 0;

        // 记录业务异常信息
        self::$logStack[] = [
            'type' => 'business_exception',
            'method' => $fullMethod,
            'exception' => [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => method_exists($e, 'getData') ? $e->getData() : null,
            ],
            'time' => $endFormatted,
            'depth' => self::$depth,
            'duration' => number_format($rawDuration, 2) . 'ms',
            'raw_duration' => $rawDuration
        ];

        // 关键: 减少深度
        self::$depth--;

        // 如果是顶层方法，写入日志
        if (self::$depth === 0) {
            self::writeLog();
            self::$logStack = [];
            self::$manualLogs = [];
            self::$requestId = null;
        }
    }

    /**
     * 记录系统异常日志
     * @param object $object 发生异常的对象实例
     * @param string $method 异常发生的方法名称
     * @param Throwable $e 异常实例
     */
    public static function logThrowableException($object, string $method, Throwable $e)
    {
        $key = spl_object_hash($object) . $method;
        unset(self::$inProgress[$key]);

        if (empty(self::$requestId)) {
            self::$requestId = (string) Str::uuid();
        }

        $className = get_class($object);
        $fullMethod = "{$className}::{$method}";

        $endTime = microtime(true);
        $endFormatted = self::getMicrotime();

        // 查找对应的开始记录
        $startRecord = null;
        foreach (array_reverse(self::$logStack) as $record) {
            if ($record['type'] === 'start' && $record['method'] === $fullMethod) {
                $startRecord = $record;
                break;
            }
        }

        $rawDuration = $startRecord
            ? ($endTime - $startRecord['start']) * 1000
            : 0;

        // 记录业务异常信息
        self::$logStack[] = [
            'type' => 'exception',
            'method' => $fullMethod,
            'exception' => [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => method_exists($e, 'getData') ? $e->getData() : null,
            ],
            'time' => $endFormatted,
            'depth' => self::$depth,
            'duration' => number_format($rawDuration, 2) . 'ms',
            'raw_duration' => $rawDuration
        ];

        // 关键: 减少深度
        self::$depth--;

        // 如果是顶层方法，写入日志
        if (self::$depth === 0) {
            self::writeLog();
            self::$logStack = [];
            self::$manualLogs = [];
            self::$requestId = null;
        }
    }

    /**
     * 生成响应摘要
     */
    private static function generateSummary(): array
    {
        $totalTime = 0;
        $longestDuration = 0;
        $longestMethod = '';

        foreach (self::$logStack as $entry) {
            if ($entry['type'] === 'end' && isset($entry['raw_duration'])) {
                $totalTime += $entry['raw_duration'];

                if ($entry['raw_duration'] > $longestDuration) {
                    $longestDuration = $entry['raw_duration'];
                    $longestMethod = $entry['method'];
                }
            }
        }

        return [
            'total_time' => number_format($totalTime, 2),
            'longest_duration' => number_format($longestDuration, 2) . "ms (方法: {$longestMethod})",
            'request_id' => self::$requestId,
            'manual_logs' => count(self::$manualLogs),
            'end_time' => self::getMicrotime()
        ];
    }

    /**
     * 优化点2：路径缩短方法
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
     * 格式化单行日志
     */
    private static function formatLine(string $line): string
    {
        $maxContentWidth = 117; // 最大内容宽度（120 - 3个边框/空格字符）
        $lines = [];
        $offset = 0;
        $length = mb_strlen($line, 'UTF-8');

        // 分割长行为多行
        while ($offset < $length) {
            $chunk = mb_substr($line, $offset, $maxContentWidth, 'UTF-8');
            $offset += mb_strlen($chunk, 'UTF-8');
            $lines[] = $chunk;
        }

        $formatted = '';
        foreach ($lines as $chunk) {
            $formattedLine = self::BORDER_CHAR_V . " " . $chunk;
            $currentLength = mb_strlen($formattedLine, 'UTF-8');
            $padding = 119 - $currentLength; // 计算需要填充的空格数

            if ($padding > 0) {
                $formattedLine .= str_repeat(' ', $padding);
            }
            $formattedLine .= self::BORDER_CHAR_V . "\n";
            $formatted .= $formattedLine;
        }

        return $formatted;
    }

    /**
     * 添加网格边框
     */
    private static function addGridBorder(string $content): string
    {
        $lines = explode("\n", trim($content));
        $maxWidth = self::MAX_LINE_WIDTH;

        // 构建顶部边框
        $topBorder = self::BORDER_CHAR_C . str_repeat(self::BORDER_CHAR_H, $maxWidth) . self::BORDER_CHAR_C . "\n";

        // 构建底部边框
        $bottomBorder = self::BORDER_CHAR_C . str_repeat(self::BORDER_CHAR_H, $maxWidth) . self::BORDER_CHAR_C;

        return $topBorder . implode("\n", $lines) . "\n" . $bottomBorder;
    }

    /**
     * 计算最大行宽（限制在MAX_LINE_WIDTH内）
     */
    private static function calculateMaxWidth(array $lines): int
    {
        $maxWidth = 0;
        foreach ($lines as $line) {
            $line = rtrim($line);
            $lineLength = mb_strlen($line);
            if ($lineLength > $maxWidth) {
                $maxWidth = min($lineLength, self::MAX_LINE_WIDTH);
            }
        }
        return $maxWidth;
    }
}