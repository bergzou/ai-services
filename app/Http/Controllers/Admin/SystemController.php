<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libraries\Response;
use App\Services\Admin\SystemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SystemController extends Controller
{

    public function tenantGetByWebsite(Request $request): JsonResponse
    {
        $params = $request->all();

        $service = new SystemService();

        $result = $service->tenantGetByWebsite($params);

        return Response::success($result);
    }

}