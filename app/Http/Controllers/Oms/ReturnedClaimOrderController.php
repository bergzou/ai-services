<?php
namespace App\Http\Controllers\Oms;


use App\Exceptions\BusinessException;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Service\ReturnedClaimOrderService;
use App\Libraries\Response;

class ReturnedClaimOrderController extends BaseController
{
    /**
     * 退件认领单列表
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $this->setInputKeyValue($requestData,['order','date','','sku']);
        $this->setInputDate($requestData,['claim_at','receiving_at']);

        $this->setInputSelleCode($requestData);

        $service = new ReturnedClaimOrderService();
        $responseData = $service->getList($requestData);
        return Response::success($responseData);
    }

    /**
     * 退件认领单数量统计
     * @param Request $request
     * @return JsonResponse
     */
    public function listCount(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $this->setInputKeyValue($requestData,['order','date','','sku']);
        $this->setInputDate($requestData,['claim_at','receiving_at']);

        $this->setInputSelleCode($requestData);

        $service = new ReturnedClaimOrderService();
        $responseData = $service->getListCount($requestData);
        return Response::success($responseData);
    }

    /**
     * 退件认领单详情
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function detail(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $service = new ReturnedClaimOrderService();
        $responseData = $service->getDetail($requestData);
        return Response::success($responseData);
    }


    /**
     * 退件单操作日志
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function log(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $service = new ReturnedClaimOrderService();
        $responseData = $service->getLog($requestData);
        return Response::success($responseData);
    }

    /**
     * 编辑退件认领单
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function claim(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $service = new ReturnedClaimOrderService();
        $responseData = $service->claim($requestData);
        return Response::success($responseData);
    }
}
