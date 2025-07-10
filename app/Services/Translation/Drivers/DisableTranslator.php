<?php

namespace App\Services\Translation\Drivers;

use App\Interfaces\TranslatorInterface;

/**
 * 翻译禁用驱动：当翻译功能关闭时使用的空实现
 * 遵循 TranslatorInterface 接口规范，返回与其他翻译驱动一致的结构化数据格式
 * 用于在翻译服务不可用时（如测试/维护模式）保持系统兼容性
 */
class DisableTranslator implements TranslatorInterface
{
    /**
     * 翻译方法（禁用模式下直接返回原文）
     * @param string $text 待翻译的原始文本（如 "Hello"）
     * @param string $sourceLang 源语言代码（如 "en" 表示英语）
     * @param string $targetLang 目标语言代码（如 "zh" 表示中文）
     * @return string JSON格式的翻译结果（包含原文、"译文"（实际为原文）、源语言和目标语言信息）
     */
    public function translate(string $text, string $sourceLang, string $targetLang): string
    {
        // 翻译功能关闭时，直接返回原文作为"译文"，保持与其他驱动一致的输出格式
        return json_encode([
            'original_text' => $text,          // 原始文本
            'translated_text' => $text,        // 禁用模式下"译文"等于原文
            'source_lang' => $sourceLang,      // 源语言代码
            'target_lang' => $targetLang       // 目标语言代码
        ]);
    }
}