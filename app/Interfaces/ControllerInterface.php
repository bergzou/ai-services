<?php

namespace App\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * 控制器基础接口
 * 定义业务控制器需实现的通用操作方法（CRUD 相关）
 */
interface ControllerInterface
{
    /**
     * 获取数据列表
     * @param Request $request 请求参数
     * @return JsonResponse 列表数据（JSON 格式）
     */
    public function getList(Request $request): JsonResponse;

    /**
     * 添加新数据
     * @param Request $request 请求参数（包含新增数据）
     * @return JsonResponse 操作结果（JSON 格式）
     */
    public function add(Request $request): JsonResponse;

    /**
     * 更新现有数据
     * @param Request $request 请求参数（包含更新数据及标识）
     * @return JsonResponse 操作结果（JSON 格式）
     */
    public function update(Request $request): JsonResponse;

    /**
     * 获取单条数据详情
     * @param Request $request 请求参数（包含数据标识）
     * @return JsonResponse 详情数据（JSON 格式）
     */
    public function getDetail(Request $request): JsonResponse;
}