<?php
namespace App\Services\Admin;

use App\Enums\EnumCommon;
use App\Enums\EnumSystemTenantPackage;
use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use App\Models\SystemTenantPackageModel;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Validates\SystemTenantValidated;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * 租户套餐管理服务类
 *
 * 提供租户套餐的增删改查、数据验证、状态枚举转换等功能
 * 依赖 `SystemTenantPackageModel` 模型操作数据库，`SystemTenantValidated` 验证器进行参数校验
 */
class SystemTenantPackageService extends BaseService
{

    /**
     * 获取租户套餐列表（带筛选和状态枚举转换）
     * @param array $params 筛选参数（支持id（精确）、name（右模糊）、created_at（时间范围）等字段筛选）
     * @return array 套餐列表数组（每个元素包含原始字段及status_name状态描述）
     */
    public function getList(array $params): array
    {
        // 初始化租户套餐模型
        $SystemTenantPackageModel = new SystemTenantPackageModel();

        // 定义查询条件映射（字段与查询方式的对应关系）
        $whereMap = [
            'id' => ['field' => 'id', 'search' => 'where'],          // ID精确查询
            'name' => ['field' => 'name', 'search' => 'like', 'operator' => 'like_after'],  // 名称右模糊查询（%name）
            'created_at' => ['field' => 'created_at', 'search' => 'whereBetween']           // 创建时间范围查询
        ];

        // 定义需要查询的字段列表（基础信息及操作人信息）
        $fields = ['id','snowflake_id','name','status','remark','created_at','created_by'];

        // 构建查询：设置字段、转换筛选条件、按ID升序排序，获取多条记录（不分页）
        $result = $SystemTenantPackageModel->setFields($fields)
            ->convertConditions($params, $whereMap) // 将参数转换为模型可识别的查询条件
            ->setOrderBy(['id' => 'asc'])
            ->getPaginateResults(); // 获取所有匹配记录（注：原注释"分页结果"为笔误，实际为不分页）

        // 补充状态枚举描述（将状态值转换为中文名称，如"启用"/"停用"）
        if (!empty($result['list'])){
            foreach ($result['list'] as &$item) {
                $item['status_name'] = EnumSystemTenantPackage::getStatusMap($item['status']);
            }
        }

        return $result;
    }


    /**
     * 添加租户套餐（带事务和数据验证）
     * @param array $params 套餐参数（包含name（名称）、status（状态）、menu_ids（关联菜单）等必填字段）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/插入失败时抛出
     * @throws Exception 其他异常
     */
    public function add(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作，失败时自动回滚）
            DB::beginTransaction();

            // 参数验证（使用SystemTenantValidated验证器，场景为"add"）
            $validated = new SystemTenantValidated($params, 'add');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常（返回验证错误信息）
            }

            // 过滤参数（仅保留模型允许批量赋值的字段，防止非法字段注入）
            $systemPackageModel = new SystemTenantPackageModel();
            $params = CommonService::filterRecursive($params, $systemPackageModel->fillable);

            // 验证套餐数据有效性（名称唯一性校验）
            $params = $this->validatedTenantPackage($params);

            // 生成雪花ID（分布式唯一ID，用于全局标识套餐）
            $snowflake = new Snowflake(Common::getWorkerId());

            // 构造插入数据（包含基础信息、操作人及时间戳）
            $insertData[] = [
                'snowflake_id' => $snowflake->next(),       // 雪花ID（全局唯一标识）
                'name' => $params['name'],                   // 套餐名称（必填）
                'status' => $params['status'],               // 状态（启用/停用）
                'remark' => $params['remark'] ?? '',         // 备注（可选）
                'menu_ids' => json_encode($params['menu_ids']), // 关联的菜单编号（JSON格式存储）
                'created_at' => date('Y-m-d H:i:s'),         // 创建时间
                'created_by' => $this->userInfo['user_name'],// 创建人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'),         // 更新时间（初始与创建时间一致）
                'updated_by' => $this->userInfo['user_name'],// 更新人（当前登录用户）
            ];

            // 执行数据插入（批量插入，此处仅单条）
            $SystemTenantPackageModel = new SystemTenantPackageModel();
            $result = $SystemTenantPackageModel->insert($insertData);
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
     * 验证租户套餐数据有效性（名称唯一性校验）
     * @param array $params 待验证的套餐参数（需包含name字段）
     * @param mixed $packageInfo 可选参数（更新场景时传入原套餐信息）
     * @return array 验证通过的原始参数
     * @throws BusinessException 名称重复时抛出（错误码500023）
     */
    public function validatedTenantPackage(array $params , $packageInfo = null): array
    {
        // 新增场景：直接校验名称是否已存在
        if (empty($packageInfo)) {
            $exists = SystemTenantPackageModel::query()->where('name', $params['name'])->exists();
        }
        // 更新场景：排除当前套餐自身的校验（避免与自身名称冲突）
        else {
            $exists = SystemTenantPackageModel::query()
                ->where('name', $params['name'])
                ->where('id', '<>', $packageInfo['id'])
                ->exists();
        }
        if ($exists) {
            throw new BusinessException(__('errors.500023'), 500023); // 名称重复异常
        }

        return $params;
    }


    /**
     * 更新租户套餐（带事务和数据验证）
     * @param array $params 更新参数（需包含snowflake_id标识目标套餐）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/更新失败时抛出
     * @throws Exception 其他异常
     */
    public function update(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作）
            DB::beginTransaction();

            // 参数验证（使用SystemTenantValidated验证器，场景为"update"）
            $validated = new SystemTenantValidated($params, 'update');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标套餐信息（通过snowflake_id查询）
            $SystemTenantPackageModel = new SystemTenantPackageModel();
            $package = (new SystemTenantPackageModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($package)) {
                throw new BusinessException(__('errors.500024'), 500024); // 套餐不存在异常
            }

            // 验证套餐数据有效性（传入原套餐信息用于唯一性校验）
            $params = $this->validatedTenantPackage($params, $package);

            // 过滤更新参数（仅保留模型允许批量赋值的字段）
            $systemPackageModel = new SystemTenantPackageModel();
            $updateData = CommonService::filterRecursive($params, $systemPackageModel->fillable);

            // 执行更新操作（根据snowflake_id定位记录）
            $result = $SystemTenantPackageModel::query()
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
     * 软删除租户套餐（标记为已删除）
     * @param array $params 删除参数（需包含snowflake_id标识目标套餐）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/删除失败时抛出
     * @throws Exception 其他异常
     */
    public function delete(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作）
            DB::beginTransaction();

            // 参数验证（使用SystemTenantValidated验证器，场景为"delete"）
            $validated = new SystemTenantValidated($params, 'delete');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标套餐信息（通过snowflake_id查询）
            $SystemTenantPackageModel = new SystemTenantPackageModel();
            $package = (new SystemTenantPackageModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($package)) {
                throw new BusinessException(__('errors.500024'), 500024); // 套餐不存在异常
            }

            // 构造软删除标记数据（标记删除时间、操作人）
            $updateData = [
                'is_deleted' => EnumCommon::IS_DELETED_1,     // 标记为已删除（1表示已删除）
                'deleted_at' => date('Y-m-d H:i:s'),          // 删除时间
                'deleted_by' => $this->userInfo['user_name'], // 删除人（当前登录用户）
            ];

            // 执行删除操作（更新删除标记）
            $result = $SystemTenantPackageModel::query()
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
     * 获取租户套餐详情（单条记录）
     * @param array $params 查询参数（需包含snowflake_id标识目标套餐）
     * @return array 套餐详情数组（包含id/snowflake_id/name等字段）
     * @throws BusinessException 参数验证失败/套餐不存在时抛出
     * @throws Exception 其他异常
     */
    public function getDetail(array $params): array
    {
        try {
            // 开启数据库事务（保证查询一致性）
            DB::beginTransaction();

            // 参数验证（使用SystemTenantValidated验证器，场景为"detail"）
            $validated = new SystemTenantValidated($params, 'detail');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 定义需要查询的字段列表（基础信息及操作人信息）
            $fields = ['id','snowflake_id','name','status','remark','created_at','created_by'];

            // 查询单条套餐记录（通过snowflake_id精确查询）
            $result = (new SystemTenantPackageModel())
                ->setFields($fields)
                ->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($result)) {
                throw new BusinessException(__('errors.500024'), 500024); // 套餐不存在异常
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