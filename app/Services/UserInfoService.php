<?php
/*
 * 登录用户信息
 */

namespace App\Services;

use App\Enums\EnumBase;
use Illuminate\Validation\Rules\Enum;

class UserInfoService
{
    protected static array $userInfo = [];

    /**
     * 设置登录用户信息
     * @param $userInfo
     * $userInfo = [
     * 'user_id'        => $reqUserId,
     * 'user_name'        => $reqUserName,
     * 'user_mobile'    => $reqUserMobile,
     * 'seller_code'    => $reqSellerCode,
     * 'tenant_code'    => $reqTenantCode,
     * 'manage_code'    => $reqMangeCode,
     * 'region_code'        => $regionCode,
     * 'warehouse_code'     => $warehouseCode,
     * 'warehouse_code_arr' => $warehouseCodeArr,
     * ];
     */
    public static function setUserInfo($userInfo)
    {
        if (!empty(self::$userInfo)){
            $userInfo = array_merge(self::$userInfo,$userInfo);
        }

        if (!empty($userInfo)) self::$userInfo = $userInfo;
    }

    /**
     * 获取登录用户信息
     * @return array
     */
    public static function getUserInfo()
    {
        return self::$userInfo;
    }


    public static function getUserId(){
        return self::$userInfo['user_id'] ?? "";
    }

    public static function getUserName(){
        $user_name = self::$userInfo['user_name'] ?? '';
        return empty($user_name) ? EnumBase::SYSTEM : $user_name;
    }

    public static function getSellerCode(){
        return self::$userInfo['seller_code'] ?? "";
    }

    public static function getTenantCode(){
        return self::$userInfo['tenant_code'] ?? "";
    }

}
