<?php

return [
    /**
     * 翻译服务全局开关（控制是否启用翻译功能）
     * - 若为 false，所有翻译请求将使用 "disable" 驱动（返回原文）
     * - 取值：true（启用）| false（禁用）
     * - 支持通过环境变量 TRANSLATION_ENABLED 动态配置（默认禁用）
     */
    'enabled' => env('TRANSLATION_ENABLED', false),

    /**
     * 默认翻译驱动（启用时使用的翻译服务）
     * - 取值需对应下方 drivers 数组中的键名（如 "baidu"、"disable"）
     * - 支持通过环境变量 TRANSLATION_DRIVER 动态指定（默认使用 "baidu"）
     */
    'default' => env('TRANSLATION_DRIVER', 'baidu'),

    /**
     * 翻译驱动配置（支持扩展多驱动）
     * 每个驱动需包含：
     * - driver: 实现 TranslatorInterface 接口的驱动类
     * - 其他驱动特定配置（如百度翻译的 API 凭证）
     */
    'drivers' => [
        // 百度翻译驱动配置
        'baidu' => [
            'driver' => \App\Services\Translation\Drivers\BaiduTranslator::class,  // 百度翻译驱动类（必须实现 TranslatorInterface）
            'api_key' => env('BAIDU_API_KEY'),  // 百度翻译 API Key（从环境变量获取）
            'secret_key' => env('BAIDU_SECRET_KEY'),  // 百度翻译 Secret Key（从环境变量获取）
        ],

        // 禁用翻译驱动配置（翻译关闭时使用）
        'disable' => [
            'driver' => \App\Services\Translation\Drivers\DisableTranslator::class  // 禁用模式驱动类（返回原文）
        ],
    ],
];