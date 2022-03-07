<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/24
 * Time: 17:00
 */

namespace klsf\dj;


class DjYile implements Dj
{

    public static function getDjGoods($domain, $user, $pass)
    {
        $url = "http://{$domain}/api/web/getGoodsList.html";
        $ret = self::getCurl($url);
        if (!$ret = json_decode($ret, true)) {
            return '打开对接网站失败';
        } elseif ($ret['code'] !== 0) {
            return $ret['message'];
        } else {
            $list = [];
            foreach ($ret['list'] as $v) {
                $list[] = array(
                    'id' => $v['goodsid'],
                    'type' => $v['modelid'],
                    'name' => $v['name'],
                    'post' => $v['post']
                );
            }
            return $list;
        }
    }

    public static function getPost($goods, $domain, $user, $pass)
    {
        $result['code'] = -1;
        $list = self::getDjGoods($domain, $user, $pass);
        if (is_array($list)) {
            $result['message'] = '不存在此商品';
            foreach ($list as $v) {
                if ($v['id'] === $goods['id']) {
                    $post = "goodsid={$goods['id']}&number=[num]";
                    foreach ($v['post'] as $k => $p) {
                        $i = $k + 1;
                        if (!in_array($p['param'], ['number', 'goodsid'])) {
                            $post .= "&{$p['param']}=[value{$i}]";
                        }
                    }
                    $result = [
                        'code' => 0,
                        'message' => '获取POST数据成功',
                        'post' => $post
                    ];
                }
            }
        } else {
            $result['message'] = $list;
        }
        return $result;
    }

    public static function order($order, $domain, $post, $user, $pass)
    {
        $result['code'] = -1;
        $num = $order['num'] * $order['unit'];
        $url = "http://{$domain}/api/web/order.html";
        $post = "api_user={$user}&api_pwd={$pass}&{$post}";
        $post = str_replace(array('[value1]', '[value2]', '[value3]', '[value4]', '[value5]', '[value6]', '[num]'), array(urlencode($order['value1']), urlencode($order['value2']), urlencode($order['value3']), urlencode($order['value4']), urlencode($order['value5']), urlencode($order['value6']), $num), $post);
        $get = self::getCurl($url, $post);
        if (!$ret = json_decode($get, true)) {
            $result['message'] = '对接超时';
        } elseif (isset($ret['id'])) {
            $result = [
                'code' => 0,
                'message' => 'success',
                'id' => $ret['id']
            ];
        } else {
            $result['message'] = $ret['message'];
        }
        return $result;
    }

    private static function getCurl($url, $post = null)
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