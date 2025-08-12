<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateMigrationSql extends Command
{
    protected $signature = 'generate:migration-sql 
                            {source : Path to source SQL file}
                            {target : Path to target SQL file}
                            {--source-db= : Source database name}
                            {--target-db= : Target database name}';

    protected $description = 'Generate data migration SQL by comparing two SQL schema files';

    public function handle()
    {
        $sourcePath = $this->argument('source');
        $targetPath = $this->argument('target');
        $sourceDb = $this->option('source-db');
        $targetDb = $this->option('target-db');

        if (!File::exists($sourcePath)) {
            $this->error("Source SQL file does not exist: $sourcePath");
            return 1;
        }

        if (!File::exists($targetPath)) {
            $this->error("Target SQL file does not exist: $targetPath");
            return 1;
        }

        $sourceContent = File::get($sourcePath);
        $targetContent = File::get($targetPath);

        $sourceTables = $this->parseSqlTables($sourceContent);
        $targetTables = $this->parseSqlTables($targetContent);

        if (empty($sourceTables)) {
            $this->error("No tables found in source SQL file");
            return 1;
        }

        if (empty($targetTables)) {
            $this->error("No tables found in target SQL file");
            return 1;
        }

        $this->info("Found ".count($sourceTables)." tables in source SQL");
        $this->info("Found ".count($targetTables)." tables in target SQL");

        $output = [];
        $matchedTables = 0;

        foreach ($targetTables as $tableName => $targetTable) {
            if (!isset($sourceTables[$tableName])) {
                $this->warn("Table '$tableName' not found in source SQL file");
                continue;
            }

            $sourceTable = $sourceTables[$tableName];
            $migrationSql = $this->generateMigrationSql($sourceTable, $targetTable, $sourceDb, $targetDb);

            $output[] = "/*--------------------------------------------------*/";
            $output[] = "/* Migration SQL for table: $tableName */";
            $output[] = "/*--------------------------------------------------*/";
            $output[] = $migrationSql;
            $output[] = "";

            $matchedTables++;
        }

        if ($matchedTables === 0) {
            $this->error("No matching tables found between source and target SQL files");
            return 1;
        }

        // 保存到文件
        $outputPath = storage_path('migration_script.sql');
        File::put($outputPath, implode("\n", $output));

        $this->info("Successfully generated migration SQL for $matchedTables tables");
        $this->info("Saved to: $outputPath");

        return 0;
    }

    private function parseSqlTables($sqlContent)
    {
        $tables = [];

        // 更健壮的正则表达式，处理各种CREATE TABLE格式
        $pattern = '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:`?([\w_]+)`?\.)?`?([\w_]+)`?\s*\(([\s\S]*?)\)\s*(?:ENGINE|TYPE)\s*=\s*(\w+)[\s\S]*?;/i';

        preg_match_all($pattern, $sqlContent, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $database = $match[1] ?? '';
            $tableName = $match[2];
            $fieldsSection = $match[3];
            $engine = $match[4] ?? 'InnoDB';

            // 解析字段定义
            $fields = [];
            $fieldLines = preg_split('/,\s*(?![^(]*\))/', $fieldsSection);

            foreach ($fieldLines as $line) {
                $line = trim($line);
                if (preg_match('/^[`]?(\w+)[`]?\s+([^,]+)/i', $line, $fieldMatch)) {
                    $fieldName = $fieldMatch[1];
                    $fieldDef = $fieldMatch[2];

                    // 跳过主键和索引定义
                    if (in_array(strtoupper($fieldName), ['PRIMARY', 'KEY', 'UNIQUE', 'INDEX', 'CONSTRAINT', 'FOREIGN'])) {
                        continue;
                    }

                    $fields[$fieldName] = [
                        'definition' => trim($fieldDef),
                        'line' => $line
                    ];
                }
            }

            $tables[$tableName] = [
                'name' => $tableName,
                'database' => $database,
                'fields' => $fields,
                'engine' => $engine
            ];
        }

        return $tables;
    }

    private function generateMigrationSql($sourceTable, $targetTable, $sourceDbOption, $targetDbOption)
    {
        $sourceFields = array_keys($sourceTable['fields']);
        $targetFields = array_keys($targetTable['fields']);

        // 确定数据库名
        $sourceDb = $sourceDbOption ?: $sourceTable['database'] ?: '';
        $targetDb = $targetDbOption ?: $targetTable['database'] ?: '';

        $sourceTableRef = $sourceDb ? "`$sourceDb`.`{$sourceTable['name']}`" : "`{$sourceTable['name']}`";
        $targetTableRef = $targetDb ? "`$targetDb`.`{$targetTable['name']}`" : "`{$targetTable['name']}`";

        $fieldMappings = [];

        // 生成字段映射
        foreach ($targetFields as $field) {
            $lowerField = strtolower($field);

            // 特殊处理：snowflake_id 使用 id 值
            if ($lowerField === 'snowflake_id') {
                $fieldMappings[$field] = "CAST(`id` AS CHAR)";
                continue;
            }

            // 字段在源表中存在 - 直接映射
            if (in_array($field, $sourceFields)) {
                $fieldMappings[$field] = "`$field`";
            }
            // 尝试匹配重命名字段
            else {
                $mappedField = $this->mapRenamedField($field, $sourceFields);
                if ($mappedField) {
                    $fieldMappings[$field] = $mappedField;
                } else {
                    $fieldMappings[$field] = $this->generateDefaultValue($field, $sourceFields);
                }
            }
        }

        // 应用特殊转换规则
        $fieldMappings = $this->applySpecialMappings($fieldMappings, $sourceFields);

        // 构建SQL
        $targetFieldsList = implode(",\n    ", array_map(function($f) {
            return "`$f`";
        }, $targetFields));

        $selectFields = [];
        foreach ($fieldMappings as $field => $expression) {
            $selectFields[] = $expression . " AS `$field`";
        }
        $selectFieldsList = implode(",\n    ", $selectFields);

        $sql = "INSERT INTO `ai-services-new`.$targetTableRef (\n    $targetFieldsList\n)\n";
        $sql .= "SELECT\n    $selectFieldsList\n";
        $sql .= "FROM `ai-services`.$sourceTableRef;";

        return $sql;
    }

    private function mapRenamedField($targetField, $sourceFields)
    {
        $lowerTarget = strtolower($targetField);

        // 常见字段映射规则
        $mappingRules = [
            'created_at' => ['create_time', 'createdat'],
            'updated_at' => ['update_time', 'updatedat'],
            'created_by' => ['creator', 'createdby'],
            'updated_by' => ['updater', 'updatedby'],
            'is_deleted' => ['deleted', 'isdeleted'],
            'deleted_at' => ['delete_time', 'deletedat'],
            'tenant_id' => ['tenantid'],
        ];

        // 检查是否有匹配的映射规则
        foreach ($mappingRules as $target => $sources) {
            if ($lowerTarget === $target) {
                foreach ($sources as $source) {
                    if (in_array($source, $sourceFields)) {
                        return "`$source`";
                    }
                }
            }
        }

        return null;
    }

    private function generateDefaultValue($targetField, $sourceFields)
    {
        $lowerTarget = strtolower($targetField);

        // 处理删除相关字段
        if ($lowerTarget === 'is_deleted') {
            return '1'; // 默认设置为1
        }

        if ($lowerTarget === 'deleted_at' || $lowerTarget === 'deleted_by') {
            return 'NULL'; // 默认设置为NULL
        }

        // 处理时间字段
        if (strpos($lowerTarget, 'time') !== false ||
            strpos($lowerTarget, 'date') !== false) {
            return 'NOW()';
        }

        // 处理用户字段
        if (strpos($lowerTarget, 'by') !== false ||
            strpos($lowerTarget, 'user') !== false) {
            return "'System'";
        }

        // 布尔值字段
        if (strpos($lowerTarget, 'is_') === 0) {
            return '0';
        }

        // 标识字段
        if (strpos($lowerTarget, 'id') !== false) {
            return 'NULL';
        }

        // 默认处理
        return 'NULL';
    }

    private function applySpecialMappings($fieldMappings, $sourceFields)
    {
        foreach ($fieldMappings as $field => &$expression) {
            $lowerField = strtolower($field);

            // 状态字段转换 (0/1 → 1/2)
            if ($lowerField === 'status') {
                if (in_array('status', $sourceFields)) {
                    $expression = "CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 ELSE 1 END";
                } else {
                    $expression = "1"; // 默认正常状态
                }
            }

            // 删除标志转换 (bit → tinyint)
            if ($lowerField === 'is_deleted' && in_array('deleted', $sourceFields)) {
                $expression = "CAST(`deleted` AS UNSIGNED)";
            }

            // 时间字段智能处理
            if (($lowerField === 'created_at' || $lowerField === 'updated_at') &&
                strpos($expression, '`') !== false) {
                $expression = "COALESCE($expression, NOW())";
            }

            // 创建者字段智能处理
            if (($lowerField === 'created_by' || $lowerField === 'updated_by') &&
                strpos($expression, '`') !== false) {
                $expression = "COALESCE($expression, 'System')";
            }
        }

        return $fieldMappings;
    }
}