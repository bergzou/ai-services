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
    const BORDER_CHAR_H = '-';   // 水平边框字符
    const BORDER_CHAR_V = '|';   // 垂直边框字符
    const BORDER_CHAR_C = '+';   // 角落连接字符

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
        $logContent = "*********\n";
        $logContent .= "*请求路由: {$request->method()} {$request->fullUrl()}\n";
        $logContent .= "*请求时间: " . self::getMicrotime() . "\n";
        $logContent .= "*请求参数: " . json_encode(array_merge(
                $request->all(),
                ['request_id' => self::$requestId]
            )) . "\n";
        $logContent .= "*执行流程\n";

        // 合并自动日志和手动日志（按时间排序）
        $allLogs = array_merge(self::$logStack, self::$manualLogs);
        usort($allLogs, function ($a, $b) {
            return strcmp($a['time'], $b['time']);
        });

        foreach ($allLogs as $entry) {
            $indent = str_repeat('    ', $entry['depth']);
            switch ($entry['type']) {
                case 'start':
                    $logContent .= self::formatLine(
                        "{$indent}执行方法【{$entry['method']}】",
                        self::MAX_LINE_WIDTH,
                        $indent
                    );

                    $paramsLine = "{$indent}   请求参数: " . json_encode($entry['params']);
                    $logContent .= self::formatLine($paramsLine, self::MAX_LINE_WIDTH, "{$indent}   ");

                    $timeLine = "{$indent}   请求时间: {$entry['time']}";
                    $logContent .= self::formatLine($timeLine, self::MAX_LINE_WIDTH, "{$indent}   ");
                    break;
                case 'end':
                    $resultLine = "{$indent}   响应结果: " . json_encode($entry['result']);
                    $logContent .= self::formatLine($resultLine, self::MAX_LINE_WIDTH, "{$indent}   ");

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

                    $timeLine = "{$indent}   响应时间: {$timeFormatted} ({$durationMark}{$duration})";
                    $logContent .= self::formatLine($timeLine, self::MAX_LINE_WIDTH, "{$indent}   ");
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
                    $messageLine = "{$indent}{$levelMark}手动日志: 【{$entry['message']}】";

                    // 添加关联数据（如果存在）
                    if ($entry['data'] !== null) {
                        $dataStr = json_encode($entry['data']);
                        $messageLine .= " 关联数据: {$dataStr}";
                    }
                    $logContent .= self::formatLine($messageLine, self::MAX_LINE_WIDTH, $indent);
                    $logContent .= self::formatLine($timeLine, self::MAX_LINE_WIDTH, "{$indent}   ");
                    break;
                case 'exception':
                case 'business_exception':

                    $logContent .= self::formatLine(
                        "{$indent}🚫 业务异常【{$entry['method']}】",
                        self::MAX_LINE_WIDTH,
                        $indent
                    );

                    $logContent .= self::formatLine(
                        "{$indent}   异常信息: {$entry['exception']['message']}",
                        self::MAX_LINE_WIDTH,
                        "{$indent}   "
                    );

                    // 如果有业务数据
                    if (!empty($entry['exception']['data'])) {
                        $dataStr = json_encode($entry['exception']['data']);
                        $logContent .= self::formatLine(
                            "{$indent}   业务数据: {$dataStr}",
                            self::MAX_LINE_WIDTH,
                            "{$indent}   "
                        );
                    }

                    $logContent .= self::formatLine(
                        "{$indent}   异常位置: {$entry['exception']['file']}:{$entry['exception']['line']}",
                        self::MAX_LINE_WIDTH,
                        "{$indent}   "
                    );

                    $logContent .= self::formatLine(
                        "{$indent}   异常时间: {$entry['time']} ({$entry['duration']})",
                        self::MAX_LINE_WIDTH,
                        "{$indent}   "
                    );
                    break;
            }
        }
        // 添加摘要信息
        $summary = self::generateSummary();
        $logContent .= "*\n";
        $logContent .= "*响应摘要: \n";
        $logContent .= self::formatLine("*   总耗时: {$summary['total_time']}ms", self::MAX_LINE_WIDTH, "*   ");
        $logContent .= self::formatLine("*   最长耗时: {$summary['longest_duration']}", self::MAX_LINE_WIDTH, "*   ");
        $logContent .= self::formatLine("*   请求ID: {$summary['request_id']}", self::MAX_LINE_WIDTH, "*   ");
        $logContent .= self::formatLine("*   手动日志: {$summary['manual_logs']}条", self::MAX_LINE_WIDTH, "*   ");
        $logContent .= "*响应时间: {$summary['end_time']}\n";
        $logContent .= "*******";
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
     * 格式化行内容（智能换行并保持缩进）
     */
    private static function formatLine(string $line, int $maxWidth, string $indent = ""): string
    {
        // 如果行长度小于等于最大宽度，直接返回
        $lineLength = mb_strlen($line);
        if ($lineLength <= $maxWidth) {
            return $line . "\n";
        }

        $result = "";
        $currentLine = "";
        $words = preg_split('/([\s,;:{}()\[\]])/u', $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $isFirstLine = true;

        foreach ($words as $word) {
            $wordLength = mb_strlen($word);
            $currentLineLength = mb_strlen($currentLine);

            // 检查单词是否包含可分割字符
            $isBreakable = preg_match('/^[\s,;:{}()\[\]]+$/', $word);

            // 计算可用宽度（考虑首行缩进）
            $availableWidth = $maxWidth - ($isFirstLine ? 0 : mb_strlen($indent));

            if ($currentLineLength + $wordLength <= $availableWidth) {
                // 单词适合当前行
                $currentLine .= $word;
            } else {
                if ($currentLine !== "") {
                    // 添加当前行到结果
                    $result .= ($isFirstLine ? $currentLine : $indent . $currentLine) . "\n";
                    $isFirstLine = false;
                    $currentLine = "";
                }

                // 处理超长单词
                if ($wordLength > $availableWidth && !$isBreakable) {
                    // 分割超长单词
                    $startPos = 0;
                    while ($startPos < $wordLength) {
                        $chunk = mb_substr($word, $startPos, $availableWidth);
                        $result .= ($isFirstLine ? $chunk : $indent . $chunk) . "\n";
                        $isFirstLine = false;
                        $startPos += mb_strlen($chunk);
                        $availableWidth = $maxWidth - mb_strlen($indent);
                    }
                } else {
                    $currentLine = $word;
                }
            }
        }

        // 添加最后一行
        if ($currentLine !== "") {
            $result .= ($isFirstLine ? $currentLine : $indent . $currentLine) . "\n";
        }

        return $result;
    }

    /**
     * 添加网格边框（去除右侧边框）
     */
    private static function addGridBorder(string $content): string
    {
        $lines = explode("\n", rtrim($content));
        $maxWidth = self::calculateMaxWidth($lines);

        // 顶部边框（无右侧边角）
        $borderLine = self::BORDER_CHAR_C . str_repeat(self::BORDER_CHAR_H, $maxWidth + 2);
        $gridContent = $borderLine . "\n";

        foreach ($lines as $line) {
            $line = rtrim($line);
            $gridLine = self::BORDER_CHAR_V . ' ' . $line;

            // 填充空格保持对齐
            $padding = $maxWidth - mb_strlen($line);
            if ($padding > 0) {
                $gridLine .= str_repeat(' ', $padding);
            }

            $gridContent .= $gridLine . "\n";
        }

        // 底部边框（无右侧边角）
        $gridContent .= $borderLine;
        return $gridContent;
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