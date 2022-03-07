<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/17
 * Time: 15:48
 */

namespace app\index\controller;


use app\index\model\PayOrder;
use app\index\model\User;

class Home extends Common
{
    private $userInfo;

    public function clone_gg()
    {
        $this->assign('webTitle', '克隆公告');
        return $this->fetch();
    }

    public function web_add()
    {
        $this->checkKind(1);
        $this->assign('webTitle', '添加分站');
        return $this->fetch();
    }

    public function web_rank()
    {
        $this->assign('webTitle', '站点排行');
        return $this->fetch();
    }

    public function web_list()
    {
        $this->checkKind(1);
        $this->assign('webTitle', '下级站点列表');
        return $this->fetch();
    }

    public function recharge()
    {
        $act = input('post.act');
        if ($act == 'recharge') {
            $rmb = input('post.rmb/d');
            $payType = input('post.pay');
            if ($rmb < 1) {
                $this->error('充值金额不正确');
            } else {
                if (!$oid = PayOrder::recharge($this->userInfo['uid'], $rmb)) {
                    $this->error('创建订单失败');
                }
                header("Location:" . url('index/pay', ['oid' => $oid, 'type' => $payType]));
            }
            exit();
        }
        $this->assign('webTitle', '余额充值');
        return $this->fetch();
    }

    public function order()
    {
        $this->assign('webTitle', '添加订单');
        return $this->fetch();
    }

    public function order_list()
    {
        $this->assign('webTitle', '订单列表');
        return $this->fetch();
    }

    public function goods_list()
    {
        $this->assign('webTitle', '商品列表');
        return $this->fetch();
    }


    public function rmb_tx()
    {
        $this->assign('webTitle', '余额提现');
        return $this->fetch();
    }

    public function rmb_record()
    {
        $this->assign('webTitle', '余额明细');
        return $this->fetch();
    }

    public function config()
    {

        $this->assign('webTitle', '站点配置');
        return $this->fetch();
    }

    public function index()
    {
        $this->assign('webTitle', '站长后台首页');
        return $this->fetch();
    }

    private function checkLogin()
    {
        if (!$this->userInfo = User::getLoginUser()) {
            $this->error('请先登录', url('index/login'));
        }
        $this->assign('userInfo', $this->userInfo);
    }

    protected function _initialize()
    {
        parent::_initialize();
        $this->assign('webTitle', '站长后台');
        $this->checkLogin();
    }
}