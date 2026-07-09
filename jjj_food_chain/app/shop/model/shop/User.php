<?php

namespace app\shop\model\shop;

use app\common\model\shop\LoginLog as LoginLogModel;
use app\common\model\shop\User as UserModel;
use app\common\service\auth\PasswordService;
use app\common\service\auth\TokenService;
use think\facade\Cache;

/**
 * 商户后台用户登录模型
 */
class User extends UserModel
{
    /**
     * 检查登录
     */
    public function checkLogin($user)
    {
        $password = $user['password'];
        $where['user_name'] = $user['username'];
        $where['is_delete'] = 0;
        if (!$user = $this->where($where)->with(['app', 'supplier'])->find()) {
            $this->error = '账号或密码错误，请重新输入';
            return false;
        }
        if (!PasswordService::verify($password, $user['password'])) {
            $this->error = '账号或密码错误，请重新输入';
            return false;
        }
        if ($user['is_status'] == 1) {
            $this->error = '账号被禁用，请联系平台';
            return false;
        }
        if (empty($user['app'])) {
            $this->error = '登录失败, 未找到应用信息';
            return false;
        }
        if ($user['app']['is_delete']) {
            $this->error = '登录失败, 当前应用已删除';
            return false;
        }
        if ($user['app']['is_recycle']) {
            $this->error = '登录失败, 当前应用已禁用';
            return false;
        }
        if ($user['app']['expire_time'] != 0 && $user['app']['expire_time'] < time()) {
            $this->error = '登录失败, 当前应用已过期，请联系平台续费';
            return false;
        }
        if (PasswordService::needsRehash($user['password'])) {
            $user->save([
                'password' => PasswordService::hash($password),
            ]);
        }
        // 保存登录状态
        $user['token'] = TokenService::sign($user['shop_user_id'], TokenService::TYPE_SHOP);
        // 写入登录日志
        LoginLogModel::add($where['user_name'], \request()->ip(), '登录成功', $user['app']['app_id'], $user['shop_supplier_id']);
        return $user;
    }

    /**
     * 修改密码
     */
    public function editPass($data, $user)
    {
        $user_info = User::detail($user['shop_user_id']);
        if ($data['password'] != $data['confirmPass']) {
            $this->error = '密码错误';
            return false;
        }
        if (!PasswordService::checkStrength($data['password'], $error)) {
            $this->error = $error;
            return false;
        }
        if (!PasswordService::verify($data['oldpass'], $user_info['password'])) {
            $this->error = '原密码错误';
            return false;
        }
        $date['password'] = PasswordService::hash($data['password']);
        $user_info->save($date);
        return true;
    }

    /**
     * 获取登录用户信息
     */
    public static function getUser($data)
    {
        return (new static())->where('shop_user_id', '=', $data['uid'])->with(['app'])->find();
    }
}
