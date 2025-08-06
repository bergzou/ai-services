<?php
namespace App\Services\Admin;

use App\Enums\EnumCommon;
use App\Enums\EnumSystemTenant;
use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use App\Logging\SqlLogger;
use App\Models\SystemTenantModel;
use App\Models\SystemTenantPackageModel;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Validates\SystemTenantValidated;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * 租户管理服务类
 *
 * 提供租户的增删改查、数据验证、状态枚举转换及套餐关联信息补充等功能
 * 依赖 `SystemTenantModel` 模型操作数据库，`SystemTenantValidated` 验证器进行参数校验
 */
class SystemTenantService extends BaseService
{

    /**
     * 获取租户列表（带筛选和状态/套餐信息补充）
     * @param array $params 筛选参数（支持id、name（右模糊）、contact_name（精确）、contact_mobile（精确）、status（精确）、created_at（时间范围）等字段筛选）
     * @return array 分页结果数组（包含total/size/current/list，list中每个元素含status_name状态描述和package_name套餐名称）
     */
    public function getList(array $params): array
    {
        // 初始化租户模型
        $systemTenantModel = new SystemTenantModel();

        // 定义查询条件映射（输入参数与数据库字段的查询方式对应关系）
        $whereMap = [
            'id' => ['field' => 'id', 'search' => 'where'],          // ID精确查询
            'name' => ['field' => 'name', 'search' => 'like', 'operator' => 'like_after'],  // 名称右模糊查询（%name）
            'contact_name' => ['field' => 'contact_name', 'search' => 'where'],             // 联系人精确查询
            'contact_mobile' => ['field' => 'contact_mobile', 'search' => 'where'],         // 联系手机精确查询
            'status' => ['field' => 'status', 'search' => 'where'],                        // 状态精确查询
            'created_at' => ['field' => 'created_at', 'search' => 'whereBetween']           // 创建时间范围查询（起始-结束）
        ];

        // 定义需要查询的字段列表（基础信息及操作人信息）
        $fields = [
            'id','snowflake_id','name','contact_name','contact_mobile','status','website','package_id','expire_time',
            'account_count','created_at','created_by','updated_at','updated_by',
        ];

        // 构建查询：设置字段、表别名、筛选条件、排序，获取分页结果
        $result = $systemTenantModel->setFields($fields)
            ->setAlias('tenant')
            ->convertConditions($params, $whereMap) // 将输入参数转换为模型可识别的查询条件
            ->setOrderBy(['id' => 'asc'])
            ->getPaginateResults(); // 执行分页查询（返回包含total/list等的分页结构）

        // 补充状态枚举描述和套餐名称（通过关联查询套餐表）
        if (!empty($result['list'])){
            // 提取所有套餐ID，去重后查询套餐名称
            $packageIds = array_unique(array_column($result['list'],'package_id'));
            $tenantPackage = (new SystemTenantPackageModel())->setFields(['id','name'])->getPaginateResults(['id' => $packageIds]);
            $tenantPackageMap = array_column($tenantPackage['list'],'name','id'); // 构建套餐ID到名称的映射

            foreach ($result['list'] as &$item) {
                // 状态枚举值转名称（如"正常"/"停用"）
                $item['status_name'] = EnumSystemTenant::getStatusMap($item['status']);
                // 套餐ID转名称（无匹配时为空）
                $item['package_name'] = $tenantPackageMap[$item['package_id']] ?? '';
            }
        }

        return $result;
    }


    /**
     * 添加租户（带事务和数据验证）
     * @param array $params 租户参数（包含name（名称）、contact_name（联系人）、contact_mobile（联系手机）等必填字段）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/插入失败时抛出（错误码400000/600000）
     * @throws Exception 其他异常（如雪花ID生成失败）
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
            $systemTenantModel = new SystemTenantModel();
            $params = CommonService::filterRecursive($params, $systemTenantModel->fillable);

            // 验证租户名称唯一性（新增场景）
            $params = $this->validatedTenant($params);

            // 生成雪花ID（分布式唯一ID，用于全局标识租户）
            $snowflake = new Snowflake(Common::getWorkerId());

            // 构造插入数据（包含基础信息、操作人及时间戳）
            $insertData[] = [
                'snowflake_id' => $snowflake->next(),       // 雪花ID（全局唯一标识）
                'name' => $params['name'],                   // 租户名称（必填）
                'contact_user_id' => $params['contact_user_id'], // 联系人的用户编号（可选）
                'contact_name' => $params['contact_name'],     // 联系人（必填）
                'contact_mobile' => $params['contact_mobile'], // 联系手机（必填）
                'website' => $params['website'],             // 绑定域名（可选）
                'package_id' => $params['package_id'],         // 租户套餐编号（必填）
                'expire_time' => $params['expire_time'],       // 过期时间（必填）
                'account_count' => $params['account_count'],   // 账号数量（必填）
                'status' => $params['status'],               // 状态（启用/停用，必填）
                'created_at' => date('Y-m-d H:i:s'),         // 创建时间
                'created_by' => $this->userInfo['user_name'],// 创建人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'),         // 更新时间（初始与创建时间一致）
                'updated_by' => $this->userInfo['user_name'],// 更新人（当前登录用户）
            ];

            // 执行数据插入（批量插入，此处仅单条）
            $SystemTenantModel = new SystemTenantModel();
            $result = $SystemTenantModel->insert($insertData);
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
     * 验证租户名称唯一性（新增/更新场景）
     * @param array $params 待验证的租户参数（需包含name字段）
     * @param mixed $tenantInfo 可选参数（更新场景时传入原租户信息）
     * @return array 验证通过的原始参数
     * @throws BusinessException 名称重复时抛出（错误码500023）
     */
    public function validatedTenant(array $params , $tenantInfo = null): array
    {
        // 新增场景：直接校验名称是否已存在
        if (empty($tenantInfo)) {
            $exists = SystemTenantModel::query()->where('name', $params['name'])->exists();
        }
        // 更新场景：排除当前租户自身的校验（避免与自身名称冲突）
        else {
            $exists = SystemTenantModel::query()
                ->where('name', $params['name'])
                ->where('id', '<>', $tenantInfo['id'])
                ->exists();
        }
        if ($exists) {
            throw new BusinessException(__('errors.500023'), 500023); // 名称重复异常
        }

        return $params;
    }


    /**
     * 更新租户信息（带事务和数据验证）
     * @param array $params 更新参数（需包含snowflake_id标识目标租户）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/更新失败时抛出（错误码400000/500024/600000）
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

            // 获取目标租户信息（通过snowflake_id查询）
            $SystemTenantModel = new SystemTenantModel();
            $tenant = (new SystemTenantModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($tenant)) {
                throw new BusinessException(__('errors.500024'), 500024); // 租户不存在异常
            }

            // 验证租户名称唯一性（传入原租户信息用于排除自身）
            $params = $this->validatedTenant($params, $tenant);

            // 过滤更新参数（仅保留模型允许批量赋值的字段）
            $systemTenantModel = new SystemTenantModel();
            $params = CommonService::filterRecursive($params, $systemTenantModel->fillable);


            // 构造插入数据（包含基础信息、操作人及时间戳）
            $updateData[] = [
                'name' => $params['name'],                   // 租户名称（必填）
                'contact_user_id' => $params['contact_user_id'], // 联系人的用户编号（可选）
                'contact_name' => $params['contact_name'],     // 联系人（必填）
                'contact_mobile' => $params['contact_mobile'], // 联系手机（必填）
                'website' => $params['website'],             // 绑定域名（可选）
                'package_id' => $params['package_id'],         // 租户套餐编号（必填）
                'expire_time' => $params['expire_time'],       // 过期时间（必填）
                'account_count' => $params['account_count'],   // 账号数量（必填）
                'status' => $params['status'],               // 状态（启用/停用，必填）
                'updated_at' => date('Y-m-d H:i:s'),         // 更新时间（初始与创建时间一致）
                'updated_by' => $this->userInfo['user_name'],// 更新人（当前登录用户）
            ];

            // 执行更新操作（根据snowflake_id定位记录）
            $result = $SystemTenantModel::query()
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
     * 软删除租户（标记为已删除）
     * @param array $params 删除参数（需包含snowflake_id标识目标租户）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/删除失败时抛出（错误码400000/500024/600000）
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

            // 获取目标租户信息（通过snowflake_id查询）
            $SystemTenantModel = new SystemTenantModel();
            $tenant = (new SystemTenantModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($tenant)) {
                throw new BusinessException(__('errors.500024'), 500024); // 租户不存在异常
            }

            // 构造软删除标记数据（标记删除时间、操作人）
            $updateData = [
                'is_deleted' => EnumCommon::IS_DELETED_1,     // 标记为已删除（1表示已删除）
                'deleted_at' => date('Y-m-d H:i:s'),          // 删除时间
                'deleted_by' => $this->userInfo['user_name'], // 删除人（当前登录用户）
            ];

            // 执行删除操作（更新删除标记）
            $result = $SystemTenantModel::query()
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
     * 获取租户详情（单条记录）
     * @param array $params 查询参数（需包含snowflake_id标识目标租户）
     * @return array 租户详情数组（包含id/snowflake_id/name等字段）
     * @throws BusinessException 参数验证失败/租户不存在时抛出（错误码400000/500024）
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
            $fields = ['id','snowflake_id','name','contact_name','contact_mobile','status','website','package_id','expire_time','account_count','created_at','created_by'];

            // 查询单条租户记录（通过snowflake_id精确查询）
            $result = (new SystemTenantModel())
                ->setFields($fields)
                ->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($result)) {
                throw new BusinessException(__('errors.500024'), 500024); // 租户不存在异常
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