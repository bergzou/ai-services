<?php

namespace App\Http\Controllers;

use App\Libraries\Response;
use App\Services\Excel\ExcelManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;


class Controller extends BaseController
{

    public function test(Request $request){
        $params = $request->all();

        /** @var ExcelManager $excel */
        $excel = App::make('excel');


        // 1. 构造模拟表头（符合 export 方法要求的 headers 格式）
        $headers = [
            ['label' => '用户姓名', 'field' => 'name'],
            ['label' => '用户年龄', 'field' => 'age'],
            ['label' => '注册时间', 'field' => 'created_at']
        ];

        // 2. 构造模拟数据（每条数据对应 headers 中的 field）
        $data = [
            ['name' => '张三', 'age' => 25, 'created_at' => '2024-01-10 10:00:00'],
            ['name' => '李四', 'age' => 30, 'created_at' => '2024-01-11 14:30:00'],
            ['name' => '王五', 'age' => 28, 'created_at' => '2024-01-12 09:15:00']
        ];
        $data = $excel->export('ceshi.xlsx', $headers, $data);

        return Response::success($data);
    }

}
