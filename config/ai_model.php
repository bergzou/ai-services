<?php

return [
    'openai' => [
        'enabled' => env('OPENAI_ENABLED', true), // ✅ 服务商总开关
        'provider_class' => \App\Services\Common\AiModel\Providers\OpenAi\OpenAiProvider::class,
        'api_key' => env('OPENAI_API_KEY'),
        'base_uri' =>  env('OPENAI_BASE_URI'),
        'drivers' => [
            'gpt4o-mini' => [
                'enabled' => true, // ✅ 单独驱动开关
                'api_key' => env('OPENAI_GPT4O_MINI_KEY', null), // 单独 key
                'class' => \App\Services\Common\AiModel\Providers\OpenAi\Drivers\Gpt4oMiniDriver::class,
            ],
            'gpt4' => [
                'enabled' => true, // ❌ 禁用 GPT-4
                'api_key' => env('OPENAI_GPT4O_MINI_KEY', null), // 单独 key
                'class' => \App\Services\Common\AiModel\Providers\OpenAi\Drivers\Gpt4Driver::class,
            ],
            'gpt35' => [
                'class' => \App\Services\Common\AiModel\Providers\OpenAi\Drivers\Gpt35TurboDriver::class,
                'api_key' => env('OPENAI_API_KEY_GPT35', null), // 可单独配置
                'enabled' => true,
            ],
        ],
    ],
    'zhipuai' => [
        'enabled' => env('ZHIPUAI_ENABLED', true), // ✅ 服务商总开关
        'provider_class' => \App\Services\Common\AiModel\Providers\ZhipuAi\ZhipuAiProvider::class,
        'api_key' => env('ZHIPUAI_API_KEY'),
        'base_uri' =>  env('ZHIPUAI_BASE_URI'),
        'drivers' => [
            'chatglm' => [
                'class' => \App\Services\Common\AiModel\Providers\ZhipuAi\Drivers\ChatGLMDriver::class,
                'api_key' => env('ZHIPUAI_API_KEY_GPT35'), // 可单独配置
                'enabled' => true,
            ],
        ],
    ],
];
