<?php
namespace App\Http\Controllers\Web;


use App\Exceptions\BusinessException;
use App\Helpers\AopProxy;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Libraries\Response;
use App\Service\ReturnedOrderService;
use App\Service\UserInfoService;

class ReturnedOrderController extends BaseController
{
    /**
     * 退件单列表
     * @param Request $request
     * @return JsonResponse
     * @var ReturnedOrderService $service
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $this->setInputKeyValue($requestData,['order','date','','sku']);
        $this->setInputDate($requestData,['created_at','submit_at','receiving_at','completion_at']);

        $service = AopProxy::make(ReturnedOrderService::class);
        $responseData = $service->getList($requestData);
        return Response::success($responseData);
    }

    /**
     * 退件单数量统计
     * @param Request $request
     * @return JsonResponse
     */
    public function listCount(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $this->setInputKeyValue($requestData,['order','date','','sku']);
        $this->setInputDate($requestData,['created_at','submit_at','receiving_at','completion_at']);
        $service = new ReturnedOrderService();
        $responseData = $service->getListCount($requestData);
        return Response::success($responseData);
    }

    /**
     * 退件单详情
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function detail(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $responseData = $service->getDetail($requestData);
        return Response::success($responseData);
    }


    public function operateList(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $responseData = $service->getOperateList($requestData);
        return Response::success($responseData);
    }

    /**
     * 退件单操作日志
     * @param Request $request
     * @return JsonResponse
     */
    public function log(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $responseData = $service->getLog($requestData);
        return Response::success($responseData);
    }


    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function selectData(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $service = new ReturnedOrderService();
        $responseData = $service->selectData($requestData,'middle',$this->userInfo);
        return Response::success($responseData);
    }

}
