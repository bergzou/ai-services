<?php

/**
 * 调用内部系统-站点
 */
namespace App\Client;

use App\Libraries\Curl;

class OaClient
{
    protected static $host = '';

    public static function getIntranetUrl(){
        self::$host = env('INTRANET_URL','http://inner-dev.tomatocross.cn:18888').'/auth/';
    }

    /**
     * 根据用户id获取用户
     * @return bool|string
     * zm 2023年6月8日18:53:51
     */
    public static function getUserList($data=[]){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/users/getUsersList",'POST',json_encode($data));
    }

    /**
     * 获取用户信息
     * @param $users_id
     * @return bool|string
     */
    public static function getUsersOne($users_id){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/users/getUsersOne",'POST',json_encode(['users_id'=>$users_id]));
    }

    /**
     *设置路由到redis
     * @return bool|string
     */
    public static function setUpRoutesRedis(){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/users/setUpRoutesRedis");
    }

    // 获取部门下的用户
    public static function getAuthorityUsers($structureId){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/webapi/structure/getAuthorityUsers",'POST',json_encode(['structure_id'=>$structureId]));
    }

    // 获取用户的部门
    public static function getStructureIdAdminUser($usersId){
        self::getIntranetUrl();
        return Curl::sendRequest(self::$host."/internal/Structure/getStructureIdAdminUser",'POST',json_encode(['users_id'=>$usersId]));
    }
}
