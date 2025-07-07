<?php
/**
 * @Date: 2024/3/21
 * @Time: 15:40
 * @Interface BaseService
 * @return
 */


namespace App\Service;



use App\Exceptions\BusinessException;
use App\Libraries\GetNumber;
use App\Libraries\Predis;
use App\Libraries\LibSnowflake;
use App\Libraries\Common;

class BaseService
{
    protected array $userInfo = [];
    protected $redis = null;

    public function __construct()
    {
        $this->userInfo = UserInfoService::getUserInfo();
    }

    //获取雪花ID
    public static function getSnowWorkId()
    {
        static $obj = null;
        if (is_null($obj)) {
            $obj = new LibSnowflake(Common::getWorkerId());
        }
        return $obj->next();
    }



    /** 获取单号
     * @param $redisKey
     * @param $prefix
     * @param int $length
     * @return string
     */
    public function getCode($redisKey , $prefix , $length = 4){
        $code = GetNumber::getStatic()->setKey($redisKey)
            ->setPrefix($prefix)->setFillLength($length)->getCode();
        return strtoupper($code);
    }

    /**
     * 主键
     * @param $key
     * @param $regionCode
     * @return string
     */
    public function  redisKey($key ,$regionCode){
        return $key.$regionCode.':'.$key;
    }

    /** 前缀
     * @param $front
     * @param string $back
     * @return string
     */
    public function getPrefix($front , $back = ''){
        if (empty($back)){
            $back = date('y') . date('m') . date('d');
        }
        return $front.$back;
    }

    /** 获取时间
     * @return false|string
     */
    public function getDate(){
        return date('Y-m-d H:i:s');
    }

    //检测参数锁
    public function setAndCheckParamsLockKey($params,$time=60)
    {
        $lockKey = $this->getParamsLockKey($params);
        $this->redis = new Predis();
        $lock = $this->redis->get($lockKey);
        if ($lock) throw new BusinessException('操作正在进行中，请稍后再试');
        $this->setParamsLockKey($lockKey,$time);
        return $lockKey;
    }

    //获取参数锁
    protected function getParamsLockKey($params)
    {
        $lockKey = json_encode($params);
        return md5($lockKey);
    }

    //加锁
    protected function setParamsLockKey($lockKey, $time=60)
    {
        return $this->redis->lock($lockKey,$time);
    }

    //删锁
    public function delParamsLockKey($lockKey)
    {
        return $this->redis->del($lockKey);
    }
}
