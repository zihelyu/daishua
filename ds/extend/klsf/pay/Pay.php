<?php

/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/3/4
 * Time: 11:18
 */

namespace klsf\pay;
interface Pay
{
    public function payReturn();

    public function payNotify();

    public function paySubmit($orderId, $title, $rmb, $description, $ip, $mobile);

    public function setNotifyUrl($url);

    public function setReturnUrl($url);

    public function setParameter($k, $v);

    public function orderQuery($orderId);

}