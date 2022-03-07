<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/3/4
 * Time: 11:48
 */

namespace klsf\pay;


class Epay implements Pay
{
    private $parameters = array();
    private $key;//密匙
    private $api_url;
    private $private_rsa_key;

    public function __construct($pid, $key, $domain = "pay.svipqq.com")
    {
        $this->api_url = "http://{$domain}/submit.php?";
        $this->key = $key;
        $this->parameters['pid'] = $pid;//商户号
        $this->init();
    }

    private function init()
    {
        $this->parameters['sign_type'] = 'MD5';
    }

    public function payReturn()
    {
        if (!empty($_GET)) {
            $sign = $this->getSign($_GET);
            if ($sign === $_GET['sign']) {
                if (!empty($_GET["trade_status"]) && $_GET['trade_status'] == 'TRADE_SUCCESS') {
                    return [
                        'out_trade_no' => $_GET['out_trade_no'],
                        'trade_no' => $_GET['trade_no'],
                        'pay_type' => $_GET['type'],
                        'total_fee' => $_GET['money']
                    ];
                }
            }
        }
        return false;
    }

    public function payNotify()
    {
        return $this->payReturn();
    }

    public function paySubmit($orderId, $title, $rmb, $description = null, $ip = null, $mobile = false)
    {
        $this->parameters['out_trade_no'] = $orderId;
        $this->parameters['name'] = $title;
        $this->parameters['money'] = $rmb;
        $this->parameters['sitename'] = $description;

        $this->parameters['sign'] = $this->getSign($this->parameters);
        return $this->api_url . $this->arr2str($this->parameters);
    }

    public function setNotifyUrl($url)
    {
        // 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
        $this->parameters['notify_url'] = $url;
    }

    public function setReturnUrl($url)
    {
        // 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
        $this->parameters['return_url'] = $url;
    }

    public function setParameter($k, $v)
    {
        $this->parameters[$k] = $v;
    }

    public function orderQuery($orderId)
    {
        return false;
    }

    private function getSign($param, $type = 'MD5')
    {
        $signPars = "";
        ksort($param);

        if ($type == 'RSA') {
            foreach ($param as $k => $v) {
                if ("sign" != $k) {
                    $signPars .= $k . "=" . $v . "&";
                }
            }
            $signPars = trim($signPars, '&');
            $sign = "";
            if ($pi_key = openssl_pkey_get_private($this->private_rsa_key)) {
                if (openssl_sign($signPars, $sign, $pi_key, OPENSSL_ALGO_SHA1)) {
                    openssl_free_key($pi_key);
                    $sign = urlencode(base64_encode($sign));
                    return $sign;
                }
            }
            return false;
        } else {
            foreach ($param as $k => $v) {
                if ("sign" != $k && "sign_type" != $k && "" != $v) {
                    $signPars .= $k . "=" . $v . "&";
                }
            }
            $signPars = trim($signPars, '&');
            $signPars .= $this->key;
            $sign = md5($signPars);
            return $sign;
        }
    }

    private function arr2str($param)
    {
        $str = "";
        foreach ($param as $k => $v) {
            $str .= "&{$k}=" . urlencode($v);
        }
        return trim($str, '&');
    }

    private function getCurl($url, $post = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

}