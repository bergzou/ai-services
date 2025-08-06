<?php
namespace App\Services\Admin;

use App\Enums\EnumCommon;
use App\Enums\EnumSystemRole;
use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use App\Logging\SqlLogger;
use App\Models\SystemRoleModel;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Validates\SystemRoleValidated;
use Exception;
use Illuminate\Support\Facades\DB;

class SystemRoleService extends BaseService
{

    /**
     * 获取系统菜单列表（带筛选和枚举值转换）
     * @param array $params 筛选参数（支持id、name（模糊）、status等字段筛选）
     * @return array 菜单列表数组（每个元素包含原始字段及type_name、status_name枚举描述）
     */
    public function getList(array $params): array
    {
        // 初始化菜单模型
        $SystemRoleModel = new SystemRoleModel();

        // 定义查询条件映射（字段与查询方式的对应关系）
        $whereMap = [
            'id' => ['field' => 'id', 'search' => 'where'],
            'name' => ['field' => 'name', 'search' => 'like', 'operator' => 'like_after'],
            'code' => ['field' => 'code', 'search' => 'like', 'operator' => 'like_after'],
            'status' => ['field' => 'status', 'search' => 'where'],
            'created_at' => ['field' => 'created_at', 'search' => 'whereBetween'],
        ];

        // 定义需要查询的字段列表
        $fields = [
            'id', 'snowflake_id', 'name', 'code', 'sort', 'status', 'type', 'remark', 'created_at', 'created_by', 'updated_at', 'updated_by',
        ];

        // 构建查询：设置字段、转换筛选条件、排序，获取多条记录（不分页）
        $result = $SystemRoleModel->setFields($fields)
            ->convertConditions($params, $whereMap) // 将参数转换为模型可识别的查询条件
            ->setOrderBy(['id' => 'asc'])
            ->getMultipleRecord(); // 获取分页结果

        // 补充枚举描述（类型名称、状态名称）
        if (!empty($result)){
            foreach ($result as &$item) {
                $item['status_name'] = EnumSystemRole::getStatusMap($item['status']); // 状态枚举值转名称（启用/停用）
            }
        }

        return $result;
    }


    /**
     * 添加系统菜单（带事务和数据验证）
     * @param array $params 菜单参数（包含name、permission、type等必填字段）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/插入失败时抛出
     * @throws Exception 其他异常
     */
    public function add(array $params): array
    {
        try {
            // 开启数据库事务
            DB::beginTransaction();

            // 参数验证（使用SystemRoleValidated验证器，场景为"add"）
            $validated = new SystemRoleValidated($params, 'add');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 过滤参数（仅保留模型允许批量赋值的字段）
            $systemMenuMode = new SystemRoleModel();
            $params = CommonService::filterRecursive($params, $systemMenuMode->fillable);

            // 验证菜单数据有效性（名称唯一性、父菜单合法性等）
            $params = $this->validatedRole($params);

            // 生成雪花ID（分布式唯一ID）
            $snowflake = new Snowflake(Common::getWorkerId());

            // 构造插入数据（包含基础信息及操作人、时间戳）
            $insertData[] = [
                'snowflake_id' => $snowflake->next(),
                'name' => $params['name'],
                'code' => $params['code'],
                'sort' => $params['sort'],
                'status' => $params['status'],
                'remark' => $params['remark'] ?? '',
                'created_by' => $this->userInfo['user_name'], // 创建人（当前登录用户）
                'created_at' => date('Y-m-d H:i:s'), // 创建时间
                'updated_by' => $this->userInfo['user_name'], // 更新人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'), // 更新时间
                'tenant_id' => $this->userInfo['tenant_id'], // 租户编号
            ];

            // 执行数据插入
            $SystemRoleModel = new SystemRoleModel();
            $result = $SystemRoleModel->insert($insertData);
            if ($result !== true) {
                throw new BusinessException(__('errors.600000'), '600000'); // 插入失败异常
            }

            // 事务提交
            DB::commit();
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
     * 验证菜单数据有效性（类型、父菜单、唯一性等）
     * @param array $params 待验证的菜单参数
     * @param mixed $menuInfo 可选参数（更新场景时传入原菜单信息）
     * @return array 验证通过的原始参数
     * @throws BusinessException 验证失败时抛出（如路径缺失、父菜单无效、名称重复等）
     */
    public function validatedRole(array $params , $roleInfo = null): array
    {

        $systemRoleModel = new SystemRoleModel();

        if (empty($roleInfo)){
            $exists = $systemRoleModel::query()->where('name',$params['name'])->where('tenant_id',$this->userInfo['tenant_id'])->exists();
            if (!empty($exists)){
                throw new BusinessException(__('errors.500037'), 500037); // 角色名称已存在
            }
            $exists = $systemRoleModel::query()->where('code',$params['code'])->where('tenant_id',$this->userInfo['tenant_id'])->exists();
        }else{
            $exists = $systemRoleModel::query()->where('name',$params['name'])->where('tenant_id',$this->userInfo['tenant_id'])->where('snowflake_id','<>',$roleInfo['snowflake_id'])->exists();
            if (!empty($exists)){
                throw new BusinessException(__('errors.500037'), 500037); // 角色名称已存在
            }
            $exists = $systemRoleModel::query()->where('code',$params['code'])->where('tenant_id',$this->userInfo['tenant_id'])->where('snowflake_id','<>',$roleInfo['snowflake_id'])->exists();
        }

        if (!empty($exists)){
            throw new BusinessException(__('errors.500038'), 500038); // 角色编码已存在
        }

        return  $params;
    }


    /**
     * 更新系统菜单（带事务和数据验证）
     * @param array $params 更新参数（需包含snowflake_id标识目标菜单）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/更新失败时抛出
     * @throws Exception 其他异常
     */
    public function update(array $params): array
    {
        try {
            // 开启数据库事务
            DB::beginTransaction();

            // 参数验证（使用SystemRoleValidated验证器，场景为"update"）
            $validated = new SystemRoleValidated($params, 'update');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标菜单信息（通过snowflake_id查询）
            $SystemRoleModel =  new SystemRoleModel();
            $role = (new SystemRoleModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($role)) throw new BusinessException(__('errors.500039'), 500039);

            // 验证菜单数据有效性（传入原菜单信息用于唯一性校验）
            $params = $this->validatedRole($params,$role);

            // 过滤更新参数（仅保留模型允许批量赋值的字段）
            $systemMenuMode = new SystemRoleModel();
            $params = CommonService::filterRecursive($params, $systemMenuMode->fillable);

            // 构造插入数据（包含基础信息、操作人及时间戳）
            $updateData[] = [
                'name' => $params['name'],
                'code' => $params['code'],
                'sort' => $params['sort'],
                'status' => $params['status'],
                'remark' => $params['remark'] ?? '',
                'updated_by' => $this->userInfo['user_name'], // 更新人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'), // 更新时间
            ];

            // 执行更新操作（根据snowflake_id定位记录）
            $result = $SystemRoleModel::query()->where('snowflake_id', $params['snowflake_id'])->update($updateData);
            if (!$result) {
                throw new BusinessException(__('errors.600000'), '600000'); // 更新失败异常
            }

            // 事务提交
            DB::commit();
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
     * 软删除系统菜单（标记为已删除）
     * @param array $params 删除参数（需包含snowflake_id标识目标菜单）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/删除失败时抛出
     * @throws Exception 其他异常
     */
    public function delete(array $params): array
    {
        try {
            // 开启数据库事务
            DB::beginTransaction();

            // 参数验证（使用SystemRoleValidated验证器，场景为"delete"）
            $validated = new SystemRoleValidated($params, 'delete');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标菜单信息（通过snowflake_id查询）
            $SystemRoleModel =  new SystemRoleModel();
            $role = (new SystemRoleModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($role)) throw new BusinessException(__('errors.500039'), 500039); // 菜单不存在

            // 构造软删除标记数据（标记删除时间、操作人）
            $updateData = [
                'is_deleted' => EnumCommon::IS_DELETED_1, // 标记为已删除（根据EnumCommon定义）
                'deleted_at' => date('Y-m-d H:i:s'), // 删除时间
                'deleted_by' => $this->userInfo['user_name'], // 删除人（当前登录用户）
            ];

            // 执行删除操作（更新删除标记）
            $result = $SystemRoleModel::query()->where('snowflake_id', $params['snowflake_id'])->update($updateData);
            if (!$result) {
                throw new BusinessException(__('errors.600000'), '600000'); // 删除失败异常
            }

            // 事务提交
            DB::commit();
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
     * 获取菜单详情（单条记录）
     * @param array $params 查询参数（需包含snowflake_id标识目标菜单）
     * @return array 菜单详情数组（包含指定字段）
     * @throws BusinessException 参数验证失败/菜单不存在时抛出
     * @throws Exception 其他异常
     */
    public function getDetail(array $params): array
    {
        try {
            // 开启数据库事务
            DB::beginTransaction();

            // 参数验证（使用SystemRoleValidated验证器，场景为"delete"）
            $validated = new SystemRoleValidated($params, 'delete');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 定义需要查询的字段列表
            $fields = [
                'id', 'snowflake_id', 'name', 'code', 'sort', 'status', 'type', 'remark', 'created_at', 'created_by', 'updated_at', 'updated_by',
            ];

            // 查询单条菜单记录（通过snowflake_id）
            $result = (new SystemRoleModel())->setFields($fields)->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($result)) throw new BusinessException(__('errors.500039'), 500039); // 菜单不存在（注：原代码中$menu未定义，此处修正为检查$result）

            // 事务提交
            DB::commit();
        } catch (BusinessException $e) {
            DB::rollBack(); // 事务回滚
            throw new BusinessException($e); // 重新抛出业务异常
        } catch (Exception $e) {
            DB::rollBack(); // 事务回滚
            throw new Exception($e); // 抛出其他异常
        }
        return $result;
    }
}