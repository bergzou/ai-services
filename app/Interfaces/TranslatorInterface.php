<?php

namespace App\Interfaces;

/**
 * 翻译服务接口：定义多语言翻译功能的标准方法
 * 所有翻译服务实现类（如百度翻译、谷歌翻译适配器）需实现此接口
 */
interface TranslatorInterface
{
    /**
     * 执行文本翻译操作
     * @param string $text 待翻译的原始文本（如 "Hello World"）
     * @param string $sourceLang 源语言代码（遵循 ISO 639-1 标准，如 "en" 表示英语）
     * @param string $targetLang 目标语言代码（如 "zh-CN" 表示简体中文）
     * @return string 翻译后的目标语言文本（如 "你好，世界"）
     *
     * @example translate("Hello", "en", "zh-CN") => "你好"
     */
    public function translate(string $text, string $sourceLang, string $targetLang): string;
}