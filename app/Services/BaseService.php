<?php

namespace App\Services;

/**
 * 服务层基础类
 *
 * 提供服务层公共方法/属性定义，其他具体服务类可通过继承此类复用基础功能
 * 建议所有业务服务类均继承此类以保持代码规范统一
 */
class BaseService
{

    public array $userInfo = [
        'user_id' => 1,
        'user_name' => 'admin',
    ];


}