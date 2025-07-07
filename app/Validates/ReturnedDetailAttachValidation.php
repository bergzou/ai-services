<?php

namespace App\Validates;

use App\Validates\ValidationService;

class ReturnedDetailAttachValidation extends ValidationService
{
    public function rules(): array
    {
        return [
            'attach_url' => 'required|max:500',
            'attach_name' => 'required|max:64',
            'box_code' => 'required|max:64'
        ];
    }

    public function messages()
    {
        return [
            'attach_url' => '图片地址',
            'attach_name' => '图片名称',
            'box_code' => '箱唛号'
        ];
    }

    public function customAttributes()
    {
        return [
            'attach_url' => '图片地址',
            'attach_name' => '图片名称',
            'box_code' => '箱唛号'
        ];
    }
}