<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/19
 * Time: 10:14
 */

namespace klsf\dj;


class DjKyx implements Dj
{
    public static function order($order, $domain, $post, $user, $pass)
    {
        $passArr = explode('@#@', $pass);
        $salePwd = isset($passArr[1]) ? $passArr[1] : null;
        $result['code'] = -1;

        if (!$cookie = self::login($domain, $user, $pass)) {
            $result['message'] = '帐号或者密码不正确';
        } else {
            $num = $order['num'] * $order['unit'];
            $url = "http://{$domain}/front/inter/uploadOrder.htm?salePwd=" . $salePwd;
            $post = str_replace(array('[value1]', '[value2]', '[value3]', '[value4]', '[value5]', '[value6]', '[num]'), array(urlencode($order['value1']), urlencode($order['value2']), urlencode($order['value3']), urlencode($order['value4']), urlencode($order['value5']), urlencode($order['value6']), $num), $post);
            $get = self::getCurl($url, $post, null, $cookie);
            if (!$arr = json_decode($get, true)) {
                $result['message'] = '对接超时';
            } elseif (isset($arr['orderNo'])) {
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'id' => 1
                ];
            } else {
                $result['message'] = $arr['mess'];
            }
        }
        return $result;
    }

    public static function getDjGoods($domain, $user, $pass)
    {
        return '不支持获取商品列表';
    }

    public static function getPost($goods = [], $domain, $user = null, $pass = null)
    {
        $result['code'] = -1;
        if (!$cookie = self::login($domain, $user, $pass)) {
            $result['message'] = '帐号或者密码不正确';
        } else {
            $get = self::getCurl($goods['url'] . "&jdfwkey=fqkrp", null, null, $cookie);
            $start = strpos($get, '<!-- 自定义充值信息 -->');
            $end = strpos($get, 'name="sumprice"');
            if (preg_match('/"(\d+)" name="goodsId"/', $get, $r1) && preg_match('/"([a-z0-9A-Z]+)" name="mainKey"/', $get, $r2)) {
                $post = [
                    'goodsId' => $r1[1],
                    'mainKey' => $r2[1],
                    'textAccountName' => '[value1]',
                    'reltextAccountName' => '[value1]',
                    'sumprice' => '[num]',
                    'messinfo' => ''

                ];
                if ($start > 1 && $end > $start) {
                    $get = substr($get, $start, $end - $start);
                    if (preg_match_all('/id="(lblName\d+)"\//is', $get, $arr)) {
                        foreach ($arr[1] as $k => $item) {
                            $i = $k + 2;
                            $post[$item] = "[value{$i}]";
                        }
                    }
                    if (preg_match_all('/value="(.*?)" name="(temptypeName\d+)"/is', $get, $arr)) {
                        foreach ($arr[2] as $k => $item) {
                            $post[$item] = $arr[1][$k];
                        }
                    }
                }
                $p = "";
                foreach ($post as $k => $v) {
                    $p .= "{$k}={$v}&";
                }

                $result = [
                    'code' => 0,
                    'message' => '获取POST数据成功',
                    'post' => $p
                ];
            } else {
                $result['message'] = '匹配商品POST数据失败';
            }
        }

        return $result;
    }

    private static function login($domain, $user, $pass)
    {
        $pass = explode('@#@', $pass);
        $get = self::getCurl("http://{$domain}/frontLogin.htm", "loginTimes=1&userName={$user}&password={$pass[0]}", 0, 1, 1);
        if (preg_match_all('/Set-Cookie:\s?([A-Za-z0-9\_=\|]+);/is', $get, $arr2)) {
            $cookie = null;
            foreach ($arr2['1'] as $item) {
                $cookie .= $item . ';';
            }
            return $cookie;
        }
        return false;
    }

    private static function getCurl($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobaody = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $klsf[] = "Accept:*";
        $klsf[] = "Accept-Encoding:gzip,deflate,sdch";
        $klsf[] = "Accept-Language:zh-CN,zh;q=0.8";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $klsf);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if ($header) {
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
        }
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if ($ua) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36');
        }
        if ($nobaody) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);//主要头部
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);//跟随重定向
        }
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

}