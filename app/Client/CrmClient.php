<?php

/**
 * 调用CRM相关
 */
namespace App\Client;

class CrmClient extends  BaseClient
{
    public function __construct()
    {
        $this->host = getenv('INTRANET_URL').'/crm';
    }


    /**
     * 获取汇率信息
     * 该方法用于获取两种货币之间的汇率，可以指定生效日期
     * @param string $fromCurrency 起始货币代码
     * @param string $toCurrency   目标货币代码
     * @param string $date 汇率日期，格式为'YYYY-MM-DD'，可选
     * @return array
     */
    public function getExchangeRate($fromCurrency, $toCurrency, $date = ''): array
    {
        $url     = $this->host . '/exchangeRate/getRate';
        $params  = [
            'from' => $fromCurrency,
            'to'   => $toCurrency,
        ];
        if (!empty($date)) {
            $params['time'] = $date;
        }
        return $this->sendClient($url, 'GET', $params);
    }
}