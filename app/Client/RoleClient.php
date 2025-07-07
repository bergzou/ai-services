<?php

/**
 * 调用内部系统-erp客户
 */
namespace App\Client;

use App\Exceptions\BusinessException;
use App\Libraries\Curl;

class RoleClient extends BaseClient
{
    public function __construct()
    {
        $this->host = getenv('INTRANET_URL') . '/reception';
    }

    /**
     * 获取用户信息
     * @param string $sellerCode
     * @param array $usersIdArr
     * @return array|bool|string
     */
    public function getUserRole($sellerCode = '',$usersIdArr = []){

        return $this->sendClient($this->host."/internal/Authority/getUserRole",'POST',json_encode(['users_id_arr'=>$usersIdArr,'seller_code' => $sellerCode]));
    }

    /*
     * 获取审核通过的卖家数据
     */
    public function getApproveSellerList($data)
    {
        if(!isset($data['system_id']) || empty($data['system_id'])){
            $data['system_id'] = ["2"];
        }

        if(!isset($data['is_approve']) || empty($data['is_approve'])){
            $data['is_approve'] = 1;
        }

        return $this->sendClient($this->host."/internal/users/getApproveList",'POST',json_encode($data));
    }


    /**
     * 根据客户获取上级代理
     * @param $params
     * @return mixed
     * @throws BusinessException
     */
    public function getSellerCodeAgent($params){

        return $this->sendClient($this->host."/internal/Company/getSellerCodeAgent",'POST',json_encode($params));
    }


    /**
     * 根据代理获取客户数据
     * @param $params
     * @return mixed
     * @throws BusinessException
     */
    public function getAgentSellerCode($params){

        return $this->sendClient($this->host."/internal/Company/getAgentSellerCode",'POST',json_encode($params));
    }




}
