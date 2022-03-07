<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/15
 * Time: 18:46
 */

namespace app\index\model;


class PayOrder extends Base
{
    protected $pk = 'oid';

    /**
     * 查找支付订单
     * @param $zid
     * @param $oid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function findByZidAndOid($zid, $oid)
    {
        $self = new static();
        return $self->where('zid', $zid)
            ->where('oid', $oid)
            ->find();
    }

    /**
     * 查找所有支付订单
     * @param $zid
     * @return array
     */
    public static function search($zid = null)
    {
        $page = input('post.page/d');
        $pageSize = input('post.pageSize/d');
        $page = ($page < 1) ? 1 : $page;
        $pageSize = ($pageSize < 1 || $pageSize > 50) ? 10 : $pageSize;
        $self = new static();
        $query = $self->alias('a');
        if ($zid) {
            $query->where('a.zid', $zid);
        } else {
            $zid = input('post.zid');
            is_numeric($zid) && $query->where('a.zid', $zid);
        }

        $status = input("post.status");
        is_numeric($status) && $query->where('a.status', $status);
        $pay_type = input("post.pay_type");
        if (in_array($pay_type, array('wxpay', 'alipay', 'qqpay'))) {
            $query->where('a.pay_type', $pay_type);
        }

        $pid = input("post.pid");
        $pid && $query->where('a.pid', $pid);
        $oid = input("post.oid");
        $oid && $query->where('a.oid', $oid);
        $id = input("post.id");
        $id && $query->where('a.id', $id);

        $query2 = clone $query;
        return [
            'total' => $query2->count('a.oid'),
            'list' => $query->page($page, $pageSize)->order('a.add_time desc')->select()
        ];
    }


    public static function recharge($uid, $rmb)
    {
        $oid = date("ymdHis") . str_pad(999, 7, 0, STR_PAD_LEFT) . rand(10000, 99999);
        $insert = [
            'oid' => $oid,
            'id' => -999,//充值
            'zid' => $uid,
            'rmb' => $rmb,
            'status' => 0,
            'add_time' => ['exp', 'now()']
        ];
        $self = new static();
        if ($self->insert($insert)) {
            return $oid;
        }
        return false;
    }


    public static function finish($oid, $pay_type, $pid)
    {
        $self = new static();
        if (!$row = $self->where('oid', $oid)->find()) {
            return ['code' => -1, 'message' => '支付订单不存在'];
        } else {
            $self->where('oid', $oid)
                ->update([
                    'status' => 1,
                    'pay_type' => $pay_type,
                    'pid' => $pid,
                    'pay_time' => ['exp', 'now()']
                ]);
        }
        if ($row['id'] === -999) {
            //余额充值
            if ($row['status'] === 0) {
                if (!User::rmb($row['zid'], "充值", $row['rmb'], "在线充值 {$row['rmb']} 元 [订单号：{$oid}]")) {
                    return ['code' => -1, 'message' => '充值失败，请联系管理员！'];
                }
            }
            return ['code' => 0, 'message' => '成功充值', 'type' => 2, 'rmb' => $row['rmb']];
        } elseif ($row['id'] < 0) {
            //搭建分站
            $id = abs($row['id']);
            if (!$order = WebRecord::findById($id)) {
                return ['code' => -1, 'message' => '分站订单已不存在'];
            }
            if ($row['status'] === 0) {
                //分站站长提成
                if ($order['price_web'] > 0) {
                    User::rmb($row['zid'], "提成", $order['price_web'], "用户开通分站提成[域名：{$order['domain']}]");
                }

                $data = [
                    'name' => $order['name'],
                    'domain' => $order['domain'],
                    'kind' => $order['kind'],
                    'user' => $order['user'],
                    'pass' => $order['pass'],
                    'qq' => $order['qq'],
                ];
                $web = Web::add($data, $row['zid']);
                if (!is_numeric($web)) {
                    return $web;
                }
            }
            return ['code' => 0, 'message' => '分站搭建成功', 'type' => 1, 'domain' => $order['domain']];
        } else {
            $id = $row['id'];
            if (!$order = Order::findById($id)) {
                return ['code' => -1, 'message' => '支付订单不存在'];
            }
            if ($row['status'] === 0) {
                //分站站长提成
                if ($info = json_decode($order['p_info'], true)) {
                    foreach ($info as $k => $v) {
                        if ($v > 0) {
                            if ($k == $row['zid']) {
                                User::rmb($k, '提成', $v * $order['num'], "用户下单提成[订单号：{$id}]");
                            } else {
                                User::rmb($k, '提成', $v * $order['num'], "下级站点[ZID:{$row['zid']}]用户下单提成[订单号：{$id}]");
                            }
                        }
                    }
                }

                Order::updateById($id, ['pay_status' => 1]);
                //订单对接
                orderDj($id);
            }
            return ['code' => 0, 'message' => '下单成功', 'type' => 0];
        }
    }
}