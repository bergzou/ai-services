<?php

namespace App\Services\Common\Sms;

use App\Exceptions\BusinessException;
use App\Interfaces\SmsInterface;



class SmsManager implements SmsInterface
{
    protected SmsInterface $driver;
    protected array $drivers = [];

    /**
     * @throws BusinessException
     */
    public function __construct(string $driver = null)
    {

        if (!config('sms.enabled', false)) {
            throw new BusinessException(__('errors.500003'),500003);
        } else {
            $driver = $driver ?: config('sms.default');
        }
        // 初始化指定驱动
        $this->driver($driver);
    }


    private function driver(string $driver): void
    {
        // 若驱动未实例化，则创建并缓存
        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }
        // 设置当前驱动为缓存中的实例
        $this->driver = $this->drivers[$driver];
    }


    /**
     * @throws BusinessException
     */
    private function createDriver(string $driver)
    {

        $config = config("sms.drivers.{$driver}");

        // 检查驱动配置是否存在
        if (empty($config)) {
            throw new BusinessException("驱动 [{$driver}] 未在配置中定义");
        }

        // 获取驱动类名（配置中的 driver 字段）
        $driverClass = $config['driver'];

        // 检查驱动类是否存在
        if (!class_exists($driverClass)) {
            throw new BusinessException("驱动类 [{$driverClass}] 不存在");
        }
        // 通过Laravel容器解析实例（支持依赖注入）
        return app($driverClass);
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
        return $this->driver->singleSend($mobile, $params);
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
        return $this->driver->batchSend($mobile, $params);
    }

    /**
     * 发送模板单条短信（模板场景）
     * @param string $mobile 接收短信的手机号码（格式：11位数字，如 "13812345678"）
     * @param string $templateId 短信模板ID（对应短信平台配置的模板唯一标识，如 "TPL_12345"）
     * @param array $templateValue 模板变量替换数据（键值对形式，如 ['code' => '1234', 'expire' => '5分钟']）
     * @return array 发送结果数组（示例：['success' => true, 'message_id' => 'SMS_456', 'error' => '']）
     * @desc 实现类需处理：模板变量格式校验、模板ID有效性验证、参数替换、模板发送API调用等逻辑
     */
    public function templateSingleSend(string $mobile, string $templateId, array $templateValue): array
    {
       return $this->driver->templateSingleSend($mobile, $templateId, $templateValue);
    }

    /**
     * 发送模板批量短信（模板场景）
     * @param array $mobile 接收短信的手机号码（批量时支持逗号分隔，如 "13812345678,13912345678"）
     * @param string $templateId 短信模板ID（对应短信平台配置的模板唯一标识，如 "TPL_12345"）
     * @param array $templateValue 批量模板变量替换数据（二维数组，如 [['code' => '1234'], ['code' => '5678']]）
     * @return array 发送结果数组（示例：['total' => 2, 'success' => 2, 'fail' => 0, 'details' => [...]]）
     * @desc 实现类需处理：手机号与变量数量匹配校验、批量模板参数替换、批量模板发送API调用、结果明细记录等逻辑
     */
    public function templateBatchSend(array $mobile, string $templateId, array $templateValue): array
    {
        return $this->driver->templateBatchSend($mobile, $templateId, $templateValue);
    }
}