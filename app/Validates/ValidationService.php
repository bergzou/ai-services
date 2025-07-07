<?php

namespace App\Validates;


use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;


abstract class ValidationService
{
    protected string $group = '';
    protected array $params = [];
    protected string $groupParamsFun = '';

    abstract function rules();
    abstract function messages();
    abstract function customAttributes();

    public function __construct($params,$group)
    {
        $this->params = $params;
        $this->group = $group;
        $this->groupParamsFun = $this->group.'Params';
        if ( !method_exists($this,$this->groupParamsFun) && method_exists($this,$group) ){
            $this->groupParamsFun = $this->group;
        }
        return $this;
    }

    public function isRunFail($isThrow = false, $isAll = false)
    {

        $params = $this->params;
        $group  = $this->group;

        if (empty($group)) return [];

        $rules = $this->$group();  #执行__call方法
        if (empty($rules)) throw new BusinessException(Lang::get('rules function name error'));

        $rules = $this->isAssoc($rules) ? $rules : $rules[$group];
        $validator = Validator::make($params, $rules, $this->messages(), $this->customAttributes());
        $messages  = [];
        if ($validator->fails()) {
            if ($isAll) {
                $messages = $validator->errors()->all();
                $messages = implode(',', $messages);
            } else {
                $messages = $validator->errors()->first();
            }
            if ($isThrow) throw new BusinessException($messages);
        }
        return $messages;
    }


    //获取参数与数据库字段交集参数
    public function getRequestParams(){

        $paramsFun = $this->groupParamsFun;
        if (!method_exists($this,$paramsFun)) return $this->params;

        return array_intersect_key($this->params,$this->$paramsFun());
    }


    // call魔术方法，获取操作方法对应的验证规则
    public function __call($name,$arguments){

        $paramsFun = $this->groupParamsFun;
        if (!method_exists($this,$paramsFun)) return []; #不存在，返回原参数
        $intersectRules = [] ;
        $rules =  $this->rules();
        foreach ($this->$paramsFun() as $key => $value){
            if (isset($rules[$key])){
                if ( !empty($value) ){
                    $intersectRules[$key] = $value;
                }else{
                    $intersectRules[$key] = $rules[$key];
                }
            }else if ( isset($rules[$value]) ){
                $intersectRules[$value] = $rules[$value];
            }
        }
        return $intersectRules;
    }



    public function errorsToStr(array $errors) : string
    {
        $str = '';
        foreach ($errors as $field => $errorMsg){
            $str .= $field .':'.$errorMsg .'; ';
        }
        return $str;
    }

    public function isAssoc($array) {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }
}
