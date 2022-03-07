<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/5
 * Time: 19:41
 */

namespace app\index\model;


class Order extends Base
{
    protected $pk = 'id';

    /**
     * 创建订单
     * @param $goods
     * @param array $data
     * @param bool $isWeb
     * @return array|string
     */
    public static function createOrder($goods, $data = [], $isWeb = false)
    {
        $insert = [
            'zid' => ZID,
            'goodsid' => $goods['goodsid'],
            'status' => 0,
            'pay_status' => 0,
            'add_time' => ['exp', 'now()'],
            'value1' => isset($data['value1']) ? trim($data['value1']) : null,
            'value2' => isset($data['value2']) ? trim($data['value2']) : null,
            'value3' => isset($data['value3']) ? trim($data['value3']) : null,
            'value4' => isset($data['value4']) ? trim($data['value4']) : null,
            'value5' => isset($data['value5']) ? trim($data['value5']) : null,
            'value6' => isset($data['value6']) ? trim($data['value6']) : null,
            'unit' => $goods['unit'] ? $goods['unit'] : 1,
            'num' => isset($data['num']) ? intval($data['num']) : 1,
            'ip' => request()->ip()
        ];
        $self = new static();
        $blacklist = explode('|', $goods['blacklist']);
        if (in_array($insert['value1'], $blacklist)) {
            return '对不起，禁止下单！';
        }
        if (!$goods['is_repeat'] && $self->where('value1', $insert['value1'])
                ->where('goodsid', $insert['goodsid'])
                ->where('pay_status', 1)
                ->where('to_days(add_time) = to_days(now())')
                //->whereNotIn('status', ['90', '2'])
                ->find()) {
            return '不允许重复下单';
        }
        if ($goods['max_num']) {
            $max_num = $self->where('value1', $insert['value1'])
                ->where('goodsid', $insert['goodsid'])
                ->where('pay_status', 1)
                ->count();
            if ($max_num >= $goods['max_num']) {
                return "最多只能购买{$goods['max_num']}次";
            }
        }
        if ($goods['price'] == 0 && $self->where('ip', request()->ip())
                ->where('goodsid', $insert['goodsid'])
                ->where('to_days(add_time) = to_days(now())')
                ->where('pay_status', 1)
                ->find()) {
            //0元商品
            return '此商品一天只能下单一次';
        }

        if ($isWeb) {
            $priceInfo = Goods::getGoodsPriceInfo(ZID, $goods, config('web_kind'), true);
            $insert['price'] = $priceInfo['price'];
            $insert['p_info'] = json_encode($priceInfo['info']);
        } else {
            $priceInfo = Goods::getGoodsPriceInfo(ZID, $goods, config('web_kind'));
            $insert['price'] = $priceInfo['price'];
            $insert['p_info'] = json_encode($priceInfo['info']);
        }

        if ($insert['price'] == 0) {
            $insert['pay_status'] = 1;
            if ($id = $self->insert($insert, false, true)) {
                orderDj($id);
                return true;
            } else {
                return false;
            }
        } elseif ($isWeb) {
            if (!User::rmb(ZID, '消费', 0 - $insert['price'] * $insert['num'], "站长后台下单【商品ID:{$goods['goodsid']}】(内容:{$data['value1']})")) {
                return "站长余额不足 {$insert['price']} 元";
            } else {
                $insert['pay_status'] = 1;
                if ($id = $self->insert($insert, false, true)) {
                    if (!config('web_kind')) {
                        //上级专业版分站站长提成
                        if ($info = json_decode($insert['p_info'], true)) {
                            foreach ($info as $k => $v) {
                                if ($v > 0) {
                                    if ($k != $insert['zid']) {
                                        User::rmb($k, '提成', $v * $insert['num'], "下级站点[ZID:{$insert['zid']}]用户下单提成[订单号：{$id}]");
                                    }
                                }
                            }
                        }
                    }
                    orderDj($id);
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            if ($id = $self->insert($insert, false, true)) {
                $oid = date("ymdHis") . str_pad($id, 7, 0, STR_PAD_LEFT) . rand(10000, 99999);
                $insert = [
                    'oid' => $oid,
                    'id' => $id,
                    'zid' => ZID,
                    'rmb' => $insert['price'] * $insert['num'],
                    'status' => 0,
                    'add_time' => ['exp', 'now()']
                ];
                if (PayOrder::insert($insert)) {
                    return [
                        'id' => $id,
                        'oid' => $oid,
                        'rmb' => $insert['rmb'],
                    ];
                } else {
                    $self->where('id', $id)->delete();
                    return '创建支付订单失败';
                }
            } else {
                return '保存订单失败';
            }
        }
    }

    /**
     * 查找订单列表
     * @param null $zid
     * @return array
     */
    public static function search($zid = null)
    {
        $page = input('post.page/d');
        $pageSize = intval(isset($_POST['pageSize']) ? $_POST['pageSize'] : 10);
        $page = ($page < 1) ? 1 : $page;
        $pageSize = ($pageSize < 1 || $pageSize > 500) ? 10 : $pageSize;
        $self = new static();
        $query = $self->alias('a')
            ->join('goods b', 'a.goodsid=b.goodsid')
            ->field([
                'a.*',
                'b.name',
                'b.did',
                'b.is_tk'
            ]);

        if ($zid) {
            $query->where('a.zid', $zid);
        } else {
            $zid = input('post.zid');
            is_numeric($zid) && $query->where('a.zid', $zid);
        }
        $id = input("post.id");
        $id && $query->where('a.id', $id);
        $value1 = input("post.value1");
        $value1 && $query->where('a.value1', $value1);
        $status = input("post.status");
        is_numeric($status) && $query->where('a.status', $status);
        $pay_status = isset($_POST['pay_status']) ? $_POST['pay_status'] : 1;
        is_numeric($pay_status) && $query->where('a.pay_status', $pay_status);
        $goodsid = input("post.goodsid");
        is_numeric($goodsid) && $query->where('a.goodsid', $goodsid);

        $query2 = clone $query;
        return [
            'total' => $query2->count('a.id'),
            'list' => $query->page($page, $pageSize)->order('a.id desc')->select()
        ];
    }

    /**
     * 修改订单状态
     * @param $id
     * @param $status
     * @return bool
     */
    public static function changeStatus($id, $status)
    {
        $self = new static();
        if ($status === 2) {
            //退单
            if (is_numeric($id)) $id = [$id];
            foreach ($id as $v) {
                if ($row = $self->where('id', $v)->where('pay_status', 1)->where('status', 'neq', 2)->find()) {
                    $price = $row['price'];
                    if ($info = json_decode($row['p_info'], true)) {
                        foreach ($info as $k => $v) {
                            $price -= $v;
                        }
                    }
                    User::rmb($row['zid'], '退款', $price * $row['num'], "管理员退单商品订单[订单号:{$row['id']}]");
                }
            }
        }

        if (is_numeric($id)) {
            return ($self->where('id', $id)
                    ->update(['status' => $status]) !== false);
        } elseif (is_array($id)) {
            return ($self->whereIn('id', $id)
                    ->update(['status' => $status]) !== false);
        }
        return false;
    }

    /**
     * 查找多个订单
     * @param $ids
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function selectByIds($ids)
    {
        $self = new static();
        return $self->whereIn('id', $ids)
            ->order('id desc')
            ->select();
    }

    /**
     * 查找商品下所有订单
     * @param $goodsid
     * @param int $status
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function selectByGoodsidAndStatus($goodsid, $status = 0)
    {
        $self = new static();
        return $self->whereIn('goodsid', $goodsid)
            ->where('status', $status)
            ->where('pay_status', 1)
            ->order('id desc')
            ->select();
    }

    /**
     * 更改订单
     * @param $goodsid
     * @param int $status
     * @param array $data
     * @return $this
     */
    public static function updateByGoodsidAndStatus($goodsid, $status = 0, $data = [])
    {
        $self = new static();
        return $self->whereIn('goodsid', $goodsid)
            ->where('status', $status)
            ->where('pay_status', 1)
            ->update($data);
    }

    /**
     * 查找订单
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function findById($id)
    {
        $self = new static();
        return $self->where('id', $id)->find();
    }

    /**
     * 获取订单包含商品信息
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function findByIdWithGoods($id)
    {
        $self = new static();
        return $self->alias('a')
            ->join('goods b', 'a.goodsid=b.goodsid')
            ->field([
                'a.*',
                'b.name',
                'b.did',
                'b.post',
                'b.api_status',
                'b.is_tk'
            ])
            ->where('a.id', $id)
            ->where('a.pay_status', 1)
            ->find();
    }

    /**
     * 修改订单
     * @param $id
     * @param array $data
     * @return bool
     */
    public static function updateById($id, $data = [])
    {
        $self = new static();
        return ($self->where('id', $id)
                ->update($data) !== false);
    }

}