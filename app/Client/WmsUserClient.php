<?php

/**
 * 调用内部系统-erp客户
 */
namespace App\Client;

use App\Exceptions\BusinessException;
use App\Libraries\Curl;

class WmsUserClient extends BaseClient
{
    public function __construct()
    {
        $this->host = env('INTRANET_URL','http://inner-test.tomatocross.cn:18888').'/reception';
    }


    /**
     * 根据用户id获取用户
     * @return bool|string
     * zm 2023年6月8日18:53:51
     */
    public function getUserList($data=[]){
        return Curl::sendRequest($this->host."/internal/users/getUsersList",'POST',json_encode($data));
    }

    /**
     * 获取所有卖家
     * @param array $data
     * @return array|bool|string
     */
    public function getCompany($data=[]){
        $url = $this->host."/internal/company/getCompany";
        return $this->sendClient($url,'POST',json_encode($data));
    }

    /**
     * 获取所有项目
     * @param array $data
     * @return array|bool|string
     */
    public function getManageCodeAll(){
        $url = $this->host."/internal/Manage/getManageCodeAll";
        return Curl::sendRequest($url,'GET');
    }


    /**
     * @throws BusinessException
     */
    public function getCompanyAll($data){
        $url = $this->host."/internal/company/getCompany";
        return $this->sendClient($url,'POST',json_encode($data));
    }



}
