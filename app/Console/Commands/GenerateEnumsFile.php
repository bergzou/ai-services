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

    private $phraseToCodeMap = [];

    public function handle()
    {
        $prefix = $this->option('prefix');
        $force = $this->option('force');

        $basePath = 'app/Enums';
        $outputOption = $this->option('output');
        $outputDir = $this->buildOutputDir($basePath, $outputOption);

        $connectionName = $this->option('connection') ?? Config::get('database.default');
        $connection = DB::connection($connectionName);

        $tables = $this->getTables($connection);
        if (empty($tables)) {
            $this->error("数据库中没有找到任何表");
            return 1;
        }

        File::ensureDirectoryExists($outputDir);

        // 收集多语言短语
        $phrases = $this->collectPhrases($tables, $connection);

        if (!empty($phrases)) {
            $this->generateLangFiles($phrases);
        }

        $generatedCount = 0;
        $skippedCount = 0;
        $noValidFieldsCount = 0;

        foreach ($tables as $table) {
            $className = 'Enum' . $this->generateClassName($table, $prefix);
            $filePath = $outputDir . '/' . $className . '.php';

            if (file_exists($filePath) && !$force) {
                $this->line("枚举文件已存在: {$className} ({$filePath}) - 使用 --force 覆盖");
                $skippedCount++;
                continue;
            }

            $columns = $this->getTableColumns($connection, $table);
            $enumContent = $this->generateEnumContent($className, $columns, $outputDir);

            if (empty(trim($enumContent))) {
                $this->line("跳过 {$table} 表：未找到有效的枚举字段");
                $noValidFieldsCount++;
                continue;
            }

            File::put($filePath, $enumContent);
            $action = $force ? '覆盖' : '创建';
            $this->info("枚举文件已{$action}: {$className} ({$filePath})");
            $generatedCount++;
        }

        $this->line("<fg=green>成功生成 {$generatedCount} 个枚举文件!</>");
        if ($skippedCount > 0) $this->line("<fg=yellow>跳过 {$skippedCount} 个已存在的枚举文件 (使用 --force 覆盖)</>");
        if ($noValidFieldsCount > 0) $this->line("<fg=blue>跳过 {$noValidFieldsCount} 个没有有效枚举字段的表</>");

        return 0;
    }

    private function buildOutputDir(string $basePath, ?string $outputOption): string
    {
        if ($outputOption && $outputOption !== $basePath) {
            $parts = array_map(fn($part) => Str::studly($part), explode('/', trim($outputOption, '/')));
            return $basePath . '/' . implode('/', $parts);
        }
        return $basePath;
    }

    private function collectPhrases(array $tables, $connection): array
    {
        $phrases = [];
        foreach ($tables as $table) {
            $columns = $this->getTableColumns($connection, $table);
            foreach ($columns as $field => $info) {
                $integerTypes = ['tinyint','smallint','integer','int','bigint','boolean'];
                if (!in_array($info['type'], $integerTypes) || empty($info['comment'])) continue;

                $parsed = $this->parseEnumComment($info['comment']);
                if (!empty($parsed['items'])) {
                    foreach ($parsed['items'] as $item) {
                        $phrases[] = $item['description'];
                    }
                }
            }
        }
        return array_unique($phrases);
    }

    private function getTables($connection): array
    {
        if ($specifiedTables = $this->option('tables')) {
            return array_map('trim', explode(',', $specifiedTables));
        }
        return $this->getSchemaManager($connection)->listTableNames();
    }

    private function getSchemaManager($connection): AbstractSchemaManager
    {
        return $connection->getDoctrineConnection()->createSchemaManager();
    }

    private function getTableColumns($connection, string $table): array
    {
        $columns = $this->getSchemaManager($connection)->listTableColumns($table);
        $result = [];
        foreach ($columns as $column) {
            $result[$column->getName()] = [
                'type' => $column->getType()->getName(),
                'comment' => $column->getComment() ?? '',
            ];
        }
        return $result;
    }

    private function generateClassName(string $tableName, ?string $prefix): string
    {
        $name = $prefix ? preg_replace("/^{$prefix}_?/", '', $tableName) : $tableName;
        return Str::studly($name);
    }

    private function generateEnumContent(string $className, array $columns, string $outputDir): string
    {
        $relativePath = preg_replace('#^app/?#', '', $outputDir);
        $parts = array_map(fn($part) => Str::studly($part), explode('/', $relativePath));
        $namespace = 'App' . (!empty($parts) ? '\\' . implode('\\', $parts) : '');
        $enumContent = "<?php\n\nnamespace {$namespace};\n\n";

        $enumContent .= "class {$className}\n{\n";
        $hasValidFields = false;
        foreach ($columns as $field => $info) {
            $integerTypes = ['tinyint','smallint','integer','int','bigint','boolean'];
            if (!in_array($info['type'], $integerTypes) || empty($info['comment'])) continue;

            $parsed = $this->parseEnumComment($info['comment']);
            if (empty($parsed['items'])) continue;

            $fieldDescription = $parsed['description'] ?? $field;
            $enumContent .= "\n    # {$fieldDescription}\n";
            $map = [];

            foreach ($parsed['items'] as $item) {
                $value = $item['value'];
                $description = $item['description'];
                $constName = Str::upper(Str::snake($field)) . '_' . $value;
                $enumContent .= "    const {$constName} = {$value}; // {$description}\n";

                $code = $this->phraseToCodeMap[$description] ?? null;
                $map[$value] = $code ?: "'{$description}'";
            }

            $enumContent .= "\n" . $this->buildMapMethod($field, $map) . "\n";
            $hasValidFields = true;
        }

        return $hasValidFields ? $enumContent . "}\n" : '';
    }

    protected function parseEnumComment(string $comment): array
    {
        $result = ['description' => '', 'items' => []];
        $comment = trim($comment);
        if (preg_match('/^([^:：]+)[:：]\s*(.+)$/us', $comment, $matches)) {
            $result['description'] = trim($matches[1]);
            $valuePart = trim($matches[2]);
        } else $valuePart = $comment;

        if (preg_match_all('/(\d+)\s*[=:：]\s*([^,\s]+)/u', $valuePart, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $result['items'][] = ['value'=>$match[1], 'description'=>str_replace(['，',','],'',trim($match[2]))];
            }
            return $result;
        }

        $lines = preg_split('/\r?\n/', $valuePart);
        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\s*[=:：]\s*(.+)$/u', $line, $match)) {
                $result['items'][] = ['value'=>$match[1], 'description'=>str_replace(['，',','],'',trim($match[2]))];
            }
        }
        return $result;
    }

    protected function buildMapMethod(string $fieldName, array $map): string
    {
        $methodName = 'get' . Str::studly($fieldName) . 'Map';
        $constPrefix = Str::upper(Str::snake($fieldName));
        $langFile = $this->option('lang-file') ?: 'enums';
        $langPrefix = $langFile ? "{$langFile}." : '';

        $method = "    /**\n     * 获取{$fieldName}映射\n     */\n";
        $method .= "    public static function {$methodName}(\$value = null)\n    {\n        \$map = [";

        foreach ($map as $val => $code) {
            $constName = $constPrefix . '_' . $val;
            $method .= "\n            self::{$constName} => " . (is_numeric($code) ? "__('{$langPrefix}{$code}')" : $code) . ",";
        }
        $method .= "\n        ];\n\n";
        $method .= "        if (\$value !== null) return \$map[\$value] ?? '';\n        return \$map;\n    }";

        return $method;
    }

    private function generateLangFiles(array $phrases)
    {
        if (empty($phrases)) return;
        $startPrefix = $this->option('lang-start') ?: '1';
        $langFile = $this->option('lang-file') ?: 'enums';
        $this->call('lang:generate', [
            'chinese' => $phrases,
            '--file' => "{$langFile}.php",
            '--start' => $startPrefix,
            '--locales' => ''
        ]);
        $this->loadLangMappings($langFile);
    }

    private function loadLangMappings(string $langFile)
    {
        $path = lang_path("zh-CN/{$langFile}.php");
        if (!File::exists($path)) return;
        $langArray = include $path;
        if (!is_array($langArray)) return;
        $this->phraseToCodeMap = array_flip($langArray);
    }
}
