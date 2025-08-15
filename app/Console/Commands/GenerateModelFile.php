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
        $prefix = $this->option('prefix');
        $force = $this->option('force');
        $outputOption = $this->option('output');
        $basePath = 'app/Models';

        // 生成输出目录
        if ($outputOption && $outputOption !== $basePath) {
            $parts = array_map(function ($part) {
                return Str::studly($part);
            }, explode('/', trim($outputOption, '/')));
            $outputDir = $basePath . '/' . implode('/', $parts);
            $namespaceSuffix = '\\' . implode('\\', $parts);
        } else {
            $outputDir = $basePath;
            $namespaceSuffix = '';
        }

        $connectionName = Config::get('database.default');
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
            $modelName = $this->generateModelName($table, $prefix).'Model';
            $filePath = $outputDir . '/' . $modelName . '.php';

            if (file_exists($filePath) && !$force) {
                $this->line("模型已存在: {$modelName} ({$filePath}) - 使用 --force 覆盖");
                $skippedCount++;
                continue;
            }

            $columns = $this->getTableColumns($connection, $table);

            $modelContent = $this->generateModelContent(
                $modelName,
                $table,
                $connectionName,
                $columns,
                $namespaceSuffix
            );

            file_put_contents($filePath, $modelContent);
            if (file_exists($filePath)) {
                $action = $force ? '覆盖' : '创建';
                $this->info("模型已{$action}: {$modelName} ({$filePath})");
                $generatedCount++;
            } else {
                $this->error("模型生成失败: {$modelName} ({$filePath})");
            }
        }

        $this->line("<fg=green>成功生成 {$generatedCount} 个模型!</>");
        if ($skippedCount > 0) {
            $this->line("<fg=yellow>跳过 {$skippedCount} 个已存在的模型 (使用 --force 覆盖)</>");
        }
        return 0;
    }

    private function getTables(Connection $connection): array
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

    private function getTableColumns(Connection $connection, string $table): array
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

    private function generateModelName(string $tableName, ?string $prefix): string
    {
        $name = $prefix ? preg_replace("/^{$prefix}_?/", '', $tableName) : $tableName;
        return Str::studly($name);
    }

    private function generateModelContent(
        string $modelName,
        string $tableName,
        ?string $connection,
        array $columns,
        string $namespaceSuffix = ''
    ): string {
        $guarded = $this->determineGuarded($columns);
        $casts = $this->generateCasts($columns);
        $fillable = $this->generateFillable($columns, $guarded);

        $content = "<?php\n\n";
        $content .= "namespace App\Models{$namespaceSuffix};\n\n";
        $content .= "use App\Models\BaseModel;\n";
        $content .= "use Illuminate\Database\Eloquent\Factories\HasFactory;\n\n";
        $content .= "class {$modelName} extends BaseModel\n{\n";
        $content .= "    # 使用Eloquent工厂模式\n";
        $content .= "    use HasFactory;\n\n";
        $content .= "    # 对应的数据库表名\n";
        $content .= "    protected \$table = '{$tableName}';\n\n";

        if ($connection) {
            $content .= "    # 数据库连接（如果使用自定义连接）\n";
            $content .= "    protected \$connection = '{$connection}';\n\n";
        }

        $content .= "    # 黑名单，指定不允许批量赋值的字段（如主键和敏感字段）\n";
        $content .= "    public \$guarded = [{$guarded}];\n\n";
        $content .= "    # 白名单，指定可以被批量赋值的字段（注意：如果同时定义了\$fillable和\$guarded，则只有\$fillable生效）\n";
        $content .= "    public \$fillable = [{$fillable}];\n\n";

        if (!empty($casts)) {
            $content .= "    # 属性类型转换（自动映射数据库类型到PHP类型）\n";
            $content .= "    public \$casts = [\n";
            foreach ($casts as $field => $castInfo) {
                $comment = $castInfo['comment'] ? ' # ' . $this->sanitizeComment($castInfo['comment']) : '';
                $content .= "        '{$field}' => '{$castInfo['type']}',{$comment}\n";
            }
            $content .= "    ];\n\n";
        }

        $content .= "}\n";

        return $content;
    }

    private function sanitizeComment(string $comment): string
    {
        return trim(preg_replace('/\s+/', ' ', $comment));
    }

    private function determineGuarded(array $columns): string
    {
        $guarded = [];
        $primaryKeys = ['id', 'snowflake_id', 'uuid', 'uid', 'rowid', 'pk'];
        foreach ($primaryKeys as $candidate) {
            if (array_key_exists($candidate, $columns)) {
                $guarded[] = $candidate;
            }
        }
        if (empty($guarded)) {
            foreach (array_keys($columns) as $field) {
                if (preg_match('/_id$/', $field) || $field === 'id') {
                    $guarded[] = $field;
                    break;
                }
            }
        }
        return !empty($guarded) ? "'" . implode("','", $guarded) . "'" : "''";
    }

    private function generateFillable(array $columns, string $guarded): string
    {
        $guardedFields = [];
        if (!empty($guarded) && $guarded !== "''") {
            $guardedFields = explode("','", trim($guarded, "'"));
        }
        $allFields = array_keys($columns);
        $fillableFields = array_diff($allFields, $guardedFields);
        return !empty($fillableFields) ? "'" . implode("','", $fillableFields) . "'" : "''";
    }

    private function generateCasts(array $columns): array
    {
        $casts = [];
        $typeMapping = [
            'integer'    => 'integer',
            'int'        => 'integer',
            'bigint'     => 'integer',
            'mediumint'  => 'integer',
            'smallint'   => 'integer',
            'tinyint'    => 'integer',
            'float'      => 'float',
            'double'     => 'float',
            'real'       => 'float',
            'decimal'    => 'decimal:2',
            'numeric'    => 'decimal:2',
            'boolean'    => 'integer',
            'bool'       => 'integer',
            'datetime'   => 'datetime',
            'timestamp'  => 'datetime',
            'datetimetz' => 'datetime',
            'date'       => 'date',
            'time'       => 'time',
            'json'       => 'array',
            'jsonb'      => 'array',
            'array'      => 'array',
            'text'       => 'string',
            'string'     => 'string',
            'varchar'    => 'string',
            'char'       => 'string',
            'enum'       => 'string',
            'set'        => 'array',
        ];

        $specialHandling = [
            '/^is_/'       => 'integer',
            '/^has_/'      => 'integer',
            '/_at$/'       => 'datetime',
            '/_date$/'     => 'date',
            '/_time$/'     => 'time',
            '/_json$/'     => 'array',
            '/_options$/'  => 'array',
            '/_metadata$/' => 'object',
            '/_encrypted$/' => 'encrypted',
        ];

        foreach ($columns as $field => $info) {
            $type = $info['type'];
            $comment = $info['comment'] ?? '';
            $castType = null;

            foreach ($specialHandling as $pattern => $candidateType) {
                if (preg_match($pattern, $field)) {
                    $castType = $candidateType;
                    break;
                }
            }

            if (!$castType && isset($typeMapping[$type])) {
                $castType = $typeMapping[$type];
                if ($type === 'decimal' || $type === 'numeric') {
                    $castType = 'decimal:2';
                }
            }

            $castType = $castType ?? 'string';

            if ($castType === 'encrypted') {
                $castType = (strpos($type, 'json') !== false || strpos($field, 'json') !== false)
                    ? 'encrypted:array'
                    : 'encrypted';
            }

            $casts[$field] = [
                'type' => $castType,
                'comment' => $comment,
            ];
        }

        return $casts;
    }
}
