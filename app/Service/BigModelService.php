<?php
namespace App\Service;


use App\Exceptions\BusinessException;
use App\Strategy\AiServicesStrategy;
use Illuminate\Support\Facades\Validator;
use Exception;
class BigModelService implements AiServicesStrategy
{


    /**
     * @throws \Exception
     */
    public function checkParams($params){


        $validator = Validator::make($params, [
            'inbound_order_code' => 'required',  // 入库单号为必填项
        ]);
        if ($validator->fails()) {
            throw new BusinessException($validator->errors()->first());  // 参数验证失败抛出异常
        }
        return $params;
    }

    /**
     * @throws \Exception
     */
    function forward($params)
    {

        try {
            $params = $this->checkParams($params);

        }catch (BusinessException|Exception $e){
            throw new Exception($e->getMessage());
        }



    }


}