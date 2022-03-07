<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/11/4
 * Time: 16:12
 */
function get_curl($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobaody = 0)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $httpheader[] = "Accept:application/json";
    $httpheader[] = "Accept-Encoding:gzip,deflate,sdch";
    $httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
    $httpheader[] = "Connection:close";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
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
    if($referer){
        if($referer==1){
            curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');
        }else{
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
    }
    if ($ua) {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    } else {
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 4.0.4; es-mx; HTC_One_X Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0');
    }
    if ($nobaody) {
        curl_setopt($ch, CURLOPT_NOBODY, 1);
    }
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($ch);
    curl_close($ch);
    return $ret;
}
/**
 * 订单自动对接
 * @param $id
 * @param bool $isReturn
 * @return array|bool
 */
function orderDj($id, $isReturn = false)
{
    if ($order = \app\index\model\Order::findByIdWithGoods($id)) {
        if ($order['did']) {
            //对接
            $status = $order['status'];
            $dj_id = 0;
            $dj_err = null;
            if ($dj = \app\index\model\Dj::findByDid($order['did'])) {
                if ($dj['kind'] === 'ylsq') {
                    $ret = \klsf\dj\DjYile::order($order, $dj['domain'], $order['post'], $dj['user'], $dj['pass']);
                } elseif ($dj['kind'] === '95sq') {
                    $ret = \klsf\dj\Dj95Sq::order($order, $dj['domain'], $order['post'], $dj['user'], $dj['pass']);
                } elseif ($dj['kind'] === 'kyx') {
                    $ret = \klsf\dj\DjKyx::order($order, $dj['domain'], $order['post'], $dj['user'], $dj['pass']);
                } else {
                    $ret = [
                        'code' => -1,
                        'message' => '不支持对接此平台'
                    ];
                }
                if ($ret['code'] === 0) {
                    $status = $order['api_status'] ? $order['api_status'] : $order['status'];
                    $dj_id = $ret['id'];
                } else {
                    $dj_err = $ret['message'];
                }
            } else {
                $dj_err = '获取对接信息失败';
            }
            \app\index\model\Order::updateById($id, [
                'status' => $status,
                'dj_id' => $dj_id,
                'dj_error' => $dj_err
            ]);
        }
    }
    if (!$isReturn) {
        return true;
    } else {
        return [
            'code' => (isset($dj_id) && $dj_id) ? 0 : -1,
            'message' => (isset($dj_err)) ? $dj_err : 'null',
        ];
    }
}

function db_execute($sql)
{
    \think\Db::execute($sql);
}

function unzip()
{
    return new \klsf\UnZip();
}

function checkQzone($uin)
{
    $get = getCurl("http://sh.taotao.qq.com/cgi-bin/emotion_cgi_feedlist_v6?hostUin={$uin}&ftype=0&sort=0&pos=0&num=10&format=json");
    if ($ret = json_decode($get, true)) {
        if ($ret['code'] === 0) {
            return true;
        }
    }
    return false;
}

function isQQ()
{
    if ((strpos($_SERVER['HTTP_USER_AGENT'], 'QQ/6') || strpos($_SERVER['HTTP_USER_AGENT'], 'QQ/7')) && strpos($_SERVER['HTTP_USER_AGENT'], 'Pixel')) {
        return true;
    }
    return false;
}

function jsbridge()
{
    if (isQQ()) {
        header('Location:jsbridge://publicAccount/openInExternalBrowser?p=' . urlencode('http://' . $_SERVER['HTTP_HOST']));
        exit();
    }
}