<?php

namespace App\Console\Commands;

use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

/**
 * 自动生成Eloquent模型的Artisan命令
 * 支持从数据库表结构反向生成模型文件，包含表名映射、字段类型转换、批量赋值控制等功能
 */
class GenerateModelFile extends Command
{
    /**
     * Artisan命令签名（定义命令参数和选项）
     * @var string
     * @example 命令使用示例：
     * php artisan model:generate --tables=users,posts --prefix=tb --output=app/Models --connection=mysql --force
     */
    protected $signature = 'model:generate 
                            {--tables= : 指定表名（多个用逗号分隔，不指定则生成所有表）}
                            {--prefix= : 要移除的表前缀（如"tb_"会将"tb_users"转为"users"）}
                            {--output=app/Models : 模型输出目录（默认app/Models）}
                            {--connection= : 数据库连接名称（默认使用config/database.php的default连接）}
                            {--force : 强制覆盖已存在的模型文件（默认跳过已存在文件）}';

    /**
     * 命令描述信息（通过php artisan list查看）
     * @var string
     */
    protected $description = '从数据库表结构生成Eloquent模型';

    /**
     * 命令执行入口方法
     * @return int 执行状态码（0=成功，非0=失败）
     * @throws Exception
     */
    public function handle()
    {
        // 获取命令选项参数
        $connectionName = $this->option('connection') ?? Config::get('database.default');
        $prefix = $this->option('prefix');
        $outputDir = $this->option('output');
        $force = $this->option('force');

        $isCustomConnection = $this->option('connection') !== null;

        // 设置当前数据库连接（临时修改默认连接）
        Config::set('database.default', $connectionName);
        $connection = DB::connection($connectionName);

        // 获取需要处理的表列表（指定表或所有表）
        $tables = $this->getTables($connection);

        if (empty($tables)) {
            $this->error("数据库中没有找到任何表");
            return 1;
        }

        // 确保输出目录存在（自动创建缺失目录）
        File::ensureDirectoryExists($outputDir);

        $generatedCount = 0; // 成功生成计数
        $skippedCount = 0;   // 跳过计数
        foreach ($tables as $table) {
            // 生成模型类名（基于表名处理前缀和单复数）
            $modelName = $this->generateModelName($table, $prefix).'Model';
            $filePath = $outputDir . '/' . $modelName . '.php';

            // 检查文件是否存在且未强制覆盖时跳过
            if (file_exists($filePath) && !$force) {
                $this->line("模型已存在: {$modelName} ({$filePath}) - 使用 --force 覆盖");
                $skippedCount++;
                continue;
            }

            // 获取表字段元数据（类型+注释）
            $columns = $this->getTableColumns($connection, $table);

            // 生成模型文件内容（包含表名、连接、guarded、casts等配置）
            $modelContent = $this->generateModelContent(
                $modelName,
                $table,
                $isCustomConnection ? $connectionName : null,
                $columns
            );

            // 写入文件并输出结果
            file_put_contents($filePath, $modelContent);
            if (file_exists($filePath)) {
                $action = $force ? '覆盖' : '创建';
                $this->info("模型已{$action}: {$modelName} ({$filePath})");
                $generatedCount++;
            } else {
                $this->error("模型生成失败: {$modelName} ({$filePath})");
            }
        }

        // 输出最终统计结果
        $this->line("<fg=green>成功生成 {$generatedCount} 个模型!</>");
        if ($skippedCount > 0) {
            $this->line("<fg=yellow>跳过 {$skippedCount} 个已存在的模型 (使用 --force 覆盖)</>");
        }
        return 0;
    }

    /**
     * 获取需要处理的表列表（指定表或所有表）
     * @param Connection $connection 数据库连接实例
     * @return array 表名数组
     * @throws Exception
     */
    private function getTables(Connection $connection): array
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
     * 获取数据库模式管理器（用于获取表结构元数据）
     * @param Connection $connection 数据库连接实例
     * @return AbstractSchemaManager Doctrine Schema管理器实例
     * @throws Exception
     */
    private function getSchemaManager($connection): AbstractSchemaManager
    {
        $doctrineConnection = $connection->getDoctrineConnection();
        return $doctrineConnection->createSchemaManager();
    }

    /**
     * 获取表字段元数据（类型+注释）
     * @param Connection $connection 数据库连接实例
     * @param string $table 表名
     * @return array 字段元数据数组（键为字段名，值包含'type'和'comment'）
     * @throws Exception
     */
    private function getTableColumns(Connection $connection, string $table): array
    {
        $schemaManager = $this->getSchemaManager($connection);
        $columns = $schemaManager->listTableColumns($table);

        $result = [];
        foreach ($columns as $column) {
            $result[$column->getName()] = [
                'type' => $column->getType()->getName(),    // 字段类型（如varchar、int）
                'comment' => $column->getComment() ?? '',   // 字段注释（数据库中定义的comment）
            ];
        }

        return $result;
    }

    /**
     * 生成模型类名（基于表名处理前缀和单复数）
     * @param string $tableName 原始表名（如"tb_users"）
     * @param string|null $prefix 需要移除的表前缀（如"tb_"）
     * @return string 模型类名（如"User"）
     */
    private function generateModelName(string $tableName, ?string $prefix): string
    {
        // 移除表前缀（如"tb_users" -> "users"）
        $name = $prefix ? preg_replace("/^{$prefix}_?/", '', $tableName) : $tableName;

        return Str::studly($name);
    }

    /**
     * 生成模型文件内容（核心模板生成逻辑）
     * @param string $modelName 模型类名（如"User"）
     * @param string $tableName 数据库表名（如"users"）
     * @param string|null $connection 数据库连接名称（非默认连接时需要）
     * @param array $columns 字段元数据数组（来自getTableColumns）
     * @return string 模型文件内容（PHP代码字符串）
     */
    private function generateModelContent(
        string $modelName,
        string $tableName,
        ?string $connection,
        array $columns
    ): string {
        $guarded = $this->determineGuarded($columns);  // 确定批量赋值黑名单字段
        $casts = $this->generateCasts($columns);       // 生成类型转换配置

        // 构建模型内容模板
        $content = "<?php\n\n";
        $content .= "namespace App\Models;\n\n";
        $content .= "use Illuminate\Database\Eloquent\Factories\HasFactory;\n\n";
        $content .= "class {$modelName} extends BaseModel\n{\n";
        $content .= "    # 使用Eloquent工厂模式\n";
        $content .= "    use HasFactory;\n\n";

        $content .= "    # 对应的数据库表名\n";
        $content .= "    protected \$table = '{$tableName}';\n\n";

        // 自定义连接时添加connection属性
        if ($connection) {
            $content .= "    # 数据库连接（如果使用自定义连接）\n";
            $content .= "    protected \$connection = '{$connection}';\n\n";
        }

        $content .= "    # 黑名单，指定不允许批量赋值的字段（空数组表示所有字段都可赋值）\n";
        $content .= "    protected \$guarded = [{$guarded}];\n\n";

        // 生成类型转换配置（如果有需要转换的字段）
        if (!empty($casts)) {
            $content .= "    # 属性类型转换（自动映射数据库类型到PHP类型）\n";
            $content .= "    protected \$casts = [\n";
            foreach ($casts as $field => $castInfo) {
                $comment = $castInfo['comment'] ? ' # ' . $this->sanitizeComment($castInfo['comment']) : '';
                $content .= "        '{$field}' => '{$castInfo['type']}',{$comment}\n";
            }
            $content .= "    ];\n\n";
        }

        $content .= "}\n";

        return $content;
    }

    /**
     * 清理注释内容（移除多余空格和换行）
     * @param string $comment 原始注释内容
     * @return string 清理后的注释字符串
     */
    private function sanitizeComment(string $comment): string
    {
        // 移除换行符和多余空格（保持单行）
        return trim(preg_replace('/\s+/', ' ', $comment));
    }

    /**
     * 确定批量赋值黑名单字段（guarded属性）
     * @param array $columns 字段元数据数组
     * @return string 黑名单字段字符串（如"'id'"或""）
     */
    private function determineGuarded(array $columns): string
    {
        // 优先匹配常见主键字段名
        $primaryKeys = ['id', 'uuid', 'uid', 'rowid', 'pk'];
        foreach ($primaryKeys as $candidate) {
            if (array_key_exists($candidate, $columns)) {
                return "'{$candidate}'";
            }
        }

        // 匹配其他可能的主键模式（如user_id）
        foreach ($columns as $field => $info) {
            if (preg_match('/_id$/', $field) || $field === 'id') {
                return "'{$field}'";
            }
        }

        // 无主键时允许所有字段批量赋值
        return "''";
    }

    /**
     * 生成类型转换配置（casts属性）
     * @param array $columns 字段元数据数组
     * @return array 类型转换配置数组（键为字段名，值包含'type'和'comment'）
     */
    private function generateCasts(array $columns): array
    {
        $casts = [];
        // 通用数据库类型到PHP类型的映射表
        $typeMapping = [
            'integer'    => 'integer',
            'int'        => 'integer',
            'bigint'     => 'integer',
            'mediumint'  => 'integer',
            'smallint'   => 'integer',
            'tinyint'    => 'integer',  // 小整数通常表示布尔值（0/1）
            'float'      => 'float',
            'double'     => 'float',
            'real'       => 'float',
            'decimal'    => 'decimal:2', // 保留两位小数
            'numeric'    => 'decimal:2',
            'boolean'    => 'integer',
            'bool'       => 'integer',
            'datetime'   => 'datetime',
            'timestamp'  => 'datetime',
            'datetimetz' => 'datetime',
            'date'       => 'date',
            'time'       => 'time',
            'json'       => 'array',     // JSON字段自动转为数组
            'jsonb'      => 'array',
            'array'      => 'array',
            'text'       => 'string',
            'string'     => 'string',
            'varchar'    => 'string',
            'char'       => 'string',
            'enum'       => 'string',    // ENUM类型转为字符串
            'set'        => 'array',     // SET类型转为数组
        ];

        // 特殊字段名模式匹配（优先级高于通用映射）
        $specialHandling = [
            '/^is_/'       => 'integer',      // 以"is_"开头的字段（如is_active）
            '/^has_/'      => 'integer',      // 以"has_"开头的字段（如has_permission）
            '/_at$/'       => 'datetime',     // 以"_at"结尾的字段（如created_at）
            '/_date$/'     => 'date',         // 以"_date"结尾的字段（如start_date）
            '/_time$/'     => 'time',         // 以"_time"结尾的字段（如login_time）
            '/_json$/'     => 'array',        // 以"_json"结尾的字段（如settings_json）
            '/_options$/'  => 'array',        // 以"_options"结尾的字段（如user_options）
            '/_metadata$/' => 'object',       // 以"_metadata"结尾的字段（如item_metadata）
            '/_encrypted$/' => 'encrypted',   // 以"_encrypted"结尾的字段（如data_encrypted）
        ];

        foreach ($columns as $field => $info) {
            $type = $info['type'];
            $comment = $info['comment'] ?? '';
            $castType = null;

            // 1. 优先匹配特殊字段名模式（如is_active -> boolean）
            foreach ($specialHandling as $pattern => $candidateType) {
                if (preg_match($pattern, $field)) {
                    $castType = $candidateType;
                    break;
                }
            }

            // 2. 匹配通用类型映射（如int -> integer）
            if (!$castType && isset($typeMapping[$type])) {
                $castType = $typeMapping[$type];
                // 处理decimal的特殊精度配置
                if ($type === 'decimal' || $type === 'numeric') {
                    $castType = 'decimal:2';
                }
            }

            // 3. 未匹配时默认转为字符串
            $castType = $castType ?? 'string';

            // 处理加密字段的特殊类型（如加密的JSON字段）
            if ($castType === 'encrypted') {
                $castType = (strpos($type, 'json') !== false || strpos($field, 'json') !== false)
                    ? 'encrypted:array'  // 加密的JSON转为加密数组
                    : 'encrypted';        // 普通加密字段
            }

            $casts[$field] = [
                'type' => $castType,    // 转换后的PHP类型
                'comment' => $comment,  // 数据库字段注释（用于代码注释）
            ];
        }

        return $casts;
    }
}