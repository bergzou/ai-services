<?php

namespace App\Services\Admin;

use App\Exceptions\BusinessException;
use App\Enums\EnumCommon;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use App\Models\AiProvidersModel;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Validates\AiProvidersValidated;
use Exception;
use Illuminate\Support\Facades\DB;

class AiProvidersService extends BaseService
{


    /**
     * 获取AiProviders列表（带筛选和状态枚举转换）
     * @param array $params 筛选参数（支持字段映射）
     * @return array 列表数据（包含可能的 *_name 枚举名称字段）
     */
    public function getList(array $params): array
    {
        // 初始化模型
        $aiProvidersModel = new AiProvidersModel();

        // 定义查询条件映射（字段与查询方式的对应关系）
        $whereMap = [
            'id'             => ['field' => 'id', 'search' => 'where'],
            'snowflake_id'   => ['field' => 'snowflake_id', 'search' => 'like', 'operator' => 'like_after'],
            'name'           => ['field' => 'name', 'search' => 'like', 'operator' => 'like_after'],
            'code'           => ['field' => 'code', 'search' => 'like', 'operator' => 'like_after'],
            'base_uri'       => ['field' => 'base_uri', 'search' => 'like', 'operator' => 'like_after'],
            'status'         => ['field' => 'status', 'search' => 'where'],
            'created_at'     => ['field' => 'created_at', 'search' => 'whereBetween'],
            'created_by'     => ['field' => 'created_by', 'search' => 'like', 'operator' => 'like_after'],
            'updated_at'     => ['field' => 'updated_at', 'search' => 'whereBetween'],
            'updated_by'     => ['field' => 'updated_by', 'search' => 'like', 'operator' => 'like_after'],
        ];

        // 定义需要查询的字段列表（包含基础信息及时间戳）
        $fields = ['id','snowflake_id','name','code','base_uri','status','created_at','created_by','updated_at','updated_by'];

        // 构建查询：设置字段、转换筛选条件、按ID升序排序，获取多条记录（不分页）
        $result = $aiProvidersModel
            ->setFields($fields)
            ->convertConditions($params, $whereMap)
            ->setOrderBy(['id' => 'asc'])
            ->getPaginateResults();

        // 补充枚举描述（仅为生成时已检测到的枚举方法生成映射行）
        if (!empty($result['list'])){
            foreach ($result['list'] as &$item) {
            }
        }

        return $result;
    }


    /**
     * 添加-AiProviders
     * @param array $params
     * @return array
     * @throws BusinessException
     * @throws Exception
     */
    public function add(array $params): array
    {
        try {
            DB::beginTransaction();

            // 参数验证
            $validated = new AiProvidersValidated($params, 'add');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000');
            }

            // 过滤参数
            $aiProvidersModel = new AiProvidersModel();
            $params = CommonService::filterRecursive($params, $aiProvidersModel->fillable);

            // 自定义业务验证
            $params = $this->validatedAiProviders($params);

            // 构造插入数据（基于表结构自动生成）
            $insertData[] = [
                'snowflake_id' => $params['snowflake_id'],
                'name' => $params['name'],
                'code' => $params['code'],
                'base_uri' => $params['base_uri'],
                'api_key' => $params['api_key'] ?? '',
                'status' => $params['status'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->userInfo['user_name'],
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->userInfo['user_name'],
            ];

            // 执行插入
            $result = $aiProvidersModel->insert($insertData);
            if ($result !== true) {
                throw new BusinessException(__('errors.600000'), '600000');
            }

            DB::commit();
        } catch (BusinessException $e) {
            DB::rollBack();
            throw new BusinessException($e);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e);
        }
        return [];
    }

    /**
     * 业务验证-AiProviders
     * @param array $params
     * @param mixed $info
     * @return array
     */
    public function validatedAiProviders(array $params, $info = null): array
    {
        return $params;
    }

    /**
     * 更新-AiProviders
     * @param array $params
     * @return array
     * @throws BusinessException
     * @throws Exception
     */
    public function update(array $params): array
    {
        try {
            DB::beginTransaction();

            // 参数验证
            $validated = new AiProvidersValidated($params, 'update');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000');
            }

            // 查询目标记录
            $aiProvidersModel = new AiProvidersModel();
            $info = $aiProvidersModel->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($info)) {
                throw new BusinessException(__('errors.500014'), '500014');
            }

            // 自定义业务验证
            $params = $this->validatedAiProviders($params, $info);

            // 过滤允许更新的字段
            $params = CommonService::filterRecursive($params, $aiProvidersModel->fillable);

            // 构造更新数据（基于表结构自动生成）
            $updateData = [
                'name' => $params['name'],
                'code' => $params['code'],
                'base_uri' => $params['base_uri'],
                'api_key' => $params['api_key'] ?? '',
                'status' => $params['status'] ?? '',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->userInfo['user_name'],
            ];

            // 执行更新
            $result = $aiProvidersModel::query()->where('snowflake_id', $params['snowflake_id'])->update($updateData);
            if (!$result) {
                throw new BusinessException(__('errors.600000'), '600000');
            }

            DB::commit();
        } catch (BusinessException $e) {
            DB::rollBack();
            throw new BusinessException($e);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e);
        }
        return [];
    }

    /**
     * 删除 - AiProviders
     * @param array $params
     * @return array
     * @throws BusinessException
     * @throws Exception
     */
    public function delete(array $params): array
    {
        try {
            // 开启事务
            DB::beginTransaction();

            // 参数验证
            $validated = new AiProvidersValidated($params, 'delete');
            $messages = $validated->isRunFail();
            if (!empty($messages)) {
                throw new BusinessException($messages, '400000');
            }

            // 查询目标记录
            $aiProvidersModel = new AiProvidersModel();
            $info = $aiProvidersModel->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($info)) {
                throw new BusinessException(__('errors.500022'), '500022');
            }

            // 构造软删除数据
            $updateData = [
                'is_deleted' => EnumCommon::IS_DELETED_1,
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => $this->userInfo['user_name'],
            ];

            // 执行软删除
            $result = $aiProvidersModel::query()
                ->where('snowflake_id', $params['snowflake_id'])
                ->update($updateData);
            if (!$result) {
                throw new BusinessException(__('errors.600000'), '600000');
            }

            DB::commit();
        } catch (BusinessException $e) {
            DB::rollBack();
            throw new BusinessException($e);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e);
        }
        return [];
    }

    /**
     * 获取AiProviders详情（单条记录）
     * @param array $params 查询参数（需包含snowflake_id标识目标记录）
     * @return array 详情数组
     * @throws BusinessException
     * @throws Exception
     */
    public function getDetail(array $params): array
    {
        try {
            // 保持和其它方法一致，使用事务以保证一致性（只读也可以，但与现有风格统一）
            DB::beginTransaction();

            // 参数验证（通过 snowflake_id 查询，使用 delete 场景或你需要的场景）
            $validated = new AiProvidersValidated($params, 'delete');
            $messages = $validated->isRunFail();
            if (!empty($messages)) {
                throw new BusinessException($messages, '400000');
            }

            // 需要查询的字段（由表结构自动生成，排除了软删除字段）
            $fields = ['id', 'snowflake_id', 'name', 'code', 'base_uri', 'api_key', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'];

            // 查询单条记录
            $result = (new AiProvidersModel())
                ->setFields($fields)
                ->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($result)) {
                throw new BusinessException(__('errors.500022'), '500022');
            }

            DB::commit();
        } catch (BusinessException $e) {
            DB::rollBack();
            throw new BusinessException($e);
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e);
        }
        return $result;
    }
}
