<?php
namespace App\Services;

use App\Enums\EnumUsers;
use App\Exceptions\BusinessException;
use App\Helpers\AopProxy;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use App\Models\UsersModel;
use App\Services\Captcha\CaptchaService;
use App\Validates\UsersValidated;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 用户服务类
 *
 * 负责用户模块的核心业务逻辑，包含用户列表查询、新增、更新、删除及详情查询功能
 * 继承自BaseService，复用基础服务类的公共方法
 */
class UsersService extends BaseService
{

    /**
     * 获取用户分页列表
     *
     * @param array $params 查询参数（支持id/name/email/mobile等字段的条件查询）
     * @return array 分页结果数组（包含total总条数、size每页数量、current当前页、list用户列表）
     */
    public function getList(array $params): array
    {
        // 分页参数：每页数量（默认20）、当前页码（默认1）
        $size = $params['size'] ?? 20;
        $current = $params['current'] ?? 1;

        // 查询条件映射：定义参数与数据库字段的查询方式对应关系
        // 支持where（等于）、like（模糊）、whereBetween（区间）等查询类型
        $whereMap = [
            'id' => ['field' => 'id', 'search' => 'where'], // ID精确查询
            'name' => ['field' => 'name', 'search' => 'like', 'operator' => 'like_after'], // 姓名后缀模糊（%值）
            'email' => ['field' => 'email', 'search' => 'like', 'operator' => 'like_after'], // 邮箱后缀模糊
            'mobile' => ['field' => 'mobile', 'search' => 'like', 'operator' => 'like_after'], // 手机号后缀模糊
            'level_id' => ['field' => 'mobile', 'search' => 'where'], // 用户等级ID精确查询
            'status' => ['field' => 'status', 'search' => 'where'], // 状态精确查询
            'created_at' => ['field' => 'created_at', 'search' => 'whereBetween'], // 创建时间区间查询
            'updated_at' => ['field' => 'updated_at', 'search' => 'whereBetween'], // 更新时间区间查询
        ];

        // 需要返回的用户字段列表
        $fields = ['id', 'name', 'email', 'mobile', 'points', 'level_id', 'status', 'created_at', 'updated_at'];

        // 通过用户模型执行分页查询
        $usersModel = new UsersModel();
        $result = $usersModel->setSize($size)
            ->setCurrent($current)
            ->setFields($fields)
            ->convertConditions($params, $whereMap) // 将参数转换为模型可识别的查询条件
            ->getPaginateResults(); // 获取分页结果

        // 补充状态名称（通过枚举类转换状态码为可读名称）
        if ($result['total'] > 0 && !empty($result['list'])) {
            foreach ($result['list'] as &$item) {
                $item['status_name'] = EnumUsers::getStatusMap($item['status']);
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
     * @throws BusinessException
     * @throws Exception
     */
    public function registerByUsername($params){

        $usersValidated = new UsersValidated($params, 'registerByUsername');
        $messages = $usersValidated->isRunFail();
        if (!empty($messages)) throw new BusinessException($messages, '400000'); // 参数验证失败异常

        $usersModel = new UsersModel();

        // 校验用户名是否存在
        $exists = $usersModel::query()->where('name', $params['name'])->where('name', '!=', $params['name'])->exists();
        if ($exists) throw new BusinessException(__('errors.600004'),'600004');

        // 校验验证码
        if (!CaptchaService::validate($params['captcha_key'], $params['captcha_key'])) {
           throw new BusinessException(__('errors.600006'),'600006');
        }



        $snowflake = new Snowflake(Common::getWorkerId());
        $insertData = [
//            'uuid' => $snowflake->next(),
            'name' => $params['name'],
            'password' => Hash::make($params['password']),
            'created_by' => 'system',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_by' => 'system',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        var_dump($insertData);die;

        $result = $usersModel::query()->insert($insertData);
        if ($result !== true) throw new BusinessException(__('errors.600000'), '600000'); // 注册用户失败

        return [];
    }
}