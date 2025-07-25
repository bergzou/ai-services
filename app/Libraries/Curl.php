<?php

namespace App\Libraries;

use App\Exceptions\BusinessException;

/**
 * cURL工具类：封装通用HTTP请求功能
 * 支持GET/POST/PUT/DELETE等方法，提供重试机制、HTTPS配置、超时控制等功能
 * 用于调用第三方API（如短信接口、支付接口）或内部服务间通信
 */
class Curl
{
    /**
     * 发送HTTP请求（核心方法）
     * @param string $url 请求URL地址（如 "https://api.example.com/user"）
     * @param string $method 请求方法（默认GET，支持GET/POST/PUT/DELETE）
     * @param array $data 请求数据（GET时拼接为查询参数，其他方法作为请求体）
     * @param array $headers 请求头数组（默认：["Content-Type: application/json;charset=UTF-8"]）
     * @param int $timeout 超时时间（秒，默认30秒）
     * @param int $maxRetries 最大重试次数（默认0次，不重试）
     * @param int $retryDelay 重试间隔（毫秒，默认0，立即重试）
     * @param bool $https 是否启用HTTPS模式（默认true，自动关闭证书验证）
     * @return string 请求返回的原始内容（如JSON字符串、HTML文本）
     * @throws BusinessException cURL执行失败时抛出异常（包含错误信息）
     * @desc 核心流程：初始化cURL句柄→配置请求参数→执行请求（含重试）→处理错误→返回结果
     */
    public static function sendRequest(
        string $url,
        string $method = 'GET',
        array $data = [],
        array $headers = ["Content-Type: application/json;charset=UTF-8"],
        int $timeout = 30,
        int $maxRetries = 0,
        int $retryDelay = 0,
        bool $https = true
    ) {
        // 初始化cURL会话
        $ch = curl_init();

        // 配置：返回响应内容而不直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 配置：HTTPS请求时关闭证书验证（避免自签名证书报错）
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 不验证CA证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 不验证HOST
        }

        // 配置：设置请求方法（强制大写）
        $method = strtoupper($method);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // 配置：处理请求数据（根据方法类型拼接参数或设置请求体）
        if (!empty($data)) {
            switch ($method) {
                case 'GET':
                    // GET方法：将数据拼接为查询参数（如 ?name=test&age=20）
                    $url .= '?' . http_build_query($data);
                    break;
                case 'POST':
                    // POST方法：启用POST并设置请求体
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    break;
                case 'PUT':
                case 'DELETE':
                    // PUT/DELETE方法：设置自定义请求方法并设置请求体
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    break;
            }
        }

        // 配置：设置最终请求URL（可能已拼接GET参数）
        curl_setopt($ch, CURLOPT_URL, $url);

        // 配置：设置请求头（如Content-Type、Authorization）
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // 配置：设置超时时间（秒）
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        // 配置：重试机制（仅当maxRetries>0时生效）
        if ($maxRetries > 0) {
            curl_setopt($ch, CURLOPT_FAILONERROR, true); // 错误时返回false
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $retryDelay); // 连接超时（毫秒）
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout * 1000); // 总超时（毫秒）
        }

        // 执行请求（含重试逻辑）
        $retryCount = 0;
        do {
            $result = curl_exec($ch); // 执行cURL请求
            $retryCount++; // 重试次数递增
        } while ($maxRetries > 0 && $result === false && $retryCount <= $maxRetries);

        // 错误处理：检查cURL错误码（非0表示有错误）
        if (curl_errno($ch)) {
            $error = curl_error($ch); // 获取错误信息
            curl_close($ch); // 关闭会话
            throw new BusinessException("CURL请求失败: $error"); // 抛出业务异常
        }

        // 关闭cURL会话并返回结果
        curl_close($ch);
        return $result;
    }
}