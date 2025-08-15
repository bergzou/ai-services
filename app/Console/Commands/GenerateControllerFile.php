<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateControllerFile extends Command
{
    protected $signature = 'controller:generate
                            {--tables= : 指定表名（多个用逗号分隔，不指定则生成所有表）}
                            {--prefix= : 要移除的表前缀}
                            {--output= : 控制器输出目录（默认 app/Http/Controllers）}
                            {--force : 强制覆盖已存在文件}';

    protected $description = '根据表名生成控制器文件';

    public function handle()
    {
        $connection =  config('database.default');
        $prefix = $this->option('prefix') ?: '';
        $tablesOption = $this->option('tables');
        $force = $this->option('force');


        $output = trim($this->option('output'), '/');


        $namespaceSuffix = '';
        if ($output) {
            $parts = array_map(function ($part) {
                return Str::studly($part);
            }, explode('/', $output));
            $namespaceSuffix = implode('\\', $parts); // namespace 用 \
            $pathSuffix = implode('/', $parts);       // 文件路径用 /
        } else {
            $pathSuffix = '';
        }


        $baseNamespace = 'App\\Http\\Controllers' . ($namespaceSuffix ? '\\' . $namespaceSuffix : '');
        $basePath = app_path('Http/Controllers' . ($pathSuffix ? '/' . $pathSuffix : ''));

        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }


        $serviceNamespace = 'App\\Services' . ($namespaceSuffix ? '\\' . $namespaceSuffix : '');


        // 获取表名
        $tables = $tablesOption
            ? explode(',', $tablesOption)
            : array_map('current', DB::connection($connection)->select('SHOW TABLES'));

        foreach ($tables as $table) {
            $table = trim($table);

            // 移除前缀
            if ($prefix && Str::startsWith($table, $prefix)) {
                $table = substr($table, strlen($prefix));
            }

            $className = Str::studly($table) . 'Controller';
            $serviceName = Str::studly($table) . 'Service';
            $serviceNamespace = 'App\\Services' . ($namespaceSuffix ? '\\' . $namespaceSuffix : '');

            $filePath = $basePath . '/' . $className . '.php';

            if (file_exists($filePath) && !$force) {
                $this->warn("跳过 {$className}，文件已存在");
                continue;
            }

            $content = <<<PHP
<?php

namespace {$baseNamespace};

use App\Exceptions\BusinessException;
use App\Helpers\AopProxy;
use App\Http\Controllers\BaseController;
use App\Interfaces\ControllerInterface;
use App\Libraries\Response;
use {$serviceNamespace}\\{$serviceName};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {$className} extends BaseController implements ControllerInterface
{
    /**
     * 获取数据列表
     * @param Request \$request 请求参数
     * @return JsonResponse 列表数据（JSON 格式）
     */
    public function getList(Request \$request): JsonResponse
    {
        \$params = \$request->all();

        \$services = new {$serviceName}();

        \$result = \$services->getList(\$params);

        return Response::success(\$result);
    }

    /**
     * 添加新数据
     * @param Request \$request 请求参数
     * @return JsonResponse 添加结果（JSON 格式）
     * @throws BusinessException
     */
    public function add(Request \$request): JsonResponse
    {
        \$params = \$request->all();

        \$services = AopProxy::make({$serviceName}::class);

        \$result = \$services->add(\$params);

        return Response::success(\$result);
    }

    /**
     * 更新现有数据
     * @param Request \$request 请求参数
     * @return JsonResponse 更新结果（JSON 格式）
     * @throws BusinessException
     */
    public function update(Request \$request): JsonResponse
    {
        \$params = \$request->all();

        \$services = AopProxy::make({$serviceName}::class);

        \$result = \$services->update(\$params);

        return Response::success(\$result);
    }

    /**
     * 删除数据
     * @param Request \$request 请求参数
     * @return JsonResponse 删除结果（JSON 格式）
     * @throws BusinessException
     */
    public function delete(Request \$request): JsonResponse
    {
        \$params = \$request->all();

        \$services = AopProxy::make({$serviceName}::class);

        \$result = \$services->delete(\$params);

        return Response::success(\$result);
    }

    /**
     * 获取单条数据详情
     * @param Request \$request 请求参数
     * @return JsonResponse 详情数据（JSON 格式）
     * @throws BusinessException
     */
    public function getDetail(Request \$request): JsonResponse
    {
        \$params = \$request->all();

        \$services = new {$serviceName}();

        \$result = \$services->getDetail(\$params);

        return Response::success(\$result);
    }
}

PHP;

            file_put_contents($filePath, $content);
            $this->info("已生成控制器: {$filePath}");
        }

        $this->info('控制器生成完成');
    }
}
