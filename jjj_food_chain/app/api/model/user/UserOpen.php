<?php

namespace app\api\model\user;

use app\api\model\plus\agent\Referee as RefereeModel;
use app\api\model\settings\Setting as SettingModel;
use think\facade\Cache;
use app\common\exception\BaseException;
use app\common\model\user\User as UserModel;
use app\common\model\user\Grade as GradeModel;
use app\common\service\auth\PasswordService;
use app\api\model\plus\invitationgift\Partake;
use app\common\service\auth\TokenService;

/**
 * H5 手机号登录模型
 */
class UserOpen extends UserModel
{
    private $token;

    /**
     * 隐藏字段
     */
    protected $hidden = [
        'open_id',
        'is_delete',
        'app_id',
        'create_time',
        'update_time'
    ];

    /**
     * 获取 token
     */
    public function getToken()
    {
        return $this->token;
    }


    /**
     * 手机号密码登录
     */
    public function phoneLogin($data)
    {
        $user = $this->where('mobile', '=', $data['mobile'])
            ->where('reg_source', '=', 'h5')
            ->where('is_delete', '=', 0)
            ->order('user_id desc')
            ->find();
        if (!$user) {
            $this->error = '手机号或密码错误';
            return false;
        }
        if (!PasswordService::verify($data['password'], $user['password'])) {
            $this->error = '手机号或密码错误';
            return false;
        }
        if (PasswordService::needsRehash($user['password'])) {
            $user->save([
                'password' => PasswordService::hash($data['password'])
            ]);
        }
        $user_id = $user['user_id'];
        // 生成 token
        $this->token = TokenService::sign($user_id, TokenService::TYPE_USER);
        return $user_id;
    }

    /**
     * 手机号注册
     */
    public function phoneRegister($data)
    {
        $setting = SettingModel::getItem('store');
        if (!PasswordService::checkStrength($data['password'], $error)) {
            $this->error = $error;
            return false;
        }
        $user = $this->where('mobile', '=', $data['mobile'])
            ->where('is_delete', '=', 0)
            ->where('reg_source', '=', 'h5')
            ->find();
        if (!$user) {
            try {
                $this->startTrans();
                $this->save([
                    'mobile' => $data['mobile'],
                    'reg_source' => 'h5',
                    'app_id' => self::$app_id,
                    'password' => PasswordService::hash($data['password'])
                ]);
                // 初始化默认昵称
                $this->save(['nickName' => $setting['user_name'] . $this['user_id']]);
                $this->commit();
                // 生成 token
                $this->token = TokenService::sign($this['user_id'], TokenService::TYPE_USER);
                return $this['user_id'];
            } catch (\Exception $e) {
                $this->rollback();
                throw new BaseException(['msg' => $e->getMessage()]);
            }
        } else {
            $this->error = '手机号已存在';
            return false;
        }
    }
}
