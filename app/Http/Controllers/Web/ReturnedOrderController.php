<?php
namespace App\Http\Controllers\Web;



use Illuminate\Http\Request;

class ReturnedOrderController extends WebBaseController
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
        $returnedOrderValidation = new ReturnedClaimOrderValidated($requestData, 'add');
        $messages = $returnedOrderValidation->isRunFail();
        var_dump($messages);die;


        $requestData = $request->all();
        $size = $requestData['size'] ?? 10;
        $current = $requestData['current'] ?? 1; //页数
        $this->setInputKeyValue($requestData,['order','date','','sku']);
        $this->setInputDate($requestData,['created_at','submit_at','receiving_at','completion_at']);

        $whereMap = [
            'id_than' => ['field' => 'id', 'search' => 'where', 'operator' => '<'],
            'created_at' => ['field' => 'created_at', 'search' => 'whereBetween'],
            'returned_type' => ['field' => 'returned_type','search' => 'where','operator' => '<'],
            'updator_name' => ['field' => 'updator_name', 'search' => 'like','operator' => 'like_before'],
        ];

        $fields = [
            "t.returned_order_code",
            "t.created_at",
            "r.customer_sku",
        ];
        $returnOrderModel = new ReturnedOrderModel();

        $joins = [
            [
                'table' => 'returned_claim_detail as r',
                'type' => 'inner',
                'conditions' => [
                    ['first' => 't.claim_order_code', 'operator' => '=', 'second' => 'r.claim_order_code']
                ]
            ]
        ];

        $responseData =  $returnOrderModel->setSize($size)->setCurrent($current)
            ->setAlias('t')->setFields($fields)->setOrderBy(['t.id' => 'desc'])
            ->setGroup('t.returned_order_codea')->convertConditions($requestData,$whereMap)
            ->getPaginateResults($joins,['r.id'=>[1]]);

        return Response::success($responseData);



        $requestData = $request->all();

        $returnedOrderValidation = new ReturnedOrderValidation($requestData, 'add');
        $messages = $returnedOrderValidation->isRunFail();
        var_dump($messages);die;

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
