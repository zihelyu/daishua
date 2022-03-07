<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/15
 * Time: 20:50
 */

namespace app\index\model;


class Config extends Base
{
    protected $pk = 'vkey';

    /**
     * 查询所有
     * @return false|static[]
     */
    public static function selectAll()
    {
        $self = new static();
        return $self->whereNotIn('vkey', [
            'v_pass', 'v_sid'
        ])->select();
    }

    /**
     * 管理员登录
     * @param $user
     * @param $pass
     * @return bool
     */
    public static function login($user, $pass)
    {
        $self = new static();
        if (!$self->where('vkey', 'v_user')->where('value', $user)->find()) {
            return false;
        } elseif (!$self->where('vkey', 'v_pass')->where('value', encryptPassword($pass))->find()) {
            return false;
        }
        $sid = createSid();
        $self->where('vkey', 'v_sid')->update(['value' => $sid]);
        cookie('adminSid', $sid, 3600 * 24 * 7 + time());
        return true;
    }

    /**
     * 检测是否登录
     * @return bool
     */
    public static function checkLogin()
    {
        $self = new static();
        $sid = cookie('adminSid');
        if (!$sid || !$self->where('vkey', 'v_sid')->where('value', $sid)->find()) {
            return false;
        }
        return true;
    }

    /**
     * 更改管理员账号密码
     * @param $user
     * @param $pass
     * @return bool
     */
    public static function changeAdmin($user, $pass)
    {
        $self = new static();
        $pass = encryptPassword(trim($pass));
        $self->where('vkey', 'v_user')->update(['value' => trim($user)]);
        $self->where('vkey', 'v_pass')->update(['value' => $pass]);
        $sid = createSid();
        $self->where('vkey', 'v_sid')->update(['value' => $sid]);
        return true;
    }

    public static function changeClone()
    {
        $self = new static();
        $sid = createSid();
        $self->where('vkey', 'clone_key')->update(['value' => $sid]);
        return true;
    }

    /**
     * 退出登录
     * @return bool
     */
    public static function logout()
    {
        $self = new static();
        $sid = createSid();
        $self->where('vkey', 'v_sid')->update(['value' => $sid]);
        return true;
    }


}