<?php

namespace App\Interfaces;

/**
 * AI模型聊天接口
 * 定义AI模型进行聊天交互的标准契约，所有AI模型实现类需遵循此接口规范
 */
interface AiModelInterface
{
    /**
     * 发送聊天消息并获取AI响应
     *
     * @param array $messages 聊天消息数组，通常包含角色(role)和内容(content)等结构化信息
     * @param array $options 可选配置参数，如模型名称、温度值、最大token数等
     * @return string AI生成的文本响应内容
     */
    public function chat(array $messages, array $options = []): string;
}