<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use File;

class GenerateFile extends Command
{
    protected $signature = 'generate:file
        {--tables= : 指定表名（多个用逗号分隔，必填）}
        {--prefix= : 要移除的表前缀}
        {--output= : 输出子目录（如 Admin 或 Admin/Root）}
        {--force : 强制覆盖已存在文件}
        {--delete : 仅删除文件，不生成}';

    protected $description = '统一生成或删除 Controller、Enum、Model、Service、Validated 文件（默认生成全部类型）';

    public function handle()
    {
        $tables = $this->option('tables');

        if (empty($tables)) {
            $this->error("<fg=red>必须指定 --tables 参数，多个表用逗号分隔</>");
            return 1;
        }

        $isDelete = $this->option('delete');

        // 文件类型和子命令对应
        $commands = [
            'controller' => 'Controller',
            'enum'       => 'Enum',
            'model'      => 'Model',
            'service'    => 'Service',
            'validated'  => 'Validated',
        ];

        // 类型对应 Laravel 默认目录
        $typeDirs = [
            'Controller' => 'Http/Controllers',
            'Model'      => 'Models',
            'Service'    => 'Services',
            'Validated'  => 'Validates',  // ✅ 修正为复数
            'Enum'       => 'Enums',
        ];

        // 类型对应命名空间
        $typeNamespaces = [
            'Controller' => 'App\Http\Controllers',
            'Model'      => 'App\Models',
            'Service'    => 'App\Services',
            'Validated'  => 'App\Validates',  // ✅ 修正为复数
            'Enum'       => 'App\Enums',
        ];

        $tableList = explode(',', $tables);
        $outputPath = $this->formatOutputPath($this->option('output'));
        $prefix = $this->option('prefix') ?? '';

        foreach ($commands as $key => $typeName) {
            foreach ($tableList as $table) {
                $classBaseName = Str::studly(Str::replaceFirst($prefix, '', $table));

                // Enum 文件名前缀特殊处理
                if ($typeName === 'Enum') {
                    $fileName = 'Enum' . $classBaseName . '.php';
                } else {
                    $fileName = $classBaseName . $typeName . '.php';
                }

                // 修正路径，加上 app 目录和 Laravel 默认子目录
                $subDir = $typeDirs[$typeName] ?? $typeName;
                $dir = base_path('app' . DIRECTORY_SEPARATOR . $subDir . ($outputPath ? DIRECTORY_SEPARATOR . $outputPath : ''));
                $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;

                // 自动计算命名空间
                $namespace = $typeNamespaces[$typeName] ?? 'App';
                if ($outputPath) {
                    $namespace .= '\\' . str_replace('/', '\\', $outputPath);
                }

                // 打印命令和命名空间
                $cmdPreview = "<fg=green>" . ($isDelete ? '删除文件' : '生成文件') . "</> <fg=yellow>{$filePath}</> <fg=cyan>namespace:</> <fg=white>{$namespace}</>";
                $this->line(str_repeat('=', 60));
                $this->line("<fg=cyan>准备执行操作：</>");
                $this->line($cmdPreview);
                $this->line(str_repeat('=', 60));

                if ($isDelete) {
                    // 删除文件操作
                    if (File::exists($filePath)) {
                        if ($this->option('force') || $this->confirm("是否删除文件：{$filePath} ?", true)) {
                            File::delete($filePath);
                            $this->info("<fg=green>已删除：</> {$filePath}");
                        }
                    } else {
                        $this->line("<fg=yellow>不存在：</> {$filePath}");
                    }
                } else {
                    // 调用生成子命令
                    $options = [
                        '--tables' => $table,
                    ];

                    if ($outputPath) {
                        $options['--output'] = $outputPath;
                    }
                    if ($prefix) {
                        $options['--prefix'] = $prefix;
                    }
                    if ($this->option('force')) {
                        $options['--force'] = true;
                    }

                    $commandName = "{$key}:generate";

                    $cmdLine = "php artisan {$commandName}";
                    foreach ($options as $optKey => $optVal) {
                        if ($optKey === '--force') {
                            $cmdLine .= " {$optKey}";
                        } else {
                            $cmdLine .= " {$optKey}=\"{$optVal}\"";
                        }
                    }

                    $this->line("<fg=green>执行生成命令：</> {$cmdLine}");
                    $this->call($commandName, $options);
                }
            }
        }

        return 0;
    }

    protected function formatOutputPath($output)
    {
        if (!$output) return '';
        $parts = explode('/', $output);
        return implode('/', array_map(function ($part) {
            return Str::ucfirst($part);
        }, $parts));
    }
}
