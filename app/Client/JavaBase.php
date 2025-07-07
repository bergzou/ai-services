<?php
/**
 * 基座
 */
namespace App\Client;

use App\Libraries\Curl;

class JavaBase extends BaseClient
{
    const HOST = "https://bms.xhsnw.com/";

    //------------1688-------------
    /**
     * 1688授权
     * @param array $data
     * @return array|bool|string
     */
    public static function aliexpressCnAuth($data=[]){
        return Curl::sendRequest(self::HOST."xh-base-auth/api/auth/generate/pms",'POST',json_encode($data));
    }
}
