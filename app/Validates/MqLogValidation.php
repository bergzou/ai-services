<?php

namespace App\Validates;


class MqLogValidation extends ValidationService
{

    public function rules(){

        return [
            'msgId' => 'required',
            'requestAt' => 'required',
            'language' => 'required',
            'operateType' => 'required',
            'data' => 'required',
        ];
    }

    public function messages(){
        return [];
    }

    public function customAttributes( ){
        return [
        ];
    }


    public function requestParams(){
        return [
            'msgId',
            'requestAt',
            'language',
            'operateType',
            'data'
        ];
    }


}
