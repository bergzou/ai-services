<?php

namespace App\Interfaces;

/**
 * 短信服务接口：定义全场景短信发送功能的标准方法
 * 所有短信驱动实现类（如云片短信、腾讯云短信、华为云短信）需实现此接口，
 * 确保不同短信服务提供商的发送逻辑（单条/批量、普通/模板）遵循统一规范，支持驱动灵活切换
 */
interface SmsInterface
{
    /**
     * 发送普通单条短信（非模板场景）
     * @param string $mobile 接收短信的手机号码（格式：11位数字，如 "13812345678"）
     * @param array $params 短信内容参数（键值对形式，如 ['content' => '您的验证码是1234']）
     * @return array 发送结果数组（示例：['success' => true, 'message_id' => 'SMS_123', 'error' => '']）
     * @desc 实现类需处理：手机号格式校验、短信内容长度限制、短信平台API调用、错误信息封装等逻辑
     */
    public function singleSend(string $mobile, array $params): array;

    /**
     * 发送普通批量短信（非模板场景）
     * @param string $mobile 接收短信的手机号码（批量时支持逗号分隔，如 "13812345678,13912345678"）
     * @param array $params 批量短信内容参数（键值对形式，如 ['content' => '系统维护通知：今晚20点...']）
     * @return array 发送结果数组（示例：['total' => 2, 'success' => 2, 'fail' => 0, 'details' => [...]]）
     * @desc 实现类需处理：批量手机号解析、内容一致性校验、批量发送API调用、结果分组统计等逻辑
     */
    public function batchSend(string $mobile, array $params): array;

    /**
     * 发送模板单条短信（模板场景）
     * @param string $mobile 接收短信的手机号码（格式：11位数字，如 "13812345678"）
     * @param string $templateId 短信模板ID（对应短信平台配置的模板唯一标识，如 "TPL_12345"）
     * @param array $templateValue 模板变量替换数据（键值对形式，如 ['code' => '1234', 'expire' => '5分钟']）
     * @return array 发送结果数组（示例：['success' => true, 'message_id' => 'SMS_456', 'error' => '']）
     * @desc 实现类需处理：模板变量格式校验、模板ID有效性验证、参数替换、模板发送API调用等逻辑
     */
    public function templateSingleSend(string $mobile, string $templateId, array $templateValue): array;

    /**
     * 发送模板批量短信（模板场景）
     * @param array $mobile 接收短信的手机号码 ['13800000000','13900000000']
     * @param string $templateId 短信模板ID（对应短信平台配置的模板唯一标识，如 "TPL_12345"）
     * @param array $templateValue 批量模板变量替换数据（二维数组，如 [['code' => '1234'], ['code' => '5678']]）
     * @return array 发送结果数组（示例：['total' => 2, 'success' => 2, 'fail' => 0, 'details' => [...]]）
     * @desc 实现类需处理：手机号与变量数量匹配校验、批量模板参数替换、批量模板发送API调用、结果明细记录等逻辑
     */
    public function templateBatchSend(array $mobile, string $templateId, array $templateValue): array;
}