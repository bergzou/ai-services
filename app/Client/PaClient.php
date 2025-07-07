<?php

/**
 * 调用内部系统-卖家中心
 */
namespace App\Client;

use App\Libraries\Curl;

class PaClient extends BaseClient
{

    /**
     * 根据平台站点获取账号
     * @return array|bool|string
     * zm 2023年6月13日15:17:51
     */
    public static function getAccount($data = []){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/account/getAccount",'POST',json_encode($data));
    }

    /**
     * 根据基座id查询账号id
     * @param array $data
     * @return bool|string
     */
    public static function getAccountId($data = []){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/account/getBaseAccountCode",'POST',json_encode($data));
    }

    /**
     * 根据平台站点获取账号
	 * $data = [
	 * 		'account_id' => '11111'
	 * ]
     * @return void
     * zm 2023年6月13日15:17:51
     */
    public static function getAccountWarehouse($data = []): string{
        self::getIntranetUrl();

        return Curl::sendRequest(self::$host."/internal/Account/getPlatformAccountJava",'POST',json_encode($data));
    }

}
