<?php

namespace App\Services\Common\Translation\Drivers;

use App\Exceptions\BusinessException;
use App\Interfaces\TranslatorInterface;
use Illuminate\Support\Facades\Cache;

/**
 * 百度翻译服务驱动：实现百度翻译API的具体调用逻辑
 * 遵循 TranslatorInterface 接口规范，用于多语言翻译场景
 */
class BaiduTranslatorDriver implements TranslatorInterface
{
    protected $apiKey;     // 百度翻译API的API Key（用于身份验证）
    protected $secretKey;  // 百度翻译API的Secret Key（用于生成访问令牌）

    /**
     * 构造函数：从配置文件初始化API凭证
     */
    public function __construct()
    {
        // 读取 config/translation.php 中百度翻译的配置
        $config = config('translation.drivers.baidu');
        $this->apiKey = $config['api_key'];      // 初始化API Key
        $this->secretKey = $config['secret_key']; // 初始化Secret Key
    }

    /**
     * 执行文本翻译（核心方法）
     * @param string $text 待翻译的原始文本（如 "Hello"）
     * @param string $sourceLang 源语言代码（如 "en" 表示英语）
     * @param string $targetLang 目标语言代码（如 "zh" 表示中文）
     * @return string 翻译结果的JSON字符串（包含原文、译文等信息）
     * @throws BusinessException 翻译请求失败时抛出（如API返回错误码）
     */
    public function translate(string $text, string $sourceLang, string $targetLang): string
    {
        // 获取百度翻译API的访问令牌（通过缓存优化，避免重复请求）
        $accessToken = $this->getAccessToken();

        // 初始化CURL请求
        $curl = curl_init();

        // 构造翻译请求参数（JSON格式）
        $params = json_encode([
            'q' => $text,           // 待翻译文本
            'from' => $sourceLang,  // 源语言代码
            'to' => $targetLang     // 目标语言代码
        ]);

        // 配置CURL选项
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://aip.baidubce.com/rpc/2.0/mt/texttrans/v1?access_token={$accessToken}", // 百度翻译API地址
            CURLOPT_TIMEOUT => 30,  // 请求超时时间（30秒）
            CURLOPT_RETURNTRANSFER => true,  // 返回响应内容而非直接输出
            CURLOPT_SSL_VERIFYPEER => false, // 关闭SSL证书验证（测试环境常用，生产环境建议开启）
            CURLOPT_SSL_VERIFYHOST => false, // 关闭SSL主机验证（同上）
            CURLOPT_CUSTOMREQUEST => 'POST', // 使用POST请求
            CURLOPT_POSTFIELDS => $params,   // 请求体参数
            CURLOPT_HTTPHEADER => [          // 请求头
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);

        // 执行CURL请求并获取响应
        $response = curl_exec($curl);
        $rest = json_decode($response, true); // 解析JSON响应为数组

        // 检查API返回的错误码（非0表示请求失败）
        if (isset($rest['error_code']) && $rest['error_code'] !== 0) {
            // 抛出业务异常，包含错误信息和错误码
            throw new BusinessException('百度翻译: ' . $rest['error_msg'], $rest['error_code']);
        }

        // 构造并返回翻译结果的结构化JSON（包含原文、译文等信息）
        return json_encode([
            'original_text' => $rest['result']['trans_result'][0]['src'], // 原文
            'translated_text' => $rest['result']['trans_result'][0]['dst'], // 译文
            'source_lang' => $sourceLang, // 源语言
            'target_lang' => $targetLang  // 目标语言
        ]);
    }

    /**
     * 获取百度翻译API的访问令牌（带缓存优化）
     * @return string 有效的访问令牌（用于调用翻译接口）
     * @throws BusinessException 令牌获取失败时抛出
     */
    protected function getAccessToken(): string
    {
        // 使用Laravel缓存（缓存时间3600秒=1小时，与百度令牌有效期一致）
        return Cache::remember('baidu_translation_token', 3600, function () {
            // 初始化CURL请求（用于获取令牌）
            $curl = curl_init();

            // 构造令牌请求参数（表单格式）
            $postData = [
                'grant_type' => 'client_credentials', // 授权类型（客户端凭证模式）
                'client_id' => $this->apiKey,         // API Key
                'client_secret' => $this->secretKey   // Secret Key
            ];

            // 配置CURL选项
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://aip.baidubce.com/oauth/2.0/token', // 百度OAuth2.0令牌接口地址
                CURLOPT_CUSTOMREQUEST => 'POST',       // 使用POST请求
                CURLOPT_SSL_VERIFYPEER => false,       // 关闭SSL证书验证（测试环境）
                CURLOPT_SSL_VERIFYHOST => false,       // 关闭SSL主机验证（测试环境）
                CURLOPT_RETURNTRANSFER => true,        // 返回响应内容
                CURLOPT_POSTFIELDS => http_build_query($postData) // 表单格式参数
            ]);

            // 执行CURL请求并获取响应
            $response = curl_exec($curl);
            $rest = json_decode($response, true); // 解析JSON响应为数组

            // 提取访问令牌（若不存在则抛出异常）
            $accessToken = $rest['access_token'] ?? '';
            if (empty($accessToken)) {
                throw new BusinessException('获取百度翻译访问令牌失败');
            }

            return $accessToken;
        });
    }
}