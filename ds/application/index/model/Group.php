<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/15
 * Time: 15:38
 */

namespace app\index\model;


class Group extends Base
{
    protected $pk = 'gid';

    /**
     * 查找所有分组
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

    /**
     * 查找商品分组列表
     * @return array
     */
    public static function search()
    {
        $page = input('post.page/d');
        $pageSize = input('post.pageSize/d');
        $page = ($page < 1) ? 1 : $page;
        $pageSize = ($pageSize < 1 || $pageSize > 50) ? 10 : $pageSize;
        $self = new static();
        $query = $self->alias('a');
        $query2 = clone $query;
        return [
            'total' => $query2->count('a.gid'),
            'list' => $query->page($page, $pageSize)->order('a.sort asc')->select()
        ];
    }

    /**
     * 删除分组
     * @param $gid
     * @return int
     */
    public static function delByGid($gid)
    {
        if (Goods::where('gid', $gid)->count() > 0) {
            return false;
        }
        $self = new static();
        return $self->where('gid', $gid)->delete();
    }

    /**
     * 商品分组排序
     * @param $gid
     * @param int $act
     * @return bool
     */
    public static function sort($gid, $act = 0)
    {
        $self = new static();
        if (!$group = $self->where('gid', $gid)->find()) {
            return false;
        }
        $sort = $group['sort'];
        switch ($act) {
            case 1://上移
                if ($row = $self->where('sort', '<', $sort)->order('sort desc')->find()) {
                    $self->where('gid', $gid)->update(['sort' => $row['sort']]);
                    $self->where('gid', $row['gid'])->update(['sort' => $sort]);
                    return true;
                }
                break;
            case 2://下移
                if ($row = $self->where('sort', '>', $sort)->order('sort asc')->find()) {
                    $self->where('gid', $gid)->update(['sort' => $row['sort']]);
                    $self->where('gid', $row['gid'])->update(['sort' => $sort]);
                    return true;
                }
                break;
            case 3://底部
                $row = $self->order('sort desc')->find();
                $self->where('sort', '>', $sort)->update([
                    'sort' => ['exp', 'sort-1']
                ]);
                $self->where('gid', $gid)->update(['sort' => $row['sort']]);
                return true;
                break;
            default://顶部
                $row = $self->order('sort asc')->find();
                $self->where('sort', '<', $sort)->update([
                    'sort' => ['exp', 'sort+1']
                ]);
                $self->where('gid', $gid)->update(['sort' => $row['sort']]);
                return true;
        }
        return false;
    }

    /**
     * 修改分组信息
     * @param $gid
     * @param array $data
     * @return bool
     */
    public static function updateByGid($gid, $data = [])
    {
        $self = new static();
        return ($self->where('gid', $gid)
                ->update($data) !== false);
    }

    /**
     * 添加分组
     * @param $name
     * @return bool
     */
    public static function add($name)
    {
        $self = new static();
        if ($gid = $self->insert(['name' => $name, 'status' => 1], false, true)) {
            $self->where('gid', $gid)->update(['sort' => $gid]);
            return true;
        }
        return false;
    }
}