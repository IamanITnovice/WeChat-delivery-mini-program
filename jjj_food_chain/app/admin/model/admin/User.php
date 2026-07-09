<?php

namespace app\admin\model\admin;

use app\common\model\admin\User as UserModel;
use app\common\service\auth\PasswordService;
use app\common\service\auth\TokenService;
use think\facade\Cache;

/**
 * 超管后台用户登录模型
 */
class User extends UserModel
{
    /**
     * 超管后台用户登录
     */
    public function login($data)
    {
        // 验证用户名密码是否正确
        if (!$user = self::where([
            'user_name' => $data['username'],
        ])->find()
        ) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
        if (!PasswordService::verify($data['password'], $user['password'])) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
        if (PasswordService::needsRehash($user['password'])) {
            $user->save([
                'password' => PasswordService::hash($data['password']),
            ]);
        }
        // 生成用户登录 token
        $user['token'] = TokenService::sign($user['admin_user_id'], TokenService::TYPE_ADMIN);
        return $user;
    }

    /**
     * 获取后台用户详情
     */
    public static function detail($admin_user_id)
    {
        $model = new static();
        return $model::find($admin_user_id);
    }

    /**
     * 修改后台用户登录密码
     */
    public function renew($data)
    {
        if ($data['pass'] !== $data['checkPass']) {
            $this->error = '确认密码不正确';
            return false;
        }
        if (!PasswordService::checkStrength($data['pass'], $error)) {
            $this->error = $error;
            return false;
        }
        return $this->save([
            'password' => PasswordService::hash($data['pass']),
        ]);
    }

    /**
     * 获取后台用户信息
     */
    public static function getUser($data)
    {
        return (new static())->where(['admin_user_id' => $data['uid']])->find();
    }
}
