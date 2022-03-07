<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/3/4
 * Time: 11:48
 */

namespace klsf\pay;


class Alipay implements Pay
{
    private $parameters = array();
    private $key;//密匙
    private $alipay_gateway_url = "https://mapi.alipay.com/gateway.do?";
    private $private_rsa_key;

    public function __construct($partner, $key)
    {
        $this->key = $key;
        $this->parameters['partner'] = $partner;//商户号
        $this->init();
    }

    private function init()
    {
        //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
        $this->parameters['seller_id'] = $this->parameters['partner'];

        //签名方式
        $this->parameters['sign_type'] = strtoupper('MD5');

        //字符编码格式 目前支持 gbk 或 utf-8
        $this->parameters['_input_charset'] = strtoupper('UTF-8');

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $this->parameters['transport'] = 'http';

        // 支付类型 ，无需修改
        $this->parameters['payment_type'] = "1";

        // 产品类型，无需修改
        $this->parameters['service'] = "create_direct_pay_by_user";//电脑网页

    }

    public function payReturn()
    {
        if (!empty($_GET)) {
            $sign = $this->getSign($_GET);
            if ($sign === $_GET['sign']) {
                if (!empty($_GET["notify_id"]) && $this->verifyReturn($_GET["notify_id"])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function payNotify()
    {
        if (!empty($_POST)) {
            $sign = $this->getSign($_POST);
            if ($sign === $_POST['sign']) {
                if (!empty($_POST["notify_id"]) && $this->verifyReturn($_POST["notify_id"])) {
                    return true;
                }
            }
        }
    }

    public function paySubmit($orderId, $title, $rmb, $description = null, $ip = null, $mobile = false)
    {
        if ($mobile) {
            $this->parameters['service'] = "alipay.wap.create.direct.pay.by.user";//手机网页
            $this->parameters['app_pay'] = 'Y';
        }
        $this->parameters['out_trade_no'] = $orderId;
        $this->parameters['subject'] = $title;
        $this->parameters['total_fee'] = $rmb;
        $this->parameters['body'] = $description;

        $this->parameters['sign'] = $this->getSign($this->parameters);
        return $this->alipay_gateway_url . $this->arr2str($this->parameters);
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
        $url = "https://openapi.alipay.com/gateway.do";
        $parameters = array(
            'app_id' => '2017021305652124',
            'method' => 'alipay.trade.query',
            'charset' => 'utf-8',
            'sign_type' => 'RSA',
            'timestamp' => date("Y-m-d H:i:s"),
            'version' => '1.0',
            'biz_content' => json_encode(array(
                'out_trade_no' => $orderId
            ))
        );
        $this->private_rsa_key = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDeHGPzXTMEpOTO/lOM109Q61WQLk/a6xdd8Wqgylq7up+s+Jrj
AbbuGrneTh0CNJuIBYnHQAU44A0Xa7RzUAn6bmuDCwJUTioX3LdHPU/d22Httcod
D01GT0NGRad+00WnBnzk4GXxrE4QhJiXYzsW8r9fvpD2oOLk6b3JcOhnOwIDAQAB
AoGACuOfbtX541dmNFO5quT/aXswJbJvjc8KxTtJu/pPunZqz6McjNaPrlq3FBQG
Tg5gNan32EHmP6SUX7qTDTg2VO5XHatXjR0KFzPhor+cpEdLfMPqb112Yqdk+xEG
jbJdvLqvfS/S1B4GvuR5DLyf6+c3VwpUPdd7N2D3Tzv8SxkCQQD/emqMVncbGAgS
2pYGbd0b8h+39ByoBmLeTbXfbqNaN7JbCptsJeN3x/2lTeUG64BHibNnTymBYvP5
fGYZngV/AkEA3pCG+Cj/z8cRJS8bZzcbIC60Tbw+Zlc2e0Q/XjR/SzTAIRxn8fps
UIBDiXwotLL7hy01Axist4R+bdmiLp0URQJAL3MBDweQH4wbE8VdT9xf0KzrjzLb
j6l/+2HbgZ/+3uaxTY9uxmtTEBAo3+bTvrFGpgLBO1LMlqdWQOQF4oQi6QJBAKvl
YmkeeV1H+cUHWhng5NF5YQa9AlDWwRx5fJvM3hf+2Pl50AqDiM6wEfmI7IOLzVr9
HnmSwCtJaMB9G5b2+SUCQFa7ALun9CA6SGVqGo0MPYKAMdxmVsX3/bi5RJWpKaEP
p5HAqrKsqqRbqxALuHly60plvWMIehhA1Hnw6hMWpN8=
-----END RSA PRIVATE KEY-----';
        if ($sign = $this->getSign($parameters, 'RSA')) {
            $parameters['sign'] = $sign;
            $post = "";
            foreach ($parameters as $k => $v) {
                $post .= $k . "=" . $v . "&";
            }
            $ret = $this->getCurl($url, $post);
            $arr = json_decode($ret, true);
            $status = isset($arr['alipay_trade_query_response']['trade_status']) ? $arr['alipay_trade_query_response']['trade_status'] : null;
            if ($status == 'TRADE_SUCCESS' || $status == 'TRADE_FINISHED') {
                return $arr['alipay_trade_query_response'];
            }
        }
        return false;
    }

    private function verifyReturn($notify_id = null)
    {
        if ($notify_id) {
            $veryfy_url = "http://notify.alipay.com/trade/notify_query.do?" . "partner=" . $this->parameters['partner'] . "&notify_id=" . $notify_id;
            $txt = $this->getCurl($veryfy_url);
            if (preg_match("/true$/i", $txt)) {
                return true;
            }
        }
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