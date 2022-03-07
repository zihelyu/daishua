<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/19
 * Time: 9:48
 */

namespace app\index\model;


class Dj extends Base
{
    protected $pk = 'goodsid';

    public static function selectAll()
    {
        $self = new static();
        return $self->all();
    }

    /**
     * 获取对接信息列表
     * @return array
     */
    public static function search()
    {
        $self = new static();
        $page = input('post.page/d');
        $pageSize = input('post.pageSize/d');
        $page = ($page < 1) ? 1 : $page;
        $pageSize = ($pageSize < 1 || $pageSize > 50) ? 10 : $pageSize;

        $query = $self->alias('a');
        $query2 = clone $query;
        return [
            'total' => $query2->count('a.did'),
            'list' => $query->page($page, $pageSize)->order('a.did desc')->select()
        ];
    }

    /**
     * 查找对接信息
     * @param $goodsid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function findByDid($did)
    {
        $self = new static();
        return $self->where('did', $did)->find();
    }

    /**
     * 添加对接信息
     * @param array $data
     * @return int|string
     */
    public static function add($data = [])
    {
        $insert = [
            'kind' => $data['kind'],
            'domain' => $data['domain'],
            'user' => $data['user'],
            'pass' => $data['pass']
        ];
        $self = new static();
        return $self->insert($insert);
    }

    /**
     * 修改对接信息
     * @param $did
     * @param array $data
     * @return bool
     */
    public static function updateByDid($did, $data = [])
    {
        $self = new static();
        return ($self->where('did', $did)
                ->update($data) !== false);
    }

    /**
     * 删除对接
     * @param $did
     * @return bool
     */
    public static function delByDid($did)
    {
        $self = new static();
        if ($self->where('did', $did)->delete()) {
            Goods::where('did', $did)->update([
                'did' => 0,
                'post' => null
            ]);
            return true;
        }
        return false;
    }

}