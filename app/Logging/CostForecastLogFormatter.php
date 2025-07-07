<?php

namespace App\Logging;

use Monolog\Formatter\FormatterInterface;

class CostForecastLogFormatter implements FormatterInterface
{
    private const LINE_WIDTH = 100;
    private static $border;

    public function __construct()
    {
        self::$border = '*' . str_repeat('*', self::LINE_WIDTH) . '*';
    }

    public function format(array $record): string
    {
        try {
            $data = $record['context'] ?? [];
            $content = $this->buildLogContent($data);
            return $this->wrapWithBorder($content);
        } catch (\Throwable $e) {
            return self::$border . PHP_EOL
                . $this->createCenteredLine('日志格式化失败: ' . $e->getMessage())
                . PHP_EOL . self::$border . PHP_EOL;
        }
    }

    private function buildLogContent(array $data): string
    {
        $lines = [
            $this->createLeftAlignedLine('开始'),
            $this->createLabelLine('配送订单', $data['shipping_code'] ?? ''),
            $this->createLabelLine('物流产品', $data['logistics_product_code'] ?? ''),
            $this->createLeftAlignedLine(''),
            $this->createLeftAlignedLine(''),
            $this->createProcessSection($data['content'] ?? []),
            $this->createResultSection($data['forecast_cost'] ?? []),
            $this->createLeftAlignedLine(''),
            $this->createLeftAlignedLine('结束'),
        ];

        return implode(PHP_EOL, $lines);
    }



    private function createLabelLine(string $label, string $value): string
    {
        return $this->createLeftAlignedLine($label . ': ' . $value);
    }

    private function createCenteredLine(string $text): string
    {
        // 最大允许显示宽度（留2字符冗余）
        $maxDisplayWidth = self::LINE_WIDTH - 2;

        // 截断过长文本并添加省略号
        $truncatedText = mb_strimwidth(
            $text,
            0,
            $maxDisplayWidth,
            '...',
            'UTF-8'
        );

        // 计算实际显示宽度
        $textWidth = mb_strwidth($truncatedText, 'UTF-8');

        // 确保填充值不为负数
        $totalPadding = max(0, self::LINE_WIDTH - $textWidth);
        $leftPadding = max(0, floor($totalPadding / 2));
        $rightPadding = max(0, ceil($totalPadding / 2));

        // 构建带边框的行
        return '*' .
            str_repeat(' ', $leftPadding) .
            $truncatedText .
            str_repeat(' ', $rightPadding) .
            '*';
    }


    private function createProcessSection($content): string
    {
        $lines = [];
        $contentArray = is_array($content) ? $content : json_decode($content, true);

        // 流程标题
        $lines[] = $this->createLeftAlignedLine('流程: 计算流程');

        // 处理每个步骤
        foreach ((array)$contentArray as $item) {
            $lines[] = $this->formatProcessItem($item);
        }

        return implode(PHP_EOL, $lines);
    }

    private function formatProcessItem(string $item): string
    {
        // 处理层级缩进
        $indentMap = [
            '开始' => 2,
            '结束' => 2,
            '验证' => 4,
            '匹配' => 4,
            '计算' => 4,
            '附加费' => 2
        ];

        $indent = 2; // 默认缩进
        foreach ($indentMap as $prefix => $space) {
            if (str_starts_with($item, $prefix)) {
                $indent = $space;
                break;
            }
        }

        // 智能分割长文本
        $maxWidth = self::LINE_WIDTH - 2 - $indent - 3; // 保留...空间
        $formatted = mb_strimwidth($item, 0, $maxWidth, '...', 'UTF-8');

        return $this->createLeftAlignedLine(
            str_repeat(' ', $indent) . $formatted
        );
    }


    private function createResultSection($result): string
    {
        $lines = [$this->createLeftAlignedLine('结果: 计算结果')];
        $resultArray = is_array($result) ? $result : json_decode($result, true);

        foreach ($resultArray as $key => $value) {
            $lines = array_merge($lines,
                $this->formatJsonItem($key, $value)
            );
        }

        return implode(PHP_EOL, $lines);
    }


    private function formatJsonItem($key, $value): array
    {
        $lines = [];
        // 生成带标准缩进的JSON
        $json = json_encode([$key => $value],
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        // 错误处理
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [$this->createLeftAlignedLine('  [JSON格式化错误]')];
        }

        // 层级缩进转换
        $formattedJson = preg_replace_callback('/^(\s+)/m', function($matches) {
            $originalIndent = strlen($matches[1]) / 4; // 计算原始缩进层级
            return str_repeat('    ', $originalIndent + 1); // 每层+4空格
        }, $json);

        // 移除引号并处理换行
        $formattedJson = str_replace(['"', "\n"], ['', "\n"], $formattedJson);

        // 逐行处理
        foreach (explode("\n", $formattedJson) as $line) {
            $maxWidth = self::LINE_WIDTH - 6; // 60 - 2(边框) - 4(左边距)
            $processedLine = mb_strimwidth($line, 0, $maxWidth, '...', 'UTF-8');
            $lines[] = $this->createLeftAlignedLine('  ' . $processedLine);
        }

        return $lines;
    }

    private function createLeftAlignedLine(string $text): string
    {
        $maxContentWidth = self::LINE_WIDTH - 3; // 总宽度62 - 3("*  ")
        $truncated = mb_strimwidth($text, 0, $maxContentWidth, '...', 'UTF-8');
        $padded = str_pad($truncated, $maxContentWidth);
        return "*  " . $padded; // 注意最后不加星号
    }


    private function formatItem($data): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return str_replace(["\n", '"'], ["\n    ", ''], $json);
    }

    private function wrapWithBorder(string $content): string
    {
        return self::$border . PHP_EOL
            . $content . PHP_EOL
            . self::$border . PHP_EOL;
    }

    public function formatBatch(array $records): string
    {
        return array_reduce($records, function ($carry, $item) {
            return $carry . $this->format($item);
        }, '');
    }
}