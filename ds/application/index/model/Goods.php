<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/5
 * Time: 19:40
 */

namespace app\index\model;


class Goods extends Base
{
    protected $pk = 'goodsid';

    /**
     * 获取商品信息
     * @param $goodsid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function findByGoodsid($goodsid, $isAvailable = true)
    {
        $self = new static();
        $query = $self->where('goodsid', $goodsid);
        if ($isAvailable) {
            $query->where('status', 1);
        }
        return $query->find();
    }

    /**
     * 获取分组下面所有商品
     * @param $gid
     * @param int $status
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function selectByGidAndStatus($gid, $status = 1)
    {
        $self = new static();
        return $self->where('gid', $gid)
            ->where('status', $status)
            ->order('sort asc')
            ->select();
    }

    /**
     * 查找所有商品
     * @param bool $isAvailable 是否要求可用
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function selectAll($isAvailable = true)
    {
        $self = new static();
        if ($isAvailable) {
            return $self->where('status', 1)
                ->order('sort asc')
                ->select();
        } else {
            return $self->order('sort asc')->select();
        }
    }

    public static function selectByGid($gid, $isAvailable = true)
    {
        $self = new static();
        if ($isAvailable) {
            return $self->where('status', 1)
                ->where('gid', $gid)
                ->order('sort asc')
                ->select();
        } else {
            return $self->order('sort asc')->select();
        }
    }

    /**
     * 获取商品下单模型
     * @param $goods
     * @return array
     */
    public static function getGoodsModel($goods)
    {
        $model = [];
        $m = explode('|', $goods['model']);
        foreach ($m as $k => $v) {
            $kind = $v;
            if ($v === 'KSID') {
                $v = '快手ID';
            } elseif ($v === 'KSZPID') {
                $v = '快手作品ID';
            } elseif ($v === 'QMGQID') {
                $v = '全名歌曲ID';
            } elseif ($v === 'QZONE') {
                $v = 'QQ号码';
            }
            $model[] = [
                'id' => 'value' . ($k + 1),
                'name' => $v,
                'tips' => "输入{$v}",
                'rule' => "require",
                'type' => 'text',
                'kind' => $kind
            ];
        }
        return $model;
    }

    /**
     * 获取下单规则
     * @param $rule
     * @return string
     */
    public static function getRule($rule)
    {
        switch ($rule) {
            case 'ksid':
                return 'require|alphaDash|min:3';
            case 'ksspid':
                return 'require|number|min:9';
            case 'dyid':
                return 'require|alphaDash|min:3';
            case 'dyspid':
                return 'require|number|length:19';
            case 'qmid':
                return 'require|alphaDash|min:5';
            case 'qmspid':
                return 'require|alphaDash|min:12';
            case 'hsid':
                return 'require|alphaDash|min:9';
            case 'hsspid':
                return 'require|number|length:19';
            case 'qqssid':
                return 'require|alphaDash|min:15';
            default:
                return $rule;
        }
    }

    /**
     * 查找商品列表
     * @param bool $isAvailable 是否只显示可用
     * @return array
     */
    public static function search($isAvailable = false)
    {
        $page = input('post.page/d');
        $pageSize = input('post.pageSize/d');
        $page = ($page < 1) ? 1 : $page;
        $pageSize = ($pageSize < 1 || $pageSize > 50) ? 10 : $pageSize;
        $self = new static();

        $query = $self->alias('a')
            ->join('group b', 'a.gid=b.gid')
            ->field([
                'a.*',
                'b.name' => 'group_name'
            ]);
        if ($isAvailable) {
            $query->where('a.status', 1);
        } else {
            $status = input("post.status");
            is_numeric($status) && $query->where('a.status', $status);
        }
        $gid = input("post.gid");
        is_numeric($gid) && $query->where('a.gid', $gid);

        $query2 = clone $query;
        return [
            'total' => $query2->count('a.gid'),
            'list' => $query->page($page, $pageSize)->order('a.sort asc')->select()
        ];
    }

    /**
     * 修改商品信息
     * @param $goodsid
     * @param array $data
     * @return bool
     */
    public static function updateByGoodsid($goodsid, $data = [])
    {
        if (isset($data['did']) && $data['did'] > 0) {
            if (!isset($data['post']) || strlen($data['post']) < 5) {
                return '对接POST不正确';
            }
        }
        $self = new static();
        return ($self->where('goodsid', $goodsid)
                ->update($data) !== false);
    }

    /**
     * 添加商品
     * @param array $data
     * @return bool|int|string
     */
    public static function add($data = [])
    {
        $insert = [
            'name' => trim($data['name']),
            'gid' => intval($data['gid']),
            'desc' => trim($data['desc']),
            'price' => sprintf("%.2f", $data['price']),
            'unit' => intval($data['unit']),
            'add_time' => ['exp', 'now()'],
            'model' => $data['model'],
            'did' => $data['did'],
            'post' => $data['post'],
            'default' => $data['default'],
            'is_repeat' => $data['is_repeat']
        ];
        if (isset($data['did']) && $data['did'] > 0) {
            if (!isset($data['post']) || strlen($data['post']) < 5) {
                return '对接POST不正确';
            }
        }
        $self = new static();
        if ($goodsid = $self->insert($insert, false, true)) {
            $self->where('goodsid', $goodsid)->update(['sort' => $goodsid]);
            return $goodsid;
        }
        return false;
    }

    /**
     * 商品排序
     * @param $goodsid
     * @param int $act
     * @return bool
     */
    public static function sort($goodsid, $act = 0)
    {
        $self = new static();
        if (!$goods = $self->findByGoodsid($goodsid, false)) {
            return false;
        }
        $gid = $goods['gid'];
        $sort = $goods['sort'];
        switch ($act) {
            case 1://上移
                if ($row = $self->where('gid', $gid)->where('sort', '<', $sort)->order('sort desc')->find()) {
                    $self->where('goodsid', $goodsid)->update(['sort' => $row['sort']]);
                    $self->where('goodsid', $row['goodsid'])->update(['sort' => $sort]);
                    return true;
                }
                break;
            case 2://下移
                if ($row = $self->where('gid', $gid)->where('sort', '>', $sort)->order('sort asc')->find()) {
                    $self->where('goodsid', $goodsid)->update(['sort' => $row['sort']]);
                    $self->where('goodsid', $row['goodsid'])->update(['sort' => $sort]);
                    return true;
                }
                break;
            case 3://底部
                $row = $self->order('sort desc')->find();
                $self->where('sort', '>', $sort)->update([
                    'sort' => ['exp', 'sort-1']
                ]);
                $self->where('goodsid', $goodsid)->update(['sort' => $row['sort']]);
                return true;
                break;
            default://顶部
                $row = $self->order('sort asc')->find();
                $self->where('sort', '<', $sort)->update([
                    'sort' => ['exp', 'sort+1']
                ]);
                $self->where('goodsid', $goodsid)->update(['sort' => $row['sort']]);
                return true;
        }
        return false;
    }

    /**
     * 删除商品
     * @param $goodsid
     * @return int
     */
    public static function delByGoodis($goodsid)
    {
        $self = new static();
        return $self->where('goodsid', $goodsid)->delete();
    }

    public static function getGoodsPriceInfo($zid, $goods, $kind = 0, $isAdmin = false)
    {
        $info = [];
        $price = $goods['price'];
        if (!$isAdmin) {
            $zz_price = WebGoods::getWebPrice($zid, $goods);
            if ($zz_price > 0) {
                $info[$zid] = $zz_price;
                $price += $zz_price;
            }
        }
        if ($kind != 2) {
            $re_price = WebGoods::getReWebPrice($zid, $goods);
            if ($re_price['price'] > 0) {
                $info[$re_price['zid']] = $re_price['price'];
                $price += $re_price['price'];
            }
        }
        return [
            'price' => $price,
            'info' => $info
        ];
    }

}