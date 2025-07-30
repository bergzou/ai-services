<?php

namespace App\Services\Common\Auth;

use App\Exceptions\BusinessException;
use App\Libraries\Predis;
use App\Models\SystemTenantModel;
use App\Services\Admin\SystemTenantService;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Validates\SystemUsersValidated;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use App\Models\SystemUsersModel;

class AuthService extends BaseService
{


    /**
     * @throws BusinessException
     */
    public function register($params)
    {

        $usersValidated = new SystemUsersValidated($params, 'register');
        $messages = $usersValidated->isRunFail();
        if (!empty($messages))   throw new BusinessException($messages, '400000'); // 参数验证失败异常

        $systemTenantService = new SystemTenantService();
        $tenant = $systemTenantService->checkTenant($params['tenant_id']);
        if ($tenant['tenant_name'] != $params['tenant_name']) throw new BusinessException(__('errors.500008'),500008);


        $systemUsersModel = new SystemUsersModel();
        $exists = $systemUsersModel::query()->where('username', $params['username'])->exists();
        if ($exists) throw new BusinessException(__('errors.500004'),500004);

        $params['password'] = bcrypt($params['password']);

        $insertData = [
            'username' => $params['name'],
            'password' => $params['password'],
            'nickname' => $params['nickname'],
            'tenant_id' => $params['tenant_id'],
            'creator' => $params['creator'],
            'create_time' => $params['create_time'],
            'updater' => $params['updater'],
            'update_time' => $params['update_time'],

        ];
        $result = $systemUsersModel->save($insertData);
        if ($result !== true) throw new BusinessException(__('errors.500005'),500005);

        return $params;
    }

    public function login($params)
    {


        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
            'client_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'invalid_request', 'message' => $validator->errors()], 400);
        }

        $client = $this->getValidClient($request->client_id);

        if (!$client) {
            return response()->json(['error' => 'invalid_client'], 400);
        }

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            return $this->issueTokens($user, $client);
        }

        return response()->json(['error' => 'invalid_credentials'], 401);
    }



    public function loginWithMobile(){
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|size:11',
            'code' => 'required|string|size:6',
            'client_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'invalid_request', 'message' => $validator->errors()], 400);
        }

        // 实际项目中应调用短信验证服务
        if (!$this->verifySMSCode($request->mobile, $request->code)) {
            return response()->json(['error' => 'invalid_code'], 401);
        }

        $user = SystemUser::where('mobile', $request->mobile)
            ->where('deleted', 0)
            ->where('status', 0)
            ->first();

        if (!$user) {
            // 自动注册新用户
            $user = new SystemUser();
            $user->username = 'm_' . $request->mobile;
            $user->mobile = $request->mobile;
            $user->nickname = '手机用户_' . substr($request->mobile, -4);
            $user->password = Hash::make(Str::random(16));
            $user->tenant_id = 0; // 默认租户
            $user->save();
        }

        $client = $this->getValidClient($request->client_id);

        if (!$client) {
            return response()->json(['error' => 'invalid_client'], 400);
        }

        return $this->issueTokens($user, $client);
    }



    // 第三方授权登录
    public function socialRedirect($provider, Request $request)
    {
        $validator = Validator::make([
            'provider' => $provider,
            'client_id' => $request->client_id
        ], [
            'provider' => 'required|in:wechat,alipay,xiaohongshu',
            'client_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'invalid_request'], 400);
        }

        // 获取社交客户端配置
        $socialClient = $this->getSocialClient($provider, $request->client_id);

        if (!$socialClient) {
            return response()->json(['error' => 'social_client_not_found'], 400);
        }

        $state = Str::random(40);
        session(['social_auth_state' => $state]);
        session([
            'social_auth_info' => [
                'provider' => $provider,
                'client_id' => $request->client_id,
                'social_client_id' => $socialClient->id
            ]
        ]);

        /** @var SocialProvider $socialProvider */
        $socialProvider = app("social_auth.{$provider}");
        return redirect($socialProvider->getAuthUrl($state));
    }

    public function socialCallback($provider, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'state' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'invalid_request'], 400);
        }

        // 验证state防止CSRF
        if ($request->state !== session('social_auth_state')) {
            return response()->json(['error' => 'invalid_state'], 400);
        }

        $socialInfo = session('social_auth_info');
        if (!$socialInfo || $socialInfo['provider'] !== $provider) {
            return response()->json(['error' => 'invalid_session'], 400);
        }

        $client = $this->getValidClient($socialInfo['client_id']);
        if (!$client) {
            return response()->json(['error' => 'invalid_client'], 400);
        }

        $socialClient = SystemSocialClient::find($socialInfo['social_client_id']);
        if (!$socialClient) {
            return response()->json(['error' => 'social_client_not_found'], 400);
        }

        /** @var SocialProvider $socialProvider */
        $socialProvider = app("social_auth.{$provider}");
        $socialUserData = $socialProvider->getUserByCode($request->code);

        // 查找或创建社交用户
        $socialUser = $this->findOrCreateSocialUser($socialUserData);

        // 查找或创建系统用户
        $user = $this->findOrCreateSystemUser($socialUser, $socialClient);

        return $this->issueTokens($user, $client);
    }

    // 令牌验证
    public function validateToken(Request $request)
    {
        $token = $request->bearerToken() ?: $request->input('access_token');

        if (!$token) {
            return response()->json(['valid' => false, 'error' => 'token_missing'], 401);
        }

        $payload = $this->jwtService->validateToken($token);

        if (isset($payload['error'])) {
            return response()->json(['valid' => false, 'error' => $payload['error']], 401);
        }

        if (!$this->jwtService->isTokenValid($token)) {
            return response()->json(['valid' => false, 'error' => 'token_revoked'], 401);
        }

        return response()->json([
            'valid' => true,
            'user_id' => $payload['sub'],
            'client_id' => $payload['client_id'],
            'scopes' => $payload['scopes'],
            'exp' => $payload['exp'],
            'user_info' => $payload['user_info']
        ]);
    }

    // 注销
    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        if ($token) {
            $this->jwtService->revokeToken($token);
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    // ========== 辅助方法 ==========

    protected function issueTokens($user, $client)
    {
        $accessToken = $this->jwtService->generateToken(
            $user,
            $client->client_id,
            $client->scopes,
            $client->access_token_validity_seconds
        );

        // 存储访问令牌到Redis
        $this->jwtService->storeToken($accessToken, [
            'user_id' => $user->id,
            'client_id' => $client->client_id,
            'scopes' => $client->scopes
        ], $client->access_token_validity_seconds);

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => $client->access_token_validity_seconds,
            'scope' => $client->scopes
        ]);
    }

    private function findOrCreateSocialUser(array $socialUserData): SystemSocialUser
    {
        // 查找是否已存在社交用户
        $socialUser = SystemSocialUser::where('type', $socialUserData['type'])
            ->where('openid', $socialUserData['openid'])
            ->first();

        if ($socialUser) {
            // 更新社交用户信息
            $socialUser->update([
                'token' => $socialUserData['token'],
                'raw_token_info' => $socialUserData['raw_token_info'],
                'nickname' => $socialUserData['nickname'],
                'avatar' => $socialUserData['avatar'],
                'raw_user_info' => $socialUserData['raw_user_info'],
                'code' => $socialUserData['code'],
            ]);
            return $socialUser;
        }

        // 创建新社交用户
        return SystemSocialUser::create([
            'type' => $socialUserData['type'],
            'openid' => $socialUserData['openid'],
            'token' => $socialUserData['token'],
            'raw_token_info' => $socialUserData['raw_token_info'],
            'nickname' => $socialUserData['nickname'],
            'avatar' => $socialUserData['avatar'],
            'raw_user_info' => $socialUserData['raw_user_info'],
            'code' => $socialUserData['code'],
            'tenant_id' => 0 // 默认租户
        ]);
    }

    private function findOrCreateSystemUser(SystemSocialUser $socialUser, SystemSocialClient $socialClient): SystemUser
    {
        // 查找绑定关系
        $bind = SystemSocialUserBind::where('social_user_id', $socialUser->id)
            ->where('social_type', $socialUser->type)
            ->first();

        if ($bind) {
            return $bind->user;
        }

        // 创建新用户
        $user = new SystemUser();
        $user->username = $socialUser->type . '_' . $socialUser->openid;
        $user->nickname = $socialUser->nickname ?: '社交用户_' . substr($socialUser->openid, -6);
        $user->avatar = $socialUser->avatar;
        $user->password = Hash::make(Str::random(32));
        $user->tenant_id = $socialClient->tenant_id;
        $user->save();

        // 创建绑定关系
        SystemSocialUserBind::create([
            'user_id' => $user->id,
            'user_type' => $socialClient->user_type,
            'social_type' => $socialUser->type,
            'social_user_id' => $socialUser->id,
            'tenant_id' => $socialClient->tenant_id
        ]);

        return $user;
    }

    private function getValidClient($clientId)
    {
        return SystemOauth2Client::where('client_id', $clientId)
            ->where('status', 1) // 1=启用
            ->where('deleted', 0)
            ->first();
    }

    private function getSocialClient($provider, $clientId)
    {
        $socialType = match ($provider) {
            'wechat' => SocialType::WECHAT,
            'alipay' => SocialType::ALIPAY,
            'xiaohongshu' => SocialType::XIAOHONGSHU,
            default => 0,
        };

        return SystemSocialClient::where('social_type', $socialType)
            ->where('client_id', $clientId)
            ->where('status', 1)
            ->where('deleted', 0)
            ->first();
    }

    private function verifySMSCode($mobile, $code)
    {
        // 实际项目中应调用短信验证服务
        // 这里简化实现，验证码为123456
        return $code === '123456';
    }

}