<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/15
 * Time: 18:35
 */

namespace app\index\model;


class WebGoods extends Base
{
    public static function getWebPriceInfo($zid, $goods)
    {
        $self = new static();
        if ($price = $self->where('zid', $zid)
            ->where('goodsid', $goods['goodsid'])
            ->find()) {
            return $price;
        }
        return [
            'price' => 0,
            'price_0' => 0
        ];
    }

    public static function getWebPrice($zid, $goods)
    {
        $self = new static();
        if ($price = $self->where('zid', $zid)
            ->where('goodsid', $goods['goodsid'])
            ->find()) {
            return $price['price'];
        }
        return 0.00;
    }

    public static function getXjWebPrice($zid, $goods)
    {
        $self = new static();
        if ($price = $self->where('zid', $zid)
            ->where('goodsid', $goods['goodsid'])
            ->find()) {
            return $price['price_0'];
        }
        return 0.00;
    }

    public static function getReWebPrice($zid, $goods)
    {
        $self = new static();
        if ($rid = Web::getRidByZid($zid)) {
            return [
                'zid' => $rid,
                'price' => $self->getXjWebPrice($rid, $goods)
            ];
        }
        return [
            'zid' => 100,
            'price' => $self->getXjWebPrice(100, $goods)
        ];
    }

    public static function setPrice($zid, $goodsid, $price_user, $price_web)
    {
        $self = new static();
        $data = [
            'zid' => $zid,
            'goodsid' => $goodsid,
            'price' => sprintf('%.2f', $price_user),
            'price_0' => sprintf('%.2f', $price_web)
        ];
        if (!$row = $self->where('zid', $zid)
            ->where('goodsid', $goodsid)
            ->find()) {
            return $self->insert($data);
        } else {
            return $row->save($data);
        }
    }

}