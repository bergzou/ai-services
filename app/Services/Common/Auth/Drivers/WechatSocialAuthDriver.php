<?php

namespace App\Services\Common\Auth\Drivers;

use App\Interfaces\SocialInterface;
use GuzzleHttp\Client;


class WechatSocialAuthDriver implements SocialInterface
{

    public function __construct(array $config)
    {
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUri = $config['redirect_uri'];
    }

    public function getAuthUrl(string $state): string
    {
        $params = [
            'appid' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => $state
        ];

        return 'https://open.weixin.qq.com/connect/qrconnect?' . http_build_query($params) . '#wechat_redirect';
    }

    public function getUserByCode(string $code): array
    {
        $client = new Client();

        // 获取access_token
        $tokenResponse = $client->get('https://api.weixin.qq.com/sns/oauth2/access_token', [
            'query' => [
                'appid' => $this->clientId,
                'secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code'
            ]
        ]);

        $tokenData = json_decode($tokenResponse->getBody(), true);

        // 获取用户信息
        $userResponse = $client->get('https://api.weixin.qq.com/sns/userinfo', [
            'query' => [
                'access_token' => $tokenData['access_token'],
                'openid' => $tokenData['openid']
            ]
        ]);

        $userData = json_decode($userResponse->getBody(), true);

        return [
            'type' => $this->getProviderType(),
            'openid' => $tokenData['openid'],
            'unionid' => $tokenData['unionid'] ?? null,
            'token' => $tokenData['access_token'],
            'raw_token_info' => json_encode($tokenData),
            'nickname' => $userData['nickname'] ?? '',
            'avatar' => $userData['headimgurl'] ?? '',
            'raw_user_info' => json_encode($userData),
            'code' => $code,
        ];
    }

    public function getProviderType(): int
    {
        return SocialType::WECHAT; // 微信类型
    }
}