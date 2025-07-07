<?php
namespace App\Http\Controllers\Internal;


use App\Enums\EnumReturnedOrder;
use App\Exceptions\BusinessException;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Libraries\Response;
use App\Service\ReturnedOrderService;

class OmsReturnedOrderController extends BaseController
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

    

    public function getOutboundOrder(Request $request){
        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $responseData = $service->getOutboundOrder($requestData);
        return Response::success($responseData);
    }



    public function generateOrderCode(Request $request){
        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $responseData = $service->generateOrderCode($requestData);
        return Response::success($responseData);
    }


    /**
     * 导出退件单
     * @param Request $request
     * @return JsonResponse
     */
    public function getExportOrder(Request $request): JsonResponse
    {

        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $responseData = $service->getExportOrder($requestData);
        
        return Response::success($responseData);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function save(Request $request): JsonResponse
    {

        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $userInfo = ['tenant_code' => $requestData['tenant_code'] ?? '', 'user_name' => "System", 'user_id' => "System"];
        $requestData['returned_status'] = EnumReturnedOrder::RETURNED_STATUS_20;

        $responseData = $service->save($requestData,$userInfo);


        return Response::success($responseData);
    }


    /**
     * @throws BusinessException
     */
    public function detail(Request $request): JsonResponse
    {

        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $responseData = $service->getDetail($requestData);

        return Response::success($responseData);
    }

    /**
     * @throws BusinessException
     */
    public function cancel(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $userInfo = ['tenant_code' => $requestData['tenant_code'] ?? '', 'user_name' => "System", 'user_id' => "System"];
        $responseData = $service->cancel($requestData,$userInfo);
        return Response::success($responseData);
    }

}
