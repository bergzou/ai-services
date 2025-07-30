<?php

namespace App\Services\Common\Auth;

use App\Exceptions\BusinessException;
use App\Interfaces\SocialInterface;


class SocialAuthManager implements SocialInterface
{
    protected SocialInterface $driver;
    protected array $drivers = [];


    public function __construct(string $driver = null)
    {
        if (!config('translation.enabled', false)) {
            $driver = 'disable';
        } else {
            $driver = $driver ?: config('translation.default');
        }
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
     * @return SocialInterface 翻译驱动实例（实现接口规范）
     * @throws BusinessException 驱动配置缺失或类不存在时抛出
     */
    private function createDriver(string $driver): SocialInterface
    {

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

}