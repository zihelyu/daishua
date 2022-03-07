<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/19
 * Time: 10:14
 */

namespace klsf\dj;


class Dj95Sq implements Dj
{
    public static function order($order, $domain, $post, $user, $pass)
    {
        $result['code'] = -1;
        $num = $order['num'] * $order['unit'];
        $url = "http://{$domain}/index.php?m=home&c=order&a=add";
        $post = "Api_UserName={$user}&Api_UserMd5Pass=" . md5($pass) . "&{$post}";
        $post = str_replace(array('[value1]', '[value2]', '[value3]', '[value4]', '[value5]', '[value6]', '[num]'), array(urlencode($order['value1']), urlencode($order['value2']), urlencode($order['value3']), urlencode($order['value4']), urlencode($order['value5']), urlencode($order['value6']), $num), $post);
        $get = self::getCurl($url, $post);
        $arr = json_decode($get, true);
        if (isset($arr['order_id'])) {
            $result = [
                'code' => 0,
                'message' => 'success',
                'id' => $arr['order_id']
            ];
        } elseif (preg_match('/class="error">(.*?)<\/p>/i', $get, $arr)) {
            $result['message'] = $arr['1'];
        } else {
            $result['message'] = '对接超时';
        }
        return $result;
    }

    public static function getDjGoods($domain, $user, $pass)
    {
        $get = self::getCurl("http://" . $domain . "/index.php");
        if (strlen($get) < 1024) {
            return '打开对接网站失败';
        } elseif (preg_match_all('/href="\/index\.php\?m=home&c=goods&a=detail&id=(\d+)&goods\_type=(\d+)"\>(.*?)alt="(.*?)"/is', $get, $arr) || preg_match_all('/href="\/index\.php\?m=home&c=goods&a=detail&id=(\d+)&goods\_type=(\d+)"\>(.*?)>([^>]*?)<\/h4>/is', $get, $arr)) {
            if (!$cookie = self::login($domain, $user, $pass)) {
                return '账号或者密码错误';
            } else {
                $cookie = base64_encode($cookie);
                @setcookie('api_cookie', $cookie, time() + 3600 * 24, '/');
                $list = array();
                foreach ($arr[1] as $k => $v) {
                    $list[] = array(
                        'id' => $v,
                        'type' => $arr[2][$k],
                        'name' => $arr[4][$k]
                    );
                }
                return $list;
            }
        } else {
            return '获取商品列表失败';
        }
    }

    public static function getPost($goods = [], $domain, $user = null, $pass = null)
    {
        $result['code'] = -1;
        $cookie = isset($_COOKIE['api_cookie']) ? base64_decode($_COOKIE['api_cookie']) : null;
        $get = self::getCurl("http://{$domain}/index.php?m=Home&c=Goods&a=detail&id={$goods['id']}", 0, 0, $cookie);
        $start = strpos($get, 'action="/index.php?m=home&c=order');
        $end = strpos($get, 'name="pay_type');
        if ($start > 1 && $end > 1) {
            $get = substr($get, $start, $end - $start);
            if (preg_match_all('/name="([a-z0-9A-Z\_\-]+)"/is', $get, $arr)) {
                $post = "";
                foreach ($arr[1] as $k => $item) {
                    if ($item == 'need_num_0') {
                        $post .= "{$item}=[num]&";
                    } elseif ($item == 'goods_id') {
                        $post .= "{$item}={$goods['id']}&";
                    } elseif ($item == 'goods_type') {
                        $post .= "{$item}={$goods['type']}&";
                    } else {
                        $i = $k + 1;
                        $post .= "{$item}=[value{$i}]&";
                    }
                }
                $post = trim($post, '&');
                $result = [
                    'code' => 0,
                    'message' => '获取POST数据成功',
                    'post' => $post
                ];
            } else {
                $result['message'] = '匹配商品POST数据失败';
            }
        } else {
            $result['message'] = '获取商品POST数据失败';
        }
        return $result;
    }

    private static function login($domain, $user, $pass)
    {
        $get = self::getCurl("http://{$domain}/index.php?m=Home&c=User&a=login", "username={$user}&username_password={$pass}", 0, 0, 1);
        if (strpos($get, "登录成功")) {
            if (preg_match_all('/Set-Cookie:\s?([A-Za-z0-9\_=\|]+);/is', $get, $arr2)) {
                $cookie = null;
                foreach ($arr2['1'] as $item) {
                    $cookie .= $item . ';';
                }
                return $cookie;
            }
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