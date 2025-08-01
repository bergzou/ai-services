<?php

namespace App\Validates;

use App\Interfaces\ValidatesInterface;
use App\Validates\BaseValidated;

class SystemMenuValidated extends BaseValidated implements ValidatesInterface
{
    /**
     * 定义验证规则数组
     * @return array 键为字段名，值为验证规则字符串（如'required|max:64'）
     */
    public function rules(): array
    {
        return [
            'id' => 'required', # 菜单ID
            'snowflake_id' => 'required', # 雪花Id
            'name' => 'required', # 菜单名称
            'permission' => 'required', # 权限标识
            'type' => 'required', # 菜单类型：1=目录， 2=菜单， 3=按钮
            'sort' => 'required', # 显示顺序
            'parent_id' => 'required', # 父菜单ID
            'path' => 'nullable', # 路由地址
            'icon' => 'nullable', # 菜单图标
            'component' => 'nullable', # 组件路径
            'component_name' => 'nullable', # 组件名
            'status' => 'required', # 菜单状态：1=启用， 2=停用
            'visible' => 'required', # 是否可见：1=显示， 2=隐藏
            'keep_alive' => 'required', # 是否缓存：1=缓存， 2=不缓存
            'always_show' => 'required', # 是否总是显示：1=总是， 2=不是
            'created_by' => 'required', # 创建人名称
            'updated_by' => 'required', # 更新人名称
            'is_deleted' => 'required', # 是否删除
            'deleted_by' => 'nullable', # 删除人名称
        ];
    }

    /**
     * 定义验证错误消息数组
     * @return array 键为'字段名.规则名'（如 'name.required'），值为自定义错误提示信息
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * 定义字段自定义别名数组（用于错误消息中显示友好名称）
     * @return array 键为字段名，值为业务友好的字段显示名称（如 'name' => '用户姓名'）
     * */
    public function customAttributes(): array
    {
        return [
            'id' => __('validated.300202'), # 菜单ID
            'snowflake_id' => __('validated.300277'), # 雪花Id
            'name' => __('validated.300142'), # 菜单名称
            'permission' => __('validated.300143'), # 权限标识
            'type' => __('validated.300144'), # 菜单类型
            'sort' => __('validated.300103'), # 显示顺序
            'parent_id' => __('validated.300145'), # 父菜单ID
            'path' => __('validated.300146'), # 路由地址
            'icon' => __('validated.300147'), # 菜单图标
            'component' => __('validated.300148'), # 组件路径
            'component_name' => __('validated.300149'), # 组件名
            'status' => __('validated.300150'), # 菜单状态
            'visible' => __('validated.300073'), # 是否可见
            'keep_alive' => __('validated.300151'), # 是否缓存
            'always_show' => __('validated.300152'), # 是否总是显示
            'created_by' => __('validated.300019'), # 创建人名称
            'updated_by' => __('validated.300020'), # 更新人名称
            'is_deleted' => __('validated.300184'), # 是否删除
            'deleted_by' => __('validated.300185'), # 删除人名称
        ];
    }

    /**
     * 新增参数
     * @return array
     */
    public function addParams(): array
    {
        return [
            'name','type','sort','parent_id','path','status',
        ];
    }

    /**
     * 更新参数
     * @return array
     */
    public function updateParams(): array
    {
        return [
            'snowflake_id', 'name', 'type', 'sort', 'parent_id', 'path', 'status',
        ];
    }

    /**
     * 删除参数
     * @return array
     */
    public function deleteParams(): array
    {
        return [
            'snowflake_id',
        ];
    }

    /**
     * 详情参数
     * @return array
     */
    public function detailParams(): array
    {
        return [
            'snowflake_id',
        ];
    }
}