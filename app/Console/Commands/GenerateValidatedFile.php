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

    // 存储短语到编码的映射 [短语 => 编码]
    private $phraseToCodeMap = [];

    // 存储收集到的所有短语
    private $phrases = [];

    public function handle()
    {

        $prefix = $this->option('prefix');
        $force = $this->option('force');




        $basePath = 'app/Validates';
        $outputOption = $this->option('output');

        if ($outputOption && $outputOption !== $basePath) {
            $parts = array_map(function ($part) {
                return Str::studly($part);
            }, explode('/', trim($outputOption, '/')));
            $outputDir = $basePath . '/' . implode('/', $parts);
        } else {
            $outputDir = $basePath;
        }

        $connectionName =  Config::get('database.default');
        $connection = DB::connection($connectionName);

        // 获取需要处理的表列表
        $tables = $this->getTables($connection);

        if (empty($tables)) {
            $this->error("数据库中没有找到任何表");
            return 1;
        }

        // 确保输出目录存在
        File::ensureDirectoryExists($outputDir);

        // 第一阶段：收集所有字段描述短语
        $this->info("收集字段描述短语...");
        foreach ($tables as $table) {
            $columns = $this->getTableColumns($connection, $table);
            foreach ($columns as $field => $info) {
                // 跳过主键和时间戳字段
                if (in_array($field, ['created_at', 'updated_at', 'deleted_at'])) {
                    continue;
                }

                // 提取字段描述
                $description = $this->extractDescription($info['comment']);
                if (!empty($description)) {
                    $this->phrases[] = $description;
                }
            }
        }

        // 去重短语
        $this->phrases = array_unique($this->phrases);

        // 生成多语言文件
        $this->generateLangFiles();

        // 加载多语言映射关系
        $this->loadLangMappings();

        // 第二阶段：生成验证器文件
        $generatedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($tables as $table) {
            // 生成验证器类名
            $className = $this->generateClassName($table, $prefix) . 'Validated';
            $filePath = $outputDir . '/' . $className . '.php';

            // 获取表字段元数据
            $columns = $this->getTableColumns($connection, $table);

            // 生成验证器文件内容
            $validatorContent = $this->generateValidatorContent($className, $columns);

            // 处理文件存在情况
            if (file_exists($filePath)) {
                if ($force) {
                    // 强制覆盖
                    File::put($filePath, $validatorContent);
                    $this->info("验证器文件已覆盖: {$className} ({$filePath})");
                    $generatedCount++;
                } else {
                    // 更新模式：只更新三个方法
                    $currentContent = File::get($filePath);
                    $updatedContent = $this->updateValidatorMethods($currentContent, $columns);

                    // 检查并添加缺失的四个方法
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
                // 文件不存在，创建新文件
                File::put($filePath, $validatorContent);
                $this->info("验证器文件已创建: {$className} ({$filePath})");
                $generatedCount++;
            }
        }

        // 输出统计结果
        $this->line("<fg=green>成功生成 {$generatedCount} 个验证器文件!</>");
        $this->line("<fg=blue>成功更新 {$updatedCount} 个验证器文件!</>");
        if ($skippedCount > 0) {
            $this->line("<fg=yellow>跳过 {$skippedCount} 个验证器文件 (使用 --force 覆盖)</>");
        }

        return 0;
    }

    /**
     * 确保验证器类包含四个额外方法（如果缺失则添加）
     * @param string $content
     * @return string
     */
    private function ensureAdditionalMethods(string $content): string
    {
        $methods = [
            'addParams',
            'updateParams',
            'deleteParams',
            'detailParams'
        ];

        $missingMethods = [];

        foreach ($methods as $method) {
            if (!preg_match("/public\s+function\s+{$method}\s*\(/", $content)) {
                $missingMethods[] = $method;
            }
        }

        if (empty($missingMethods)) {
            return $content;
        }

        $methodsTemplate = $this->getAdditionalMethodsTemplate();
        return preg_replace(
            '/\n}\s*$/s',
            "\n" . $methodsTemplate . "\n}",
            $content
        );
    }




    private function getAdditionalMethodsTemplate(array $columns = []): string
    {
        // 过滤规则
        $excludeForAdd = [
            'id',
            'snowflake_id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'is_deleted',
            'deleted_at',
            'deleted_by',
        ];

        $excludeForUpdate = [
            'id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'is_deleted',
            'deleted_at',
            'deleted_by',
        ];

        // 格式化数组（无键值，缩进对齐）
        $formatArray = function (array $items) {
            $lines = array_map(fn($v) => "            '{$v}',", $items); // 12 空格
            return "[\n" . implode("\n", $lines) . "\n        ]"; // 8 空格
        };

        $fieldsForAdd = array_keys(array_filter($columns, function ($info, $field) use ($excludeForAdd) {
            return !in_array($field, $excludeForAdd, true);
        }, ARRAY_FILTER_USE_BOTH));

        $fieldsForUpdate = array_keys(array_filter($columns, function ($info, $field) use ($excludeForUpdate) {
            return !in_array($field, $excludeForUpdate, true);
        }, ARRAY_FILTER_USE_BOTH));

        $fieldsAddExport = $formatArray($fieldsForAdd);
        $fieldsUpdateExport = $formatArray($fieldsForUpdate);

        return <<<TEXT
    /**
     * 新增参数
     * @return array
     */
    public function addParams(): array
    {
        return {$fieldsAddExport};
    }

    /**
     * 更新参数
     * @return array
     */
    public function updateParams(): array
    {
        return {$fieldsUpdateExport};
    }

    /**
     * 删除参数
     * @return array
     */
    public function deleteParams(): array
    {
        return [
            'snowflake_id',
        ];
    }

    /**
     * 详情参数
     * @return array
     */
    public function detailParams(): array
    {
        return [
            'snowflake_id',
        ];
    }
TEXT;
    }





    /**
     * 从注释中提取字段描述（去掉枚举值和规则部分）
     * @param string $comment
     * @return string
     */
    private function extractDescription(string $comment): string
    {
        // 去除额外规则部分（{...}）
        $comment = preg_replace('/\{[^}]*\}/', '', $comment);

        // 提取冒号前的描述
        if (preg_match('/^([^:：]+)[:：]/u', $comment, $matches)) {
            return trim($matches[1]);
        }

        // 提取井号前的描述
        if (preg_match('/^([^#]+)#/u', $comment, $matches)) {
            return trim($matches[1]);
        }

        // 直接返回整个注释（去除数字=值部分）
        return trim(preg_replace('/\d+\s*=\s*[^,]+(,\s*)?/u', '', $comment));
    }

    /**
     * 获取需要处理的表列表
     * @param \Illuminate\Database\Connection $connection
     * @return array
     */
    private function getTables($connection): array
    {
        if ($specifiedTables = $this->option('tables')) {
            return array_map('trim', explode(',', $specifiedTables));
        }

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
                'notnull' => $column->getNotnull(),
                'length' => $column->getLength(),
                'comment' => $column->getComment() ?? '',
                'default' => $column->getDefault(),
            ];
        }

        return $result;
    }

    /**
     * 生成类名
     * @param string $tableName
     * @param string|null $prefix
     * @return string
     */
    private function generateClassName(string $tableName, ?string $prefix): string
    {
        // 移除表前缀
        $name = $prefix ? preg_replace("/^{$prefix}_?/", '', $tableName) : $tableName;
        // 转换为单数形式并转为大驼峰
        return Str::studly($name);
    }

    /**
     * 生成验证器文件内容
     * @param string $className
     * @param array $columns
     * @return string
     */
    private function generateValidatorContent(string $className, array $columns): string
    {
        $rules = $this->generateRules($columns);
        $attributes = $this->generateCustomAttributes($columns);
        $additionalMethods = $this->getAdditionalMethodsTemplate($columns);

        return <<<PHP
<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class {$className} extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return {$rules};
    }

    /**
     * 定义验证错误消息数组
     * @return array 键为'字段名.规则名'（如 'name.required'），值为自定义错误提示信息
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * 定义字段自定义别名数组（用于错误消息中显示友好名称）
     * @return array 键为字段名，值为业务友好的字段显示名称（如 'name' => '用户姓名'）
     * */
    public function customAttributes(): array
    {
        return {$attributes};
    }

{$additionalMethods}
}
PHP;
    }

    /**
     * 生成验证规则
     * @param array $columns
     * @return string
     */

    private function generateRules(array $columns): string
    {
        $rules = "[\n";

        foreach ($columns as $field => $info) {
            // 只跳过时间戳字段
            if (in_array($field, ['created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $fieldRules = [];


            if ($info['notnull']) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // ... [类型规则部分保持不变] ...

            $rules .= "            '{$field}' => '" . implode('|', $fieldRules) . "', # " . $info['comment'] . "\n";
        }

        $rules .= "        ]";
        return $rules;
    }

    /**
     * 生成自定义属性（使用多语言编码）
     * @param array $columns
     * @return string
     */
    private function generateCustomAttributes(array $columns): string
    {
        $attributes = "[\n";

        foreach ($columns as $field => $info) {
            // 只跳过时间戳字段
            if (in_array($field, ['created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            // 提取字段描述
            $description = $this->extractDescription($info['comment']);

            if (!empty($description)) {
                // 查找描述对应的多语言编码
                $code = $this->phraseToCodeMap[$description] ?? null;

                if ($code) {
                    $attributes .= "            '{$field}' => __('{$this->option('lang-file')}.{$code}'), # {$description}\n";
                } else {
                    // 如果找不到编码，使用原始描述
                    $attributes .= "            '{$field}' => '{$description}', # {$description}\n";
                }
            } else {
                $attributes .= "            '{$field}' => '{$field}', # {$field}\n";
            }
        }

        $attributes .= "        ]";
        return $attributes;
    }

    /**
     * 更新验证器文件中的方法（保留其他方法）
     * @param string $currentContent
     * @param array $columns
     * @return string
     */
    private function updateValidatorMethods(string $currentContent, array $columns): string
    {
        // 生成新的规则和属性
        $newRules = $this->generateRules($columns);
        $newAttributes = $this->generateCustomAttributes($columns);

        // 更新 rules() 方法
        $currentContent = preg_replace(
            '/(public function rules\(\): array\s*{)[^}]*}/s',
            "\$1\n        return {$newRules};\n    }",
            $currentContent
        );

        // 更新 messages() 方法（保持为空数组）
        $currentContent = preg_replace(
            '/(public function messages\(\): array\s*{)[^}]*}/s',
            "\$1\n        return [];\n    }",
            $currentContent
        );

        // 更新 customAttributes() 方法
        $currentContent = preg_replace(
            '/(public function customAttributes\(\): array\s*{)[^}]*}/s',
            "\$1\n        return {$newAttributes};\n    }",
            $currentContent
        );

        return $currentContent;
    }

    /**
     * 生成多语言文件
     */
    private function generateLangFiles()
    {
        if (empty($this->phrases)) {
            $this->line('没有需要生成的多语言条目');
            return;
        }

        $startPrefix = $this->option('lang-start') ?? '3';
        $langFile = $this->option('lang-file') ?? 'validated';

        $this->info('开始生成多语言文件...');

        // 调用多语言生成命令
        $this->call('lang:generate', [
            'chinese' => $this->phrases,
            '--file' => "{$langFile}.php",
            '--start' => $startPrefix,
            '--locales' => ''
        ]);
    }

    /**
     * 加载多语言映射关系
     */
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

        // 反转数组：描述文本 => 编码
        $this->phraseToCodeMap = array_flip($langArray);
    }
}