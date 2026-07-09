<?php

namespace app\shop\model\auth;

use app\common\model\shop\User as UserModel;
use app\common\service\auth\PasswordService;

/**
 * 商户后台用户模型
 */
class User extends UserModel
{
    /**
     * 获取用户列表
     */
    public function getList($limit = 20)
    {
        return $this->with(['userRole.role', 'supplier'])
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($limit);
    }

    /**
     * 获取所有上级 ID
     */
    public function getTopRoleIds($role_id, &$all = null)
    {
        static $ids = [];
        is_null($all) && $all = $this->getAll();
        foreach ($all as $item) {
            if ($item['role_id'] == $role_id && $item['parent_id'] > 0) {
                $ids[] = $item['parent_id'];
                $this->getTopRoleIds($item['parent_id'], $all);
            }
        }
        return $ids;
    }

    /**
     * 获取所有角色
     */
    private function getAll()
    {
        $data = $this->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
        return $data ? $data->toArray() : [];
    }

    /**
     * 新增用户
     */
    public function add($data, $user)
    {
        $this->startTrans();
        try {
            if (!PasswordService::checkStrength($data['password'], $error)) {
                $this->error = $error;
                return false;
            }
            $arr = [
                'user_name' => trim($data['user_name']),
                'password' => PasswordService::hash($data['password']),
                'real_name' => trim($data['real_name']),
                'role_id' => $data['role_id'],
                'shop_supplier_id' => $user['shop_supplier_id'],
                'user_type' => $user['user_type'],
                'app_id' => self::$app_id
            ];

            $res = self::create($arr);
            $add_arr = [];
            $model = new UserRole();
            foreach ($data['role_id'] as $val) {
                $add_arr[] = [
                    'shop_user_id' => $res['shop_user_id'],
                    'role_id' => $val,
                    'app_id' => self::$app_id,
                ];
            }
            $model->saveAll($add_arr);
            // 提交事务
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 获取用户名数量
     */
    public function getUserName($where, $shop_user_id = 0)
    {
        if ($shop_user_id > 0) {
            return $this->withoutGlobalScope()->where($where)->where('shop_user_id', '<>', $shop_user_id)->count();
        }
        return $this->withoutGlobalScope()->where($where)->count();
    }

    /**
     * 获取门店用户名数量
     */
    public function getSupplierUserName($where, $shop_user_id = 0)
    {
        if ($shop_user_id > 0) {
            return $this->withoutGlobalScope()->where($where)->where('shop_user_id', '<>', $shop_user_id)->count();
        }
        return $this->withoutGlobalScope()->where($where)->count();
    }

    /**
     * 编辑用户
     */
    public function edit($data)
    {
        $this->startTrans();
        try {
            $arr = [
                'user_name' => $data['user_name'],
                'real_name' => $data['real_name'],
            ];
            if (empty($data['password'])) {
                unset($arr['password']);
            } else {
                if (!PasswordService::checkStrength($data['password'], $error)) {
                    $this->error = $error;
                    return false;
                }
                $arr['password'] = PasswordService::hash($data['password']);
            }

            $where['shop_user_id'] = $data['shop_user_id'];
            self::update($arr, $where);

            $model = new UserRole();
            UserRole::destroy($where);
            $add_arr = [];
            foreach ($data['access_id'] as $val) {
                $add_arr[] = [
                    'shop_user_id' => $data['shop_user_id'],
                    'role_id' => $val,
                    'app_id' => self::$app_id
                ];
            }
            $model->saveAll($add_arr);
            // 提交事务
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 获取子级数量
     */
    public function getChild($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 删除用户
     */
    public function del($shop_user_id, $user)
    {
        if ($user['shop_user_id'] == $shop_user_id) {
            $this->error = '不能删除当前登录账号';
            return false;
        }
        $where = ['shop_user_id' => $shop_user_id];
        self::update(['is_delete' => 1], $where);
        return UserRole::destroy($where);
    }

    /**
     * 设置状态
     */
    public function setStatus($status)
    {
        return $this->save([
            'is_status' => $status
        ]);
    }
}
