<?php
/**
 * 调用内部系统-java
 */
namespace App\Client;

use App\Libraries\Curl;

class UicClient extends BaseClient
{


    protected string $prefix = '/uic';

    public function __construct()
    {
        $this->host = getenv('INTRANET_URL') . $this->prefix;
    }



    public function userTopWarehouse($params)
    {
        return $this->sendClient($this->host."/inner/manage/userTopWarehouse",'POST',json_encode($params));
    }

    public function innerWarehouse()
    {
        return $this->sendClient($this->host."/inner/manage/innerWarehouse",'GET');
    }


    public function getManageByName($params)
    {
        return $this->sendClient($this->host."/inner/manage/getManageByName",'POST',json_encode($params));
    }

    public function getManageByCode($params)
    {
        return $this->sendClient($this->host."/inner/manage/getManageByCode",'POST',json_encode($params));
    }


}