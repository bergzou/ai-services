<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BusinessException;
use App\Helpers\AopProxy;
use App\Http\Controllers\BaseController;
use App\Interfaces\ControllerInterface;
use App\Libraries\Response;
use App\Services\Admin\AiProvidersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiProvidersController extends BaseController implements ControllerInterface
{
    /**
     * 获取数据列表
     * @param Request $request 请求参数
     * @return JsonResponse 列表数据（JSON 格式）
     * @throws BusinessException
     */
    public function getList(Request $request): JsonResponse
    {
        $params = $request->all();

        $services = new AiProvidersService();

        $result = $services->getList($params);

        return Response::success($result);
    }

    /**
     * 添加新数据
     * @param Request $request 请求参数
     * @return JsonResponse 添加结果（JSON 格式）
     * @throws BusinessException
     */
    public function add(Request $request): JsonResponse
    {
        $params = $request->all();

        $services = AopProxy::make(AiProvidersService::class);

        $result = $services->add($params);

        return Response::success($result);
    }

    /**
     * 更新现有数据
     * @param Request $request 请求参数
     * @return JsonResponse 更新结果（JSON 格式）
     * @throws BusinessException
     */
    public function update(Request $request): JsonResponse
    {
        $params = $request->all();

        $services = AopProxy::make(AiProvidersService::class);

        $result = $services->update($params);

        return Response::success($result);
    }

    /**
     * 删除数据
     * @param Request $request 请求参数
     * @return JsonResponse 删除结果（JSON 格式）
     * @throws BusinessException
     */
    public function delete(Request $request): JsonResponse
    {
        $params = $request->all();

        $services = AopProxy::make(AiProvidersService::class);

        $result = $services->delete($params);

        return Response::success($result);
    }

    /**
     * 获取单条数据详情
     * @param Request $request 请求参数
     * @return JsonResponse 详情数据（JSON 格式）
     * @throws BusinessException
     */
    public function getDetail(Request $request): JsonResponse
    {
        $params = $request->all();

        $services = new AiProvidersService();

        $result = $services->getDetail($params);

        return Response::success($result);
    }
}
