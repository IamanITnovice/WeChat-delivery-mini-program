<?php

namespace app\shop\model\auth;

use app\common\model\shop\UserRole as UserRoleModel;
use think\facade\Cache;


/**
 * 角色模型
 */
class UserRole extends UserRoleModel
{

    public function getUserRole($where)
    {
        return $this->where($where)->column('role_id');

    }

    /**
     * 获取指定管理员的所有角色id
     * @param $shop_user_id
     * @return array
     */
    public static function getRoleIds($shop_user_id)
    {
        return (new self)->where('shop_user_id', '=', $shop_user_id)->column('role_id');
    }

    /**
     * 获取角色下的用户
     */
    public static  function getUserRoleCount($role_id){
        $model = new static();
        return $model->alias('userRole')
            ->join('shop_user', 'userRole.shop_user_id = shop_user.shop_user_id', 'left')
            ->where('userRole.role_id', '=', $role_id)
            ->where('shop_user.is_delete', '=', 0)
            ->count();
    }

    /**
     * 新增后清理缓存
     */
    public static function onAfterInsert($model)
    {
        self::clearUserAccessCache($model->shop_user_id);
    }

    /**
     * 更新后清理缓存
     */
    public static function onAfterUpdate($model)
    {
        self::clearUserAccessCache($model->shop_user_id);
    }

    /**
     * 删除后清理缓存
     */
    public static function onAfterDelete($model)
    {
        self::clearUserAccessCache($model->shop_user_id);
    }

    /**
     * 清理用户权限缓存
     */
    private static function clearUserAccessCache($shop_user_id)
    {
        if ($shop_user_id) {
            Cache::delete('user_access_urls_' . $shop_user_id);
        }
    }
}
