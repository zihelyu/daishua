<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/19
 * Time: 10:13
 */

namespace klsf\dj;


interface Dj
{
    public static function getDjGoods($domain, $user, $pass);

    public static function getPost($goods, $domain, $user, $pass);

    public static function order($order, $domain, $post, $user, $pass);
}