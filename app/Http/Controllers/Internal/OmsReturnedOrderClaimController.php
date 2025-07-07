<?php
namespace App\Http\Controllers\Internal;


use App\Exceptions\BusinessException;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Libraries\Response;
use App\Service\ReturnedOrderService;

class OmsReturnedOrderClaimController extends BaseController
{


    /**
     * 同步退件单
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function sync(Request $request): JsonResponse
    {

        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $responseData = $service->sync($requestData);
        return Response::success($responseData);

    }

}
