<?php

namespace App\Services\Translation;

use App\Exceptions\BusinessException;
use App\Interfaces\TranslatorInterface;


/**
 * 翻译服务管理器：负责翻译驱动的初始化、切换和翻译请求分发
 * 支持多驱动扩展（如百度翻译、谷歌翻译），通过配置灵活切换
 */
class TranslatorManager implements TranslatorInterface
{
    protected TranslatorInterface $driver;       // 当前使用的翻译驱动实例
    protected array $drivers = []; // 已实例化的驱动缓存（避免重复创建）

    /**
     * 构造函数：初始化默认翻译驱动
     * @param string|null $driver 手动指定的驱动名称（可选）
     * @throws BusinessException
     */
    public function __construct(string $driver = null)
    {
        // 若翻译功能全局禁用（配置 translation.enabled 为 false），强制使用 "disable" 驱动
        if (!config('translation.enabled', false)) {
            $driver = 'disable';
        } else {
            // 未手动指定时，使用配置中的默认驱动（translation.default）
            $driver = $driver ?: config('translation.default');
        }
        // 初始化指定驱动
        $this->driver($driver);
    }

    /**
     * 设置当前使用的翻译驱动（支持动态切换）
     * @param string $driver 驱动名称（对应 config/translation.php 中的驱动键名）
     * @return void 管理器实例（支持链式调用）
     * @throws BusinessException
     */
    private function driver(string $driver): void
    {
        // 若驱动未实例化，则创建并缓存
        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        // 设置当前驱动为缓存中的实例
        $this->driver = $this->drivers[$driver];
    }

    /**
     * 创建具体翻译驱动实例（核心工厂方法）
     * @param string $driver 驱动名称（如 "baidu"、"disable"）
     * @return TranslatorInterface 翻译驱动实例（实现接口规范）
     * @throws BusinessException 驱动配置缺失或类不存在时抛出
     */
    private function createDriver(string $driver): TranslatorInterface
    {
        // 从配置文件读取驱动配置（config/translation.php 中 drivers.{$driver} 部分）
        $config = config("translation.drivers.{$driver}");

        // 检查驱动配置是否存在
        if (empty($config)) {
            throw new BusinessException("翻译驱动 [{$driver}] 未在配置中定义");
        }

        // 获取驱动类名（配置中的 driver 字段）
        $driverClass = $config['driver'];

        // 检查驱动类是否存在
        if (!class_exists($driverClass)) {
            throw new BusinessException("翻译驱动类 [{$driverClass}] 不存在");
        }

        // 通过Laravel容器解析实例（支持依赖注入）
        return app($driverClass);
    }


    /**
     * 执行翻译请求（委托给当前驱动）
     * @param string $text 待翻译的原始文本
     * @param string $sourceLang 源语言代码（如 "en"）
     * @param string $targetLang 目标语言代码（如 "zh"）
     * @return string 翻译结果（格式由具体驱动决定）
     */
    public function translate(string $text, string $sourceLang, string $targetLang): string
    {
        // 调用当前驱动的 translate 方法（实现 TranslatorInterface 接口）
        return $this->driver->translate($text, $sourceLang, $targetLang);
    }
}