<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * 多语言文件生成命令：自动生成多语言键值对文件（简体中文→繁体/英文等）
 * 支持自定义编码前缀、指定目标地区，自动处理重复检查和文件写入
 *
 * @example php artisan lang:generate "登录成功" "注册失败" --file=messages.php --start=5 --locales=zh-CN,en-US
 */
class GenerateLangFile extends Command
{
    /**
     * Artisan 命令签名（定义命令参数和选项）
     * - {chinese*}：必填参数，一个或多个待翻译的中文短语（空格分隔）
     * - --file：选填，输出文件名（默认 errors.php）
     * - --start：选填，起始编码前缀（1-9之间的数字，用于分组）
     * - --locales：选填，目标地区（逗号分隔，如 zh-CN,en-US）
     */
    protected $signature = 'lang:generate {chinese* : 一个或多个中文短语} 
                            {--file=errors.php : 输出文件名} 
                            {--start= : 起始编码前缀} 
                            {--locales= : 指定地区(逗号分隔)}';

    /**
     * 命令描述（通过 php artisan list 查看）
     */
    protected $description = '生成多语言键值对文件';

    /**
     * 地区到翻译服务语言代码的映射（用于调用翻译接口）
     * - zh-CN：简体中文 → 翻译服务使用 "zh"
     * - zh-HK：香港繁体 → 翻译服务使用 "yue"（粤语）
     * - zh-TW：台湾繁体 → 翻译服务使用 "cht"（繁体中文）
     * - en-US：美国英语 → 翻译服务使用 "en"
     */
    private $traditionalMaps = [
        'zh-CN' => 'zh',   // 简体中文直接使用 zh
        'zh-HK' => 'yue',  // 粤语中文使用 yue
        'zh-TW' => 'cht',  // 繁体中文使用 cht
        'en-US' => 'en',   // 英文使用 en
    ];

    /**
     * 命令执行入口（Artisan 框架自动调用）
     * @return int 命令退出码（0=成功，非0=失败）
     */
    public function handle()
    {
        try {
            // 1. 获取用户输入参数
            $phrases = $this->argument('chinese');       // 待处理的中文短语数组
            $filename = $this->option('file');           // 输出文件名
            $startPrefix = $this->option('start');       // 编码前缀（1-9）
            $localesOption = $this->option('locales');   // 指定地区参数

            // 2. 获取需要处理的多语言地区（如未指定则读取 lang 目录下的所有地区）
            $locales = $this->getLocales($localesOption);


            // 3. 验证短语是否已存在（避免重复生成）
            [$validPhrases, $duplicates] = $this->validatePhrases($phrases, $locales, $filename);

            // 4. 提示重复短语（已存在的短语不生成新编码）
            if (!empty($duplicates)) {
                $this->warn('以下短语已存在:');
                $this->table(['短语', '编码'], array_map(
                    fn($phrase, $code) => [$phrase, $code],
                    array_keys($duplicates),
                    array_values($duplicates)
                ));
            }

            // 5. 无有效短语时提前退出
            if (empty($validPhrases)) {
                $this->info('没有可生成的短语');
                return;
            }

            // 6. 以简体中文（zh-CN）为基础语言生成编码
            $baseLocale = 'zh-CN';
            $basePath = $this->getLocalePath($baseLocale, $filename);  // 基础语言文件路径
            $baseArray = $this->getLangArray($basePath);               // 读取现有基础语言数组

            // 7. 为每个有效短语生成唯一编码（如 500001、500002...）
            $generated = [];
            foreach ($validPhrases as $phrase) {
                $code = $this->generateCode($baseArray, $startPrefix);  // 生成编码（核心逻辑）
                $generated[$phrase] = $code;
                $baseArray[$code] = $phrase;  // 将新短语-编码对添加到基础语言数组
            }

            // 8. 对基础语言数组按键（编码）排序（确保文件内容有序）
            $this->sortArray($baseArray);

            // 9. 写入基础语言文件（zh-CN/文件名.php）
            $this->writeLangFile($basePath, $baseArray);

            // 10. 处理其他目标地区的语言文件（如 en-US、zh-TW）
            foreach ($locales as $locale) {
                if ($locale === $baseLocale) continue;  // 跳过已处理的基础语言

                $path = $this->getLocalePath($locale, $filename);  // 目标地区文件路径
                $array = $this->getLangArray($path);               // 读取现有目标语言数组

                // 为每个新生成的编码添加翻译后的短语
                foreach ($generated as $phrase => $code) {
                    // 跳过目标语言中已存在的短语
                    if (in_array($phrase, $array)) continue;

                    // 调用翻译服务获取目标语言的翻译结果（如简体→繁体/英文）
                    $value = $this->translatePhrase($phrase, $locale);
                    $array[$code] = $value;  // 将翻译结果添加到目标语言数组
                }

                // 排序并写入目标语言文件
                $this->sortArray($array);
                $this->writeLangFile($path, $array);
            }

            // 11. 输出生成结果（编码列表和文件路径）
            $this->showResults($generated, $locales, $filename);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());  // 输入参数错误提示
            return 1;
        } catch (\Exception $e) {
            $this->error('发生意外错误: '.$e->getMessage());  // 其他异常提示
            return 2;
        }
    }

    /**
     * 获取需要处理的多语言地区列表
     * @param string|null $localesOption 用户指定的地区参数（逗号分隔）
     * @return array 地区代码数组（如 ['zh-CN', 'en-US']）
     */
    private function getLocales($localesOption): array
    {
        // 若用户指定了地区，直接解析逗号分隔的字符串
        if ($localesOption) {
            return explode(',', $localesOption);
        }

        // 未指定时，读取 lang 目录下的所有子目录（自动识别现有地区）
        $langPath = lang_path();
        if (!File::exists($langPath)) {
            File::makeDirectory($langPath, 0755, true);  // 目录不存在则创建
        }

        return collect(File::directories($langPath))
            ->map(fn($path) => basename($path))  // 提取目录名（如 zh-CN）
            ->all();
    }

    /**
     * 获取指定地区的语言文件路径
     * @param string $locale 地区代码（如 zh-CN）
     * @param string $filename 文件名（如 errors.php）
     * @return string 文件绝对路径（如 /project/lang/zh-CN/errors.php）
     */
    private function getLocalePath(string $locale, string $filename): string
    {
        return lang_path("{$locale}/{$filename}");
    }

    /**
     * 读取语言文件内容（返回键值对数组）
     * @param string $path 语言文件路径
     * @return array 语言数组（如 ['500001' => '登录成功']）
     */
    private function getLangArray(string $path): array
    {
        if (!File::exists($path)) {
            return [];  // 文件不存在返回空数组
        }

        return File::getRequire($path);  // 读取并执行 PHP 文件，返回数组
    }

    /**
     * 验证短语是否已存在（避免重复生成编码）
     * @param array $phrases 待验证的短语数组
     * @param array $locales 需要检查的地区数组
     * @param string $filename 文件名
     * @return array [有效短语数组, 重复短语-编码映射]
     */
    private function validatePhrases(array $phrases, array $locales, string $filename): array
    {
        $valid = [];       // 有效短语（未重复）
        $duplicates = [];  // 重复短语（键：短语，值：已存在的编码）

        foreach ($phrases as $phrase) {
            $duplicateFound = false;

            // 检查所有目标地区的语言文件中是否已存在该短语
            foreach ($locales as $locale) {
                $path = $this->getLocalePath($locale, $filename);
                $array = $this->getLangArray($path);

                // 查找短语对应的编码（array_search 反向查找值对应的键）
                $code = array_search($phrase, $array);
                if ($code !== false) {
                    $duplicates[$phrase] = $code;  // 记录重复短语和对应的编码
                    $duplicateFound = true;
                    break;
                }
            }

            if (!$duplicateFound) {
                $valid[] = $phrase;  // 未重复的短语加入有效列表
            }
        }

        return [$valid, $duplicates];
    }

    /**
     * 生成唯一编码（核心逻辑）
     * @param array $array 基础语言数组（用于检查编码是否已存在）
     * @param string|null $startPrefix 起始编码前缀（1-9之间的数字）
     * @return string 生成的编码（如 500001）
     * @throws \InvalidArgumentException 前缀格式错误时抛出
     */
    private function generateCode(array &$array, ?string $startPrefix): string
    {
        // 验证前缀格式（必须是1-9的数字）
        if ($startPrefix !== null) {
            if (!in_array($startPrefix, range('1', '9'))) {
                throw new \InvalidArgumentException('--start 参数必须是1-9之间的数字');
            }
        }

        // 提取现有数组中所有6位数字键（如 500001）
        $keys = [];
        foreach (array_keys($array) as $key) {
            if (preg_match('/^\d{6}$/', (string)$key)) {  // 正则匹配6位数字
                $keys[] = (string)$key;
            }
        }

        // 无现有编码时，根据前缀生成初始编码（如 --start=5 → 500000）
        if (empty($keys)) {
            return $startPrefix ? $startPrefix . '00000' : '500000';  // 默认起始编码500000
        }

        // 有前缀时，仅处理该前缀的编码（如 --start=5 → 处理5xxxx的编码）
        if ($startPrefix !== null) {
            $prefixKeys = array_filter($keys, fn($k) => $k[0] === $startPrefix);  // 过滤同前缀的编码

            if (!empty($prefixKeys)) {
                $maxKey = max($prefixKeys);  // 取同前缀的最大编码（如 500000）
                $nextNum = (int)substr($maxKey, 1) + 1;  // 后缀数字+1（如 00000 → 00001）
                return $startPrefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);  // 补零生成新编码（如 500001）
            }

            // 无前缀编码时，生成前缀+00000（如 500000）
            return $startPrefix . '00000';
        }

        // 无指定前缀时，使用所有编码的最大值生成下一个编码
        $lastKey = max($keys);  // 取所有编码的最大值（如 500000）
        $prefix = $lastKey[0];  // 提取前缀（如 5）
        $nextNum = (int)substr($lastKey, 1) + 1;  // 后缀数字+1（如 00000 → 00001）

        return $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);  // 生成新编码（如 500001）
    }

    /**
     * 翻译短语到目标地区语言（调用翻译服务）
     * @param string $phrase 待翻译的中文短语（如 "登录成功"）
     * @param string $locale 目标地区（如 en-US）
     * @return string 翻译后的短语（如 "Login successful"）
     * @throws \InvalidArgumentException 不支持的地区时抛出
     */
    private function translatePhrase(string $phrase, string $locale): string
    {
        switch ($locale) {
            case 'zh-CN':  // 简体中文无需翻译
                break;
            case 'zh-HK':  // 香港繁体（粤语）
            case 'zh-TW':  // 台湾繁体
            case 'en-US':  // 美国英语
                // 调用翻译服务（通过 app('translation') 获取翻译管理器实例）
                $result = app('translation')->translate(
                    $phrase,
                    $this->traditionalMaps['zh-CN'],  // 源语言代码（zh）
                    $this->traditionalMaps[$locale]    // 目标语言代码（如 yue/cht/en）
                );
                $result = json_decode($result, true);  // 解析翻译结果（假设返回 JSON）
                $phrase = $result['translated_text'];  // 提取翻译后的文本
                break;
            default:
                throw new \InvalidArgumentException("不支持的地区: {$locale}");
        }
        return $phrase;
    }

    /**
     * （备用方法）将简体中文转换为繁体（基于预定义映射表）
     * @param string $phrase 待转换的简体中文短语
     * @param string $locale 目标地区（如 zh-TW）
     * @return string 转换后的繁体短语
     */
    private function convertToTraditional(string $phrase, string $locale): string
    {
        $map = $this->traditionalMaps[$locale] ?? [];
        return strtr($phrase, $map);
    }

    /**
     * 按编码对语言数组排序（确保文件内容按编码升序排列）
     * @param array $array 待排序的语言数组（键为编码，值为短语）
     */
    private function sortArray(array &$array): void
    {
        uksort($array, function($a, $b) {
            return (int)$a <=> (int)$b;  // 按键（编码）的数值大小排序
        });
    }

    /**
     * 显示生成结果（编码列表和文件路径）
     * @param array $generated 生成的短语-编码映射（如 ['登录成功' => '500001']）
     * @param array $locales 处理的地区数组
     * @param string $filename 文件名
     */
    private function showResults(array $generated, array $locales, string $filename): void
    {
        // 显示生成的编码列表
        $this->info('生成以下编码:');
        $this->table(['短语', '编码'], array_map(
            fn($p, $c) => [$p, $c],
            array_keys($generated),
            array_values($generated)
        ));

        // 显示各地区的语言文件路径
        $this->info('文件路径:');
        foreach ($locales as $locale) {
            $path = $this->getLocalePath($locale, $filename);
            $realPath = realpath($path) ?: $path;  // 转换为绝对路径（若存在）
            $this->line("{$locale}: {$realPath}");
        }
    }


    /**
     * 将多语言键值对数组写入指定路径的PHP文件
     * @param string $path 目标文件路径（如 lang/zh-CN/errors.php）
     * @param array $array 待写入的多语言键值对数组（键为编码，值为短语）
     */
    private function writeLangFile(string $path, array $array): void
    {
        // 获取文件所在目录路径
        $dir = dirname($path);
        // 若目录不存在则递归创建（权限0755），确保文件可写入
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // 按编码前缀（第一个数字）对键值对分组（便于文件内容分类展示）
        $groups = [];
        foreach ($array as $key => $value) {
            // 正则匹配编码前缀（如 "500001" 的前缀是 "5"）
            if (preg_match('/^(\d)/', (string)$key, $matches)) {
                $prefix = $matches[1];  // 提取前缀数字
                $groups[$prefix][$key] = $value;  // 按前缀分组存储
            } else {
                // 非数字前缀的键统一归类到 "other" 组（如自定义键名）
                $groups['other'][$key] = $value;
            }
        }

        // 按前缀数字升序排序（1-9，最后是 "other" 组），确保文件内容有序
        ksort($groups, SORT_NUMERIC);

        // 初始化文件内容（标准PHP数组格式）
        $content = "<?php\n\nreturn [\n";

        // 遍历分组生成文件内容
        $firstGroup = true;  // 标记是否为第一个分组（控制组间空行）
        foreach ($groups as $prefix => $items) {
            // 非第一个分组前添加空行，增强可读性
            if (!$firstGroup) {
                $content .= "\n";
            }
            $firstGroup = false;

            // 对组内键值对按编码数值升序排序（确保同一前缀内的顺序）
            ksort($items, SORT_NUMERIC);

            // 生成组内键值对内容
            foreach ($items as $key => $value) {
                // 转义值中的单引号（避免PHP语法错误）
                $safeValue = str_replace("'", "\\'", $value);
                // 格式化为 "    '编码' => '短语'," 形式
                $content .= "    '{$key}' => '{$safeValue}',\n";
            }
        }

        // 闭合数组并写入文件
        $content .= "];\n";
        File::put($path, $content);
    }

}