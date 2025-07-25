<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Services\Captcha\CaptchaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthService
{
    // 验证码长度（可配置）
    protected int $captchaLength = 4;
    // 验证码缓存前缀（避免与其他缓存冲突）
    protected string $captchaCachePrefix = 'auth_captcha_';
    // 验证码有效期（分钟）
    protected int $captchaExpire = 5;


    public function generateCaptcha(array $params): array
    {
        // 生成随机数字验证码
        $captcha = random_int(100000, 999999);

        // 缓存验证码（若为短信验证码，缓存键关联手机号）
        $cacheKey = $this->captchaCachePrefix . ($params['mobile'] ?? 'default');
        Cache::put($cacheKey, $captcha, now()->addMinutes($this->captchaExpire));

        // 返回验证码（实际图形验证码需生成图片，此处简化为数字）
        return [
            'captcha' => $captcha,
            'expire' => $this->captchaExpire * 60 // 秒数
        ];
    }

    /**
     * 发送短信验证码
     * @param array $params 请求参数（mobile=手机号, captcha=用户输入的图形验证码）
     * @return array 发送结果
     * @throws BusinessException 发送失败时抛出异常
     */
    public function sendSMS(array $params): array
    {
        // 验证手机号格式
        $validator = Validator::make($params, [
            'mobile' => 'required|regex:/^1[3-9]\d{9}$/',
            'captcha' => 'required|string'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 验证图形验证码是否正确（假设图形验证码已缓存）
        $imageCaptchaCacheKey = $this->captchaCachePrefix . 'image_' . $params['mobile'];
        if (Cache::get($imageCaptchaCacheKey) !== $params['captcha']) {
            throw new BusinessException('图形验证码错误');
        }

        // 调用短信网关发送（示例逻辑，实际需替换为具体API）
        $smsResult = $this->callSmsGateway($params['mobile'], $params['captcha']);
        if (!$smsResult['success']) {
            throw new BusinessException('短信发送失败：' . $smsResult['message']);
        }

        return ['message' => '短信验证码已发送'];
    }


    /**
     * @throws BusinessException
     * @throws ValidationException
     */
    public function registerByUsername(array $params)
    {

        $captchaService= new CaptchaService();
        $captchaService->validate($params['captcha_code']);


        // 验证参数
        $validator = Validator::make($params, [
            'username' => 'required|unique:users|min:3|max:20',
            'password' => 'required|min:6|confirmed',
            'captcha' => 'required|string'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 验证验证码（假设验证码已缓存）
        $captchaCacheKey = $this->captchaCachePrefix . 'register_username_' . $params['username'];
        if (Cache::get($captchaCacheKey) !== $params['captcha']) {
            throw new BusinessException('验证码错误');
        }

        // 创建用户
        return User::create([
            'username' => $params['username'],
            'password' => bcrypt($params['password']),
            'mobile' => $params['mobile'] ?? null // 可选手机号
        ]);
    }

    /**
     * 手机号注册
     * @param array $params 请求参数（mobile=手机号, sms_code=短信验证码, password=密码等）
     * @return User 注册成功的用户模型
     * @throws BusinessException 注册失败时抛出异常
     */
    public function registerByMobile(array $params): User
    {
        // 验证参数
        $validator = Validator::make($params, [
            'mobile' => 'required|regex:/^1[3-9]\d{9}$/|unique:users',
            'sms_code' => 'required|string',
            'password' => 'required|min:6|confirmed'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 验证短信验证码（缓存中获取）
        $smsCacheKey = $this->captchaCachePrefix . 'sms_' . $params['mobile'];
        if (Cache::get($smsCacheKey) !== $params['sms_code']) {
            throw new BusinessException('短信验证码错误');
        }

        // 创建用户
        return User::create([
            'mobile' => $params['mobile'],
            'password' => bcrypt($params['password']),
            'username' => 'user_' . $params['mobile'] // 自动生成用户名
        ]);
    }

    /**
     * 用户名登录
     * @param array $params 请求参数（username=用户名, password=密码）
     * @return array 登录结果（包含用户信息和 JWT）
     * @throws BusinessException 登录失败时抛出异常
     */
    public function loginByUsername(array $params): array
    {
        // 验证参数
        $validator = Validator::make($params, [
            'username' => 'required|min:3|max:20',
            'password' => 'required|min:6'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 查找用户
        $user = User::where('username', $params['username'])->first();
        if (!$user || !password_verify($params['password'], $user->password)) {
            throw new BusinessException('用户名或密码错误');
        }

        // 生成 JWT（使用之前实现的 JwtService）
        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);

        return [
            'user' => $user->toArray(),
            'token' => $token
        ];
    }

    /**
     * 手机号登录
     * @param array $params 请求参数（mobile=手机号, sms_code=短信验证码）
     * @return array 登录结果（包含用户信息和 JWT）
     * @throws BusinessException 登录失败时抛出异常
     */
    public function loginByMobile(array $params): array
    {
        // 验证参数
        $validator = Validator::make($params, [
            'mobile' => 'required|regex:/^1[3-9]\d{9}$/',
            'sms_code' => 'required|string'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 验证短信验证码
        $smsCacheKey = $this->captchaCachePrefix . 'sms_' . $params['mobile'];
        if (Cache::get($smsCacheKey) !== $params['sms_code']) {
            throw new BusinessException('短信验证码错误');
        }

        // 查找用户（或自动注册未注册的手机号）
        $user = User::where('mobile', $params['mobile'])->first();
        if (!$user) {
            // 可选：自动注册手机号用户（根据业务需求）
            $user = User::create([
                'mobile' => $params['mobile'],
                'password' => bcrypt(random_int(100000, 999999)), // 随机密码（仅用于手机号登录）
                'username' => 'user_' . $params['mobile']
            ]);
        }

        // 生成 JWT
        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);

        return [
            'user' => $user->toArray(),
            'token' => $token
        ];
    }

    /**
     * 调用短信网关（示例方法，需替换为实际API）
     * @param string $mobile 手机号
     * @param string $code 验证码
     * @return array 发送结果
     */
    protected function callSmsGateway(string $mobile, string $code): array
    {
        // 实际应调用第三方短信API（如阿里云、腾讯云等）
        // 此处模拟成功响应
        return [
            'success' => true,
            'message' => '短信发送成功'
        ];
    }
}