<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/6
 * Time: 9:06
 */

namespace app\index\model;


use think\Model;

class RmbRecord extends Model
{
    protected $pk = 'id';

    /**
     * 记录余额变动明细
     * @param array $data
     * @return bool
     */
    public static function record($data = [])
    {
        $insert = [
            'uid' => $data['uid'],
            'action' => $data['action'] ? $data['action'] : '消费',
            'value' => $data['value'],
            'now' => $data['now'],
            'remark' => $data['remark'],
            'add_time' => ['exp', 'now()']
        ];
        $self = new static();
        if ($self->insert($insert, false, true)) {
            return true;
        }
        return false;
    }

    /**
     * 获取余额明细记录列表
     * @param null $zid
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
            $query->where('a.uid', $zid);
        } else {
            $zid = input('post.zid');
            is_numeric($zid) && $query->where('a.uid', $zid);
        }

        $action = input("post.action");
        $action && $query->where('a.action', $action);

        $query2 = clone $query;
        return [
            'total' => $query2->count('a.id'),
            'list' => $query->page($page, $pageSize)->order('a.id desc')->select()
        ];
    }
}