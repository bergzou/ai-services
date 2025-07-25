<?php

namespace App\Interfaces;

/**
 * 验证码服务接口：定义验证码生成与验证的标准方法
 * 所有验证码实现类（如图片验证码、短信验证码、邮箱验证码）需实现此接口，确保行为一致性
 */
interface CaptchaInterface
{
    /**
     * 生成验证码（核心方法）
     * @return array 生成的验证码数据（通常包含：唯一标识key、验证码值value、附加信息如图片Base64/有效期等）
     * 示例返回：['key' => 'captcha_123', 'value' => 'ABCD', 'image' => 'data:image...']
     */
    public function generate(): array;

    /**
     * 验证用户输入的验证码是否有效
     * @param array $params 验证参数（包含验证码的唯一标识key、用户输入的验证码值value等）
     * @return bool 验证成功返回 true，失败返回 false
     */
    public function validate(array $params): bool;
}
