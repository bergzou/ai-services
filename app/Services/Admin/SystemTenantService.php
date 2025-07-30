<?php
namespace App\Services\Admin;

use App\Enums\EnumSystemTenant;
use App\Enums\EnumSystemTenantPackage;
use App\Exceptions\BusinessException;
use App\Models\SystemTenantModel;
use App\Services\BaseService;
use App\Services\CommonService;

class SystemTenantService extends BaseService
{

    public function getList(array $params): array
    {
        // 分页参数：每页数量（默认20）、当前页码（默认1）
        $size = $params['size'] ?? 20;
        $current = $params['current'] ?? 1;

        $systemTenantModel = new SystemTenantModel();


        $whereMap = [
            'id' => ['field' => 'id', 'search' => 'where'],
            'name' => ['field' => 'name', 'search' => 'like', 'operator' => 'like_after'],
            'status' => ['field' => 'status', 'search' => 'where'],
            'create_time' => ['field' => 'create_time', 'search' => 'whereBetween'],
        ];

        $fields = ['id','name','status','remark','create_time'];

        $result = $systemTenantModel->setSize($size)
            ->setCurrent($current)
            ->setFields($fields)
            ->convertConditions($params, $whereMap) // 将参数转换为模型可识别的查询条件
            ->getPaginateResults(); // 获取分页结果

        // 补充状态名称（通过枚举类转换状态码为可读名称）
        if ($result['total'] > 0 && !empty($result['list'])) {
            foreach ($result['list'] as &$item) {
                $item['status_name'] = EnumSystemTenantPackage::getStatusMap($item['status']);
            }
        }

        return $result;
    }

    /**
     * 新增用户
     *
     * @param array $params 用户信息参数（包含name/password/email/mobile等）
     * @return array 空数组（操作成功无具体返回）
     * @throws BusinessException 参数验证失败或插入失败时抛出异常
     * @throws Exception
     */
    public function add(array $params): array
    {
        try {
            DB::beginTransaction(); // 开启数据库事务

            // 参数验证（使用UsersValidated验证器，场景为"add"）
            $usersValidated = new UsersValidated($params, 'add');
            $messages = $usersValidated->isRunFail();
            if (!empty($messages)) {
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 生成雪花ID（用于唯一标识用户）
//            $snowflake = new Snowflake(Common::getWorkerId());
            $insertData[] = [
//                'uuid' => $snowflake->next(), // 雪花算法生成唯一ID
                'name' => $params['name'],
                'password' => $params['password'],
                'mobile' => $params['mobile'],
                'level_id' => 1, // 默认用户等级（可根据业务调整）
                'created_by' => $this->userInfo['user_name'], // 创建人（当前登录用户）
                'created_at' => date('Y-m-d H:i:s'), // 创建时间
                'updated_by' => $this->userInfo['user_name'], // 更新人（初始为创建人）
                'updated_at' => date('Y-m-d H:i:s'), // 更新时间
            ];

            // 执行用户数据插入
            $usersModel = new UsersModel();
            $result = $usersModel->insert($insertData);
            if ($result !== true) {
                throw new BusinessException(__('errors.600000'), '600000'); // 插入失败异常
            }

            DB::commit(); // 事务提交
        } catch (BusinessException $e) {
            DB::rollBack(); // 事务回滚
            throw new BusinessException($e); // 重新抛出业务异常
        } catch (Exception $e) {
            DB::rollBack(); // 事务回滚
            throw new Exception($e); // 抛出其他异常
        }
        return [];
    }












    /**
     * 更新用户信息
     *
     * @param array $params 更新参数（包含uuid/name/email/mobile等）
     * @return array 空数组（操作成功无具体返回）
     * @throws BusinessException 参数验证失败、用户不存在或更新失败时抛出异常
     * @throws Exception
     */
    public function update(array $params): array
    {
        try {
            DB::beginTransaction(); // 开启数据库事务

            // 参数验证（使用UsersValidated验证器，场景为"update"）
            $usersValidated = new UsersValidated($params, 'update');
            $messages = $usersValidated->isRunFail();
            if (!empty($messages)) {
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            $usersModel = new UsersModel();

            // 通过AOP代理获取用户逻辑服务实例，检查更新参数的唯一性（手机号/邮箱/用户名）
            $usersLogicService = AopProxy::make(UsersLogicService::class);
            $userInfo = $usersLogicService->checkUpdateParams($usersModel, $params); // 验证用户存在性及字段唯一性

            // 构造更新数据（仅允许更新部分字段）
            $updateData = [
                'name' => $params['name'],
                'email' => $params['email'],
                'mobile' => $params['mobile'],
                'updated_by' => $this->userInfo['user_name'], // 更新人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'), // 更新时间
            ];

            // 执行用户信息更新（根据uuid定位用户）
            $result = $usersModel::query()->where('uuid', $userInfo['uuid'])->update($updateData);
            if ($result <= 0) {
                throw new BusinessException(__('errors.600001'), '600001'); // 更新失败异常
            }

            DB::commit(); // 事务提交
        } catch (BusinessException $e) {
            DB::rollBack(); // 事务回滚
            throw new BusinessException($e); // 重新抛出业务异常
        } catch (Exception $e) {
            DB::rollBack(); // 事务回滚
            throw new Exception($e); // 抛出其他异常
        }
        return [];
    }

    /**
     * 删除用户
     *
     * @param array $params 删除参数（包含uuid）
     * @return array 空数组（操作成功无具体返回）
     * @throws BusinessException 参数验证失败时抛出异常
     * @throws Exception
     */
    public function delete(array $params): array
    {
        try {
            DB::beginTransaction(); // 开启数据库事务

            // 参数验证（使用UsersValidated验证器，场景为"delete"）
            $usersValidated = new UsersValidated($params, 'delete');
            $messages = $usersValidated->isRunFail();
            if (!empty($messages)) {
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            $usersModel = new UsersModel();
            // 执行用户删除（根据uuid删除）
            $usersModel::query()->where('uuid', $params['uuid'])->delete();

            DB::commit(); // 事务提交
        } catch (BusinessException $e) {
            DB::rollBack(); // 事务回滚
            throw new BusinessException($e); // 重新抛出业务异常
        } catch (Exception $e) {
            DB::rollBack(); // 事务回滚
            throw new Exception($e); // 抛出其他异常
        }
        return [];
    }

    /**
     * 获取用户详情
     *
     * @param array $params 查询参数（包含uuid）
     * @return array 用户详情数组（包含id/name/email等字段）
     * @throws BusinessException 参数验证失败或用户不存在时抛出异常
     */
    public function getDetail(array $params): array
    {
        // 参数验证（使用UsersValidated验证器，场景为"delete"，此处场景可能需确认是否适用）
        $usersValidated = new UsersValidated($params, 'delete');
        $messages = $usersValidated->isRunFail();
        if (!empty($messages)) {
            throw new BusinessException($messages, '400000'); // 参数验证失败异常
        }

        $usersModel = new UsersModel();
        // 指定需要查询的用户字段
        $fields = [
            'id', 'name', 'email', 'mobile', 'status', 'points', 'level_id', 'status', 'created_at', 'updated_at'
        ];
        // 根据uuid查询用户详情
        $result = $usersModel->setFields($fields)->getSingleRecord(['uuid' => $params['uuid']]);
        if (empty($result)) {
            throw new BusinessException(__('errors.600005'), '600005'); // 用户不存在异常
        }

        return $result;
    }

    /**
     * 验证租户有效性（存在性+状态检查）
     *
     * @param mixed $tenantId 待验证的租户ID（通常为整数或字符串）
     * @return array 有效租户的基础信息（包含status/tenant_id/name字段）
     * @throws BusinessException 当租户不存在或状态无效时抛出异常：
     */
    public function checkTenant($tenantId): array
    {
        // 初始化租户模型实例
        $systemTenantModel = new SystemTenantModel();

        // 查询指定租户ID的基础信息（状态、ID、名称）
        $tenant = $systemTenantModel::query()
            ->select(['status', 'id', 'name'])  // 仅查询必要字段
            ->where('id', $tenantId)            // 根据租户ID过滤
            ->first();                                 // 获取单条记录

        // 检查租户是否存在
        if (empty($tenant)) {
            throw new BusinessException(__('errors.500006'), 500006);
        }

        // 将模型对象转换为数组（便于后续状态判断）
        $tenant = CommonService::convertToArray($tenant);

        // 检查租户状态是否为有效状态（根据枚举定义，STATUS_1表示停用状态）
        if ($tenant['status'] != EnumSystemTenant::STATUS_1) {
            throw new BusinessException(__('errors.500007'), 500007);
        }

        return $tenant;
    }
}