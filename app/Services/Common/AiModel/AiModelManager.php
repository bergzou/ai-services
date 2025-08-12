<?php

namespace App\Services\Common\AiModel;

use InvalidArgumentException;

class AiModelManager
{
    protected $config;
    protected $providers = [];

    public function __construct()
    {
        // 从配置文件加载所有 AI 服务商配置
        $this->config = config('ai_model', []);
    }

    /**
     * 获取指定服务商
     *
     * @param string $providerName 服务商名称（如 openai、zhipuai）
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function provider(string $providerName)
    {
        $providerName = strtolower($providerName);

        if (!isset($this->config[$providerName])) {
            throw new InvalidArgumentException("未找到 AI 服务商配置：{$providerName}");
        }

        $providerConfig = $this->config[$providerName];

        if (empty($providerConfig['enabled']) || $providerConfig['enabled'] === false) {
            throw new InvalidArgumentException("AI 服务商 {$providerName} 已被禁用，请检查配置。");
        }

        if (empty($providerConfig['provider_class']) || !class_exists($providerConfig['provider_class'])) {
            throw new InvalidArgumentException("AI 服务商 {$providerName} 的 Provider 类未正确配置。");
        }

        // 缓存 Provider 实例
        if (!isset($this->providers[$providerName])) {
            $this->providers[$providerName] = new $providerConfig['provider_class']($providerConfig);
        }

        return $this->providers[$providerName];
    }

    /**
     * 快捷调用单个模型
     *
     * @param string $providerName 服务商
     * @param string $driverName 模型
     * @return mixed
     */
    public function driver(string $providerName, string $driverName)
    {
        return $this->provider($providerName)->driver($driverName);
    }
}
