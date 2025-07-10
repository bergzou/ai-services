<?php
namespace App\Http\Controllers\Internal;



use App\Http\Controllers\BaseController;
use App\Services\AiService;

use App\Services\BigModelService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Libraries\Response;


class BigModelController extends BaseController
{


    public function forward(Request $request): JsonResponse
    {

        $params = $request->all();
        $service = new AiService(BigModelService::class);
        $responseData = $service->forward($params);
        return Response::success($responseData);

    }

}
