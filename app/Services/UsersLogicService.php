<?php
namespace App\Services;



use App\Exceptions\BusinessException;
use App\Exceptions\ThrowableException;
use App\Libraries\Common;
use App\Libraries\LibSnowflake;
use App\Libraries\Snowflake;
use App\Models\UsersModel;
use App\Validates\UsersValidated;
use Exception;
use Illuminate\Support\Facades\DB;

class UsersLogicService extends BaseService
{
    /**
     * @throws BusinessException
     */
    public function checkUpdateParams(UsersModel $usersModel, array $params): array
    {

        $userInfo = $usersModel->setFields(['uuid'])->getSingleRecord(['uuid' => $params['uuid']]);
        if (empty($userInfo)) throw new BusinessException(__('errors.600005'),'600005');

        $exists = $usersModel::query()->where('mobile', $params['mobile'])->where('uuid', '!=', $userInfo['uuid'])->exists();
        if ($exists) throw new BusinessException(__('errors.600002'),'600002');

        $exists = $usersModel::query()->where('email', $params['email'])->where('uuid', '!=', $userInfo['uuid'])->exists();
        if ($exists) throw new BusinessException(__('errors.600003'),'600003');



        return $userInfo;
    }
}