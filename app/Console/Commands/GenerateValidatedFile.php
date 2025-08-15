<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

class GenerateValidatedFile extends Command
{
    protected $signature = 'validated:generate
                            {--tables= : 指定表名（多个用逗号分隔，不指定则生成所有表）}
                            {--prefix= : 要移除的表前缀}
                            {--output=app/Validates : 输出目录}
                            {--force : 强制覆盖已存在的验证器文件}
                            {--lang-start=3 : 多语言编码起始值}
                            {--lang-file=validated : 多语言文件名（不含扩展名）}';

    protected $description = '从数据库表结构生成验证器文件';

    private $phraseToCodeMap = [];
    private $phrases = [];

    public function handle()
    {
        $prefix = $this->option('prefix');
        $force = $this->option('force');

        $basePath = 'app/Validates';
        $outputOption = $this->option('output');

        if ($outputOption && $outputOption !== $basePath) {
            $parts = array_map(fn($part) => Str::studly($part), explode('/', trim($outputOption, '/')));
            $outputDir = $basePath . '/' . implode('/', $parts);
        } else {
            $outputDir = $basePath;
        }

        $connectionName = Config::get('database.default');
        $connection = DB::connection($connectionName);

        $tables = $this->getTables($connection);
        if (empty($tables)) {
            $this->error("数据库中没有找到任何表");
            return 1;
        }

        File::ensureDirectoryExists($outputDir);

        $this->info("收集字段描述短语...");
        foreach ($tables as $table) {
            $columns = $this->getTableColumns($connection, $table);
            foreach ($columns as $field => $info) {
                if (in_array($field, ['created_at', 'updated_at', 'deleted_at'])) continue;
                $description = $this->extractDescription($info['comment']);
                if (!empty($description)) $this->phrases[] = $description;
            }
        }

        $this->phrases = array_unique($this->phrases);
        $this->generateLangFiles();
        $this->loadLangMappings();

        $generatedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($tables as $table) {
            $className = $this->generateClassName($table, $prefix) . 'Validated';
            $filePath = $outputDir . '/' . $className . '.php';
            $columns = $this->getTableColumns($connection, $table);
            $validatorContent = $this->generateValidatorContent($className, $columns, $outputDir);

            if (file_exists($filePath)) {
                if ($force) {
                    File::put($filePath, $validatorContent);
                    $this->info("验证器文件已覆盖: {$className} ({$filePath})");
                    $generatedCount++;
                } else {
                    $currentContent = File::get($filePath);
                    $updatedContent = $this->updateValidatorMethods($currentContent, $columns);
                    $updatedContent = $this->ensureAdditionalMethods($updatedContent);

                    if ($updatedContent !== $currentContent) {
                        File::put($filePath, $updatedContent);
                        $this->info("验证器文件已更新: {$className} ({$filePath})");
                        $updatedCount++;
                    } else {
                        $this->line("验证器文件无需更新: {$className} ({$filePath}) - 使用 --force 覆盖");
                        $skippedCount++;
                    }
                }
            } else {
                File::put($filePath, $validatorContent);
                $this->info("验证器文件已创建: {$className} ({$filePath})");
                $generatedCount++;
            }
        }

        $this->line("<fg=green>成功生成 {$generatedCount} 个验证器文件!</>");
        $this->line("<fg=blue>成功更新 {$updatedCount} 个验证器文件!</>");
        if ($skippedCount > 0) {
            $this->line("<fg=yellow>跳过 {$skippedCount} 个验证器文件 (使用 --force 覆盖)</>");
        }

        return 0;
    }

    private function ensureAdditionalMethods(string $content): string
    {
        $methods = ['addParams', 'updateParams', 'deleteParams', 'detailParams'];
        $missingMethods = [];
        foreach ($methods as $method) {
            if (!preg_match("/public\s+function\s+{$method}\s*\(/", $content)) {
                $missingMethods[] = $method;
            }
        }
        if (empty($missingMethods)) return $content;

        $methodsTemplate = $this->getAdditionalMethodsTemplate();
        return preg_replace('/\n}\s*$/s', "\n" . $methodsTemplate . "\n}", $content);
    }

    private function getAdditionalMethodsTemplate(array $columns = []): string
    {
        $excludeForAdd = ['id','snowflake_id','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];
        $excludeForUpdate = ['id','created_at','created_by','updated_at','updated_by','is_deleted','deleted_at','deleted_by'];

        $formatArray = fn(array $items) => "[\n" . implode("\n", array_map(fn($v) => "            '{$v}',", $items)) . "\n        ]";

        $fieldsForAdd = array_keys(array_filter($columns, fn($info,$field) => !in_array($field,$excludeForAdd,true), ARRAY_FILTER_USE_BOTH));
        $fieldsForUpdate = array_keys(array_filter($columns, fn($info,$field) => !in_array($field,$excludeForUpdate,true), ARRAY_FILTER_USE_BOTH));

        $fieldsAddExport = $formatArray($fieldsForAdd);
        $fieldsUpdateExport = $formatArray($fieldsForUpdate);

        return <<<TEXT
    public function addParams(): array
    {
        return {$fieldsAddExport};
    }

    public function updateParams(): array
    {
        return {$fieldsUpdateExport};
    }

    public function deleteParams(): array
    {
        return [
            'snowflake_id',
        ];
    }

    public function detailParams(): array
    {
        return [
            'snowflake_id',
        ];
    }
TEXT;
    }

    private function extractDescription(string $comment): string
    {
        $comment = preg_replace('/\{[^}]*\}/', '', $comment);
        if (preg_match('/^([^:：]+)[:：]/u', $comment, $matches)) return trim($matches[1]);
        if (preg_match('/^([^#]+)#/u', $comment, $matches)) return trim($matches[1]);
        return trim(preg_replace('/\d+\s*=\s*[^,]+(,\s*)?/u', '', $comment));
    }

    private function getTables($connection): array
    {
        if ($specifiedTables = $this->option('tables')) {
            return array_map('trim', explode(',', $specifiedTables));
        }
        $schemaManager = $this->getSchemaManager($connection);
        return $schemaManager->listTableNames();
    }

    private function getSchemaManager($connection): AbstractSchemaManager
    {
        $doctrineConnection = $connection->getDoctrineConnection();
        return $doctrineConnection->createSchemaManager();
    }

    private function getTableColumns($connection, $table): array
    {
        $schemaManager = $this->getSchemaManager($connection);
        $columns = $schemaManager->listTableColumns($table);

        $result = [];
        foreach ($columns as $column) {
            $result[$column->getName()] = [
                'type' => $column->getType()->getName(),
                'notnull' => $column->getNotnull(),
                'length' => $column->getLength(),
                'comment' => $column->getComment() ?? '',
                'default' => $column->getDefault(),
            ];
        }
        return $result;
    }

    private function generateClassName(string $tableName, ?string $prefix): string
    {
        $name = $prefix ? preg_replace("/^{$prefix}_?/", '', $tableName) : $tableName;
        return Str::studly($name);
    }

    private function generateValidatorContent(string $className, array $columns, string $outputDir): string
    {
        $rules = $this->generateRules($columns);
        $attributes = $this->generateCustomAttributes($columns);
        $additionalMethods = $this->getAdditionalMethodsTemplate($columns);

        $relativePath = preg_replace('#^app/?#', '', $outputDir);
        $parts = array_map(fn($part) => Str::studly($part), explode('/', $relativePath));
        $namespace = 'App' . (!empty($parts) ? '\\' . implode('\\', $parts) : '');

        return <<<PHP
<?php

namespace {$namespace};

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class {$className} extends BaseValidated implements ValidatesInterface
{
    public function rules(): array
    {
        return {$rules};
    }

    public function messages(): array
    {
        return [];
    }

    public function customAttributes(): array
    {
        return {$attributes};
    }

{$additionalMethods}
}
PHP;
    }

    private function generateRules(array $columns): string
    {
        $rules = "[\n";
        foreach ($columns as $field => $info) {
            if (in_array($field, ['created_at','updated_at','deleted_at'])) continue;
            $fieldRules = $info['notnull'] ? ['required'] : ['nullable'];
            $rules .= "            '{$field}' => '" . implode('|', $fieldRules) . "', # " . $info['comment'] . "\n";
        }
        $rules .= "        ]";
        return $rules;
    }

    private function generateCustomAttributes(array $columns): string
    {
        $attributes = "[\n";
        foreach ($columns as $field => $info) {
            if (in_array($field, ['created_at','updated_at','deleted_at'])) continue;
            $description = $this->extractDescription($info['comment']);
            if (!empty($description)) {
                $code = $this->phraseToCodeMap[$description] ?? null;
                $attributes .= $code
                    ? "            '{$field}' => __('{$this->option('lang-file')}.{$code}'), # {$description}\n"
                    : "            '{$field}' => '{$description}', # {$description}\n";
            } else {
                $attributes .= "            '{$field}' => '{$field}', # {$field}\n";
            }
        }
        $attributes .= "        ]";
        return $attributes;
    }

    private function updateValidatorMethods(string $currentContent, array $columns): string
    {
        $newRules = $this->generateRules($columns);
        $newAttributes = $this->generateCustomAttributes($columns);

        $currentContent = preg_replace('/(public function rules\(\): array\s*{)[^}]*}/s', "\$1\n        return {$newRules};\n    }", $currentContent);
        $currentContent = preg_replace('/(public function messages\(\): array\s*{)[^}]*}/s', "\$1\n        return [];\n    }", $currentContent);
        $currentContent = preg_replace('/(public function customAttributes\(\): array\s*{)[^}]*}/s', "\$1\n        return {$newAttributes};\n    }", $currentContent);

        return $currentContent;
    }

    private function generateLangFiles()
    {
        if (empty($this->phrases)) {
            $this->line('没有需要生成的多语言条目');
            return;
        }

        $startPrefix = $this->option('lang-start') ?? '3';
        $langFile = $this->option('lang-file') ?? 'validated';
        $this->info('开始生成多语言文件...');

        $this->call('lang:generate', [
            'chinese' => $this->phrases,
            '--file' => "{$langFile}.php",
            '--start' => $startPrefix,
            '--locales' => ''
        ]);
    }

    private function loadLangMappings()
    {
        $langFile = $this->option('lang-file') ?? 'validated';
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

        $this->phraseToCodeMap = array_flip($langArray);
    }
}
