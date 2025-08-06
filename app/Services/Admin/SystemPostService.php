<?php
namespace App\Services\Admin;

use App\Enums\EnumCommon;
use App\Enums\EnumSystemPost;
use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use App\Logging\SqlLogger;
use App\Models\SystemPostModel;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Validates\SystemPostValidated;
use Exception;
use Illuminate\Support\Facades\DB;

class SystemPostService extends BaseService
{

    /**
     * 获取系统岗位列表（带筛选和状态枚举转换）
     * @param array $params 筛选参数（支持id、code（精确）、name（模糊）、status（精确）等字段筛选）
     * @return array 岗位列表数组（每个元素包含原始字段及status_name状态描述）
     */
    public function getList(array $params): array
    {
        // 初始化岗位模型
        $systemPostModel = new SystemPostModel();

        // 定义查询条件映射（字段与查询方式的对应关系）
        $whereMap = [
            'id' => ['field' => 'id', 'search' => 'where'],          // ID精确查询
            'code' => ['field' => 'post_code', 'search' => 'where'],  // 岗位编码精确查询（注意模型字段为post_code）
            'name' => ['field' => 'name', 'search' => 'like', 'operator' => 'like_after'],  // 名称右模糊查询（%name）
            'status' => ['field' => 'status', 'search' => 'where'],   // 状态精确查询
        ];

        // 定义需要查询的字段列表（包含基础信息及时间戳）
        $fields = ['id','snowflake_id','code','name','sort','status','remark','created_at'];

        // 构建查询：设置字段、转换筛选条件、按ID升序排序，获取多条记录（不分页）
        $result = $systemPostModel->setFields($fields)
            ->convertConditions($params, $whereMap) // 将参数转换为模型可识别的查询条件
            ->setOrderBy(['id' => 'asc'])
            ->getPaginateResults(); // 获取分页结果（注：实际可能为不分页，需根据模型实现确认）

        // 补充状态枚举描述（将状态值转换为中文名称）
        if (!empty($result['list'])){
            foreach ($result['list'] as &$item) {
                $item['status_name'] = EnumSystemPost::getStatusMap($item['status']); // 状态枚举值转名称（如"启用"/"停用"）
            }
        }

        return $result;
    }


    /**
     * 添加系统岗位（带事务和数据验证）
     * @param array $params 岗位参数（包含code、name、sort、status等必填字段）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/插入失败时抛出
     * @throws Exception 其他异常
     */
    public function add(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作）
            DB::beginTransaction();

            // 参数验证（使用SystemPostValidated验证器，场景为"add"）
            $validated = new SystemPostValidated($params, 'add');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常（返回验证错误信息）
            }

            // 过滤参数（仅保留模型允许批量赋值的字段，防止非法字段注入）
            $systemPostMode = new SystemPostModel();
            $params = CommonService::filterRecursive($params, $systemPostMode->fillable);

            // 验证岗位数据有效性（名称/编码唯一性校验）
            $params = $this->validatedPost($params);

            // 生成雪花ID（分布式唯一ID，用于全局标识岗位）
            $snowflake = new Snowflake(Common::getWorkerId());

            // 构造插入数据（包含基础信息、操作人及时间戳）
            $insertData[] = [
                'snowflake_id' => $snowflake->next(),       // 雪花ID（全局唯一标识）
                'code' => $params['code'],                   // 岗位编码
                'name' => $params['name'],                   // 岗位名称
                'sort' => $params['sort'],                   // 显示顺序（用于排序）
                'status' => $params['status'],               // 状态（启用/停用）
                'remark' => $params['remark'] ?? '',         // 备注（可选）
                'created_at' => date('Y-m-d H:i:s'),         // 创建时间
                'created_by' => $this->userInfo['user_name'],// 创建人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'),         // 更新时间（初始与创建时间一致）
                'updated_by' => $this->userInfo['user_name'],// 更新人（当前登录用户）
                'tenant_id' => $this->userInfo['tenant_id'], // 租户ID（多租户隔离）
            ];

            // 执行数据插入（批量插入，此处仅单条）
            $SystemPostModel = new SystemPostModel();
            $result = $SystemPostModel->insert($insertData);
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
     * 验证岗位数据有效性（名称/编码唯一性校验）
     * @param array $params 待验证的岗位参数（需包含name、code字段）
     * @param mixed $postInfo 可选参数（更新场景时传入原岗位信息）
     * @return array 验证通过的原始参数
     * @throws BusinessException 验证失败时抛出（名称/编码重复）
     */
    public function validatedPost(array $params , $postInfo = null): array
    {
        // 查询同名岗位（限制当前租户）
        $exists = SystemPostModel::query()
            ->where('name', $params['name'])
            ->where('tenant_id', $this->userInfo['tenant_id'])
            ->first();

        if (empty($postInfo)) {
            // 新增场景：直接校验名称和编码是否已存在
            if (!empty($exists)) {
                throw new BusinessException(__('errors.500016'), 500017); // 岗位名称重复异常
            }

            // 校验岗位编码唯一性（限制当前租户）
            $exists = SystemPostModel::query()
                ->where('code', $params['code'])
                ->where('tenant_id', $this->userInfo['tenant_id'])
                ->first();
            if (!empty($exists)) {
                throw new BusinessException(__('errors.500016'), 500016); // 岗位编码重复异常
            }
        } else {
            // 更新场景：排除当前岗位自身的校验
            if (!empty($exists) && $exists['id'] != $postInfo['id']) {
                throw new BusinessException(__('errors.500016'), 500017); // 名称与其他岗位重复
            }

            // 仅当编码被修改时校验唯一性
            if ($postInfo['code'] != $params['code']) {
                $exists = SystemPostModel::query()
                    ->where('code', $params['code'])
                    ->where('tenant_id', $this->userInfo['tenant_id'])
                    ->first();
                if (!empty($exists)) {
                    throw new BusinessException(__('errors.500016'), 500016); // 编码与其他岗位重复
                }
            }
        }

        return $params;
    }


    /**
     * 更新系统岗位（带事务和数据验证）
     * @param array $params 更新参数（需包含snowflake_id标识目标岗位）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/更新失败时抛出
     * @throws Exception 其他异常
     */
    public function update(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作）
            DB::beginTransaction();

            // 参数验证（使用SystemPostValidated验证器，场景为"update"）
            $validated = new SystemPostValidated($params, 'update');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标岗位信息（通过snowflake_id查询）
            $SystemPostModel = new SystemPostModel();
            $post = (new SystemPostModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($post)) {
                throw new BusinessException(__('errors.500018'), 500018); // 岗位不存在异常
            }

            // 验证岗位数据有效性（传入原岗位信息用于唯一性校验）
            $params = $this->validatedPost($params, $post);

            // 过滤更新参数（仅保留模型允许批量赋值的字段）
            $systemPostMode = new SystemPostModel();
            $params = CommonService::filterRecursive($params, $systemPostMode->fillable);

            // 构造插入数据（包含基础信息、操作人及时间戳）
            $updateData[] = [
                'code' => $params['code'],                   // 岗位编码
                'name' => $params['name'],                   // 岗位名称
                'sort' => $params['sort'],                   // 显示顺序（用于排序）
                'status' => $params['status'],               // 状态（启用/停用）
                'remark' => $params['remark'] ?? '',         // 备注（可选）
                'updated_at' => date('Y-m-d H:i:s'),         // 更新时间（初始与创建时间一致）
                'updated_by' => $this->userInfo['user_name'],// 更新人（当前登录用户）
            ];

            // 执行更新操作（根据snowflake_id定位记录）
            $result = $SystemPostModel::query()
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
     * 软删除系统岗位（标记为已删除）
     * @param array $params 删除参数（需包含snowflake_id标识目标岗位）
     * @return array 空数组（操作结果通过异常传递）
     * @throws BusinessException 参数验证失败/删除失败时抛出
     * @throws Exception 其他异常
     */
    public function delete(array $params): array
    {
        try {
            // 开启数据库事务（保证原子性操作）
            DB::beginTransaction();

            // 参数验证（使用SystemPostValidated验证器，场景为"delete"）
            $validated = new SystemPostValidated($params, 'delete');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标岗位信息（通过snowflake_id查询）
            $SystemPostModel = new SystemPostModel();
            $post = (new SystemPostModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($post)) {
                throw new BusinessException(__('errors.500018'), 500018); // 岗位不存在异常
            }

            // 构造软删除标记数据（标记删除时间、操作人）
            $updateData = [
                'is_deleted' => EnumCommon::IS_DELETED_1,     // 标记为已删除（1表示已删除）
                'deleted_at' => date('Y-m-d H:i:s'),          // 删除时间
                'deleted_by' => $this->userInfo['user_name'], // 删除人（当前登录用户）
            ];

            // 执行删除操作（更新删除标记）
            $result = $SystemPostModel::query()
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
     * 获取岗位详情（单条记录）
     * @param array $params 查询参数（需包含snowflake_id标识目标岗位）
     * @return array 岗位详情数组（包含指定字段）
     * @throws BusinessException 参数验证失败/岗位不存在时抛出
     * @throws Exception 其他异常
     */
    public function getDetail(array $params): array
    {
        try {
            // 开启数据库事务（保证查询一致性）
            DB::beginTransaction();

            // 参数验证（使用SystemPostValidated验证器，场景为"detail"）
            $validated = new SystemPostValidated($params, 'detail');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 定义需要查询的字段列表（基础信息及时间戳）
            $fields = ['id','snowflake_id','code','name','sort','status','remark','created_at'];

            // 查询单条岗位记录（通过snowflake_id精确查询）
            $result = (new SystemPostModel())
                ->setFields($fields)
                ->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($result)) {
                throw new BusinessException(__('errors.500018'), 500018); // 岗位不存在异常
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