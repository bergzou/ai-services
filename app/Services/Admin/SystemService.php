<?php
namespace App\Services\Admin;

use App\Models\SystemTenantModel;
use App\Services\BaseService;


class SystemService extends BaseService
{

    public function tenantGetByWebsite($params)
    {
        $website = '';
        if (!empty($params['website'])) {
            $systemTenantModel = new SystemTenantModel();
            $result = $systemTenantModel->setFields(['name'])->getSingleRecord(['website' => $params['website']]);
            if (!empty($result))  $website = $result['name'];
        }

        return $website;
    }
}