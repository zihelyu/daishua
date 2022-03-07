<?php

require_once __DIR__ . '/functions.php';
function encryptPassword($password)
{
    return md5(md5($password) . md5('1251251214'));
}
function createSid()
{
    return md5(uniqid(mt_rand(), 1) . time());
}
function arrayField($arr = array(), $field = array(), $default = null)
{
    $new = array();
    foreach ($field as $k) {
        $new[$k] = isset($arr[$k]) ? $arr[$k] : $default;
    }
    return $new;
}
function getWebKindName($kind)
{
    if ($kind == 0) {
        return '普及版';
    }
    if ($kind == 1) {
        return '专业版';
    }
    if ($kind == 2) {
        return '旗舰版';
    }
    return '未知版本';
    return $kind;
}
function getHtmlCode($value, $html = false)
{
    $value = stripslashes($value);
    if ($html) {
        $value = htmlspecialchars($value);
    }
    return $value;
}
function getDomainList()
{
    $domains = config('sys_domains');
    $domains = explode("\r\n", $domains);
    $list = array();
    foreach ($domains as $domain) {
        $domain = trim($domain);
        if (strlen($domain) > 3) {
            $list[] = $domain;
        }
    }
    return $list;
}
function getCurl($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobaody = 0)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $klsf[] = 'Accept:*';
    $klsf[] = 'Accept-Encoding:gzip,deflate,sdch';
    $klsf[] = 'Accept-Language:zh - CN,zh;q = 0.8';
    $klsf[] = 'Accept-Domain:' . $_SERVER['HTTP_HOST'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $klsf);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    if ($header) {
        curl_setopt($ch, CURLOPT_HEADER, true);
    }
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    if ($referer) {
        if ($referer == 1) {
            curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');
        } else {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
    }
    if ($ua) {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    } else {
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36');
    }
    if ($nobaody) {
        curl_setopt($ch, CURLOPT_NOBODY, 1);
    }
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($ch);
    curl_close($ch);
    return $ret;
}