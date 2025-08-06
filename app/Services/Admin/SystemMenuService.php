<?php
namespace App\Services\Admin;

use App\Enums\EnumCommon;
use App\Enums\EnumSystemMenu;
use App\Exceptions\BusinessException;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use App\Logging\SqlLogger;
use App\Models\SystemMenuModel;
use App\Services\BaseService;
use App\Services\CommonService;
use App\Validates\SystemMenuValidated;
use Exception;
use Illuminate\Support\Facades\DB;

class SystemMenuService extends BaseService
{

    /**
     * 获取系统菜单列表（带筛选和枚举值转换）
     * @param array $params 筛选参数（支持id、name（模糊）、status等字段筛选）
     * @return array 菜单列表数组（每个元素包含原始字段及type_name、status_name枚举描述）
     */
    public function getList(array $params): array
    {
        // 初始化菜单模型
        $systemMenuModel = new SystemMenuModel();

        // 定义查询条件映射（字段与查询方式的对应关系）
        $whereMap = [
            'id' => ['field' => 'id', 'search' => 'where'],
            'name' => ['field' => 'name', 'search' => 'like', 'operator' => 'like_after'],
            'status' => ['field' => 'status', 'search' => 'where'],
        ];

        // 定义需要查询的字段列表
        $fields = ['id','snowflake_id','name','permission','type','sort','parent_id','path','icon','component','component_name','status',
            'visible','keep_alive','always_show'];

        // 构建查询：设置字段、转换筛选条件、排序，获取多条记录（不分页）
        $result = $systemMenuModel->setFields($fields)
            ->convertConditions($params, $whereMap) // 将参数转换为模型可识别的查询条件
            ->setOrderBy(['id' => 'asc'])
            ->getMultipleRecord(); // 获取分页结果

        // 补充枚举描述（类型名称、状态名称）
        if (!empty($result)){
            foreach ($result as &$item) {
                $item['type_name'] = EnumSystemMenu::getTypeMap($item['type']); // 类型枚举值转名称（目录/菜单/按钮）
                $item['status_name'] = EnumSystemMenu::getStatusMap($item['status']); // 状态枚举值转名称（启用/停用）
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

            // 参数验证（使用SystemMenuValidated验证器，场景为"add"）
            $validated = new SystemMenuValidated($params, 'add');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 过滤参数（仅保留模型允许批量赋值的字段）
            $systemMenuMode = new SystemMenuModel();
            $params = CommonService::filterRecursive($params, $systemMenuMode->fillable);

            // 验证菜单数据有效性（名称唯一性、父菜单合法性等）
            $params = $this->validatedMenu($params);

            // 生成雪花ID（分布式唯一ID）
            $snowflake = new Snowflake(Common::getWorkerId());

            // 构造插入数据（包含基础信息及操作人、时间戳）
            $insertData[] = [
                'snowflake_id' => $snowflake->next(),
                'name' => $params['name'],
                'permission' => $params['permission'],
                'type' => $params['type'],
                'sort' => $params['sort'],
                'parent_id' => $params['parent_id'],
                'path' => $params['path'],
                'icon' => $params['icon'] ?? '',
                'component' => $params['component'] ?? '',
                'component_name' => $params['component_name'] ?? '',
                'status' => $params['status'] ?? EnumSystemMenu::STATUS_1, // 默认启用
                'visible' => $params['visible'] ?? EnumSystemMenu::VISIBLE_1 , // 默认显示
                'keep_alive' => $params['keep_alive'] ?? EnumSystemMenu::KEEP_ALIVE_1, // 默认缓存
                'always_show' => $params['always_show'] ?? EnumSystemMenu::ALWAYS_SHOW_1, // 默认总是显示
                'created_by' => $this->userInfo['user_name'], // 创建人（当前登录用户）
                'created_at' => date('Y-m-d H:i:s'), // 创建时间
                'updated_by' => $this->userInfo['user_name'], // 更新人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'), // 更新时间
            ];

            // 执行数据插入
            $systemMenuModel = new SystemMenuModel();
            $result = $systemMenuModel->insert($insertData);
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
    public function validatedMenu(array $params , $menuInfo = null): array
    {
        $systemMenuModel = new SystemMenuModel();

        // 验证菜单类型对应的必填字段（目录/菜单类型必须有路径）
        switch ($params['type']){
            case EnumSystemMenu::TYPE_1: // 目录
            case EnumSystemMenu::TYPE_2: // 菜单
                if (empty($params['path'])) throw new BusinessException(__('validated.300146').__('common.500002'), 500002);
                break;
        }

        // 验证父菜单有效性（若存在父ID）
        if (!empty($params['parent_id'])){
            // 查询父菜单信息（仅获取类型、ID、名称）
            $menu = $systemMenuModel->setFields(['type','id','name'])->getSingleRecord(['id' => $params['parent_id']]);
            if (empty($menu)) throw new BusinessException(__('errors.500010'), 500010); // 父菜单不存在
            if (!in_array($menu['type'], [EnumSystemMenu::TYPE_1, EnumSystemMenu::TYPE_2])) {
                throw new BusinessException(__('errors.500011'), 500011); // 父菜单类型无效（仅允许目录/菜单作为父级）
            }
            if ($menu['name'] == $params['name']) throw new BusinessException(__('errors.500009'), 500010); // 子菜单名称与父菜单重复
        }

        // 验证菜单名称唯一性（新增/更新场景）
        if (!empty($menuInfo)){
            // 更新场景：检查除当前菜单外是否有同名菜单
            $menu = $systemMenuModel->setFields(['type','id','name'])->getSingleRecord(['name' => $params['name']]);
            if (!empty($menu) && $menu['id'] != $menuInfo['id']) throw new BusinessException(__('errors.500015'), 500015);
        }else{
            // 新增场景：检查是否已存在同名菜单
            $exists = $systemMenuModel::query()->where('name', $params['name'])->exists();
            if ($exists) throw new BusinessException(__('errors.500015'), 500015);
        }

        // 验证组件名称唯一性（若存在组件名称）
        if (!empty($params['component_name'])){
            if (!empty($menuInfo)){
                // 更新场景：检查除当前菜单外是否有同组件名
                $exists = $systemMenuModel::query()->where('component_name', $params['component_name'])
                    ->where('id', '<>', $menuInfo['id'])->exists();
            }else{
                // 新增场景：检查是否已存在同组件名
                $exists = $systemMenuModel::query()->where('component_name', $params['component_name'])->exists();
            }
            if ($exists) throw new BusinessException(__('errors.500013'), 500013);
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

            // 参数验证（使用SystemMenuValidated验证器，场景为"update"）
            $validated = new SystemMenuValidated($params, 'update');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标菜单信息（通过snowflake_id查询）
            $systemMenuModel =  new SystemMenuModel();
            $menu = (new SystemMenuModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($menu)) throw new BusinessException(__('errors.500014'), 500014); // 菜单不存在

            // 验证菜单数据有效性（传入原菜单信息用于唯一性校验）
            $params = $this->validatedMenu($params,$menu);

            // 过滤更新参数（仅保留模型允许批量赋值的字段）
            $systemMenuMode = new SystemMenuModel();
            $params = CommonService::filterRecursive($params, $systemMenuMode->fillable);

            // 构造插入数据（包含基础信息、操作人及时间戳）
            $updateData[] = [
                'name' => $params['name'],
                'permission' => $params['permission'],
                'type' => $params['type'],
                'sort' => $params['sort'],
                'parent_id' => $params['parent_id'],
                'path' => $params['path'],
                'icon' => $params['icon'] ?? '',
                'component' => $params['component'] ?? '',
                'component_name' => $params['component_name'] ?? '',
                'status' => $params['status'] ?? EnumSystemMenu::STATUS_1, // 默认启用
                'visible' => $params['visible'] ?? EnumSystemMenu::VISIBLE_1 , // 默认显示
                'keep_alive' => $params['keep_alive'] ?? EnumSystemMenu::KEEP_ALIVE_1, // 默认缓存
                'always_show' => $params['always_show'] ?? EnumSystemMenu::ALWAYS_SHOW_1, // 默认总是显示
                'updated_by' => $this->userInfo['user_name'], // 更新人（当前登录用户）
                'updated_at' => date('Y-m-d H:i:s'), // 更新时间
            ];


            // 执行更新操作（根据snowflake_id定位记录）
            $result = $systemMenuModel::query()->where('snowflake_id', $params['snowflake_id'])->update($updateData);
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

            // 参数验证（使用SystemMenuValidated验证器，场景为"delete"）
            $validated = new SystemMenuValidated($params, 'delete');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 获取目标菜单信息（通过snowflake_id查询）
            $systemMenuModel =  new SystemMenuModel();
            $menu = (new SystemMenuModel())->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($menu)) throw new BusinessException(__('errors.500014'), 500014); // 菜单不存在

            // 构造软删除标记数据（标记删除时间、操作人）
            $updateData = [
                'is_deleted' => EnumCommon::IS_DELETED_1, // 标记为已删除（根据EnumCommon定义）
                'deleted_at' => date('Y-m-d H:i:s'), // 删除时间
                'deleted_by' => $this->userInfo['user_name'], // 删除人（当前登录用户）
            ];

            // 执行删除操作（更新删除标记）
            $result = $systemMenuModel::query()->where('snowflake_id', $params['snowflake_id'])->update($updateData);
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

            // 参数验证（使用SystemMenuValidated验证器，场景为"delete"）
            $validated = new SystemMenuValidated($params, 'detail');
            $messages = $validated->isRunFail();
            if (!empty($messages)){
                throw new BusinessException($messages, '400000'); // 参数验证失败异常
            }

            // 定义需要查询的字段列表
            $fields = ['id','snowflake_id','name','permission','type','sort','parent_id','path','icon','component','component_name','status',
                'visible','keep_alive','always_show'];

            // 查询单条菜单记录（通过snowflake_id）
            $result = (new SystemMenuModel())->setFields($fields)->getSingleRecord(['snowflake_id' => $params['snowflake_id']]);
            if (empty($result)) throw new BusinessException(__('errors.500014'), 500014); // 菜单不存在（注：原代码中$menu未定义，此处修正为检查$result）

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