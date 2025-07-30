<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

class GenerateEnumsFile extends Command
{
    protected $signature = 'enum:generate
                            {--tables= : 指定表名（多个用逗号分隔，不指定则生成所有表）}
                            {--prefix= : 要移除的表前缀}
                            {--output=app/Enums : 输出目录}
                            {--connection= : 数据库连接名称（默认使用config/database.php的default连接）}
                            {--force : 强制覆盖已存在的枚举文件}
                            {--lang-start=1 : 多语言编码起始前缀（1-9）}
                            {--lang-file=enums : 多语言文件名（不含扩展名）}';

    protected $description = '从数据库表结构生成枚举文件（支持多种注释格式）';

    // 存储短语到编码的映射 [短语 => 编码]
    private $phraseToCodeMap = [];

    public function handle()
    {
        // 获取命令选项参数
        $connectionName = $this->option('connection') ?? Config::get('database.default');
        $prefix = $this->option('prefix');
        $outputDir = $this->option('output');
        $force = $this->option('force');

        // 设置当前数据库连接
        Config::set('database.default', $connectionName);
        $connection = DB::connection($connectionName);

        // 获取需要处理的表列表
        $tables = $this->getTables($connection);

        if (empty($tables)) {
            $this->error("数据库中没有找到任何表");
            return 1;
        }

        // 确保输出目录存在
        File::ensureDirectoryExists($outputDir);

        // 第一阶段：收集所有多语言短语
        $phrases = [];
        $this->info("收集多语言短语...");
        foreach ($tables as $table) {
            // 获取表字段元数据
            $columns = $this->getTableColumns($connection, $table);

            foreach ($columns as $field => $info) {
                // 只处理整数类型字段
                $integerTypes = ['tinyint', 'smallint', 'integer', 'int', 'bigint', 'boolean'];
                if (!in_array($info['type'], $integerTypes)) continue;
                if (empty($info['comment'])) continue;

                // 解析注释
                $parsedComment = $this->parseEnumComment($info['comment']);
                if (empty($parsedComment['items'])) continue;

                foreach ($parsedComment['items'] as $item) {
                    $phrases[] = $item['description'];
                }
            }
        }

        // 去重短语
        $phrases = array_unique($phrases);

        if (empty($phrases)) {
            $this->info("没有需要生成的多语言条目");
        } else {
            // 生成多语言文件（先于枚举文件生成）
            $this->generateLangFiles($phrases);
        }

        // 第二阶段：生成枚举文件（使用多语言编码）
        $generatedCount = 0;
        $skippedCount = 0;
        $noValidFieldsCount = 0;

        foreach ($tables as $table) {
            // 生成枚举类名
            $className = 'Enum' . $this->generateClassName($table, $prefix);
            $filePath = $outputDir . '/' . $className . '.php';

            // 检查文件是否存在且未强制覆盖时跳过
            if (file_exists($filePath)) {
                if (!$force) {
                    $this->line("枚举文件已存在: {$className} ({$filePath}) - 使用 --force 覆盖");
                    $skippedCount++;
                    continue;
                } else {
                    // 强制覆盖时删除旧文件
                    File::delete($filePath);
                }
            }

            // 获取表字段元数据
            $columns = $this->getTableColumns($connection, $table);

            // 生成枚举文件内容
            $enumContent = $this->generateEnumContent($className, $columns);

            // 如果内容为空（没有有效字段），则跳过
            if (empty(trim($enumContent))) {
                $this->line("跳过 {$table} 表：未找到有效的枚举字段");
                $noValidFieldsCount++;
                continue;
            }

            // 写入文件
            if (File::put($filePath, $enumContent) !== false) {
                $action = $force ? '覆盖' : '创建';
                $this->info("枚举文件已{$action}: {$className} ({$filePath})");
                $generatedCount++;
            } else {
                $this->error("枚举文件生成失败: {$className} ({$filePath})");
            }
        }

        // 输出统计结果
        $this->line("<fg=green>成功生成 {$generatedCount} 个枚举文件!</>");
        if ($skippedCount > 0) {
            $this->line("<fg=yellow>跳过 {$skippedCount} 个已存在的枚举文件 (使用 --force 覆盖)</>");
        }
        if ($noValidFieldsCount > 0) {
            $this->line("<fg=blue>跳过 {$noValidFieldsCount} 个没有有效枚举字段的表</>");
        }

        return 0;
    }

    /**
     * 获取需要处理的表列表
     * @param \Illuminate\Database\Connection $connection 数据库连接实例
     * @return array 表名数组
     */
    private function getTables($connection): array
    {
        // 如果指定了--tables选项，直接返回指定表
        if ($specifiedTables = $this->option('tables')) {
            return array_map('trim', explode(',', $specifiedTables));
        }

        // 未指定时通过SchemaManager获取数据库所有表名
        $schemaManager = $this->getSchemaManager($connection);
        return $schemaManager->listTableNames();
    }

    /**
     * 获取数据库模式管理器
     * @param \Illuminate\Database\Connection $connection
     * @return AbstractSchemaManager
     */
    private function getSchemaManager($connection): AbstractSchemaManager
    {
        $doctrineConnection = $connection->getDoctrineConnection();
        return $doctrineConnection->createSchemaManager();
    }

    /**
     * 获取表字段元数据
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     * @return array
     */
    private function getTableColumns($connection, $table): array
    {
        $schemaManager = $this->getSchemaManager($connection);
        $columns = $schemaManager->listTableColumns($table);

        $result = [];
        foreach ($columns as $column) {
            $result[$column->getName()] = [
                'type' => $column->getType()->getName(),
                'comment' => $column->getComment() ?? '',
            ];
        }

        return $result;
    }

    /**
     * 生成类名（基于表名处理前缀和单复数）
     * @param string $tableName
     * @param string|null $prefix
     * @return string
     */
    private function generateClassName(string $tableName, ?string $prefix): string
    {
        // 移除表前缀
        $name = $prefix ? preg_replace("/^{$prefix}_?/", '', $tableName) : $tableName;

        return Str::studly($name);
    }

    /**
     * 生成枚举文件内容
     * @param string $className
     * @param array $columns
     * @return string
     */
    private function generateEnumContent(string $className, array $columns): string
    {
        $enumContent = "<?php\n\n";
        $enumContent .= "namespace App\\Enums;\n\n";
        $enumContent .= "class {$className}\n{\n";

        $blocks = []; // 存储每个字段的代码块
        $hasValidFields = false;

        foreach ($columns as $field => $info) {
            // 处理所有整数类型字段
            $integerTypes = ['tinyint', 'smallint', 'integer', 'int', 'bigint', 'boolean'];

            if (!in_array($info['type'], $integerTypes)) {
                if ($this->getOutput()->isVerbose()) {
                    $this->line("字段 {$field} 类型 [{$info['type']}] 不是整数类型，跳过");
                }
                continue;
            }

            $comment = $info['comment'];
            if (empty($comment)) {
                if ($this->getOutput()->isVerbose()) {
                    $this->line("字段 {$field} 无注释，跳过");
                }
                continue;
            }

            // 解析注释
            $parsedComment = $this->parseEnumComment($comment);
            if (empty($parsedComment['items'])) {
                if ($this->getOutput()->isVerbose()) {
                    $this->line("字段 {$field} 的注释无法解析，跳过。注释内容：{$comment}");
                }
                continue;
            }

            // 提取字段描述（注释中冒号前的部分）
            $fieldDescription = $parsedComment['description'] ?? '';
            if (empty($fieldDescription)) {
                $fieldDescription = $field;
            }

            $blockContent = "\n    # {$fieldDescription}\n";

            $map = [];
            foreach ($parsedComment['items'] as $item) {
                $value = $item['value'];
                $description = $item['description'];
                $constName = Str::upper(Str::snake($field)) . '_' . $value;

                $blockContent .= "    const {$constName} = {$value}; // {$description}\n";

                // 查找描述对应的多语言编码
                $code = $this->phraseToCodeMap[$description] ?? null;
                if ($code) {
                    $map[$value] = $code;
                } else {
                    // 如果找不到编码，使用原始描述
                    $map[$value] = "'{$description}'";
                }
            }

            $method = $this->buildMapMethod($field, $map);
            $blockContent .= "\n{$method}";

            $blocks[] = $blockContent;
            $hasValidFields = true;
        }

        // 没有有效的枚举字段时返回空内容
        if (!$hasValidFields) {
            return '';
        }

        $enumContent .= implode("\n\n", $blocks);
        $enumContent .= "\n}\n";

        return $enumContent;
    }

    /**
     * 解析枚举注释（支持多种格式）
     * @param string $comment
     * @return array
     */
    protected function parseEnumComment(string $comment): array
    {
        $result = [
            'description' => '',
            'items' => []
        ];

        $comment = trim($comment);

        // 尝试提取字段描述（冒号前的部分）
        if (preg_match('/^([^:：]+)[:：]\s*(.+)$/us', $comment, $wholeMatch)) {
            $result['description'] = trim($wholeMatch[1]);
            $valuePart = trim($wholeMatch[2]);
        } else {
            $valuePart = $comment;
        }

        // 尝试解析标准格式
        if (preg_match_all('/(\d+)\s*[=:：]\s*([^,\s]+)/u', $valuePart, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $description = str_replace(['，', ','], '', trim($match[2]));
                $result['items'][] = [
                    'value' => $match[1],
                    'description' => $description
                ];
            }
            return $result;
        }

        // 尝试解析换行格式
        $lines = preg_split('/\r?\n/', $valuePart);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(\d+)\s*[=:：]\s*(.+)$/u', $line, $match)) {
                $description = str_replace(['，', ','], '', trim($match[2]));
                $result['items'][] = [
                    'value' => $match[1],
                    'description' => $description
                ];
            }
        }

        return $result;
    }

    /**
     * 构建映射方法（使用多语言编码）
     * @param string $fieldName
     * @param array $map [值 => 编码]
     * @return string
     */
    protected function buildMapMethod(string $fieldName, array $map): string
    {
        $methodName = 'get' . Str::studly($fieldName) . 'Map';
        $constPrefix = Str::upper(Str::snake($fieldName));

        $langFile = $this->option('lang-file');
        $langPrefix = $langFile ? "{$langFile}." : '';

        $method = <<<METHOD
    /**
     * 获取{$fieldName}映射
     * @return array|string
     */
    public static function {$methodName}( \$value = null)
    {
        \$map = [
METHOD;

        foreach ($map as $val => $code) {
            $constName = $constPrefix . '_' . $val;

            // 判断是编码还是原始文本
            if (is_numeric($code)) {
                // 使用多语言翻译函数
                $method .= "\n            self::{$constName} => __('{$langPrefix}{$code}'),";
            } else {
                // 直接使用原始文本
                $method .= "\n            self::{$constName} => $code,";
            }
        }

        $method .= "\n        ];\n\n";
        $method .= <<<METHOD
        if (\$value !== null) {
            return \$map[\$value] ?? '';
        }
        
        return \$map;
    }
METHOD;

        return $method;
    }

    /**
     * 生成多语言文件
     * @param array $phrases
     */
    private function generateLangFiles(array $phrases)
    {
        if (empty($phrases)) {
            $this->line('没有需要生成的多语言条目');
            return;
        }

        $startPrefix = $this->option('lang-start') ?? '1';
        $langFile = $this->option('lang-file') ?? 'enums';

        $this->info('开始生成多语言文件...');

        // 调用多语言生成命令
        $this->call('lang:generate', [
            'chinese' => $phrases,
            '--file' => "{$langFile}.php",
            '--start' => $startPrefix,
            '--locales' => ''
        ]);

        // 加载多语言映射关系
        $this->loadLangMappings($langFile);
    }

    /**
     * 加载多语言映射关系
     * @param string $langFile
     */
    private function loadLangMappings(string $langFile)
    {
        $path = lang_path("zh-CN/{$langFile}.php");

        if (!File::exists($path)) {
            $this->error("多语言文件不存在: {$path}");
            return;
        }

        $langArray = include $path;

        if (!is_array($langArray)) {
            $this->error("多语言文件格式错误: {$path}");
            return;
        }

        // 反转数组：描述文本 => 编码
        $this->phraseToCodeMap = array_flip($langArray);
    }
}