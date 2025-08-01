<?php
namespace App\Services\Admin;

use App\Enums\EnumCommon;
use App\Enums\EnumSystemDept;
use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use App\Logging\SqlLogger;
use App\Models\SystemDeptModel;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Validates\SystemDeptValidated;
use Exception;
use Illuminate\Support\Facades\DB;

class SystemDeptService extends BaseService
{

    /**
     * 获取系统部门列表（带筛选和状态枚举转换）
     * @param array $params 筛选参数（支持id（精确）、name（右模糊）、status（精确）等字段筛选）
     * @return array 部门列表数组（每个元素包含原始字段及type_name、status_name枚举描述）
     */
    public function getList(array $params): array
    {
        // 初始化部门模型
        $systemDeptModel = new SystemDeptModel();

        // 定义查询条件映射（字段与查询方式的对应关系）
        $whereMap = [
            'id' => ['field' => 'id', 'search' => 'where'],          // ID精确查询
            'name' => ['field' => 'name', 'search' => 'like', 'operator' => 'like_after'],  // 名称右模糊查询（%name）
            'status' => ['field' => 'status', 'search' => 'where'],   // 状态精确查询
        ];

        // 定义需要查询的字段列表（包含基础信息及时间戳）
        $fields = [
            'id',
            'snowflake_id',
            'name',
            'parent_id',
            'sort',
            'leader_user_id',
            'phone',
            'email',
            'status',
            'tenant_id',
            'created_at',
            'created_by',
        ];

        // 构建查询：设置字段、转换筛选条件、按ID升序排序，获取多条记录（不分页）
        $result = $systemDeptModel->setFields($fields)
            ->convertConditions($params, $whereMap) // 将参数转换为模型可识别的查询条件
            ->setOrderBy(['id' => 'asc'])
            ->getMultipleRecord(); // 获取分页结果（注：实际可能为不分页，需根据模型实现确认）

        if (!empty($result)){
            foreach ($result as &$item) {
                $item['status_name'] = EnumSystemDept::getStatusMap($item['status']); // 状态枚举值转名称（启用/停用）
            }
        }

        return $result;
    }


    /**
     * 添加系统部门（带事务和数据验证）
     * @param array $params 部门参数（包含name、parent_id、sort、status等必填字段）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/插入失败时抛出
     * @throws Exception 其他异常
     */
    public function add(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作）
            DB::beginTransaction();

            // 参数验证（使用SystemDeptValidated验证器，场景为"add"）
            $validated = new SystemDeptValidated($params, 'add');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常（返回验证错误信息）
            }

            // 过滤参数（仅保留模型允许批量赋值的字段，防止非法字段注入）
            $systemDeptMode = new SystemDeptModel();
            $params = CommonService::filterRecursive($params, $systemDeptMode->fillable);

            // 验证部门数据有效性（父部门存在性、名称唯一性等）
            $params = $this->validatedDept($params);

            // 生成雪花ID（分布式唯一ID，用于全局标识部门）
            $snowflake = new Snowflake(Common::getWorkerId());

            // 构造插入数据（包含基础信息、操作人及时间戳）
            $insertData[] = [
                'snowflake_id' => $snowflake->next(),       // 雪花ID（全局唯一标识）
                'name' => $params['name'],                   // 部门名称
                'parent_id' => $params['parent_id'],         // 父部门ID（0表示顶级部门）
                'sort' => $params['sort'],                   // 显示顺序（用于排序）
                'leader_user_id' => $params['leader_user_id'] ?? '', // 负责人用户ID（可选）
                'phone' => $params['phone'] ?? '',           // 联系电话（可选）
                'email' => $params['email'] ?? '',           // 邮箱（可选）
                'status' => $params['status'],               // 部门状态（启用/停用）
                'tenant_id' => $this->userInfo['tenant_id'], // 租户ID（多租户隔离）
                'created_by' => $this->userInfo['user_name'],// 创建人（当前登录用户）
                'created_at' => date('Y-m-d H:i:s'),         // 创建时间
                'updated_by' => $this->userInfo['user_name'],// 更新人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'),         // 更新时间（初始与创建时间一致）
            ];

            // 执行数据插入（批量插入，此处仅单条）
            $SystemDeptModel = new SystemDeptModel();
            $result = $SystemDeptModel->insert($insertData);
            if ($result !== true) {
                throw new BusinessException(__('errors.600000'), '600000'); // 插入失败异常（数据库操作失败）
            }

            // 事务提交（所有操作成功后确认）
            DB::commit();
        } catch (BusinessException $e) {
            DB::rollBack(); // 事务回滚（出现异常时撤销所有操作）
            throw new BusinessException($e); // 重新抛出业务异常（传递错误信息）
        } catch (Exception $e) {
            DB::rollBack(); // 事务回滚（其他异常时撤销操作）
            throw new Exception($e); // 抛出其他异常（如系统错误）
        }
        return [];
    }


    /**
     * 验证部门数据有效性（父部门存在性、名称唯一性）
     * @param array $params 待验证的部门参数（需包含name、parent_id字段）
     * @param mixed $deptInfo 可选参数（更新场景时传入原部门信息）
     * @return array 验证通过的原始参数
     * @throws BusinessException 验证失败时抛出（父部门不存在/名称重复）
     */
    public function validatedDept(array $params , $deptInfo = null): array
    {
        $SystemDeptModel = new SystemDeptModel();

        // 验证父部门有效性（若存在父ID）
        if (!empty($params['parent_id'])){
            // 查询父部门信息（仅获取ID、名称）
            $parentDept = $SystemDeptModel->setFields(['id','name'])->getSingleRecord(['id' => $params['parent_id']]);
            if (empty($parentDept)) {
                throw new BusinessException(__('errors.500019'), 500019); // 父部门不存在异常
            }
            if ($parentDept['name'] == $params['name']) {
                throw new BusinessException(__('errors.500020'), 500020); // 部门名称与父部门名称重复异常
            }
        }

        // 验证同层级部门名称唯一性（新增/更新场景）
        if (!empty($deptInfo)){
            // 更新场景：检查同父部门下是否存在其他同名部门（排除当前部门自身）
            $existingDept = $SystemDeptModel->setFields(['id','name'])
                ->getSingleRecord(['name' => $params['name'], 'parent_id' => $params['parent_id']]);
            if (!empty($existingDept) && $existingDept['id'] != $deptInfo['id']) {
                throw new BusinessException(__('errors.500021'), 500021); // 同父部门下名称已存在异常
            }
        } else {
            // 新增场景：检查同父部门下是否已存在同名部门
            $exists = $SystemDeptModel::query()
                ->where('name', $params['name'])
                ->where('parent_id', $params['parent_id'])
                ->exists();
            if ($exists) {
                throw new BusinessException(__('errors.500021'), 500021); // 同父部门下名称已存在异常
            }
        }

        return $params;
    }


    /**
     * 更新系统部门（带事务和数据验证）
     * @param array $params 更新参数（需包含snowflake_id标识目标部门）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/更新失败时抛出
     * @throws Exception 其他异常
     */
    public function update(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作）
            DB::beginTransaction();

            // 参数验证（使用SystemDeptValidated验证器，场景为"update"）
            $validated = new SystemDeptValidated($params, 'update');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标部门信息（通过snowflake_id查询）
            $SystemDeptModel = new SystemDeptModel();
            $dept = (new SystemDeptModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($dept)) {
                throw new BusinessException(__('errors.500022'), 500022); // 部门不存在异常
            }

            // 验证部门数据有效性（传入原部门信息用于唯一性校验）
            $params = $this->validatedDept($params, $dept);

            // 过滤更新参数（仅保留模型允许批量赋值的字段）
            $systemDeptMode = new SystemDeptModel();
            $updateData = CommonService::filterRecursive($params, $systemDeptMode->fillable);

            // 执行更新操作（根据snowflake_id定位记录）
            $result = $SystemDeptModel::query()
                ->where('snowflake_id', $params['snowflake_id'])
                ->update($updateData);
            if (!$result) {
                throw new BusinessException(__('errors.600000'), '600000'); // 更新失败异常
            }

            // 事务提交（所有操作成功后确认）
            DB::commit();
        } catch (BusinessException $e) {
            DB::rollBack(); // 事务回滚（出现异常时撤销所有操作）
            throw new BusinessException($e); // 重新抛出业务异常
        } catch (Exception $e) {
            DB::rollBack(); // 事务回滚（其他异常时撤销操作）
            throw new Exception($e); // 抛出其他异常
        }
        return [];
    }


    /**
     * 软删除系统部门（标记为已删除）
     * @param array $params 删除参数（需包含snowflake_id标识目标部门）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/删除失败时抛出
     * @throws Exception 其他异常
     */
    public function delete(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作）
            DB::beginTransaction();

            // 参数验证（使用SystemDeptValidated验证器，场景为"delete"）
            $validated = new SystemDeptValidated($params, 'delete');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标部门信息（通过snowflake_id查询）
            $SystemDeptModel = new SystemDeptModel();
            $dept = (new SystemDeptModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($dept)) {
                throw new BusinessException(__('errors.500022'), 500022); // 部门不存在异常
            }

            // 构造软删除标记数据（标记删除时间、操作人）
            $updateData = [
                'is_deleted' => EnumCommon::IS_DELETED_1,     // 标记为已删除（1表示已删除）
                'deleted_at' => date('Y-m-d H:i:s'),          // 删除时间
                'deleted_by' => $this->userInfo['user_name'], // 删除人（当前登录用户）
            ];

            // 执行删除操作（更新删除标记）
            $result = $SystemDeptModel::query()
                ->where('snowflake_id', $params['snowflake_id'])
                ->update($updateData);
            if (!$result) {
                throw new BusinessException(__('errors.600000'), '600000'); // 删除失败异常
            }

            // 事务提交（所有操作成功后确认）
            DB::commit();
        } catch (BusinessException $e) {
            DB::rollBack(); // 事务回滚（出现异常时撤销所有操作）
            throw new BusinessException($e); // 重新抛出业务异常
        } catch (Exception $e) {
            DB::rollBack(); // 事务回滚（其他异常时撤销操作）
            throw new Exception($e); // 抛出其他异常
        }
        return [];
    }


    /**
     * 获取部门详情（单条记录）
     * @param array $params 查询参数（需包含snowflake_id标识目标部门）
     * @return array 部门详情数组（包含指定字段）
     * @throws BusinessException 参数验证失败/部门不存在时抛出
     * @throws Exception 其他异常
     */
    public function getDetail(array $params): array
    {
        try {
            // 开启数据库事务（保证查询一致性）
            DB::beginTransaction();

            // 参数验证（使用SystemDeptValidated验证器，场景为"delete"）
            $validated = new SystemDeptValidated($params, 'delete');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 定义需要查询的字段列表（基础信息及时间戳）
            $fields = [
                'id',
                'snowflake_id',
                'name',
                'parent_id',
                'sort',
                'leader_user_id',
                'phone',
                'email',
                'status',
                'tenant_id',
                'created_at',
                'created_by',
            ];

            // 查询单条部门记录（通过snowflake_id精确查询）
            $result = (new SystemDeptModel())
                ->setFields($fields)
                ->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($result)) {
                throw new BusinessException(__('errors.500022'), 500022); // 部门不存在异常
            }

            // 事务提交（查询完成后确认）
            DB::commit();
        } catch (BusinessException $e) {
            DB::rollBack(); // 事务回滚（出现异常时撤销操作）
            throw new BusinessException($e); // 重新抛出业务异常
        } catch (Exception $e) {
            DB::rollBack(); // 事务回滚（其他异常时撤销操作）
            throw new Exception($e); // 抛出其他异常
        }
        return $result;
    }
}