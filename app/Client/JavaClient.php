<?php
/**
 * 调用内部系统-java
 */
namespace App\Client;

use App\Libraries\Curl;

class JavaClient extends BaseClient
{
	protected string $prefix = '/uic';

    public function __construct(){
        $this->host = env('INTRANET_URL','http://inner-dev.tomatocross.cn:18888').$this->prefix;
    }

    /**
     * 查询用户店铺数据
     * @param $data
     * @return bool|string
     */
    public function userQueryStore($data=[]){
        return $this->sendClient($this->host."/inner/manage/userQueryStore",'POST',json_encode($data));
    }


    /**
     * 根据项目代码查项目
     * @param array $manageCode
     * @return array|bool|string
     */
	public function getManageCode(array $manageCode) {
        return $this->sendClient($this->host.'inner/manage/querySellerAndTenant', 'POST', json_encode(['manageCodeList' => $manageCode]));
	}

    /**
     * 根据用户查询项目
     * @param string $sellerCode
     * @return array|bool|string
     */
    public function getManageBySeller(string $sellerCode) {
        return $this->sendClient($this->host.'inner/manage/queryTopBySellerCode', 'POST', json_encode(['sellerCode' => $sellerCode]));
    }

    /**
     * 项目代码查询用户
     * @param array $manageCodes
     * @return array|bool|string
     */
    public function queryManageUser(array $manageCodes) {
        return $this->sendClient($this->host.'inner/manage/batchQueryUser', 'POST', json_encode(['manageCodeList' => $manageCodes]));
    }
}
