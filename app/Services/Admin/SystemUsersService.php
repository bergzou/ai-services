<?php
namespace App\Services\Admin;

use App\Enums\EnumCommon;
use App\Enums\EnumSystemDept;
use App\Enums\EnumSystemPost;
use App\Enums\EnumSystemUsers;
use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use App\Logging\SqlLogger;
use App\Models\SystemDeptModel;
use App\Models\SystemPostModel;
use App\Models\SystemTenantModel;
use App\Models\SystemUserPostModel;
use App\Models\SystemUsersModel;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Validates\SystemUsersValidated;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * 系统用户管理服务类
 * 提供用户的增删改查、数据验证、状态枚举转换及部门/岗位关联管理功能
 */
class SystemUsersService extends BaseService
{

    /**
     * 获取系统用户列表（带筛选和状态/部门名称补充）
     * @param array $params 筛选参数（支持id、username（精确）、nickname（右模糊）、mobile（右模糊）、email（右模糊）、dept_id（精确）、status（精确）、created_at（时间范围）等字段筛选）
     * @return array 分页结果数组（包含total/size/current/list，list中每个元素含status_name状态描述和dept_name部门名称）
     */
    public function getList(array $params): array
    {
        // 初始化用户模型
        $systemUsersModel = new SystemUsersModel();

        // 定义查询条件映射（输入参数与数据库字段的查询方式对应关系）
        $whereMap = [
            'id' => ['field' => 'id', 'search' => 'where'],          // ID精确查询
            'username' => ['field' => 'username', 'search' => 'where'],  // 用户名精确查询
            'nickname' => ['field' => 'nickname', 'search' => 'like', 'operator' => 'like_after'],  // 昵称右模糊查询（%nickname）
            'mobile' => ['field' => 'mobile', 'search' => 'like', 'operator' => 'like_after'],  // 手机号右模糊查询（%mobile）
            'email' => ['field' => 'email', 'search' => 'like', 'operator' => 'like_after'],  // 邮箱右模糊查询（%email）
            'dept_id' => ['field' => 'dept_id', 'search' => 'where'],  // 部门ID精确查询
            'status' => ['field' => 'status', 'search' => 'where'],  // 状态精确查询
            'created_at' => ['field' => 'created_at', 'search' => 'whereBetween'],  // 创建时间范围查询（起始-结束）
        ];

        // 定义需要查询的字段列表（用户基础信息及操作人信息）
        $fields = ['id','snowflake_id','username','nickname','email','mobile','sex','avatar','status','dept_id','login_ip','login_date','created_at','created_by','updated_at','updated_by','tenant_id','level'];

        // 构建查询：设置字段、转换筛选条件、按ID升序排序，获取分页结果
        $result = $systemUsersModel->setFields($fields)
            ->convertConditions($params, $whereMap) // 将参数转换为模型可识别的查询条件
            ->setOrderBy(['id' => 'asc'])
            ->getPaginateResults(); // 执行分页查询（返回包含total/list等的分页结构）


        // 补充状态枚举描述和部门名称（通过关联查询部门表）
        if (!empty($result['list'])){
            // 提取所有部门ID，去重后查询部门名称
            $deptIds = array_column($result['list'], 'dept_id');
            $filteredDeptIds = array_filter($deptIds); // 过滤空值
            $deptMap = [];
            if (!empty($filteredDeptIds)){
                $deptMap = SystemDeptModel::query()
                    ->select(['id','name'])
                    ->whereIn('id', $filteredDeptIds)
                    ->get()
                    ->toArray();
                $deptMap = array_column($deptMap, 'name', 'id'); // 构建部门ID到名称的映射
            }

            foreach ($result['list'] as &$item) {
                // 状态枚举值转名称（如"启用"/"停用"）
                $item['status_name'] = EnumSystemUsers::getStatusMap($item['status']);
                // 部门ID转名称（无匹配时为空）
                $item['dept_name'] = $deptMap[$item['dept_id']] ?? '';
            }
        }

        return $result;
    }


    /**
     * 添加系统用户（带事务和数据验证）
     * @param array $params 用户参数（包含username（用户名）、password（密码）、nickname（昵称）、dept_id（部门ID）、post_ids（岗位ID数组）等必填字段）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/插入失败时抛出（错误码400000/600000）
     * @throws Exception 其他异常（如雪花ID生成失败）
     */
    public function add(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作，失败时自动回滚）
            DB::beginTransaction();

            // 参数验证（使用SystemUsersValidated验证器，场景为"add"）
            $validated = new SystemUsersValidated($params, 'add');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常（返回验证错误信息）
            }

            // 过滤参数（仅保留模型允许批量赋值的字段，防止非法字段注入）
            $systemUsersModel = new SystemUsersModel();
            $params = CommonService::filterRecursive($params, $systemUsersModel->fillable);

            // 验证用户数据有效性（昵称/用户名唯一性、租户账号数量限制等）
            $params = $this->validatedUsers($params);

            // 生成雪花ID（分布式唯一ID，用于全局标识用户）
            $snowflake = new Snowflake(Common::getWorkerId());

            // 构造插入数据（包含基础信息、操作人及时间戳）
            $insertData = [
                'snowflake_id' => $snowflake->next(),       // 雪花ID（全局唯一标识）
                'username' => $params['username'],          // 用户名（唯一）
                'password' => password_hash($params['password'], PASSWORD_DEFAULT), // 密码哈希存储
                'nickname' => $params['nickname'],          // 昵称
                'remark' => $params['remark'] ?? '',         // 备注（可选）
                'dept_id' => json_encode($params['dept_id']), // 所属部门ID（JSON格式存储）
                'post_ids' => json_encode($params['post_ids']), // 关联岗位ID数组（JSON格式存储）
                'email' => $params['email'] ?? '',           // 邮箱（可选）
                'mobile' => $params['mobile'] ?? '',         // 手机号（可选）
                'status' => $params['status'],               // 用户状态（启用/停用）
                'created_at' => date('Y-m-d H:i:s'),         // 创建时间
                'created_by' => $this->userInfo['user_name'],// 创建人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'),         // 更新时间（初始与创建时间一致）
                'updated_by' => $this->userInfo['user_name'],// 更新人（当前登录用户）
                'tenant_id' => $this->userInfo['tenant_id'], // 租户ID（多租户隔离）
            ];

            // 执行用户数据插入（批量插入，此处仅单条）
            $SystemUsersModel = new SystemUsersModel();
            $result = $SystemUsersModel->insert($insertData);
            if ($result !== true) {
                throw new BusinessException(__('errors.600000'), '600000'); // 插入失败异常（数据库操作失败）
            }


            // 关联用户与岗位（插入用户-岗位关联表）
            if (!empty($params['post_ids'])){
                $usersPostInsertData = [];
                foreach ($params['post_ids'] as $item){
                    $usersPostInsertData[] = [
                        'snowflake_id' => $snowflake->next(),       // 雪花ID（关联记录唯一标识）
                        'user_id' => $insertData['snowflake_id'],   // 用户雪花ID
                        'post_id' => $item,                          // 岗位ID
                        'created_at' => date('Y-m-d H:i:s'),         // 创建时间
                        'created_by' => $this->userInfo['user_name'],// 创建人
                        'updated_at' => date('Y-m-d H:i:s'),         // 更新时间
                        'updated_by' => $this->userInfo['user_name'],// 更新人
                        'tenant_id' => $this->userInfo['tenant_id'], // 租户ID
                    ];
                }
                $systemUserPostModel = new SystemUserPostModel();
                $result = $systemUserPostModel->insert($usersPostInsertData);
                if ($result !== true) {
                    throw new BusinessException(__('errors.600000'), '600000'); // 关联插入失败异常
                }
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
     * 验证用户数据有效性（唯一性及关联数据校验）
     * @param array $params 待验证的用户参数（需包含nickname、username、mobile、email、dept_id、post_ids等字段）
     * @param mixed $userInfo 可选参数（更新场景时传入原用户信息）
     * @return array 验证通过的原始参数
     * @throws BusinessException 验证失败时抛出（昵称/用户名/手机号/邮箱重复、部门/岗位无效等）
     */
    public function validatedUsers(array $params , $userInfo = null): array
    {
        // 新增场景：校验昵称/用户名唯一性及租户账号数量限制
        if (empty($userInfo)) {
            // 昵称唯一性校验（同租户）
            $exists = SystemUsersModel::query()
                ->where('nickname', $params['nickname'])
                ->where('tenant_id', $this->userInfo['tenant_id'])
                ->exists();
            if ($exists){
                throw new BusinessException(__('errors.500025'), 500025); // 昵称重复异常
            }

            // 用户名唯一性校验（同租户）
            $exists = SystemUsersModel::query()
                ->where('username', $params['username'])
                ->where('tenant_id', $this->userInfo['tenant_id'])
                ->exists();
            if ($exists){
                throw new BusinessException(__('errors.500026'), 500026); // 用户名重复异常
            }

            // 租户账号数量限制校验（当前用户数不能超过租户套餐限制）
            $tenantAccountCount = SystemTenantModel::query()
                ->where('id', $this->userInfo['tenant_id'])
                ->value('account_count');
            $userCount = SystemUsersModel::query()
                ->where('tenant_id', $this->userInfo['tenant_id'])
                ->count();
            if ($userCount >= $tenantAccountCount){
                throw new BusinessException(__('errors.500028', ['number' => $tenantAccountCount]), 500028); // 账号数量超限异常
            }
        }
        // 更新场景：校验昵称/用户名是否与其他用户重复（排除当前用户）
        else {
            if ($userInfo['username'] != $params['username']){
                $exists = SystemUsersModel::query()
                    ->where('username', $params['username'])
                    ->where('tenant_id', $this->userInfo['tenant_id'])
                    ->where('id', '<>', $userInfo['id'])
                    ->exists();
                if ($exists){
                    throw new BusinessException(__('errors.500026'), 500026); // 用户名重复异常
                }
            }
            if ($userInfo['nickname'] != $params['nickname']){
                $exists = SystemUsersModel::query()
                    ->where('nickname', $params['nickname'])
                    ->where('tenant_id', $this->userInfo['tenant_id'])
                    ->where('id', '<>', $userInfo['id'])
                    ->exists();
                if ($exists){
                    throw new BusinessException(__('errors.500025'), 500025); // 昵称重复异常
                }
            }
        }

        // 手机号有效性校验（格式+唯一性）
        if (!empty($params['mobile'])){
            // 格式校验（中国大陆手机号）
            if (!preg_match('/^1[3456789]\d{9}$/', $params['mobile'])){
                throw new BusinessException(__('errors.500030'), 500030); // 手机号格式错误异常
            }
            // 唯一性校验（同租户）
            $exists = SystemUsersModel::query()
                ->where('mobile', $params['mobile'])
                ->where('tenant_id', $this->userInfo['tenant_id'])
                ->when($userInfo, fn($query) => $query->where('id', '<>', $userInfo['id']))
                ->exists();
            if ($exists){
                throw new BusinessException(__('errors.500029'), 500029); // 手机号重复异常
            }
        }

        // 邮箱有效性校验（格式+唯一性）
        if (!empty($params['email'])){
            // 格式校验（标准邮箱格式）
            if (!preg_match('/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/', $params['email'])){
                throw new BusinessException(__('errors.500032'), 500032); // 邮箱格式错误异常
            }
            // 唯一性校验（同租户）
            $exists = SystemUsersModel::query()
                ->where('email', $params['email'])
                ->where('tenant_id', $this->userInfo['tenant_id'])
                ->when($userInfo, fn($query) => $query->where('id', '<>', $userInfo['id']))
                ->exists();
            if ($exists){
                throw new BusinessException(__('errors.500031'), 500031); // 邮箱重复异常
            }
        }

        // 部门有效性校验（部门存在且状态正常）
        if (!empty($params['dept_id'])){
            $systemDept = SystemDeptModel::query()
                ->select(['id','status'])
                ->where('id', $params['dept_id'])
                ->where('tenant_id', $this->userInfo['tenant_id'])
                ->first();
            if (empty($systemDept)){
                throw new BusinessException(__('errors.500022'), 500022); // 部门不存在异常
            }
            $systemDept = CommonService::convertToArray($systemDept);
            if ($systemDept['status'] != EnumSystemDept::STATUS_1){
                throw new BusinessException(__('errors.500033'), 500033); // 部门已停用异常
            }
        }

        // 岗位有效性校验（岗位存在且状态正常）
        if (!empty($params['post_ids'])){
            $systemPost = SystemPostModel::query()
                ->select(['id','status'])
                ->whereIn('id', $params['post_ids'])
                ->where('tenant_id', $this->userInfo['tenant_id'])
                ->get();
            if (empty($systemPost)){
                throw new BusinessException(__('errors.500035'), 500035); // 岗位不存在异常
            }
            $systemPost = CommonService::convertToArray($systemPost);
            foreach ($systemPost as $item){
                if ($item['status'] != EnumSystemPost::STATUS_1){
                    throw new BusinessException(__('errors.500034'), 500034);
                }
            }
        }

        return $params;
    }
    /**
     * 更新系统用户信息（带事务和数据验证）
     *
     * 包含主表字段更新及用户-岗位关联关系维护，支持更新用户名、昵称、部门、岗位等核心信息
     * @param array $params 更新参数（需包含`snowflake_id`标识目标用户，其他可选字段：username/nickname/remark/dept_id/post_ids/email/mobile/status）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/用户不存在/更新失败时抛出（错误码400000/500035/600000）
     * @throws Exception 其他系统异常（如雪花ID生成失败）
     */
    public function update(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作，失败时自动回滚）
            DB::beginTransaction();

            // 参数验证（使用SystemUsersValidated验证器，场景为"update"）
            $validated = new SystemUsersValidated($params, 'update');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常（返回验证错误信息）
            }

            // 获取目标用户信息（通过snowflake_id查询，确保用户存在且属于当前租户）
            $userInfo = SystemUsersModel::query()
                ->select(['snowflake_id','username','nickname','email','mobile'])
                ->where('snowflake_id', $params['snowflake_id'])
                ->where('tenant_id', $this->userInfo['tenant_id'])
                ->first();
            if (empty($userInfo)){
                throw new BusinessException(__('errors.500035'), 500035); // 用户不存在异常
            }
            $userInfo = CommonService::convertToArray($userInfo);

            // 过滤参数（仅保留模型允许批量赋值的字段，防止非法字段注入）
            $systemUsersModel = new SystemUsersModel();
            $params = CommonService::filterRecursive($params, $systemUsersModel->fillable);

            // 验证用户数据有效性（用户名/昵称/手机号/邮箱唯一性校验，部门/岗位有效性校验）
            $params = $this->validatedUsers($params, $userInfo);

            // 构造用户主表更新数据（包含可更新字段及操作人信息）
            $updateData = [
                'username' => $params['username'],          // 用户名（需唯一）
                'nickname' => $params['nickname'],          // 昵称（需唯一）
                'remark' => $params['remark'] ?? '',         // 备注（可选）
                'dept_id' => json_encode($params['dept_id']), // 所属部门ID（JSON格式存储）
                'post_ids' => json_encode($params['post_ids']), // 关联岗位ID数组（JSON格式存储）
                'email' => $params['email'] ?? '',           // 邮箱（可选，需唯一）
                'mobile' => $params['mobile'] ?? '',         // 手机号（可选，需唯一）
                'status' => $params['status'],               // 用户状态（启用/停用）
                'updated_at' => date('Y-m-d H:i:s'),         // 更新时间
                'updated_by' => $this->userInfo['user_name'],// 更新人（当前登录用户）
            ];

            // 执行用户主表更新（通过snowflake_id定位记录）
            $SystemUsersModel = new SystemUsersModel();
            $result = $SystemUsersModel::query()
                ->where('snowflake_id', $userInfo['snowflake_id'])
                ->update($updateData);
            if (!$result) {
                throw new BusinessException(__('errors.600000'), '600000'); // 更新失败异常（数据库操作失败）
            }

            // 清除旧的用户-岗位关联记录（先删除再插入保证数据一致性）
            $systemUserPostModel = new SystemUserPostModel();
            $systemUserPostModel::query()
                ->where('user_id', $userInfo['snowflake_id'])
                ->delete();

            // 插入新的用户-岗位关联记录（仅当有新岗位ID时执行）
            if (!empty($params['post_ids'])){
                $usersPostInsertData = [];
                foreach ($params['post_ids'] as $item) {
                    $usersPostInsertData[] = [
                        'snowflake_id' => (new Snowflake(Common::getWorkerId()))->next(), // 关联记录雪花ID
                        'user_id' => $userInfo['snowflake_id'], // 用户雪花ID
                        'post_id' => $item,                     // 岗位ID
                        'created_at' => date('Y-m-d H:i:s'),     // 创建时间
                        'created_by' => $this->userInfo['user_name'], // 创建人
                        'updated_at' => date('Y-m-d H:i:s'),     // 更新时间
                        'updated_by' => $this->userInfo['user_name'], // 更新人
                        'tenant_id' => $this->userInfo['tenant_id'], // 租户ID（多租户隔离）
                    ];
                }
                $result = $systemUserPostModel->insert($usersPostInsertData);
                if ($result !== true) {
                    throw new BusinessException(__('errors.600000'), '600000'); // 关联插入失败异常
                }
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
     * 软删除系统用户（标记为已删除）
     *
     * 通过更新`is_deleted`字段实现逻辑删除，保留历史数据可追溯
     * @param array $params 删除参数（需包含`snowflake_id`标识目标用户）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/用户不存在/删除失败时抛出（错误码400000/500035/600000）
     * @throws Exception 其他系统异常（如数据库连接失败）
     */
    public function delete(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作）
            DB::beginTransaction();

            // 参数验证（使用SystemUsersValidated验证器，场景为"delete"）
            $validated = new SystemUsersValidated($params, 'delete');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标用户信息（通过snowflake_id查询，确保用户存在）
            $SystemUsersModel = new SystemUsersModel();
            $user = (new SystemUsersModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($user)) {
                throw new BusinessException(__('errors.500035'), 500035); // 用户不存在异常
            }

            // 构造软删除标记数据（记录删除时间和操作人）
            $updateData = [
                'is_deleted' => EnumCommon::IS_DELETED_1,     // 标记为已删除（1表示已删除）
                'deleted_at' => date('Y-m-d H:i:s'),          // 删除时间
                'deleted_by' => $this->userInfo['user_name'], // 删除人（当前登录用户）
            ];

            // 执行删除操作（通过snowflake_id定位记录并更新删除标记）
            $result = $SystemUsersModel::query()
                ->where('snowflake_id', $params['snowflake_id'])
                ->update($updateData);
            if (!$result) {
                throw new BusinessException(__('errors.600000'), '600000'); // 删除失败异常（数据库操作失败）
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
     * 获取用户详情（单条记录）
     *
     * 查询指定用户的完整信息，包含基础字段及操作人信息
     * @param array $params 查询参数（需包含`snowflake_id`标识目标用户）
     * @return array 用户详情数组（包含id/snowflake_id/username/nickname等字段）
     * @throws BusinessException 参数验证失败/用户不存在时抛出（错误码400000/500035）
     * @throws Exception 其他系统异常（如数据库查询失败）
     */
    public function getDetail(array $params): array
    {
        try {
            // 开启数据库事务（保证查询一致性）
            DB::beginTransaction();

            // 参数验证（使用SystemUsersValidated验证器，场景为"detail"）
            $validated = new SystemUsersValidated($params, 'detail');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 定义需要查询的字段列表（用户基础信息及操作人信息）
            $fields = [
                'id','snowflake_id','username','nickname','email','mobile','sex','avatar','status',
                'dept_id','login_ip','login_date','created_at','created_by','updated_at','updated_by','tenant_id','level'
            ];

            // 查询单条用户记录（通过snowflake_id精确查询）
            $result = (new SystemUsersModel())
                ->setFields($fields)
                ->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($result)) {
                throw new BusinessException(__('errors.500035'), 500035); // 用户不存在异常
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