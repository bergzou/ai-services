<?php

namespace App\Services\Captcha\Drivers;

use App\Exceptions\BusinessException;
use App\Interfaces\CaptchaInterface;
use App\Libraries\Common;
use App\Libraries\Predis;
use App\Libraries\Snowflake;
use Exception;

/**
 * 文本验证码驱动类（实现图片验证码功能）
 * 基于 GD 库生成简单文本验证码图片，支持 Redis 存储验证码值并校验
 * 特点：轻量级、配置灵活（支持图片尺寸/字符数/过期时间配置）
 */
class TextCaptchaDriver implements CaptchaInterface
{
    /** @var int 验证码图片宽度（像素） */
    private int $width = 160;
    /** @var int 验证码图片高度（像素） */
    private int $height = 50;
    /** @var int 验证码字符数量（默认4位） */
    private int $characters = 4;
    /** @var int 验证码过期时间（秒，默认60秒） */
    private int $expire = 60;

    /**
     * 构造方法：从配置文件加载验证码参数（覆盖默认值）
     * 配置示例（config/captcha.php）：
     * 'drivers' => [
     *     'text' => [
     *         'width' => 200,
     *         'height' => 60,
     *         'characters' => 5,
     *         'expire' => 120
     *     ]
     * ]
     */
    public function __construct()
    {
        // 从配置文件读取参数（未配置时使用默认值）
        $this->width = config('captcha.drivers.text.width', $this->width);
        $this->height = config('captcha.drivers.text.height', $this->height);
        $this->characters = config('captcha.drivers.text.characters', $this->characters);
        $this->expire = config('captcha.drivers.text.expire', $this->expire);
    }

    /**
     * 生成验证码（核心方法）
     * @return array 包含验证码唯一键和图片Base64的数组（格式：['captcha_key' => '唯一键', 'captcha_image' => '图片Base64']）
     * @throws Exception 生成过程中可能抛出的异常（如GD库错误）
     */
    public function generate(): array
    {
        // 获取Redis实例（用于存储验证码值）
        $redis = Predis::getInstance();

        // 生成唯一验证码键（使用雪花算法确保全局唯一）
        $snowflake = new Snowflake(Common::getWorkerId());
        $captchaKey = 'captcha_' . $snowflake->next();

        // 生成随机验证码值（取md5哈希前4位并转大写）
        $captchaValue = strtoupper(mb_substr(md5(microtime(true)), 0, $this->characters));

        // 生成验证码图片的Base64字符串
        $captchaImage = $this->generateImage($captchaValue);

        // 将验证码值存储到Redis（设置过期时间防止内存泄漏）
        $redis->set($captchaKey, $captchaValue);
        $redis->expire($captchaKey, $this->expire);

        return ['captcha_key' => $captchaKey, 'captcha_image' => $captchaImage];
    }

    /**
     * 生成验证码图片并转换为Base64
     * @param string $captchaValue 验证码字符（如 "ABCD"）
     * @return string 图片的Base64编码字符串（data:image/png;base64,...）
     * @throws Exception GD库函数调用失败时抛出异常
     */
    public function generateImage(string $captchaValue): string
    {
        // 1. 创建画布（真彩色图像资源）
        $image = imagecreatetruecolor($this->width, $this->height);

        // 2. 生成随机浅色背景（防止字符与背景混淆）
        $bgR = random_int(230, 255);
        $bgG = random_int(230, 255);
        $bgB = random_int(230, 255);
        $bgColor = imagecolorallocate($image, $bgR, $bgG, $bgB);
        imagefill($image, 0, 0, $bgColor);

        // 3. 添加简单干扰元素（防止OCR识别）
        $this->addSimpleNoise($image);

        // 4. 绘制验证码字符（随机颜色、内置字体）
        $this->drawSimpleText($image, $captchaValue);

        // 5. 转换为Base64并释放资源
        ob_start();
        imagepng($image); // 输出PNG图像到缓冲区
        $imageData = ob_get_clean(); // 获取缓冲区内容
        imagedestroy($image); // 释放图像资源

        return base64_encode($imageData);
    }

    /**
     * 添加简单干扰元素（干扰线+干扰点）
     * @param resource $image GD图像资源
     * @throws Exception 颜色分配失败时抛出异常
     */
    private function addSimpleNoise($image)
    {
        // 干扰线（3条随机颜色线段）
        for ($i = 0; $i < 3; $i++) {
            $color = imagecolorallocate($image,
                random_int(150, 220), // 随机红色通道值
                random_int(150, 220), // 随机绿色通道值
                random_int(150, 220)  // 随机蓝色通道值
            );
            imageline(
                $image,
                random_int(0, $this->width),  // 起点X坐标
                random_int(0, $this->height), // 起点Y坐标
                random_int(0, $this->width),  // 终点X坐标
                random_int(0, $this->height), // 终点Y坐标
                $color
            );
        }

        // 干扰点（30个随机颜色像素）
        for ($i = 0; $i < 30; $i++) {
            $color = imagecolorallocate($image,
                random_int(180, 240), // 随机红色通道值
                random_int(180, 240), // 随机绿色通道值
                random_int(180, 240)  // 随机蓝色通道值
            );
            imagesetpixel(
                $image,
                random_int(0, $this->width),  // 像素X坐标
                random_int(0, $this->height), // 像素Y坐标
                $color
            );
        }
    }

    /**
     * 绘制验证码字符（内置字体，随机颜色）
     * @param resource $image GD图像资源
     * @param string $captchaValue 验证码字符（如 "ABCD"）
     * @throws Exception 颜色分配失败时抛出异常
     */
    private function drawSimpleText($image, string $captchaValue)
    {
        // 可选字符颜色（深色系，与背景区分）
        $textColors = [
            [50, 80, 100],   // 深蓝
            [100, 50, 80],   // 紫红
            [80, 100, 50],   // 深绿
            [30, 30, 30],    // 深灰
            [120, 30, 30]    // 深红
        ];

        // 逐个绘制每个字符
        for ($i = 0; $i < $this->characters; $i++) {
            $char = $captchaValue[$i]; // 当前字符（如 "A"）
            $colorIndex = random_int(0, count($textColors) - 1); // 随机选择颜色
            $textColor = imagecolorallocate($image,
                $textColors[$colorIndex][0],
                $textColors[$colorIndex][1],
                $textColors[$colorIndex][2]
            );

            // 使用内置字体（尺寸5，最大字体）
            $font = 5;
            $charHeight = imagefontheight($font); // 获取字体高度（用于垂直居中）

            // 计算字符位置（水平均匀分布，垂直居中）
            $x = 10 + ($i * ($this->width - 20) / $this->characters); // 水平位置（避免边缘留白）
            $y = ($this->height / 2) - ($charHeight / 2); // 垂直居中（减去字体高度的一半）

            // 绘制字符到图像（使用imagestring函数）
            imagestring($image, $font, $x, $y, $char, $textColor);
        }
    }

    /**
     * 验证用户输入的验证码是否有效
     * @param array $params 包含验证码键和用户输入值的数组（格式：['captcha_key' => '唯一键', 'captcha_value' => '用户输入']）
     * @return bool 验证成功返回true，失败抛出异常
     * @throws BusinessException 参数缺失/验证码过期/验证码错误时抛出异常（错误码对应lang/errors.php）
     */
    public function validate(array $params): bool
    {
        // 1. 参数校验（确保包含必要字段）
        if (!isset($params['captcha_key'], $params['captcha_value'])) {
            throw new BusinessException(__('errors.500000'), '500000'); // 参数缺失异常
        }

        // 2. 从Redis获取存储的验证码值
        $redis = Predis::getInstance();
        $captchaValue = $redis->get($params['captcha_key']);

        // 3. 检查验证码是否存在（未生成或已过期）
        if (!$captchaValue) {
            throw new BusinessException(__('errors.500001'), '500001'); // 验证码过期/不存在异常
        }

        // 4. 比对用户输入与存储值（不区分大小写）
        if (strtoupper($params['captcha_value']) !== strtoupper($captchaValue)) {
            throw new BusinessException(__('errors.500002'), '500002'); // 验证码错误异常
        }

        // 5. 验证成功后清除Redis缓存（防止重复使用）
        $this->clear($params['captcha_key']);
        return true;
    }

    /**
     * 清除Redis中的验证码缓存
     * @param string $captchaKey 验证码唯一键（如 "captcha_123456"）
     */
    private function clear(string $captchaKey)
    {
        $redis = Predis::getInstance();
        $redis->del($captchaKey); // 删除Redis中的键
    }
}