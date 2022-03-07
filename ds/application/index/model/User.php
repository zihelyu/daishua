<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/4
 * Time: 12:50
 */

namespace app\index\model;


use think\exception\PDOException;

class User extends Base
{
    protected $pk = "uid";

    /**
     * 获取登录用户
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */
    public static function getLoginUser()
    {
        $self = new static();
        $sid = cookie('userSid');
        if (!$sid || !$userInfo = $self->where('sid', $sid)->where('uid', ZID)->find()) {
            return false;
        } else {
            return $userInfo;
        }
    }

    /**
     * 用户添加
     * @param array $data
     * @return bool|int|string
     */
    public static function add($data = [])
    {
        $insert = [
            'user' => $data['user'],
            'pass' => encryptPassword($data['pass']),
            'sid' => createSid(),
            'rmb' => 0,
            'status' => 1,
            'add_time' => ['exp', 'now()']
        ];
        $self = new static();
        try {
            if ($uid = $self->insert($data, false, true)) {
                return $uid;
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        return false;
    }

    /**
     * 用户登录
     * @param array $data
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */
    public static function login($user, $pass)
    {
        $self = new static();
        if ($user = $self->where('uid', ZID)
            ->where('user', $user)
            ->where('pass', encryptPassword($pass))
            ->find()) {
            $sid = createSid();
            $user->save(['sid' => $sid]);
            cookie('userSid', $sid, 3600 * 24 * 7 + time());
            return $user;
        }
        return false;
    }


    /**
     * 站长余额记录
     * @param $uid
     * @param $action
     * @param $value
     * @param null $remark
     * @return bool
     */
    public static function rmb($uid, $action, $value, $remark = null)
    {
        $self = new static();
        if (!is_numeric($value)) return false;
        if ($user = $self->where('uid', $uid)->field('rmb')->find()) {
            if ($value < 0 && abs($value) > $user['rmb']) {
                return false;
            }
            $now = $user['rmb'] + $value;
            $update = $user->save(['rmb' => ['exp', 'rmb + ' . $value]]);
            if (false !== $update && $update > 0) {
                RmbRecord::record([
                    'uid' => $uid,
                    'action' => $action,
                    'value' => $value,
                    'now' => $now,
                    'remark' => $remark
                ]);
                return true;
            }
        }
        return false;
    }

    /**
     * 获取用户余额
     * @param $uid
     * @return float
     */
    public static function getRmbByUid($uid)
    {
        $self = new static();
        if ($user = $self->where('uid', $uid)->field('rmb')->find()) {
            return $user['rmb'];
        }
        return 0.00;
    }

    /**
     * 删除用户
     * @param $uid
     * @return int
     */
    public static function delByUid($uid)
    {
        $self = new static();
        return $self->where('uid', $uid)->delete();
    }

    /**
     * 修改账号信息
     * @param $uid
     * @param array $data
     * @return bool
     */
    public static function updateByUid($uid, $data = [])
    {
        $self = new static();
        return ($self->where('uid', $uid)
                ->update($data) !== false);
    }

}