<?php

namespace App\Services\Common\AiModel\Providers\OpenAi\Drivers;

use App\Exceptions\BusinessException;
use App\Interfaces\AiModelInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class Gpt35TurboDriver implements AiModelInterface
{
    protected string $apiKey;
    protected string $baseUri;
    protected string $model = 'gpt-3.5-turbo'; // 固定为 gpt-3.5-turbo

    public function __construct(string $apiKey, string $baseUri)
    {
        $this->apiKey = $apiKey;
        $this->baseUri = rtrim($baseUri, '/'); // 确保没有多余的斜杠
    }

    /**
     * 发送聊天消息并获取 AI 响应
     *
     * @param array $messages 聊天消息数组（必须包含 role 和 content）
     * @param array $options 额外参数（如 temperature、max_tokens）
     * @return string
     * @throws BusinessException
     */
    public function chat(array $messages, array $options = []): string
    {
        // 参数校验
        if (empty($messages)) {
            throw new BusinessException('❌ 请求消息不能为空，必须是包含 role 和 content 的数组。');
        }

        $client = new Client([
            'base_uri' => $this->baseUri,
            'timeout'  => 30,
            'verify'   => false, // 跳过 SSL 证书验证（生产建议改为 true 并配置证书）
        ]);

        $payload = array_merge([
            'model'    => $this->model,
            'messages' => $messages,
        ], $options);

        try {
            $response = $client->post('/v1/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // 返回结果校验
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new BusinessException('⚠️ OpenAI 返回数据异常，请检查请求参数或接口状态。');
            }

            return trim($data['choices'][0]['message']['content']);

        } catch (RequestException $e) {
            // HTTP 状态码提示
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $errorBody  = (string) $e->getResponse()->getBody();
                $errorData  = json_decode($errorBody, true);

                $errorMsg = $errorData['error']['message'] ?? $errorBody;

                switch ($statusCode) {
                    case 401:
                        throw new BusinessException("❌ API Key 无效，请检查配置。\n错误信息：{$errorMsg}");
                    case 404:
                        throw new BusinessException("❌ 模型 {$this->model} 不存在，或无访问权限。\n错误信息：{$errorMsg}");
                    case 429:
                        throw new BusinessException("⚠️ 请求频率或配额已超限，请稍后重试。\n错误信息：{$errorMsg}");
                    case 500:
                        throw new BusinessException("🚨 OpenAI 服务器内部错误，请稍后再试。\n错误信息：{$errorMsg}");
                    default:
                        throw new BusinessException("❌ HTTP 请求失败，状态码：{$statusCode}。\n错误信息：{$errorMsg}");
                }
            }

            throw new BusinessException("❌ 请求失败，可能是网络问题或 SSL 证书错误。\n详细信息：" . $e->getMessage());

        } catch (GuzzleException $e) {
            throw new BusinessException("❌ 网络请求异常：" . $e->getMessage());
        }
    }
}
