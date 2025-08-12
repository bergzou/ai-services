<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateFile extends Command
{
    protected $signature = 'generate:file
        {type : 生成类型（controller|enum|model|service|validated|all）}
        {--tables= : 指定表名（多个用逗号分隔，必填）}
        {--prefix= : 要移除的表前缀}
        {--output= : 输出子目录（如 Admin 或 Admin/Root）}
        {--force : 强制覆盖已存在文件}
        {--lang-start= : 多语言编码起始值（仅 enum/validated 可用）}
        {--lang-file= : 多语言文件名（仅 enum/validated 可用）}';

    protected $description = '统一生成 Controller、Enum、Model、Service、Validated 等文件的命令';

    public function handle()
    {
        $type = strtolower($this->argument('type'));
        $tables = $this->option('tables');

        if (empty($tables)) {
            $this->error("必须指定 --tables 参数，多个表用逗号分隔");
            return 1;
        }

        $commands = [
            'controller' => 'controller:generate',
            'enum'       => 'enum:generate',
            'model'      => 'model:generate',
            'service'    => 'service:generate',
            'validated'  => 'validated:generate',
        ];

        $runTypes = $type === 'all' ? array_keys($commands) : [$type];

        foreach ($runTypes as $key) {
            if (!isset($commands[$key])) {
                $this->error("未知生成类型: {$key}");
                continue;
            }

            // 格式化 output（首字母大写 + 多级路径支持）
            $output = $this->formatOutputPath($this->option('output'));

            $options = [
                '--tables' => $tables,
                '--prefix' => $this->option('prefix'),
                '--output' => $output,
                '--force'  => $this->option('force'),
            ];

            if (in_array($key, ['enum', 'validated'])) {
                $options['--lang-start'] = $this->option('lang-start') ?: ($key === 'enum' ? 1 : 3);
                $options['--lang-file']  = $this->option('lang-file') ?: ($key === 'enum' ? 'enums' : 'validated');
            }

            // 打印执行命令
            $cmdPreview = "php artisan {$commands[$key]}";
            foreach ($options as $optKey => $optVal) {
                if ($optVal !== null && $optVal !== false) {
                    $cmdPreview .= " {$optKey}=\"{$optVal}\"";
                }
            }
            $this->info("执行: {$cmdPreview}");

            $this->call($commands[$key], $options);
        }

        return 0;
    }

    /**
     * 格式化输出路径：首字母大写 + 多级路径支持
     */
    protected function formatOutputPath($output)
    {
        if (!$output) return '';
        $parts = explode('/', $output);
        return implode('/', array_map(function ($part) {
            return Str::ucfirst($part);
        }, $parts));
    }
}
