<?php

/**
 * 调用内部系统-站点
 */
namespace App\Client;

use App\Libraries\Curl;

class AdsClient
{

    protected static $host = '192.168.1.64:808/';//内部ip
    public static function getIntranetUrl(){
		self::$host = env('INTRANET_URL','http://inner-dev.tomatocross.cn:18888').'/admin/';
    }
    /**
     * 获取站点数据
     * @return array|bool|string
     * zm 2023年6月6日16:28:22
     */
    public static function getSite($data=[]){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/Platform/getSite",'POST',json_encode($data));
    }
    /**
     * 获取站点数据
     * @return array|bool|string
     * zm 2023年6月6日16:28:22
     */
    public static function getSiteOne($site_id_arr = ''){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/Platform/getSite",'POST',json_encode(['site_id_arr'=>$site_id_arr]));
    }

    /**
     *获取当前公司下的授权账号
     * @param $data
     * @return bool|string
     * zm 2023年6月8日18:53:31
     */
    public static function getDeveloper($data = []){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/Platform/getDeveloper",'POST',json_encode($data));
    }

    /**
     *获取客户当前公司下的授权账号
     * @param $data
     * @return bool|string
     * zm 2023年6月8日18:53:31
     */
    public static function getCustomerDeveloper($data = []){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/Platform/getCustomerDeveloper",'POST',json_encode($data));
    }

    /**
     * 根据用户id获取用户
     * @return bool|string
     * zm 2023年6月8日18:53:51
     */
    public static function GetUserList($data=[]){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/Platform/getUserList",'POST',json_encode($data));
    }

    /**
     * 获取所有平台
     * @return array|bool|string
     * zm 2023年6月8日19:26:00
     */
    public static function getPlatform($data=[]){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/Platform/getPlatform",'POST',json_encode($data));
    }
    /**
     * 获取所有平台
     * @return void
     * zm 2023年6月8日19:26:00
     */
    public static function getDeveloperList($data=[]){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/Platform/getPlatform",'POST',json_encode($data));
    }
}
