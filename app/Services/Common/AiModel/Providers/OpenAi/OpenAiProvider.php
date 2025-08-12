<?php

namespace App\Services\Common\AiModel\Providers\OpenAi;

use App\Exceptions\BusinessException;

class OpenAiProvider
{
    protected $apiKey;      // 公共 API Key
    protected string $baseUri;     // API 基础地址
    protected $drivers = []; // 驱动配置列表

    /**
     * 初始化 OpenAI 服务商
     *
     * @param array $config 配置数组
     */
    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? null;
        // 自动补齐末尾斜杠，防止 Guzzle 拼接 URL 出错
        $this->baseUri = rtrim($config['base_uri'] ?? 'https://api.openai.com/v1', '/') . '/';
        $this->drivers = $config['drivers'] ?? [];
    }

    /**
     * 获取指定驱动实例
     *
     * @param string $driverName 驱动名称
     * @return mixed 驱动实例
     * @throws BusinessException
     */
    public function driver(string $driverName)
    {
        if (!isset($this->drivers[$driverName])) {
            throw new BusinessException("❌ 未找到 OpenAI 的驱动 [{$driverName}]，请检查配置文件。");
        }

        $driverConfig = $this->drivers[$driverName];

        if (isset($driverConfig['enabled']) && $driverConfig['enabled'] === false) {
            throw new BusinessException("⚠️ OpenAI 驱动 [{$driverName}] 已被禁用。");
        }

        $driverClass = $driverConfig['class'];

        // 模型优先使用自己的 API Key，否则使用公共 Key
        $apiKey = $driverConfig['api_key'] ?? $this->apiKey;

        if (empty($apiKey)) {
            throw new BusinessException("❌ OpenAI 驱动 [{$driverName}] 未配置 API Key，请在配置文件中添加。");
        }

        return new $driverClass($apiKey, $this->baseUri, $driverConfig);
    }
}
