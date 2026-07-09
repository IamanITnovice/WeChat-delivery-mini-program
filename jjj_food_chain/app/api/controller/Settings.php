<?php

namespace app\api\controller;

use app\api\model\settings\Setting as SettingModel;
use app\common\model\app\AppOpen as AppOpenModel;

/**
 * 页面控制器
 */
class Settings extends Controller
{
    // 订单支付方式
    public function checkPay()
    {
        $setting = SettingModel::getItem('store');
        $checkedPay = $setting['checkedPay'];
        $user = $this->getUser();
        return $this->renderSuccess('', [
            'checkedPay' => $checkedPay,
            'balance' => $user['balance'],
        ]);
    }
}
