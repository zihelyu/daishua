<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/15
 * Time: 15:25
 */

namespace app\index\model;


class Web extends Base
{
    protected $pk = 'zid';

    /**
     * 通过域名获取分站
     * @param $domain
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function findByDomain($domain)
    {
        $self = new static();
        return $self->where('domain', $domain)
            ->whereOr('extra_domain', $domain)
            ->find();
    }

    /**
     * 查找分站列表
     * @param null $rid
     * @return array
     */
    public static function search($rid = null)
    {
        $page = input('post.page/d');
        $pageSize = input('post.pageSize/d');
        $page = ($page < 1) ? 1 : $page;
        $pageSize = ($pageSize < 1 || $pageSize > 50) ? 10 : $pageSize;
        $self = new static();
        $query = $self->alias('a')
            ->join('user b', 'a.uid=b.uid')
            ->field([
                'a.*',
                'b.user',
                'b.rmb'
            ]);
        if ($rid) {
            $query->where('a.rid', $rid)->where('a.kind', 0);
        }

        $zid = input('post.zid');
        is_numeric($zid) && $query->where('a.zid', $zid);
        $status = input("post.status");
        is_numeric($status) && $query->where('a.status', $status);
        $domain = input("post.domain");
        $domain && $query->where(function ($query) use ($domain) {
            $query->where('a.domain', $domain)
                ->whereOr('a.extra_domain', $domain);
        });

        $query2 = clone $query;
        return [
            'total' => $query2->count('a.zid'),
            'list' => $query->page($page, $pageSize)->order('a.zid desc')->select()
        ];
    }

    /**
     * 创建站点
     * @param array $data
     * @param int $rid
     * @param bool $useRmb
     * @return bool|string
     */
    public static function add($data = [], $rid = 0, $useRmb = false)
    {
        $self = new static();
        $price = config('sys_price_web_' . config('web_kind') . '_' . $data['kind']);

        if ($self->findByDomain($data['domain'])) {
            return '此域名已被使用';
        } elseif ($useRmb && !$price) {
            return '获取价格错误';
        } elseif ($useRmb && !User::rmb($rid, "消费", 0 - $price, "搭建分站【{$data['domain']}】")) {
            return "账户余额不足 {$price} 元";
        }
        $insert = [
            'user' => $data['user'],
            'pass' => encryptPassword($data['pass']),
            'sid' => createSid(),
            'rmb' => 0,
            'status' => 1,
            'add_time' => ['exp', 'now()']
        ];
        if ($uid = User::insert($insert, false, true)) {
            $insert = [
                'zid' => $uid,
                'uid' => $uid,
                'rid' => $rid,
                'kind' => $data['kind'],
                'name' => $data['name'],
                'status' => 1,
                'qq' => $data['qq'],
                'domain' => $data['domain'],
                'title' => $data['name'],
                'keywords' => $data['name'],
                'description' => $data['name'],
                'add_time' => ['exp', 'now()']
            ];
            if ($web = $self->where('zid', 100)->find()) {
                $insert['gg_index'] = $web['gg_index'];
                $insert['gg_search'] = $web['gg_search'];
                $insert['gg_web'] = $web['gg_web'];
                $insert['gg_notice'] = $web['gg_notice'];
                $insert['index_foot'] = $web['index_foot'];
                $insert['index_dialog'] = $web['index_dialog'];
                $insert['logo'] = $web['logo'];
                $insert['bg_image'] = $web['bg_image'];
                $insert['is_tz'] = $web['is_tz'];
            }
            if ($zid = $self->insert($insert, false, true)) {
                return $zid;
            } else {
                User::delByUid($uid);
                $useRmb && User::rmb($rid, "退款", $price, "搭建分站失败");
                return '创建站点失败';
            }
        } else {
            $useRmb && User::rmb($rid, "退款", $price, "搭建分站失败");
            return '创建站长账号失败';
        }
    }

    /**
     * 修改站点
     * @param $zid
     * @param array $data
     * @return bool
     */
    public static function updateByZid($zid, $data = [])
    {
        $self = new static();
        return ($self->where('zid', $zid)
                ->update($data) !== false);
    }

    /**
     * 删除站点
     * @param $zid
     * @return int
     */
    public static function delByZid($zid)
    {
        $self = new static();
        return $self->where('zid', $zid)->delete();
    }

    /**
     * 查找站点版本
     * @param $zid
     * @return bool|mixed
     */
    public static function getKindByZid($zid)
    {
        $self = new static();
        if ($web = $self->where('zid', $zid)->field('kind')->find()) {
            return $web['kind'];
        }
        return false;
    }

    /**
     * 查找上级站点
     * @param $zid
     * @return bool|mixed
     */
    public static function getRidByZid($zid)
    {
        $self = new static();
        if ($web = $self->where('zid', $zid)->field(['kind', 'rid'])->find()) {
            if ($web['kind'] == 2) {
                return $zid;
            } else {
                return $self->getRidByZid($web['rid']);
            }
        }
        return false;
    }


    public static function getPayRank()
    {
        $self = new self();
        return $self->alias('a')
            ->join('pay_order b', 'a.zid=b.zid')
            ->where('b.status', 1)
            ->field([
                'a.zid', 'a.name', 'a.kind',
                'sum(b.rmb)' => 'total'
            ])
            ->group('a.zid')
            ->order('total desc')
            ->limit(0, 10)
            ->select();
    }

    public static function getOrderRank()
    {
        $self = new self();
        return $self->alias('a')
            ->join('order b', 'a.zid=b.zid')
            ->where('b.pay_status', 1)
            ->field([
                'a.zid', 'a.name', 'a.kind',
                'count(b.id)' => 'total'
            ])
            ->group('a.zid')
            ->order('total desc')
            ->limit(0, 10)
            ->select();
    }
}