<?php

namespace App\Services\Common\AiModel\Providers\ZhipuAi\Drivers;

use App\Interfaces\AiModelInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use RuntimeException;

class ChatGLMDriver implements AiModelInterface
{
    protected string $apiKey;
    protected string $baseUri;
    protected string $model = 'glm-4'; // 默认智谱 ChatGLM4，可改成 glm-3-turbo 等

    public function __construct(string $apiKey, string $baseUri = 'https://open.bigmodel.cn')
    {
        $this->apiKey = $apiKey;
        $this->baseUri = rtrim($baseUri, '/');
    }

    public function chat(array $messages, array $options = []): string
    {
        if (empty($messages) || !is_array($messages)) {
            throw new RuntimeException('❌ 请求消息不能为空，必须是包含 role 和 content 的数组。');
        }

        $client = new Client([
            'base_uri' => $this->baseUri,
            'timeout'  => 30,
            'verify'   => false, // 智谱证书是可信的
        ]);

        $payload = array_merge([
            'model'    => $this->model,
            'messages' => $messages,
        ], $options);

        try {
            $response = $client->post('/api/paas/v4/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['choices'][0]['message']['content'])) {
                throw new RuntimeException('⚠️ 智谱AI 返回数据异常，请检查参数或接口状态。');
            }

            return trim($data['choices'][0]['message']['content']);

        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $errorBody  = (string) $e->getResponse()->getBody();
                $errorData  = json_decode($errorBody, true);
                $errorMsg   = $errorData['error']['message'] ?? $errorBody;

                switch ($statusCode) {
                    case 401:
                        throw new RuntimeException("❌ API Key 无效，请检查智谱AI配置。\n错误信息：{$errorMsg}");
                    case 404:
                        throw new RuntimeException("❌ 模型 {$this->model} 不存在或无权限。\n错误信息：{$errorMsg}");
                    case 429:
                        throw new RuntimeException("⚠️ 请求频率或配额超限，请稍后重试。\n错误信息：{$errorMsg}");
                    default:
                        throw new RuntimeException("❌ HTTP 请求失败（{$statusCode}）。\n错误信息：{$errorMsg}");
                }
            }
            throw new RuntimeException("❌ 请求失败，网络或证书异常。\n详细信息：" . $e->getMessage());
        } catch (GuzzleException $e) {
            throw new RuntimeException("❌ 网络请求异常：" . $e->getMessage());
        }
    }
}
