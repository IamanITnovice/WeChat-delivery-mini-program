<?php

namespace app\shop\model\auth;

use app\common\model\shop\RoleAccess as RoleAccessModel;
use think\facade\Cache;


/**
 * 角色模型
 */
class RoleAccess extends RoleAccessModel
{
    /**
     * 获取指定角色的所有权限id
     * @param int|array $role_id 角色id (支持数组)
     */
    public static function getAccessIds($role_id)
    {
        $roleIds = is_array($role_id) ? $role_id : [(int)$role_id];
        return (new self)->where('role_id', 'in', $roleIds)->column('access_id');
    }

    /**
     * 新增后清理缓存
     */
    public static function onAfterInsert($model)
    {
        self::clearRoleAccessCache($model->role_id);
    }

    /**
     * 更新后清理缓存
     */
    public static function onAfterUpdate($model)
    {
        self::clearRoleAccessCache($model->role_id);
    }

    /**
     * 删除后清理缓存
     */
    public static function onAfterDelete($model)
    {
        self::clearRoleAccessCache($model->role_id);
    }

    /**
     * 清理角色相关的所有用户权限缓存
     */
    private static function clearRoleAccessCache($role_id)
    {
        if ($role_id) {
            // 获取该角色下的所有用户
            $userIds = (new UserRole())->where('role_id', '=', $role_id)->column('shop_user_id');
            foreach ($userIds as $userId) {
                Cache::delete('user_access_urls_' . $userId);
            }
        }
    }
}
