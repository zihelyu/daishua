<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/18
 * Time: 19:39
 */

namespace app\index\controller;


use app\index\model\PayOrder;
use klsf\pay\Alipay;
use klsf\pay\Epay;
use klsf\pay\Qpay;
use klsf\pay\Weixin;

class Pay extends Common
{
    public function kkNotify()
    {
        return $this->kkReturn();
    }

    public function kkReturn()
    {
        $pay = new Epay(config('sys_pay_kk_pid'), config('sys_pay_kk_key'), 'pay.koock.cn');
        if (!$info = $pay->payReturn()) {
            $this->error('支付验证失败', '/');
        } else {
            $result = PayOrder::finish($info['out_trade_no'], $info['pay_type'], $info['trade_no']);
            if ($result['code'] === 0) {
                switch ($result['type']) {
                    case 2:
                        $this->success("成功充值 {$result['rmb']} 元", '/');
                        break;
                    case 1:
                        $this->success($result['message'] . ",马上跳转至你的分站！", 'http://' . $result['domain']);
                        break;
                    default:
                        $this->success($result['message'], '/');
                }
            } else {
                $this->error($result['message'], '/');
            }
        }
    }

    public function waNotify()
    {
        return $this->waReturn();
    }

    public function waReturn()
    {
        $pay = new Epay(config('sys_pay_52_pid'), config('sys_pay_52_key'), 'tx87.cn');
        if (!$info = $pay->payReturn()) {
            $this->error('支付验证失败', '/');
        } else {
            $result = PayOrder::finish($info['out_trade_no'], $info['pay_type'], $info['trade_no']);
            if ($result['code'] === 0) {
                switch ($result['type']) {
                    case 2:
                        $this->success("成功充值 {$result['rmb']} 元", '/');
                        break;
                    case 1:
                        $this->success($result['message'] . ",马上跳转至你的分站！", 'http://' . $result['domain']);
                        break;
                    default:
                        $this->success($result['message'], '/');
                }
            } else {
                $this->error($result['message'], '/');
            }
        }
    }

    public function blNotify()
    {
        return $this->blReturn();
    }

    public function blReturn()
    {
        $pay = new Epay(config('sys_pay_bl_pid'), config('sys_pay_bl_key'), 'pay.blyzf.cn');
        if (!$info = $pay->payReturn()) {
            $this->error('支付验证失败', '/');
        } else {
            $result = PayOrder::finish($info['out_trade_no'], $info['pay_type'], $info['trade_no']);
            if ($result['code'] === 0) {
                switch ($result['type']) {
                    case 2:
                        $this->success("成功充值 {$result['rmb']} 元", '/');
                        break;
                    case 1:
                        $this->success($result['message'] . ",马上跳转至你的分站！", 'http://' . $result['domain']);
                        break;
                    default:
                        $this->success($result['message'], '/');
                }
            } else {
                $this->error($result['message'], '/');
            }
        }
    }

    public function chNotify()
    {
        return $this->chReturn();
    }

    public function chReturn()
    {
        $pay = new Epay(config('sys_pay_ch_pid'), config('sys_pay_ch_key'), 'pay.v8jisu.cn');
        if (!$info = $pay->payReturn()) {
            $this->error('支付验证失败', '/');
        } else {
            $result = PayOrder::finish($info['out_trade_no'], $info['pay_type'], $info['trade_no']);
            if ($result['code'] === 0) {
                switch ($result['type']) {
                    case 2:
                        $this->success("成功充值 {$result['rmb']} 元", '/');
                        break;
                    case 1:
                        $this->success($result['message'] . ",马上跳转至你的分站！", 'http://' . $result['domain']);
                        break;
                    default:
                        $this->success($result['message'], '/');
                }
            } else {
                $this->error($result['message'], '/');
            }
        }
    }

    public function qpayNotify()
    {
        $pay = new Qpay(config('sys_pay_qpay_partner'), config('sys_pay_qpay_key'));
        if ($return = $pay->payNotify()) {
            $orderid = $return['out_trade_no'];
            $payid = $return['transaction_id'];
            $result = PayOrder::finish($orderid, 'qqpay', $payid);
            return '<xml>
<return_code>SUCCESS</return_code>
</xml>';
        } else {
            return '<xml>
<return_code>FAIL</return_code>
</xml>';
        }
    }


    public function weixinNotify()
    {
        $pay = new Weixin(config('sys_pay_weixin_appid'), config('sys_pay_weixin_mchid'), config('sys_pay_weixin_key'));
        if ($return = $pay->payNotify()) {
            if (isset($return['result_code']) && $return['result_code'] == 'SUCCESS') {
                $orderid = $return['out_trade_no'];
                $payid = $return['transaction_id'];
                $result = PayOrder::finish($orderid, 'wxpay', $payid);
            }
            return '<xml>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <return_msg><![CDATA[OK]]></return_msg>
</xml>';
        } else {
            return '<xml>
  <return_code><![CDATA[FAIL]]></return_code>
  <return_msg><![CDATA[ERROR]]></return_msg>
</xml>';
        }

    }

    public function alipayNotify()
    {
        $pay = new Alipay(config('sys_pay_alipay_partner'), config('sys_pay_alipay_key'));
        if ($pay->payNotify()) {
            if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                $orderid = $_POST['out_trade_no'];
                $payid = $_POST['trade_no'];
                $result = PayOrder::finish($orderid, 'alipay', $payid);
            }
            return 'success';
        } else {
            return 'fail';
        }
    }

    public function alipayReturn()
    {
        $pay = new Alipay(config('sys_pay_alipay_partner'), config('sys_pay_alipay_key'));
        if ($pay->payReturn()) {
            if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                $orderid = $_GET['out_trade_no'];
                $payid = $_GET['trade_no'];
                $result = PayOrder::finish($orderid, 'alipay', $payid);
                if ($result['code'] === 0) {
                    switch ($result['type']) {
                        case 2:
                            $this->success("成功充值 {$result['rmb']} 元", '/');
                            break;
                        case 1:
                            $this->success($result['message'] . ",马上跳转至你的分站！", 'http://' . $result['domain']);
                            break;
                        default:
                            $this->success($result['message'], '/');
                    }
                } else {
                    $this->error($result['message'], '/');
                }
            } else {
                $this->error('未完成支付', '/');
            }
        } else {
            $this->error('支付验证失败', '/');
        }
    }

}