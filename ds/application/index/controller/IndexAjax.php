<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/4
 * Time: 13:19
 */

namespace app\index\controller;


use app\index\model\Config;
use app\index\model\Goods;
use app\index\model\Group;
use app\index\model\Order;
use app\index\model\PayOrder;
use app\index\model\User;
use app\index\model\WebRecord;

class IndexAjax extends Ajax
{
    public function getSSList()
    {
        $result['code'] = -1;
        $qq = input('post.qq');
        if (!$qq || !preg_match('/^\d{5,10}$/', $qq)) {
            $result['message'] = "QQ号码不正确";
        } else {
            $get = getCurl("http://sh.taotao.qq.com/cgi-bin/emotion_cgi_feedlist_v6?hostUin={$qq}&ftype=0&sort=0&pos=0&num=10&format=json");
            if ($ret = json_decode($get, true)) {
                if ($ret['code'] === 0) {
                    $list = [];
                    foreach ($ret['msglist'] as $v) {
                        $list[] = [
                            'id' => $v['tid'],
                            'title' => $v['content']
                        ];
                    }
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'list' => $list
                    ];
                } else {
                    $result['message'] = '请设置QQ空间所有可访问';
                }
            } else {
                $result['message'] = '网络繁忙，请稍后再试';
            }
        }
        return $result;
    }

    public function checkPay()
    {
        $result['code'] = -1;
        $oid = input('post.oid');
        if (!$oid || !$order = PayOrder::findByZidAndOid(ZID, $oid)) {
            $result['message'] = '订单不存在';
        } elseif ($order['status'] === 1) {
            $result = [
                'code' => 0,
                'message' => '支付完成'
            ];
        } else {
            $result['message'] = '未完成支付';
        }
        return $result;
    }

    public function getCloneGoods()
    {
        $result['code'] = -1;
        $key = input('post.key');
        if (strlen(config('sys_clone_key')) != 32) {
            $result['message'] = "不允许被克隆";
        } elseif (!$key || strlen($key) != 32 || config('sys_clone_key') != $key) {
            $result['message'] = "克隆密匙不正确";
        } else {
            $goods = Goods::all();
            foreach ($goods as $k => $v) {
                unset($goods[$k]['did']);
                unset($goods[$k]['post']);
            }
            $result = [
                'code' => 0,
                'message' => 'success',
                'goods' => $goods,
                'group' => Group::all()
            ];
        }
        return $result;
    }

    public function getCloneGG()
    {
        $result['code'] = -1;
        $key = input('post.key');
        if (strlen(config('web_clone_key')) != 32) {
            $result['message'] = "不允许被克隆";
        } elseif (!$key || strlen($key) != 32 || config('web_clone_key') != $key) {
            $result['message'] = "克隆密匙不正确";
        } else {
            $result = [
                'code' => 0,
                'message' => 'success',
                'data' => model('web')->where('zid', ZID)->field([
                    'gg_search', 'gg_search', 'gg_web', 'gg_notice', 'index_foot'
                ])->find(),
            ];
        }
        return $result;
    }

    public function search()
    {
        $result['code'] = -1;
        $value1 = input("post.value1");
        if (strlen($value1) < 2) {
            $result['message'] = "请输入正确的下单账号";
        } else {
            $_POST['pageSize'] = 10;
            $_POST['pay_status'] = 1;
            $select = Order::search(ZID);
            if (false === $select) {
                $result['message'] = '获取订单失败，请稍后再试';
            } elseif (is_string($select)) {
                $result['message'] = $select;
            } else {
                $list = [];
                foreach ($select['list'] as $v) {
                    $list[] = [
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'value1' => $v['value1'],
                        'status' => $v['status'],
                        'add_time' => $v['add_time'],
                        'num' => $v['num'],
                    ];
                }
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'total' => $select['total'],
                    'list' => $list
                ];
            }
        }
        return $result;
    }

    public function adminLogin()
    {
        $result['code'] = -1;
        $user = input('post.user');
        $pass = input('post.pass');
        $code = input('post.code/d');
        /*session_start();
        if (!$code || ($code != $_SESSION['vc_code'])) {
            $result['message'] = '验证码错误';
        } else
			*/if (strlen($user) < 3 || strlen($pass) < 5) {
            $result['message'] = '请输入正确账号和密码';
        } elseif (Config::login($user, $pass)) {
            $result = [
                'code' => 0,
                'message' => '登录成功'
            ];
        } else {
            $result['message'] = '账号或者密码不正确';
        }
        return $result;
    }

    public function login()
    {
        $result['code'] = -1;
        $user = input('post.user');
        $pass = input('post.pass');
        $code = input('post.code/d');
        /*
        session_start();
        if (!$code || ($code != $_SESSION['vc_code'])) {
            $result['message'] = '验证码错误';
        } else*/
        if (strlen($user) < 3 || strlen($pass) < 5) {
            $result['message'] = '请输入正确账号和密码';
        } elseif (User::login($user, $pass)) {
            $result = [
                'code' => 0,
                'message' => '登录成功'
            ];
        } else {
            $result['message'] = '账号或者密码不正确';
        }
        return $result;
    }

    public function createWeb()
    {
        $this->checkKind(1);
        $result['code'] = -1;
        $data = [
            'name' => input('post.name'),
            'qz' => strtolower(input("post.qz")),
            'domain' => input("post.domain"),
            'kind' => input("post.kind/d"),
            'user' => input("post.user"),
            'pass' => input("post.pass"),
            'qq' => input("post.qq"),
        ];
        $rule = [
            'user|站长账号' => 'require|length:5,18',
            'pass|站长密码' => 'require|min:6',
            'qq|联系QQ' => 'require|number|length:5,10',
            'name|站点名称' => 'require|length:2,15',
            'qz|域名前缀' => 'require|alphaDash|min:2',
            'kind|站点版本' => 'require|in:0,1'
        ];
        $validate = $this->validate($data, $rule);
        if (true !== $validate) {
            $result['message'] = $validate;
        } else {
            $data['domain'] = $data['qz'] . '.' . $data['domain'];
            $order = WebRecord::createOrder($data);
            if (is_array($order)) {
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'oid' => $order['oid'],
                    'rmb' => $order['rmb'],
                    'domain' => $order['domain']
                ];
            } elseif (true === $order) {
                $result = [
                    'code' => 1,
                    'message' => '开通分站成功',
                    'domain' => $data['domain']
                ];
            } else {
                $result['message'] = $order;
            }
        }
        return $result;
    }

    public function createOrder()
    {
        $result['code'] = -1;
        $goodsid = input('post.goodsid/d');
        if (!$goodsid || !$goods = Goods::findByGoodsid($goodsid)) {
            $result['message'] = '商品不存在';
        } else {
            $model = Goods::getGoodsModel($goods);
            $data = [
                'num' => input('post.num/d')
            ];
            $rules = [
                'num|下单数量' => 'require|number|gt:0'
            ];
            foreach ($model as $v) {
                $data[$v['id']] = input('post.' . $v['id']);
                $rules[$v['id'] . '|' . $v['name']] = Goods::getRule($v['rule']);
                if ($v['kind'] === 'QZONE') {
                    if (!checkQzone($data[$v['id']])) {
                        $this->error('请设置QQ空间所有可访问');
                    }
                }
            }
            $validate = $this->validate($data, $rules);
            if ($validate !== true) {
                $result['message'] = $validate;
            } else {
                $num = isset($data['num']) ? intval($data['num']) : 1;
                if ($num < 1) {
                    $result['message'] = '购买数量不能小于1';
                } elseif ($num < $goods['min'] || $num > $goods['max']) {
                    $result['message'] = "购买数量只能在{$goods['min']}-{$goods['max']}之间";
                } elseif ($goods['rate'] > 0 && ($num % $goods['rate'] != 0)) {
                    $result['message'] = "购买数量必须是{$goods['rate']}的倍数";
                } elseif ($goods['default'] && $num !== $goods['default']) {
                    $result['message'] = '下单数量错误';
                } else {
                    $order = Order::createOrder($goods, $data);
                    if (is_array($order)) {
                        $result = [
                            'code' => 0,
                            'message' => 'success',
                            'oid' => $order['oid'],
                            'rmb' => $order['rmb']
                        ];
                    } elseif (true === $order) {
                        $result = [
                            'code' => 1,
                            'message' => '下单成功'
                        ];
                    } else {
                        $result['message'] = $order;
                    }
                }
            }
        }
        return $result;
    }

    public function getGroupList()
    {
        return [
            'code' => 0,
            'message' => 'success',
            'list' => Group::selectAll()
        ];
    }

    public function getGoodsList()
    {
        $gid = input('post.gid/d');
        $list = Goods::selectByGid($gid, true);
        foreach ($list as $k => $v) {
            $priceInfo = Goods::getGoodsPriceInfo(ZID, $v, config('web_kind'));
            $v['price'] = $priceInfo['price'];
            $v['model'] = Goods::getGoodsModel($v);
            $list[$k] = $v;
        }
        return [
            'code' => 0,
            'message' => 'success',
            'list' => $list
        ];
    }

    public function count()
    {
        $result = [
            'code' => 0,
            'message' => 'success',
            'count' => [
                'order' => [
                    'all' => model('order')->where('pay_status', 1)->count('id'),
                    'today' => model('order')->where('pay_status', 1)->where('to_days(add_time) = to_days(now())')->count('id')
                ],

                'pay' => [
                    'all' => sprintf('%.2f', model('payOrder')->where('status', 1)->sum('rmb')),
                    'today' => sprintf('%.2f', model('payOrder')->where('status', 1)->where('to_days(add_time) = to_days(now())')->sum('rmb')),
                ]

            ]
        ];
        return $result;
    }


    protected function _initialize()
    {
        parent::_initialize();
    }

}