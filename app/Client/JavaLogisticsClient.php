<?php
/**
 * 调用内部系统-LIMS
 */
namespace App\Client;

use App\Libraries\Curl;

class JavaLogisticsClient extends BaseClient
{
	protected string $prefix = '/lims/';

    public function __construct(){
        $this->host = env('INTRANET_URL','http://inner-dev.tomatocross.cn:18888').$this->prefix;
    }

    /*
     * 获取运输方式
     */
    public function getShippingTypeData($data=array())
    {
        $res = Curl::sendRequest($this->host."shipping/first/query/shippingTypeData",'POST',json_encode($data));
        return json_decode($res,true);
    }
}
