<?php

namespace App\Logging;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class SqlLogger
{
    // 日志格式常量
    const BORDER_H = '═';
    const BORDER_V = '║';
    const BORDER_C = '╬';
    const MAX_WIDTH = 120;
    const SQL_KEYWORDS = ['SELECT', 'FROM', 'WHERE', 'JOIN', 'ORDER BY', 'GROUP BY', 'LIMIT', 'INSERT', 'UPDATE', 'DELETE'];

    // SQL格式化常量
    const MAX_LINE_WIDTH = 80;
    const INDENT_SIZE = 4;

    // 敏感字段列表
    const SENSITIVE_FIELDS = ['password', 'token', 'api_key', 'secret', 'credit_card'];

    // 请求级存储
    protected static $requestId;
    protected static $sqlLogs = [];
    protected static $totalTime = 0;
    protected static $queryCount = 0;

    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = config('logging.sql_enabled', false);

        // 注册请求结束处理器
        if ($this->enabled && empty(self::$requestId)) {
            $this->registerRequestTerminator();
        }
    }

    public function handle(QueryExecuted $query): void
    {
        if (!$this->enabled) return;

        // 确保请求ID已初始化
        if (empty(self::$requestId)) {
            self::$requestId = Str::uuid()->toString();
        }

        // 获取调用位置
        $caller = $this->getCaller();

        // 格式化 SQL
        $formattedSql = $this->formatSql($query->sql, $query->bindings);
        $prettySql = $this->prettyPrintSql($formattedSql);

        // 添加到请求日志
        self::$sqlLogs[] = [
            'sql' => $prettySql,
            'time' => $query->time . 'ms',
            'connection' => $query->connectionName,
            'caller' => $caller
        ];

        // 更新统计信息
        self::$totalTime += $query->time;
        self::$queryCount++;
    }

    /**
     * 注册请求结束处理器
     */
    protected function registerRequestTerminator(): void
    {
        // 使用应用终止事件记录所有SQL
        app()->terminating(function () {
            if (!empty(self::$sqlLogs)) {
                $logContent = $this->buildLogContent();
                Log::channel('sql')->debug($logContent);

                // 重置请求级数据
                self::$sqlLogs = [];
                self::$totalTime = 0;
                self::$queryCount = 0;
            }
        });
    }

    /**
     * 构建结构化日志内容（包含所有SQL）
     */
    protected function buildLogContent(): string
    {
        // 顶部边框
        $content = self::BORDER_C . str_repeat(self::BORDER_H, self::MAX_WIDTH - 2) . self::BORDER_C . "\n";

        // 标题行
        $title = " SQL Request [ID: " . self::$requestId . "] ";
        $titlePadding = self::MAX_WIDTH - mb_strlen($title) - 4;
        $content .= self::BORDER_V . $title . str_repeat(' ', $titlePadding) . self::BORDER_V . "\n";

        // 请求信息
        $request = Request::instance();
        $content .= self::BORDER_V . " Request: " . ($request ? $request->method() . ' ' . $request->fullUrl() : 'CLI') . "\n";
        $content .= self::BORDER_V . str_repeat(self::BORDER_H, self::MAX_WIDTH - 2) . self::BORDER_V . "\n";

        // 添加所有SQL查询
        foreach (self::$sqlLogs as $index => $log) {
            $content .= self::BORDER_V . " Query #" . ($index + 1) . "\n";
            $content .= self::BORDER_V . " Caller: " . $log['caller'] . "\n";
            $content .= self::BORDER_V . " Time: " . $log['time'] . "\n";
            $content .= self::BORDER_V . " Connection: " . $log['connection'] . "\n";

            $sqlLines = explode("\n", $log['sql']);
            foreach ($sqlLines as $line) {
                $content .= self::formatLine($line);
            }

            // 添加查询分隔线（最后一个查询除外）
            if ($index < count(self::$sqlLogs) - 1) {
                $content .= self::BORDER_V . " " . str_repeat('─', self::MAX_WIDTH - 4) . " \n";
            }
        }

        // 添加统计信息
        $content .= self::BORDER_V . str_repeat(self::BORDER_H, self::MAX_WIDTH - 2) . self::BORDER_V . "\n";
        $content .= self::formatLine("Total Queries: " . self::$queryCount);
        $content .= self::formatLine("Total SQL Time: " . number_format(self::$totalTime, 2) . "ms");
        $content .= self::formatLine("Request Duration: " . number_format(microtime(true) - LARAVEL_START, 3) . "s");

        // 底部边框
        $content .= self::BORDER_C . str_repeat(self::BORDER_H, self::MAX_WIDTH - 2) . self::BORDER_C;

        return $content;
    }

    /**
     * 格式化单行日志内容
     */
    protected static function formatLine(string $line): string
    {
        $line = self::BORDER_V . "   " . $line;
        $padding = self::MAX_WIDTH - mb_strlen($line) - 1;

        if ($padding > 0) {
            $line .= str_repeat(' ', $padding);
        }

        return $line . self::BORDER_V . "\n";
    }

    /**
     * 美化 SQL 格式（添加缩进和换行）
     */
    protected function prettyPrintSql(string $sql): string
    {
        $formatted = $sql;

        // 添加换行和缩进
        foreach (self::SQL_KEYWORDS as $keyword) {
            $regex = '/\b' . preg_quote($keyword, '/') . '\b/i';
            $formatted = preg_replace($regex, "\n" . str_repeat(' ', self::INDENT_SIZE) . '$0', $formatted);
        }

        // 移除多余的空白
        $formatted = preg_replace('/\s+/', ' ', $formatted);

        // 添加子句缩进
        $formatted = preg_replace('/(\s(ON|AND|OR|SET)\s)/i', "\n" . str_repeat(' ', self::INDENT_SIZE * 2) . '$1', $formatted);

        // 按行分割并进行智能换行处理
        $lines = explode("\n", trim($formatted));
        $resultLines = [];

        foreach ($lines as $line) {
            $resultLines = array_merge(
                $resultLines,
                $this->wrapLongLine($line)
            );
        }

        return implode("\n", $resultLines);
    }

    /**
     * 智能换行处理（不分割单词）
     */
    protected function wrapLongLine(string $line): array
    {
        // 如果行长度未超过限制，直接返回
        if (mb_strlen($line) <= self::MAX_LINE_WIDTH) {
            return [$line];
        }

        // 提取基础缩进
        preg_match('/^(\s*)/', $line, $matches);
        $baseIndent = $matches[1] ?? '';
        $content = mb_substr($line, mb_strlen($baseIndent));

        // 计算可用宽度（总宽度减去缩进）
        $availableWidth = self::MAX_LINE_WIDTH - mb_strlen($baseIndent);
        $wrappedLines = [];
        $currentLine = '';

        // 按单词分割处理
        $words = preg_split('/(\s+)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($words as $word) {
            // 如果当前行加上新单词不超过宽度，则添加到当前行
            if (mb_strlen($currentLine) + mb_strlen($word) <= $availableWidth) {
                $currentLine .= $word;
            }
            // 如果单词本身超过宽度，强制分割
            elseif (mb_strlen($word) > $availableWidth) {
                if (!empty($currentLine)) {
                    $wrappedLines[] = $baseIndent . $currentLine;
                    $currentLine = '';
                }

                // 分割超长单词
                $parts = $this->splitLongWord($word, $availableWidth);
                foreach ($parts as $index => $part) {
                    $wrappedLines[] = $baseIndent . ($index > 0 ? str_repeat(' ', self::INDENT_SIZE) : '') . $part;
                }
            }
            // 否则换行并添加缩进
            else {
                $wrappedLines[] = $baseIndent . $currentLine;
                $currentLine = $word;
                // 后续行增加额外缩进
                $baseIndent = str_repeat(' ', self::INDENT_SIZE);
                $availableWidth = self::MAX_LINE_WIDTH - mb_strlen($baseIndent);
            }
        }

        // 添加最后一行
        if (!empty($currentLine)) {
            $wrappedLines[] = $baseIndent . $currentLine;
        }

        return $wrappedLines;
    }

    /**
     * 分割超长单词（当单个单词超过行宽时）
     */
    protected function splitLongWord(string $word, int $maxWidth): array
    {
        $parts = [];
        $start = 0;
        $length = mb_strlen($word);

        while ($start < $length) {
            $part = mb_substr($word, $start, $maxWidth);
            $parts[] = $part;
            $start += $maxWidth;
        }

        return $parts;
    }

    /**
     * 格式化 SQL 语句
     */
    protected function formatSql(string $sql, array $bindings): string
    {
        $isSensitive = $this->isSensitiveQuery($sql);

        $processedBindings = array_map(function ($value) use ($isSensitive) {
            // 安全处理二进制数据
            if (is_resource($value)) return '<binary>';

            // 处理字符串（限制长度）
            if (is_string($value)) {
                // 如果是敏感查询，直接返回屏蔽值
                if ($isSensitive) return "'*****'";

                return "'" . Str::limit($value, 100) . "'";
            }

            // 处理其他类型
            return match (true) {
                is_bool($value) => $value ? 'true' : 'false',
                is_null($value) => 'null',
                is_array($value) => json_encode($value),
                is_object($value) => method_exists($value, 'toSql') ? $value->toSql() : get_class($value),
                default => $value,
            };
        }, $bindings);

        return vsprintf(str_replace('?', '%s', $sql), $processedBindings);
    }

    /**
     * 检查是否包含敏感字段
     */
    protected function isSensitiveQuery(string $sql): bool
    {
        foreach (self::SENSITIVE_FIELDS as $field) {
            if (stripos($sql, $field) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取调用 SQL 的代码位置 (修复版本)
     */
    protected function getCaller(): string
    {
        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);
        $appPath = base_path();
        $skipClasses = [
            __CLASS__,
            'Illuminate\\Database\\Connection',
            'Illuminate\\Database\\ConnectionInterface',
            'Illuminate\\Database\\Query\\Builder',
            'Illuminate\\Database\\Eloquent\\Builder',
            'Illuminate\\Database\\Eloquent\\Model'
        ];

        foreach ($traces as $trace) {
            if (!isset($trace['file'])) continue;

            $file = $trace['file'];
            $class = $trace['class'] ?? '';

            // 跳过框架、包文件以及数据库相关类
            if (str_contains($file, '/vendor/') ||
                str_contains($file, '/laravel/') ||
                str_contains($file, '/illuminate/') ||
                in_array($class, $skipClasses)) {
                continue;
            }

            // 简化文件路径
            $shortFile = str_replace($appPath, '', $file);
            $shortFile = ltrim($shortFile, '/');

            return $shortFile . ':' . ($trace['line'] ?? '?');
        }

        // 备用方案：尝试找到任何应用代码
        foreach ($traces as $trace) {
            if (!isset($trace['file'])) continue;

            $file = $trace['file'];

            // 仅接受应用目录内的文件
            if (str_contains($file, $appPath) && !str_contains($file, '/vendor/')) {
                $shortFile = str_replace($appPath, '', $file);
                $shortFile = ltrim($shortFile, '/');
                return $shortFile . ':' . ($trace['line'] ?? '?');
            }
        }

        return 'unknown';
    }
}