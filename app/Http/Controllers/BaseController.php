<?php
namespace App\Http\Controllers;
/**
 * @Notes:  仓库模块基类
 * @Date: 2024/3/26
 * @Time: 13:53
 * @Interface BaseController
 * @return
 */


use App\Service\UserInfoService;
use Illuminate\Routing\Controller;
class BaseController extends Controller
{

    public array $helpers = [];

    /**
     * 用户id
     * @var mixed|string
     */
    public $reqUserId;

    /** 用户姓名
     * @var string
     */
    public $reqUserName;

    /**
     * 用户手机号
     * @var mixed|string
     */
    public $reqUserMobile;


    /**
     * 租户编码
     * @var mixed|string
     */
    protected $reqTenantCode;

    /**
     * 仓库编码数组
     * @var array|string[]
     */
    protected $warehouseCodeArr = [];

    /**
     * 仓库编码 字符串
     * @var array|string[]
     */
    protected mixed $warehouseCode ;

    /**
     * 用户基础数据
     * @var array
     */
    public array $userInfo = [];
    /**
     * @var mixed|string
     */
    public string $regionCode ;
    /**
     * @var mixed|string
     */
    public string $reqCityName ;
    /**
     * @var mixed|string
     */
    public mixed $reqSellerCode;
    /**
     * @var mixed|string
     */
    public mixed $reqMangeCode;

    public int $reqAdminType;


    public function __construct(){

        $headers = apache_request_headers();

        $this->reqUserId        = $headers['Req-User-Id'] ?? '4513557392780206080';
        $this->reqUserName      = isset($headers['Req-User-Name']) ? urldecode($headers['Req-User-Name']) : '总账号';
        $this->reqUserMobile    = $headers['Req-User-Mobile'] ?? '13922166032';
        $this->reqCityName      = $headers['Req-City-Name'] ?? 'America/Mexico_City';
        $this->regionCode       = $headers['Req-Region-Code'] ?? "ZS";
        $this->warehouseCode    = $headers['Req-Warehouse-Code'] ?? "CDMX1,CDMX3,CSCK,CDMX4,A0014,ZSC,HN";
        $this->reqTenantCode    = $headers['Req-Tenant-Code'] ?? 'xhs';
        $this->warehouseCodeArr = explode(',', $this->warehouseCode);
        $this->reqSellerCode    = $headers['Req-Seller-Code'] ?? 'XH0038';
        $this->reqMangeCode    = $headers['Req-Manage-Code'] ?? '';
        $this->reqAdminType  = $headers['Req-Admin-Type'] ?? '1';

        $this->userInfo = [
            'user_id'            => $this->reqUserId,
            'user_name'          => $this->reqUserName,
            'user_mobile'        => $this->reqUserMobile,
            'tenant_code'        => $this->reqTenantCode,
            'seller_code'	     => $this->reqSellerCode,
            'manage_code'	     => $this->reqMangeCode,
            'region_code'        => $this->regionCode,
            'warehouse_code'     => $this->warehouseCode,
            'warehouse_code_arr' => $this->warehouseCodeArr,
            'city_name'          => $this->reqCityName,
            'admin_type'          => $this->reqAdminType,
        ];

        UserInfoService::setUserInfo($this->userInfo);

    }


    /**
     * 下拉多选匹配
     * @param $input
     * @param $fields
     * @return array
     */
    public function setInputKeyValue(&$input, $fields){
        if (empty($fields)) return [];
        foreach ($fields as $field) {
            $keyField   = $field . '_key';
            $valueField = $field . '_value';
            if (!empty($input[$keyField]) && !empty($input[$valueField])){
                $input[$input[$keyField]] = $input[$valueField];
                unset($input[$keyField]);
                unset($input[$valueField]);
            }
        }

        return $input;
    }

    //附加默认权限参数
    public function setInputDefaultAuth(&$input){

//        $input['seller_code'] = $this->reqSellerCode;

        if (empty($input['warehouse_code'])) $input['warehouse_code'] = $this->warehouseCodeArr;

        ////处理成只有一种数据格式（数组）到service层
        if (!empty($input['warehouse_code'])) {
            $input['warehouse_code'] = is_array($input['warehouse_code']) ? $input['warehouse_code'] : explode(',',$input['warehouse_code']);
        }

        return $input;
    }

    //列表时间参数处理
    public function setInputDate(&$input, array $fields){

        if (empty($fields)) return [];

        foreach ($fields as $field) {
            if ( empty($input[$field]) || !is_array($input[$field]) ){
                unset($input[$field]);
                continue;
            }

            if (isset($input[$field][0])){
                if ( strlen($input[$field][0]) == 10 ) $input[$field][0] .= ' 00:00:00';
            }
            if (isset($input[$field][1])){
                if ( strlen($input[$field][1]) == 10 ) $input[$field][1] .= ' 23:59:59';
            }
        }
        return $input;
    }

    /**
     * 转换仓库code参数为数组格式
     * @param $input
     * @return void
     */
    public function setInputWarehouseCodeArr(&$input){

        if (!empty($input['warehouse_code'])){
            $input['warehouse_code'] = is_array($input['warehouse_code']) ? $input['warehouse_code'] : explode(',',$input['warehouse_code']);
        }
    }


    public function setInputSelleCode(&$requestData){

        if (!empty($requestData['seller_code_child'])){
            $requestData['seller_code'] = array_merge($requestData['seller_code_child'],[$requestData['seller_code']]);
        }


        return $requestData;
    }
}
