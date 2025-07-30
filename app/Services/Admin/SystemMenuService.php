<?php
namespace App\Services\Admin;

use App\Enums\EnumSystemMenu;
use App\Enums\EnumSystemTenant;
use App\Enums\EnumSystemTenantPackage;
use App\Exceptions\BusinessException;
use App\Models\SystemMenuModel;
use App\Models\SystemTenantModel;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Validates\SystemMenuValidated;
use Exception;
use Illuminate\Support\Facades\DB;

class SystemMenuService extends BaseService
{

    public function getList(array $params): array
    {

        $systemMenuModel = new SystemMenuModel();


        $whereMap = [
            'id' => ['field' => 'id', 'search' => 'where'],
            'name' => ['field' => 'name', 'search' => 'like', 'operator' => 'like_after'],
            'status' => ['field' => 'status', 'search' => 'where'],
        ];

        $fields = ['id','name','permission','type','sort','parent_id','path','icon','component','component_name','status',
            'visible','keep_alive','always_show'];

        $result = $systemMenuModel->setFields($fields)
            ->convertConditions($params, $whereMap) // 将参数转换为模型可识别的查询条件
            ->setOrderBy(['id' => 'asc'])
            ->getMultipleRecord(); // 获取分页结果

        if (!empty($result)){
            foreach ($result as &$item) {
                $item['type_name'] = EnumSystemMenu::getTypeMap($item['type']);
                $item['status_name'] = EnumSystemMenu::getStatusMap($item['status']);
            }
        }

        return $result;
    }


    /**
     * @throws BusinessException
     * @throws Exception
     */
    public function add(array $params): array
    {
        try {

            DB::beginTransaction();

            $validated = new SystemMenuValidated($params, 'add');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            $params = $this->validatedMenu($params);

            $insertData[] = [
                'name' => $params['name'],
                'permission' => $params['permission'],
                'type' => $params['type'],
                'sort' => $params['sort'],
                'parent_id' => $params['parent_id'],
                'path' => $params['path'],
                'icon' => $params['icon'] ?? '',
                'component' => $params['component'] ?? '',
                'component_name' => $params['component_name'] ?? '',
                'status' => $params['status'] ?? EnumSystemMenu::STATUS_1,
                'visible' => $params['visible'] ?? EnumSystemMenu::VISIBLE_1 ,
                'keep_alive' => $params['keep_alive'] ?? EnumSystemMenu::KEEP_ALIVE_1,
                'always_show' => $params['always_show'] ?? EnumSystemMenu::ALWAYS_SHOW_1,
                'created_by' => $this->userInfo['user_name'], // 创建人（当前登录用户）
                'created_at' => date('Y-m-d H:i:s'), // 创建时间
                'updated_by' => $this->userInfo['user_name'], // 更新人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'), // 更新时间
            ];

            // 执行用户数据插入
            $systemMenuModel = new SystemMenuModel();
            $result = $systemMenuModel->insert($insertData);
            if ($result !== true) {
                throw new BusinessException(__('errors.600000'), '600000'); // 插入失败异常
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
     * @throws BusinessException
     */
    public function validatedMenu(array $params): array
    {

        $systemMenuModel = new SystemMenuModel();
        switch ($params['type']){
            case EnumSystemMenu::TYPE_1:
            case EnumSystemMenu::TYPE_2:
                if (empty($params['path'])) throw new BusinessException(__('validated.300146').__('common.500002'), 500002);
                break;
        }
        if (!empty($params['snowflake_id'])){
            $menu = $systemMenuModel->setFields(['type','snowflake_id'])->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($menu)) throw new BusinessException(__('errors.500014'), 500014);
        }

        if (!empty($params['parent_id'])){
            $menu = $systemMenuModel->setFields(['type','id'])->getSingleRecord(['id' => $params['parent_id']]);
            if (empty($menu)) throw new BusinessException(__('errors.500010'), 500010);
            if (!in_array($menu['type'], [EnumSystemMenu::TYPE_1, EnumSystemMenu::TYPE_2])) {
                throw new BusinessException(__('errors.500011'), 500011);
            }
        }

        $exists = $systemMenuModel::query()->where('parent_id', $params['parent_id'])->where('name', $params['name'])->exists();
        if ($exists) throw new BusinessException(__('errors.500013'), 500013);

        if (!empty($params['component_name'])){
            if (!empty($params['snowflake_id'])){
                $exists = $systemMenuModel::query()->where('component_name', $params['component_name'])
                    ->where('snowflake_id', '<>', $params['snowflake_id'])->exists();
            }else{
                $exists = $systemMenuModel::query()->where('component_name', $params['component_name'])->exists();
            }
            if ($exists) throw new BusinessException(__('errors.500013'), 500013);
        }

        return  $params;
    }


    /**
     * @throws BusinessException
     * @throws Exception
     */
    public function update(array $params): array
    {

        try {

            DB::beginTransaction(); // 开启数据库事务

            $validated = new SystemMenuValidated($params, 'update');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }


            $insertData[] = [
                'name' => $params['name'],
                'permission' => $params['permission'],
                'type' => $params['type'],
                'sort' => $params['sort'],
                'parent_id' => $params['parent_id'],
                'path' => $params['path'],
                'icon' => $params['icon'] ?? '',
                'component' => $params['component'] ?? '',
                'component_name' => $params['component_name'] ?? '',
                'status' => $params['status'] ?? EnumSystemMenu::STATUS_1,
                'visible' => $params['visible'] ?? EnumSystemMenu::VISIBLE_1 ,
                'keep_alive' => $params['keep_alive'] ?? EnumSystemMenu::KEEP_ALIVE_1,
                'always_show' => $params['always_show'] ?? EnumSystemMenu::ALWAYS_SHOW_1,
                'created_by' => $this->userInfo['user_name'], // 创建人（当前登录用户）
                'created_at' => date('Y-m-d H:i:s'), // 创建时间
                'updated_by' => $this->userInfo['user_name'], // 更新人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'), // 更新时间
            ];
            // 执行用户数据插入
            $systemMenuModel = new SystemMenuModel();
            $result = $systemMenuModel->insert($insertData);
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