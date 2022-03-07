<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/17
 * Time: 19:26
 */

namespace app\index\model;


class TxRecord extends Base
{
    protected $pk = 'id';

    /**
     * 申请提现
     * @param $uid
     * @param $rmb
     * @return bool
     */
    public static function add($uid, $rmb)
    {
        $rmb = sprintf('%.2f', $rmb);
        $sxf = sprintf('%.2f', config('sys_tx_rate') * $rmb);
        $sxf = ($sxf > 0) ? $sxf : 0;
        $_rmb = $sxf + $rmb;
        $self = new static();
        if (User::rmb($uid, '提现', 0 - $_rmb, "申请提现 {$rmb} 元")) {
            $self->insert([
                'uid' => $uid,
                'rmb' => $rmb,
                'sxf' => $sxf,
                'status' => 0,
                'add_time' => ['exp', 'now()']
            ]);
            return true;
        }
        return false;
    }

    /**
     * 查找提现记录列表
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
        $query = $self->alias('a')
            ->join('user b', 'a.uid=b.uid')
            ->field([
                'a.*',
                'b.tx_info'
            ]);
        if ($zid) {
            $query->where('a.uid', $zid);
        } else {
            $zid = input('post.zid');
            is_numeric($zid) && $query->where('a.uid', $zid);
        }

        $status = input("post.status");
        is_numeric($status) && $query->where('a.status', $status);

        $query2 = clone $query;
        return [
            'total' => $query2->count('a.id'),
            'list' => $query->page($page, $pageSize)->order('a.id desc')->select()
        ];
    }

    /**
     * 修改提现记录
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

    /**
     * 处理提现
     * @param $id
     * @param $status
     * @return bool
     */
    public static function changeStatus($id, $status)
    {
        $self = new static();
        if ($status === 2) {
            //驳回退款
            if (!$record = $self->where('id', $id)->find()) {
                return false;
            }
            if ($record['status'] !== 2) {
                User::rmb($record['uid'], '退款', $record['rmb'] + $record['sxf'], "管理员驳回你的退款申请[ID:{$record['id']}]");
            }
        }
        return ($self->where('id', $id)
                ->update([
                    'status' => $status,
                    'finish_time' => ['exp', 'now()']
                ]) !== false);

        return false;
    }
}