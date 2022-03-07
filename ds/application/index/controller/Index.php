<?php

namespace app\index\controller;

use app\index\model\PayOrder;
use klsf\pay\Alipay;
use klsf\pay\Epay;
use klsf\pay\Qpay;
use klsf\pay\Weixin;

class Index extends Common
{
    public function cs()
    {
        $result = PayOrder::finish("171103142406000100716413", "qqpay", "123456789");
        print_r($result);
    }

    public function pay($oid, $type)
    {
        $payDomain = config('sys_pay_domain');
        $pay = null;

        if (!$oid || !$order = PayOrder::findByZidAndOid(ZID, $oid)) {
            $this->error('订单不存在', '/');
        } elseif ($type === 'alipay') {
            if (!$pay = config('sys_pay_alipay')) {
                $this->error('未开启此支付方式', '/');
            } else {
                if ($pay === 'gf') {
                    if (!$payDomain) {
                        $this->error('站长未配置支付域名', '/');
                    } elseif (!config('sys_pay_alipay_partner') || !config('sys_pay_alipay_key')) {
                        $this->error('站长未配置支付接口', '/');
                    }
                    $pay = new Alipay(config('sys_pay_alipay_partner'), config('sys_pay_alipay_key'));
                    $pay->setNotifyUrl('http://' . $payDomain . url('index/Pay/alipayNotify'));
                    $pay->setReturnUrl('http://' . $payDomain . url('index/Pay/alipayReturn'));
                    $url = $pay->paySubmit($order['oid'], '支付订单：[ID:' . $order['id'] . ']', $order['rmb'], config('web_name'), null, request()->isMobile());
                    header("Location:" . $url);
                    exit();
                }
            }
        } elseif ($type === 'qqpay') {
            if (!$pay = config('sys_pay_qqpay')) {
                $this->error('未开启此支付方式', '/');
            } else {
                if ($pay === 'gf') {
                    if (!$payDomain) {
                        $this->error('站长未配置支付域名', '/');
                    } elseif (!config('sys_pay_qpay_partner') || !config('sys_pay_qpay_key')) {
                        $this->error('站长未配置支付接口', '/');
                    }
                    $pay = new Qpay(config('sys_pay_qpay_partner'), config('sys_pay_qpay_key'));
                    $pay->setNotifyUrl('http://' . $payDomain . url('index/Pay/qpayNotify'));
                    $url = $pay->paySubmit($order['oid'], '支付订单：[ID:' . $order['id'] . ']', $order['rmb'], config('web_name'));
                    $this->assign('pay', [
                        'type' => $type,
                        'typeName' => 'QQ钱包',
                        'oid' => $order['oid'],
                        'rmb' => $order['rmb'],
                        'name' => '支付订单：[ID:' . $order['id'] . ']',
                        'url' => $url
                    ]);
                    return $this->fetch();
                }
            }
        } elseif ($type === 'wxpay') {
            if (!$pay = config('sys_pay_wxpay')) {
                $this->error('未开启此支付方式', '/');
            } else {
                if ($pay === 'gf') {
                    if (!$payDomain) {
                        $this->error('站长未配置支付域名', '/');
                    } elseif (!config('sys_pay_weixin_appid') || !config('sys_pay_weixin_mchid') || !config('sys_pay_weixin_key')) {
                        $this->error('站长未配置支付接口', '/');
                    }
                    $pay = new Weixin(config('sys_pay_weixin_appid'), config('sys_pay_weixin_mchid'), config('sys_pay_weixin_key'));
                    $pay->setNotifyUrl('http://' . $payDomain . url('index/Pay/weixinNotify'));
                    $url = $pay->paySubmit($order['oid'], '支付订单：[ID:' . $order['id'] . ']', $order['rmb'], config('web_name'));
                    $this->assign('pay', [
                        'type' => $type,
                        'typeName' => '微信',
                        'oid' => $order['oid'],
                        'rmb' => $order['rmb'],
                        'name' => '支付订单：[ID:' . $order['id'] . ']',
                        'url' => $url
                    ]);
                    return $this->fetch();
                }
            }
        } else {
            $this->error('不支持此支付方式', '/');
        }
        switch ($pay) {
            case 'kk':
                if (!config('sys_pay_kk_pid') || !config('sys_pay_kk_key')) {
                    $this->error('站长未配置支付接口', '/');
                }
                $pay = new Epay(config('sys_pay_kk_pid'), config('sys_pay_kk_key'), 'pay.koock.cn');
                $pay->setNotifyUrl("http://{$_SERVER['HTTP_HOST']}" . url('pay/kkNotify'));
                $pay->setReturnUrl("http://{$_SERVER['HTTP_HOST']}" . url('pay/kkReturn'));
                break;
            case '52':
                if (!config('sys_pay_52_pid') || !config('sys_pay_52_key')) {
                    $this->error('站长未配置支付接口', '/');
                }
                $pay = new Epay(config('sys_pay_52_pid'), config('sys_pay_52_key'), 'tx87.cn');
                $pay->setNotifyUrl("http://{$_SERVER['HTTP_HOST']}" . url('pay/waNotify'));
                $pay->setReturnUrl("http://{$_SERVER['HTTP_HOST']}" . url('pay/waReturn'));
                break;
            case 'ch':
                if (!config('sys_pay_ch_pid') || !config('sys_pay_ch_key')) {
                    $this->error('站长未配置支付接口', '/');
                }
                $pay = new Epay(config('sys_pay_ch_pid'), config('sys_pay_ch_key'), 'pay.v8jisu.cn');
                $pay->setNotifyUrl("http://{$_SERVER['HTTP_HOST']}" . url('pay/chNotify'));
                $pay->setReturnUrl("http://{$_SERVER['HTTP_HOST']}" . url('pay/chReturn'));
                break;
            case 'bl':
                if (!config('sys_pay_bl_pid') || !config('sys_pay_bl_key')) {
                    $this->error('站长未配置支付接口', '/');
                }
                $pay = new Epay(config('sys_pay_bl_pid'), config('sys_pay_bl_key'), 'pay.blyzf.cn');
                $pay->setNotifyUrl("http://{$_SERVER['HTTP_HOST']}" . url('pay/blNotify'));
                $pay->setReturnUrl("http://{$_SERVER['HTTP_HOST']}" . url('pay/blReturn'));
                break;
            case 'hack':
                if (!config('sys_pay_hack_pid') || !config('sys_pay_hack_key')) {
                    $this->error('站长未配置支付接口', '/');
                }
                $pay = new Epay(config('sys_pay_hack_pid'), config('sys_pay_hack_key'), 'pay.hackwl.cn');
                $pay->setNotifyUrl("http://{$_SERVER['HTTP_HOST']}" . url('pay/hackNotify'));
                $pay->setReturnUrl("http://{$_SERVER['HTTP_HOST']}" . url('pay/hackReturn'));
                break;
            default:
                $this->error('不支持此支付方式', '/');
                break;
        }
        $pay->setParameter('type', $type);
        $url = $pay->paySubmit($order['oid'], '支付订单：[ID:' . $order['id'] . ']', $order['rmb'], config('web_name'));
        header("Location:" . $url);
    }

    public function adminLogin()
    {
        $this->assign('webTitle', '管理员登录');
        return $this->fetch();
    }

    public function login()
    {
        $this->assign('webTitle', '站长登录');
        return $this->fetch();
    }

    public function web()
    {
        $this->checkKind(1);
        $this->assign('webTitle', '搭建分站');
        return $this->fetch();
    }

    public function index()
    {
        if (config('web_is_tz')) {
            jsbridge();
        }
        return $this->fetch();
    }
}
