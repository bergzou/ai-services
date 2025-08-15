<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateServiceFile extends Command
{
    protected $signature = 'service:generate
                            {--tables= : 指定表名（多个用逗号分隔）}
                            {--prefix= : 要移除的表前缀}
                            {--output= : 服务类输出目录（默认 app/Services）}
                            {--connection= : 数据库连接名称（默认 config/database.php 的 default 连接）}
                            {--force : 强制覆盖已存在文件}';

    protected $description = 'Generate service files from database tables (version1 + version2: createBase + createGetList)';

    public function handle()
    {
        $connection   =  config('database.default');
        $prefix       = $this->option('prefix') ?: '';
        $tablesOption = $this->option('tables');
        $force        = (bool)$this->option('force');


        // 获取表列表（与原逻辑兼容）
        if (!empty($tablesOption)) {
            $tables = array_filter(array_map('trim', explode(',', $tablesOption)));
        } else {
            $rows = DB::connection($connection)->select('SHOW TABLES');
            $tables = array_map(function ($r) { $arr = (array)$r; return array_values($arr)[0] ?? null; }, $rows);
            $tables = array_filter($tables);
        }

        if (empty($tables)) {
            $this->error('未找到需要生成的表');
            return 1;
        }

        // 执行：先 createBase（版本1），再 createGetList（版本2）
        foreach ($tables as $originTableName) {
            $originTableName = trim($originTableName);
            if ($originTableName === '') continue;


            $this->createBase($originTableName, $prefix, $connection, $force);

            $this->createGetList($originTableName, $prefix, $connection, $force);

            $this->createAdd($originTableName, $prefix, $connection, $force);

            $this->createUpdate($originTableName, $prefix, $connection, $force);

            $this->createDelete($originTableName, $prefix, $connection, $force);

            $this->createGetDetail($originTableName, $prefix, $connection, $force);

        }

        $this->info('Service 文件全部生成完成');
        return 0;
    }


    public function getServicePath($ucModel): string
    {
        $outputOption = $this->option('output');
        if ($outputOption) {
            // 将每一层目录首字母大写
            $parts = array_map(function ($part) {
                return Str::studly($part);
            }, explode('/', trim($outputOption, '/')));
            $subPath = implode('/', $parts);
            $servicePath = app_path("Services/{$subPath}/{$ucModel}Service.php");
        } else {
            $servicePath = app_path("Services/{$ucModel}Service.php");
        }
        return $servicePath;
    }


    /**
     * 版本1：生成基础 Service 文件（header、use 等）
     * - Service、Model、Validated 命名空间根据 output 自动加载
     * - 如果 Enum 文件存在，则在头部添加 use App\Enums\EnumX;
     */
    protected function createBase(string $originTableName, string $prefix, string $connection, bool $force)
    {
        $table = $originTableName;
        if ($prefix && Str::startsWith($originTableName, $prefix)) {
            $table = Str::replaceFirst($prefix, '', $originTableName);
        }

        $classBase    = Str::studly($table);                  // e.g. AiProviders
        $className    = $classBase . 'Service';               // e.g. AiProvidersService
        $modelName    = $classBase . 'Model';
        $validateName = $classBase . 'Validated';
        $enumName     = 'Enum' . $classBase;

        // 输出目录与命名空间：默认 app/Services，--output 为子目录
        $outputSubPath = trim($this->option('output') ?: '', '/');
        $servicePath   = app_path('Services' . ($outputSubPath ? '/' . Str::studly($outputSubPath) : ''));
        $serviceNs     = 'App\\Services' . ($outputSubPath ? '\\' . str_replace('/', '\\', Str::studly($outputSubPath)) : '');

        $modelPath     = app_path('Models' . ($outputSubPath ? '/' . Str::studly($outputSubPath) : ''));
        $modelNs       = 'App\\Models' . ($outputSubPath ? '\\' . str_replace('/', '\\', Str::studly($outputSubPath)) : '');

        $validatePath  = app_path('Validates' . ($outputSubPath ? '/' . Str::studly($outputSubPath) : ''));
        $validateNs    = 'App\\Validates' . ($outputSubPath ? '\\' . str_replace('/', '\\', Str::studly($outputSubPath)) : '');

        if (!is_dir($servicePath)) {
            mkdir($servicePath, 0755, true);
            $this->info("创建目录：{$servicePath}");
        }

        $filePath = $servicePath . '/' . $className . '.php';
        if (file_exists($filePath) && ! $force) {
            $this->warn("跳过 {$className}（文件已存在）：{$filePath}");
            return;
        }

        // 判断 Enum 文件是否存在（仅用于生成 use 语句）
        $enumFilePath = app_path("Enums/{$enumName}.php");
        $enumUseLine = '';
        if (file_exists($enumFilePath)) {
            $enumUseLine = "use App\\Enums\\{$enumName};\n";
        }

        // 生成基础文件内容（严格保持头部 use 顺序）
        $content = "<?php\n\n";
        $content .= "namespace {$serviceNs};\n\n";
        $content .= "use App\\Exceptions\\BusinessException;\n";
        $content .= "use App\\Enums\\EnumCommon;\n";
        $content .= "use App\\Libraries\\Common;\n";
        $content .= "use App\\Libraries\\Snowflake;\n";
        $content .= "use {$modelNs}\\{$modelName};\n";
        $content .= "use App\\Services\\BaseService;\n";
        $content .= "use App\\Services\\CommonService;\n";
        $content .= "use {$validateNs}\\{$validateName};\n";
        $content .= $enumUseLine;
        $content .= "use Exception;\n";
        $content .= "use Illuminate\\Support\\Facades\\DB;\n\n";
        $content .= "class {$className} extends BaseService\n{\n\n}\n";

        file_put_contents($filePath, $content);
        $this->info("已生成基础服务类：{$filePath}");
    }


    /**
     * 版本2：在基础 Service 文件上追加 getList 方法（如果不存在）
     * - 自动根据表字段生成 $whereMap 和 $fields（排除 is_deleted/deleted_at/deleted_by）
     * - int -> where； varchar/char/string -> like + operator like_after； datetime/date/timestamp -> whereBetween
     * - text/longtext 类型直接跳过（既不放入 whereMap 也不放入 fields）
     * - 如果 Enum 文件在磁盘上存在，且枚举类定义了对应的 get<Field>Map 方法，则为该字段生成 映射行（生成时检测）
     * - 不在此方法中变动头部 use（createBase 负责 enum use）
     */


    protected function createGetList(string $originTableName, string $prefix, string $connection, bool $force)
    {
        $table = $originTableName;
        if ($prefix && Str::startsWith($originTableName, $prefix)) {
            $table = Str::replaceFirst($prefix, '', $originTableName);
        }

        $classBase = Str::studly($table); // e.g. SystemUsers
        $className = $classBase . 'Service';
        $modelName = $classBase . 'Model';
        $enumName  = 'Enum' . $classBase;

        $outputSubPath = trim($this->option('output') ?: '', '/');

        // Service 文件路径
        $filePath = app_path('Services' . ($outputSubPath ? '/' . Str::studly($outputSubPath) : '') . '/' . $className . '.php');
        if (!file_exists($filePath)) {
            $this->warn("基础服务文件不存在，跳过追加 getList: {$filePath}");
            return;
        }

        // 枚举文件路径（根据 --output）
        $enumPath = app_path('Enums' . ($outputSubPath ? '/' . Str::studly($outputSubPath) : '') . '/' . $enumName . '.php');

        // 读取表字段
        try {
            $schemaManager = DB::connection($connection)->getDoctrineSchemaManager();
            $platform = $schemaManager->getDatabasePlatform();
            $platform->registerDoctrineTypeMapping('enum', 'string');
            $columns = $schemaManager->listTableColumns($originTableName);
        } catch (\Throwable $e) {
            $this->error("读取表结构失败：{$originTableName}，错误：" . $e->getMessage());
            return;
        }

        $skipFields = ['is_deleted', 'deleted_at', 'deleted_by'];
        $whereMap = [];
        $fieldsArr = [];

        foreach ($columns as $colObj) {
            $colName = $colObj->getName();
            $typeName = strtolower($colObj->getType()->getName());

            if (in_array($colName, $skipFields, true)) continue;
            if (in_array($typeName, ['text', 'longtext'])) continue;

            $fieldsArr[] = $colName;

            if (in_array($typeName, ['integer', 'bigint', 'smallint', 'tinyint'])) {
                $whereMap[$colName] = ['field' => $colName, 'search' => 'where'];
            } elseif (in_array($typeName, ['string', 'char', 'varchar'])) {
                $whereMap[$colName] = ['field' => $colName, 'search' => 'like', 'operator' => 'like_after'];
            } elseif (in_array($typeName, ['datetime', 'datetimetz', 'timestamp', 'date', 'time'])) {
                $whereMap[$colName] = ['field' => $colName, 'search' => 'whereBetween'];
            } else {
                $whereMap[$colName] = ['field' => $colName, 'search' => 'where'];
            }
        }

        // 格式化 whereMap
        $maxKeyLen = 0;
        foreach ($whereMap as $k => $_) $maxKeyLen = max($maxKeyLen, strlen($k));
        $whereLines = [];
        foreach ($whereMap as $k => $v) {
            $quotedKey = "'{$k}'";
            $padQuoted = str_pad($quotedKey, $maxKeyLen + 4);
            $innerParts = [];
            foreach ($v as $innerK => $innerV) {
                $innerParts[] = "'{$innerK}' => '{$innerV}'";
            }
            $whereLines[] = "            {$padQuoted} => [" . implode(', ', $innerParts) . "],";
        }
        $whereText = "[\n" . implode("\n", $whereLines) . "\n        ]";

        // 格式化 fields
        $fieldsQuoted = array_map(fn($f) => "'{$f}'", $fieldsArr);
        $fieldsText = "[" . implode(',', $fieldsQuoted) . "]";

        // 枚举映射文本
        $enumMappingLines = [];
        if (file_exists($enumPath)) {
            require_once $enumPath;
            if (class_exists($enumName)) {
                if (method_exists($enumName, 'getStatusMap')) {
                    $enumMappingLines[] = "                \$item['status_name'] = {$enumName}::getStatusMap(\$item['status'] ?? null);";
                }
                if (method_exists($enumName, 'getLevelMap')) {
                    $enumMappingLines[] = "                \$item['level_name']  = {$enumName}::getLevelMap(\$item['level'] ?? null);";
                }
            }
        }
        $enumMappingText = $enumMappingLines ? implode("\n", $enumMappingLines) . "\n" : '';

        // 生成 getList 方法模板
        $getListTemplate = <<<'TPL'

    /**
     * 获取{{CLASS_BASE}}列表（带筛选和状态枚举转换）
     * @param array $params 筛选参数（支持字段映射）
     * @return array 列表数据（包含 *_name 枚举名称字段）
     */
    public function getList(array $params): array
    {
        // 初始化模型
        $model = new {{MODEL_CLASS}}();

        // 定义查询条件映射
        $whereMap = {{WHERE_MAP}};

        // 定义需要查询的字段列表
        $fields = {{FIELDS}};

        // 构建查询：设置字段、转换筛选条件、按ID升序排序，获取多条记录
        $result = $model
            ->setFields($fields)
            ->convertConditions($params, $whereMap)
            ->setOrderBy(['id' => 'asc'])
            ->getPaginateResults();

        // 补充枚举描述
        if (!empty($result['list'])) {
            foreach ($result['list'] as &$item) {
{{ENUM_MAPPING}}            }
        }

        return $result;
    }

TPL;

        $replacements = [
            '{{CLASS_BASE}}' => $classBase,
            '{{MODEL_CLASS}}'=> $modelName,
            '{{WHERE_MAP}}'  => $whereText,
            '{{FIELDS}}'     => $fieldsText,
            '{{ENUM_MAPPING}}'=> $enumMappingText,
        ];

        $getListMethod = str_replace(array_keys($replacements), array_values($replacements), $getListTemplate);

        // 插入 getList 方法
        $fileContent = file_get_contents($filePath);
        if (strpos($fileContent, 'function getList') !== false) {
            $this->warn("getList 已存在于 {$filePath}，跳过追加");
            return;
        }

        $newContent = preg_replace('/}\s*$/', $getListMethod . "\n}\n", $fileContent);
        if ($newContent === null) {
            $this->error("插入 getList 方法失败：{$filePath}");
            return;
        }

        file_put_contents($filePath, $newContent);
        $this->info("已追加 getList 方法：{$filePath}");
    }






    private function createAdd(string $originTableName, string $prefix, string $connection, bool $force = false): void
    {
        $ucModel = Str::studly(str_replace($prefix, '', $originTableName)); // 保留复数
        $lcModel = Str::camel($ucModel);



        $servicePath = $this->getServicePath($ucModel);

        if (!file_exists($servicePath)) {
            echo "Service 文件不存在，无法追加 add 方法: {$servicePath}\n";
            return;
        }


        $content = file_get_contents($servicePath);
        if (strpos($content, 'public function add(') !== false) {
            echo "add 方法已存在，跳过: {$servicePath}\n";
            return;
        }

        // 读取表字段信息
        $columns = \DB::connection($connection)->select("SHOW FULL COLUMNS FROM {$originTableName}");

        $insertFields = [];
        foreach ($columns as $col) {
            $field = strtolower($col->Field);

            // 跳过主键 id 和软删除相关字段
            if (in_array($field, ['id', 'is_deleted', 'deleted_at', 'deleted_by'], true)) {
                continue;
            }

            // 固定值字段处理
            $fixedMap = [
                'created_by' => "\$this->userInfo['user_name']",
                'created_at' => "date('Y-m-d H:i:s')",
                'updated_by' => "\$this->userInfo['user_name']",
                'updated_at' => "date('Y-m-d H:i:s')",
                'tenant_id'  => "\$this->userInfo['tenant_id']",
            ];

            if (isset($fixedMap[$field])) {
                $insertFields[] = "'{$field}' => {$fixedMap[$field]},";
                continue;
            }

            // 必填（NOT NULL 且无默认值）
            if ($col->Null === 'NO' && $col->Default === null) {
                $insertFields[] = "'{$field}' => \$params['{$field}'],";
            } else {
                $insertFields[] = "'{$field}' => \$params['{$field}'] ?? '',";
            }
        }

        $insertDataStr = implode("\n                ", $insertFields);

        $addMethod = <<<PHP

    /**
     * 添加-{$ucModel}
     * @param array \$params
     * @return array
     * @throws BusinessException
     * @throws Exception
     */
    public function add(array \$params): array
    {
        try {
            DB::beginTransaction();

            // 参数验证
            \$validated = new {$ucModel}Validated(\$params, 'add');
            \$messages = \$validated->isRunFail();
            if (!empty(\$messages)){
                throw new BusinessException(\$messages, '400000');
            }

            // 过滤参数
            \${$lcModel}Model = new {$ucModel}Model();
            \$params = CommonService::filterRecursive(\$params, \${$lcModel}Model->fillable);

            // 自定义业务验证
            \$params = \$this->validated{$ucModel}(\$params);

            // 构造插入数据（基于表结构自动生成）
            \$insertData[] = [
                {$insertDataStr}
            ];

            // 执行插入
            \$result = \${$lcModel}Model->insert(\$insertData);
            if (\$result !== true) {
                throw new BusinessException(__('errors.600000'), '600000');
            }

            DB::commit();
        } catch (BusinessException \$e) {
            DB::rollBack();
            throw new BusinessException(\$e);
        } catch (Exception \$e) {
            DB::rollBack();
            throw new Exception(\$e);
        }
        return [];
    }

    /**
     * 业务验证-{$ucModel}
     * @param array \$params
     * @param mixed \$info
     * @return array
     */
    public function validated{$ucModel}(array \$params, \$info = null): array
    {
        return \$params;
    }

PHP;

        $content = preg_replace('/}\s*$/', $addMethod . "}\n", $content);

        file_put_contents($servicePath, $content);

    }


    private function createUpdate(string $originTableName, string $prefix, string $connection, bool $force = false): void
    {
        $ucModel = Str::studly(str_replace($prefix, '', $originTableName)); // 保留复数
        $lcModel = Str::camel($ucModel);



        $servicePath = $this->getServicePath($ucModel);
        if (!file_exists($servicePath)) {
            echo "Service 文件不存在，无法追加 update 方法: {$servicePath}\n";
            return;
        }

        $content = file_get_contents($servicePath);
        if (strpos($content, 'public function update(') !== false) {
            echo "update 方法已存在，跳过: {$servicePath}\n";
            return;
        }

        // 读取表字段信息
        $columns = \DB::connection($connection)->select("SHOW FULL COLUMNS FROM {$originTableName}");

        // 要排除的字段
        $excludeFields = [
            'id',
            'snowflake_id',
            'created_at',
            'created_by',
            'is_deleted',
            'deleted_at',
            'deleted_by'
        ];

        $updateFields = [];
        foreach ($columns as $col) {
            $field = $col->Field;
            $lowerField = strtolower($field);

            // 跳过排除字段
            if (in_array($lowerField, $excludeFields, true)) {
                continue;
            }

            // 固定值字段
            if ($lowerField === 'updated_by') {
                $updateFields[] = "'{$field}' => \$this->userInfo['user_name'],";
                continue;
            }
            if ($lowerField === 'updated_at') {
                $updateFields[] = "'{$field}' => date('Y-m-d H:i:s'),";
                continue;
            }

            // 必填（NOT NULL 且无默认值）
            if ($col->Null === 'NO' && $col->Default === null) {
                $updateFields[] = "'{$field}' => \$params['{$field}'],";
            } else {
                $updateFields[] = "'{$field}' => \$params['{$field}'] ?? '',";
            }
        }

        $updateDataStr = implode("\n                ", $updateFields);

        $updateMethod = <<<PHP

    /**
     * 更新-{$ucModel}
     * @param array \$params
     * @return array
     * @throws BusinessException
     * @throws Exception
     */
    public function update(array \$params): array
    {
        try {
            DB::beginTransaction();

            // 参数验证
            \$validated = new {$ucModel}Validated(\$params, 'update');
            \$messages = \$validated->isRunFail();
            if (!empty(\$messages)){
                throw new BusinessException(\$messages, '400000');
            }

            // 查询目标记录
            \${$lcModel}Model = new {$ucModel}Model();
            \$info = \${$lcModel}Model->getSingleRecord(['snowflake_id' => \$params['snowflake_id']]);
            if (empty(\$info)) {
                throw new BusinessException(__('errors.500014'), '500014');
            }

            // 自定义业务验证
            \$params = \$this->validated{$ucModel}(\$params, \$info);

            // 过滤允许更新的字段
            \$params = CommonService::filterRecursive(\$params, \${$lcModel}Model->fillable);

            // 构造更新数据（基于表结构自动生成）
            \$updateData = [
                {$updateDataStr}
            ];

            // 执行更新
            \$result = \${$lcModel}Model::query()->where('snowflake_id', \$params['snowflake_id'])->update(\$updateData);
            if (!\$result) {
                throw new BusinessException(__('errors.600000'), '600000');
            }

            DB::commit();
        } catch (BusinessException \$e) {
            DB::rollBack();
            throw new BusinessException(\$e);
        } catch (Exception \$e) {
            DB::rollBack();
            throw new Exception(\$e);
        }
        return [];
    }

PHP;

        $content = preg_replace('/}\s*$/', $updateMethod . "}\n", $content);

        file_put_contents($servicePath, $content);
    }

    private function createDelete(string $originTableName, string $prefix, string $connection, bool $force = false): void
    {
        $ucModel = Str::studly(str_replace($prefix, '', $originTableName)); // 模型名
        $lcModel = Str::camel($ucModel);



        $servicePath = $this->getServicePath($ucModel);

        if (!file_exists($servicePath)) {
            echo "Service 文件不存在，无法追加 delete 方法: {$servicePath}\n";
            return;
        }

        $content = file_get_contents($servicePath);
        if (strpos($content, 'public function delete(') !== false) {
            echo "delete 方法已存在，跳过: {$servicePath}\n";
            return;
        }

        $deleteMethod = <<<PHP

    /**
     * 删除 - {$ucModel}
     * @param array \$params
     * @return array
     * @throws BusinessException
     * @throws Exception
     */
    public function delete(array \$params): array
    {
        try {
            // 开启事务
            DB::beginTransaction();

            // 参数验证
            \$validated = new {$ucModel}Validated(\$params, 'delete');
            \$messages = \$validated->isRunFail();
            if (!empty(\$messages)) {
                throw new BusinessException(\$messages, '400000');
            }

            // 查询目标记录
            \${$lcModel}Model = new {$ucModel}Model();
            \$info = \${$lcModel}Model->getSingleRecord(['snowflake_id' => \$params['snowflake_id']]);
            if (empty(\$info)) {
                throw new BusinessException(__('errors.500022'), '500022');
            }

            // 构造软删除数据
            \$updateData = [
                'is_deleted' => EnumCommon::IS_DELETED_1,
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => \$this->userInfo['user_name'],
            ];

            // 执行软删除
            \$result = \${$lcModel}Model::query()
                ->where('snowflake_id', \$params['snowflake_id'])
                ->update(\$updateData);
            if (!\$result) {
                throw new BusinessException(__('errors.600000'), '600000');
            }

            DB::commit();
        } catch (BusinessException \$e) {
            DB::rollBack();
            throw new BusinessException(\$e);
        } catch (Exception \$e) {
            DB::rollBack();
            throw new Exception(\$e);
        }
        return [];
    }

PHP;

        $content = preg_replace('/}\s*$/', $deleteMethod . "}\n", $content);

        file_put_contents($servicePath, $content);
    }


    private function createGetDetail(string $originTableName, string $prefix, string $connection, bool $force = false): void
    {
        $ucModel = Str::studly(str_replace($prefix, '', $originTableName)); // 保留复数或原表形式
        $lcModel = Str::camel($ucModel);


        $servicePath = $this->getServicePath($ucModel);
        if (!file_exists($servicePath)) {
            echo "Service 文件不存在，无法追加 getDetail 方法: {$servicePath}\n";
            return;
        }

        $content = file_get_contents($servicePath);
        if (strpos($content, 'public function getDetail(') !== false) {
            echo "getDetail 方法已存在，跳过: {$servicePath}\n";
            return;
        }

        // 使用 DB 获取表字段（和 createAdd/createUpdate 保持一致，避免使用 Schema facade）
        $columns = DB::connection($connection)->select("SHOW FULL COLUMNS FROM {$originTableName}");

        // 排除不需要查询的字段
        $excludeFields = ['is_deleted', 'deleted_at', 'deleted_by'];

        $fieldsArr = [];
        foreach ($columns as $col) {
            $field = $col->Field;
            if (in_array(strtolower($field), $excludeFields, true)) {
                continue;
            }
            $fieldsArr[] = "'{$field}'";
        }

        if (empty($fieldsArr)) {
            echo "未读取到表字段，跳过 getDetail: {$originTableName}\n";
            return;
        }

        $fieldsString = '[' . implode(', ', $fieldsArr) . ']';

        $getDetailMethod = <<<PHP

    /**
     * 获取{$ucModel}详情（单条记录）
     * @param array \$params 查询参数（需包含snowflake_id标识目标记录）
     * @return array 详情数组
     * @throws BusinessException
     * @throws Exception
     */
    public function getDetail(array \$params): array
    {
        try {
            // 保持和其它方法一致，使用事务以保证一致性（只读也可以，但与现有风格统一）
            DB::beginTransaction();

            // 参数验证（通过 snowflake_id 查询，使用 delete 场景或你需要的场景）
            \$validated = new {$ucModel}Validated(\$params, 'delete');
            \$messages = \$validated->isRunFail();
            if (!empty(\$messages)) {
                throw new BusinessException(\$messages, '400000');
            }

            // 需要查询的字段（由表结构自动生成，排除了软删除字段）
            \$fields = {$fieldsString};

            // 查询单条记录
            \$result = (new {$ucModel}Model())
                ->setFields(\$fields)
                ->getSingleRecord(['snowflake_id' => \$params['snowflake_id']]);
            if (empty(\$result)) {
                throw new BusinessException(__('errors.500022'), '500022');
            }

            DB::commit();
        } catch (BusinessException \$e) {
            DB::rollBack();
            throw new BusinessException(\$e);
        } catch (Exception \$e) {
            DB::rollBack();
            throw new Exception(\$e);
        }
        return \$result;
    }

PHP;

        // 插入到类的末尾
        $content = preg_replace('/}\s*$/', $getDetailMethod . "}\n", $content);
        file_put_contents($servicePath, $content);
    }


}
