<?php

namespace App\Services\Common\Sms\Drivers;

use App\Exceptions\BusinessException;
use App\Interfaces\SmsInterface;
use App\Libraries\Curl;


class YunPianSmsDriver implements SmsInterface
{
    protected $config;

    protected array $headers = [
        'Accept: application/json;charset=utf-8',
        'Content-Type: application/x-www-form-urlencoded;charset=utf-8',
    ];

    public function __construct()
    {
        $this->config = config('sms.drivers.yunpian');
    }


    /**
     * 发送普通单条短信（非模板场景）
     * @param string $mobile 接收短信的手机号码（格式：11位数字，如 "13812345678"）
     * @param array $params 短信内容参数（键值对形式，如 ['content' => '您的验证码是1234']）
     * @return array 发送结果数组（示例：['success' => true, 'message_id' => 'SMS_123', 'error' => '']）
     * @desc 实现类需处理：手机号格式校验、短信内容长度限制、短信平台API调用、错误信息封装等逻辑
     */
    public function singleSend(string $mobile, array $params): array
    {
        // TODO: Implement singleSend() method.
    }

    /**
     * 发送普通批量短信（非模板场景）
     * @param string $mobile 接收短信的手机号码（批量时支持逗号分隔，如 "13812345678,13912345678"）
     * @param array $params 批量短信内容参数（键值对形式，如 ['content' => '系统维护通知：今晚20点...']）
     * @return array 发送结果数组（示例：['total' => 2, 'success' => 2, 'fail' => 0, 'details' => [...]]）
     * @desc 实现类需处理：批量手机号解析、内容一致性校验、批量发送API调用、结果分组统计等逻辑
     */
    public function batchSend(string $mobile, array $params): array
    {
        // TODO: Implement batchSend() method.
    }


    /**
     * 发送云片单条模板短信（对接云片API）
     * @param string $mobile 接收短信的手机号码（格式：11位数字，如 "13812345678"）
     * @param string $templateId 云片短信模板ID（如 "123456"，需在云片平台预先配置）
     * @param array $templateValue 模板变量替换数据（键值对形式，如 ['code' => '1234', 'expire' => '5分钟']）
     * @return array 云片API返回的原始结果（示例：['code' => 0, 'msg' => '发送成功', 'count' => 1, ...]）
     * @throws BusinessException 模板变量处理失败、API请求失败或云片返回非0状态码时抛出异常
     * @desc 核心流程：模板变量编码→构造请求参数→调用云片API→解析并验证响应结果
     */
    public function templateSingleSend(string $mobile, string $templateId, array $templateValue): array
    {
        // 1. 处理模板变量（转换为云片要求的格式）
        $tplValue = '';
        foreach ($templateValue as $key => $value) {
            // 替换%为全角％（避免云片模板解析错误）
            $encodedValue = str_replace('%', '％', $value);
            // 按云片要求格式拼接变量（#key#=value，需URL编码）
            $tplValue .= '&' . urlencode('#' . $key . '#') . '=' . urlencode($encodedValue);
        }
        // 去除首字符多余的&符号（拼接后第一个字符为&）
        $tplValue = ltrim($tplValue, '&');

        // 2. 构造云片API请求参数（根据云片文档要求）
        $url = $this->config['domain'] . "/v2/sms/tpl_single_send.json"; // 云片单条模板发送接口地址
        $params = [
            'apikey'    => $this->config['api_key'],  // 云片API密钥（从配置文件获取）
            'mobile'    => $mobile,                   // 接收短信的手机号
            'tpl_id'    => $templateId,               // 模板ID
            'tpl_value' => $tplValue                  // 编码后的模板变量（格式：#key#=value&...）
        ];

        // 3. 调用Curl发送POST请求（使用预定义的请求头）
        $result = Curl::sendRequest($url, 'POST', $params, $this->headers);

        // 4. 解析并验证API响应结果
        $resultJson = json_decode($result, true); // 将返回的JSON字符串转为数组
        if (empty($resultJson)) {
            // JSON解析失败时抛出异常（错误码500001对应"系统内部错误"）
            throw new BusinessException(__('common.500001') . "::响应内容非JSON格式: $result", 500001);
        }

        // 云片API返回code=0表示成功，非0为失败（具体含义参考云片错误码文档）
        if ($resultJson['code'] != 0) {
            $msg = $resultJson['msg'] ?? '未知错误';       // 错误信息（如"模板未审核"）
            $detail = $resultJson['detail'] ?? '无详细信息'; // 错误详情（如"模板ID不存在"）
            throw new BusinessException(__('common.500001') . "::云片错误: $msg - $detail", 500001);
        }

        return $resultJson; // 返回成功的响应数据（包含发送数量、消耗条数等信息）
    }

    /**
     * 发送模板批量短信（模板场景）
     * @param array $mobile 接收短信的手机号码 ['13800000000','13900000000']
     * @param string $templateId 短信模板ID（对应短信平台配置的模板唯一标识，如 "TPL_12345"）
     * @param array $templateValue 批量模板变量替换数据（二维数组，如 [['code' => '1234'], ['code' => '5678']]）
     * @return array 发送结果数组（示例：['total' => 2, 'success' => 2, 'fail' => 0, 'details' => [...]]）
     * @desc 实现类需处理：手机号与变量数量匹配校验、批量模板参数替换、批量模板发送API调用、结果明细记录等逻辑
     */
    public function templateBatchSend(array $mobile, string $templateId, array $templateValue): array
    {
        // TODO: Implement templateBatchSend() method.
    }
}