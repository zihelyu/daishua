<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/18
 * Time: 15:14
 */

namespace app\index\model;


class WebRecord extends Base
{
    protected $pk = 'id';

    /**
     * 创建分站订单
     * @param array $data
     * @return array|string
     */
    public static function createOrder($data = [])
    {
        $insert = [
            'kind' => $data['kind'],
            'rid' => ZID,
            'user' => trim($data['user']),
            'pass' => trim($data['pass']),
            'qq' => $data['qq'],
            'name' => $data['name'],
            'domain' => $data['domain'],
            'status' => 0,
            'add_time' => ['exp', 'now()']
        ];
        if (Web::findByDomain($data['domain'])) {
            return '此域名已被使用';
        }
        $insert['price_web'] = 0;
        $price = config('sys_price_web_' . $data['kind']);
        if ($price < 0) return '获取商品价格错误';
        if (config('web_kind')) {
            $price_web = config('sys_price_web_' . config('web_kind') . '_' . $data['kind']);//专业版分站价格
            $insert['price_web'] = $price - $price_web;
            $insert['price_web'] = ($insert['price_web'] < 0) ? 0 : $insert['price_web'];
        }
        $insert['price'] = $price;
        $self = new static();

        if ($insert['price'] == 0) {
            $web = Web::add($data, ZID);
            if (is_numeric($web)) {
                return true;
            } else {
                return $web;
            }
        } elseif ($id = $self->insert($insert, false, true)) {
            $oid = date("ymdHis") . str_pad($id, 7, 0, STR_PAD_LEFT) . rand(10000, 99999);
            $insert = [
                'oid' => $oid,
                'id' => 0 - $id,
                'zid' => ZID,
                'rmb' => $insert['price'],
                'status' => 0,
                'add_time' => ['exp', 'now()']
            ];
            if (PayOrder::insert($insert)) {
                return [
                    'id' => $id,
                    'oid' => $oid,
                    'rmb' => $insert['rmb'],
                    'domain' => $data['domain'],
                ];
            } else {
                $self->where('id', $id)->delete();
                return '创建支付订单失败';
            }
        } else {
            return '保存订单失败';
        }
    }

    /**
     * 查找开通分站订单
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function findById($id)
    {
        $self = new static();
        return $self->where('id', $id)->find();
    }

}