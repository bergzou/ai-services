<?php

namespace App\Services\Captcha;


use App\Exceptions\BusinessException;
use App\Interfaces\CaptchaInterface;



class CaptchaManager implements CaptchaInterface
{
    protected CaptchaInterface $driver;       // 当前使用的翻译驱动实例
    protected array $drivers = []; // 已实例化的驱动缓存（避免重复创建）

    /**
     * 构造函数：初始化默认翻译驱动
     * @param string|null $driver 手动指定的驱动名称（可选）
     * @throws BusinessException
     */
    public function __construct(string $driver = null)
    {
        $driver = $driver ?: config('captcha.default');
        $this->driver($driver);
    }

    /**
     * 设置当前使用的翻译驱动（支持动态切换）
     * @param string $driver 驱动名称
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
     * @throws BusinessException
     */
    private function createDriver(string $driver)
    {
        $config = config("captcha.drivers.{$driver}");

        // 检查驱动配置是否存在
        if (empty($config)) {
            throw new BusinessException("Captcha驱动 [{$driver}] 未在配置中定义");
        }

        // 获取驱动类名（配置中的 driver 字段）
        $driverClass = $config['driver'];

        // 检查驱动类是否存在
        if (!class_exists($driverClass)) {
            throw new BusinessException("Captcha驱动类 [{$driverClass}] 不存在");
        }

        // 通过Laravel容器解析实例（支持依赖注入）
        return app($driverClass);
    }

    /**
     * 生成验证码（核心方法）
     * @return array 生成的验证码数据（通常包含：唯一标识key、验证码值value、附加信息如图片Base64/有效期等）
     * 示例返回：['key' => 'captcha_123', 'value' => 'ABCD', 'image' => 'data:image...']
     */
    public function generate(): array
    {
        return $this->driver->generate();
    }

    /**
     * 验证用户输入的验证码是否有效
     * @param array $params 验证码的唯一标识（由 generate 方法返回，用于定位存储的验证码值）
     * @return bool 验证成功返回 true，失败返回 false
     */
    public function validate(array $params): bool
    {
        return $this->driver->validate($params);
    }
}