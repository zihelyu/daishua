<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/4
 * Time: 13:19
 */

namespace app\index\controller;


use app\index\model\Config;
use app\index\model\Dj;
use app\index\model\Goods;
use app\index\model\Group;
use app\index\model\Order;
use app\index\model\PayOrder;
use app\index\model\RmbRecord;
use app\index\model\TxRecord;
use app\index\model\User;
use app\index\model\Web;
use klsf\dj\Dj95Sq;
use klsf\dj\DjKyx;
use klsf\dj\DjYile;
use think\Db;

class AdminAjax extends Ajax
{
    public function dj($action)
    {
        $result = [
            'code' => -1,
            'message' => '无此操作'
        ];
        switch ($action) {
            case 'all':
                $select = Dj::selectAll();
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'list' => $select
                ];
                break;
            case 'del':
                $did = input('post.did/d');
                if (Dj::delByDid($did)) {
                    $result = [
                        'code' => 0,
                        'message' => '删除成功'
                    ];
                } else {
                    $result['message'] = '删除失败，可能原因是该分组下还有商品';
                }
                break;
            case 'update':
                $did = input('post.did/d');
                $data = [
                    'kind' => trim(input('post.kind')),
                    'domain' => trim(input('post.domain')),
                    'user' => trim(input('post.user')),
                    'pass' => trim(input('post.pass')),
                ];
                if (!preg_match('/([a-z0-9]+\.)+[a-z0-9]+/', $data['domain'])) {
                    $result['message'] = '对接平台域名格式不正确';
                } elseif (strlen($data['user']) < 3) {
                    $result['message'] = '对接账号不正确';
                } else {
                    if (Dj::updateByDid($did, $data)) {
                        $result = [
                            'code' => 0,
                            'message' => '修改对接成功'
                        ];
                    } else {
                        $result['message'] = '修改对接失败，请稍后再试';
                    }
                }
                break;
                break;
            case 'add':
                $data = [
                    'kind' => trim(input('post.kind')),
                    'domain' => trim(input('post.domain')),
                    'user' => trim(input('post.user')),
                    'pass' => trim(input('post.pass')),
                ];
                if (!preg_match('/([a-z0-9]+\.)+[a-z0-9]+/', $data['domain'])) {
                    $result['message'] = '对接平台域名格式不正确';
                } elseif (strlen($data['user']) < 3) {
                    $result['message'] = '对接账号不正确';
                } else {
                    if (Dj::add($data)) {
                        $result = [
                            'code' => 0,
                            'message' => '添加对接成功'
                        ];
                    } else {
                        $result['message'] = '添加对接失败，请稍后再试';
                    }
                }
                break;
            case 'list':
                $select = Dj::search();
                if (false === $select) {
                    $result['message'] = '获取列表失败，请稍后再试';
                } elseif (is_string($select)) {
                    $result['message'] = $select;
                } else {
                    foreach ($select['list'] as $k => $v) {
                        unset($v['model']);
                        $select[$k] = $v;
                    }
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'total' => $select['total'],
                        'list' => $select['list']
                    ];
                }
                break;
            case 'getPost':
                $did = input('post.did/d');
                $data = [
                    'id' => input("post.id/d"),
                    'type' => input("post.type/d")
                ];
                if (!$dj = Dj::findByDid($did)) {
                    $result['message'] = '对接信息不存在';
                } else {
                    if ($data['id'] < 1) {
                        $result['message'] = '对接商品ID不能为空';
                    } else {
                        if ($dj['kind'] === 'ylsq') {
                            $info = DjYile::getPost($data, $dj['domain'], $dj['user'], $dj['pass']);
                        } elseif ($dj['kind'] === '95sq') {
                            $info = Dj95Sq::getPost($data, $dj['domain'], $dj['user'], $dj['pass']);
                        } elseif ($dj['kind'] === 'kyx') {
                            $url = input('post.url');
                            if (strlen($url) < 10) {
                                $this->error('请输入正确的商品下单地址');
                            }
                            $info = DjKyx::getPost(['url' => $url], $dj['domain'], $dj['user'], $dj['pass']);
                        } else {
                            $this->error('不支对接持此平台');
                        }
                        if ($info['code'] === 0) {
                            $result = [
                                'code' => 0,
                                'message' => 'success',
                                'post' => $info['post']
                            ];
                        } else {
                            $result['message'] = $info['message'];
                        }
                    }
                }
                break;
            case 'getGoods':
                $did = input('post.did/d');
                if (!$dj = Dj::findByDid($did)) {
                    $result['message'] = '对接信息不存在';
                } else {
                    if (strlen($dj['pass']) < 3) {
                        $result['message'] = '对接账号密码错误';
                    } else {
                        if ($dj['kind'] === 'ylsq') {
                            $info = DjYile::getDjGoods($dj['domain'], $dj['user'], $dj['pass']);
                        } elseif ($dj['kind'] === '95sq') {
                            $info = Dj95Sq::getDjGoods($dj['domain'], $dj['user'], $dj['pass']);
                        } else {
                            $this->error('不支对接持此平台');
                        }
                        if (is_array($info)) {
                            $result = [
                                'code' => 0,
                                'message' => 'success',
                                'list' => $info
                            ];
                        } else {
                            $result['message'] = $info;
                        }
                    }
                }
                break;
        }
        return $result;
    }

    public function changeAdmin()
    {
        $result['code'] = -1;
        $pass = trim(input('post.pass'));
        $user = trim(input('post.user'));
        if (strlen($pass) < 5) {
            $result['message'] = '密码太简单';
        } elseif (strlen($user) < 3) {
            $result['message'] = '用户名太短';
        } elseif (Config::changeAdmin($user, $pass)) {
            $result = [
                'code' => 0,
                'message' => '修改管理员账号成功'
            ];
        } else {
            $result['message'] = '修改管理员账号失败，请稍后再试';
        }
        return $result;
    }

    public function logout()
    {
        if (Config::logout()) {
            return [
                'code' => 0,
                'message' => '注销登录成功'
            ];
        } else {
            return [
                'code' => -1,
                'message' => '注销登录失败'
            ];
        }
    }


    public function web($action)
    {
        $result = [
            'code' => -1,
            'message' => '无此操作'
        ];
        switch ($action) {
            case 'pass':
                $zid = input('post.zid/d');
                $pass = input('post.pass');
                if (strlen($pass) < 5) {
                    $result['message'] = '新密码太简单';
                } elseif (User::updateByUid($zid, [
                    'sid' => createSid(),
                    'pass' => encryptPassword($pass)
                ])) {
                    $result = [
                        'code' => 0,
                        'message' => '修改密码成功'
                    ];
                } else {
                    $result['message'] = '修改密码失败';
                }
                break;
            case 'txRemark':
                $id = input('post.id/d');
                $remark = input('post.remark');
                if (TxRecord::updateById($id, ['remark' => $remark])) {
                    $result = [
                        'code' => 0,
                        'message' => '备注成功'
                    ];
                } else {
                    $result['message'] = '备注失败';
                }
                break;
            case 'txStatus':
                $id = input('post.id/d');
                $status = input('post.status/d');
                if (TxRecord::changeStatus($id, $status)) {
                    $result = [
                        'code' => 0,
                        'message' => 'success'
                    ];
                } else {
                    $result['message'] = '操作失败';
                }
                break;
            case 'txRecord':
                $select = TxRecord::search();
                if (false === $select) {
                    $result['message'] = '获取记录失败，请稍后再试';
                } elseif (is_string($select)) {
                    $result['message'] = $select;
                } else {
                    $list = $select['list'];
                    foreach ($list as $k => $v) {
                        $v['tx_info'] = explode("|#|", $v['tx_info']);
                        $list[$k] = $v;
                    }
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'total' => $select['total'],
                        'list' => $list
                    ];
                }
                break;
            case 'rmbRecord':
                $select = RmbRecord::search();
                if (false === $select) {
                    $result['message'] = '获取明细失败，请稍后再试';
                } elseif (is_string($select)) {
                    $result['message'] = $select;
                } else {
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'total' => $select['total'],
                        'list' => $select['list']
                    ];
                }
                break;
            case 'del':
                $zid = input('post.zid/d');
                if ($zid == 100) {
                    $result['message'] = '不可删除默认站点';
                } elseif (Web::delByZid($zid)) {
                    $result = [
                        'code' => 0,
                        'message' => '删除成功'
                    ];
                } else {
                    $result['message'] = '删除失败，请稍后再试';
                }
                break;
            case 'update':
                $zid = input('post.zid/d');
                $data = [
                    'name' => input('post.name'),
                    'domain' => trim(input('post.domain')),
                    'extra_domain' => trim(input('post.extra_domain')),
                    'kind' => input('post.kind/d')
                ];
                $checkDomain = Web::findByDomain($data['domain']);
                $checkDomain2 = $data['extra_domain'] ? Web::findByDomain($data['extra_domain']) : false;
                if (($checkDomain && $checkDomain['zid'] != $zid) || ($checkDomain2 && $checkDomain2['zid'] != $zid)) {
                    $result['message'] = '此域名已被其他站点绑定';
                } else {
                    if (Web::updateByZid($zid, $data)) {
                        $result = [
                            'code' => 0,
                            'message' => '修改成功'
                        ];
                    } else {
                        $result['message'] = '修改失败，请稍后再试';
                    }
                }
                break;
            case 'recharge':
                $do = input('post.do/d');
                $uid = input('post.uid/d');
                $rmb = input('post.rmb');
                $_rmb = $do ? 0 - $rmb : $rmb;
                $_act = $do ? '扣除' : '充值';
                if (!is_numeric($rmb) || $rmb <= 0) {
                    $result['message'] = '请输入正确的金额';
                } elseif (!$uid) {
                    $result['message'] = '请先选择要操作的站长';
                } elseif (User::rmb($uid, $_act, $_rmb, "管理员后台为你{$_act} {$rmb} 元")) {
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'rmb' => User::getRmbByUid($uid)
                    ];
                } else {
                    $result['message'] = '操作站长余额失败';
                }
                break;
            case 'change':
                $zid = input('post.zid/d');
                $act = input('post.act');
                $value = input('post.value/d');
                if (in_array($act, ['status', 'kind'])) {
                    if (Web::updateByZid($zid, [$act => $value])) {
                        $result = [
                            'code' => 0,
                            'message' => 'success'
                        ];
                    } else {
                        $result['message'] = '操作失败';
                    }
                }
                break;
            case 'add':
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
                    $web = Web::add($data);
                    if (is_numeric($web)) {
                        $result = [
                            'code' => 0,
                            'message' => '搭建分站成功',
                            'info' => [
                                'domain' => $data['domain'],
                                'name' => $data['name'],
                            ]
                        ];
                    } else {
                        $result['message'] = $web;
                    }
                }
                break;
            case 'list':
                $select = Web::search();
                if (false === $select) {
                    $result['message'] = '获取站点失败，请稍后再试';
                } elseif (is_string($select)) {
                    $result['message'] = $select;
                } else {
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'total' => $select['total'],
                        'list' => $select['list']
                    ];
                }
                break;
        }
        return $result;
    }

    public function order($action)
    {
        $result = [
            'code' => -1,
            'message' => '无此操作'
        ];
        switch ($action) {
            case 'tk':
                $id = input('post.id/d');
                $rmb = sprintf('%.2f', input('post.rmb'));
                if (!is_numeric($rmb) || $rmb < 0) {
                    $result['message'] = '退款金额不正确';
                } elseif (!$order = Order::findByIdWithGoods($id)) {
                    $result['message'] = '订单不存在';
                } elseif (!$order['is_tk']) {
                    $result['message'] = '此类商品不支持退款';
                } elseif ($order['status'] == 5) {
                    $result['message'] = '当前状态不支持退款';
                } elseif (Order::updateById($id, ['status' => 5, 'tk' => $rmb])) {
                    User::rmb($order['zid'], "退款", $rmb, "订单退款[订单ID:{$id}]");
                    $result = [
                        'code' => 0,
                        'message' => '退款成功',
                        'tk' => $rmb
                    ];
                } else {
                    $result['message'] = '退款成功，请稍后再试';
                }
                break;
            case 'dj':
                $id = input('post.id/d');
                $ret = orderDj($id, true);
                if ($ret['code'] === 0) {
                    $result = [
                        'code' => 0,
                        'message' => '对接成功',
                    ];
                } else {
                    $result['message'] = $ret['message'];
                }
                break;
            case 'plStatus':
                $ids = isset($_POST['ids']) ? $_POST['ids'] : null;
                if (!$ids) {
                    $result['message'] = '未选择订单';
                } else {
                    $status = input('post.status/d');
                    if (Order::changeStatus($ids, $status)) {
                        $result = [
                            'code' => 0,
                            'message' => '修改成功',
                            'status' => $status
                        ];
                    } else {
                        $result['message'] = '修改失败，请稍后再试';
                    }
                }
                break;
            case 'status':
                $id = input('post.id/d');
                $status = input('post.status/d');
                if (Order::changeStatus($id, $status)) {
                    $result = [
                        'code' => 0,
                        'message' => '修改成功',
                        'status' => $status
                    ];
                } else {
                    $result['message'] = '修改失败，请稍后再试';
                }
                break;
            case 'list':
                $select = Order::search();
                if (false === $select) {
                    $result['message'] = '获取订单失败，请稍后再试';
                } elseif (is_string($select)) {
                    $result['message'] = $select;
                } else {
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'total' => $select['total'],
                        'list' => $select['list']
                    ];
                }
                break;
        }
        return $result;
    }

    public function goods($action)
    {
        $result = [
            'code' => -1,
            'message' => '无此操作'
        ];
        switch ($action) {
            case 'groupAdd':
                $name = input('post.name');
                if (strlen($name) < 2) {
                    $result['message'] = '分组名称太简单';
                } else {
                    if (Group::add($name)) {
                        $result = [
                            'code' => 0,
                            'message' => '添加分组成功'
                        ];
                    } else {
                        $result['message'] = '添加分组失败，请稍后再试';
                    }
                }
                break;
            case 'groupUpdate':
                $gid = input('post.gid/d');
                $name = input('post.name');
                if (strlen($name) < 2) {
                    $result['message'] = '分组名称太简单';
                } else {
                    if (Group::updateByGid($gid, ['name' => $name])) {
                        $result = [
                            'code' => 0,
                            'message' => '修改成功'
                        ];
                    } else {
                        $result['message'] = '修改失败，请稍后再试';
                    }
                }
                break;
            case 'delGroup':
                $gid = input('post.gid/d');
                if (Group::delByGid($gid)) {
                    $result = [
                        'code' => 0,
                        'message' => '删除成功'
                    ];
                } else {
                    $result['message'] = '删除失败，可能原因是该分组下还有商品';
                }
                break;
            case 'groupSort':
                $gid = input('post.gid/d');
                $act = input('post.act/d');
                if (Group::sort($gid, $act)) {
                    $result = [
                        'code' => 0,
                        'message' => '操作成功'
                    ];
                } else {
                    $result['message'] = '操作失败';
                }
                break;
            case 'groupStatus':
                $gid = input('post.gid/d');
                $act = input('post.act');
                $value = input('post.value/d');
                if (in_array($act, ['status'])) {
                    if (Group::updateByGid($gid, [$act => $value])) {
                        $result = [
                            'code' => 0,
                            'message' => 'success'
                        ];
                    } else {
                        $result['message'] = '操作失败';
                    }
                }
                break;
            case 'del':
                $goodsid = input('post.goodsid/d');
                if (Goods::delByGoodis($goodsid)) {
                    $result = [
                        'code' => 0,
                        'message' => '删除成功'
                    ];
                } else {
                    $result['message'] = '删除失败，请稍后再试';
                }
                break;
            case 'sort':
                $goodsid = input('post.goodsid/d');
                $act = input('post.act/d');
                if (Goods::sort($goodsid, $act)) {
                    $result = [
                        'code' => 0,
                        'message' => '操作成功'
                    ];
                } else {
                    $result['message'] = '操作失败';
                }
                break;
            case 'change':
                $goodsid = input('post.goodsid/d');
                $act = input('post.act');
                $value = input('post.value/d');
                if (in_array($act, ['status'])) {
                    if (Goods::updateByGoodsid($goodsid, [$act => $value])) {
                        $result = [
                            'code' => 0,
                            'message' => 'success'
                        ];
                    } else {
                        $result['message'] = '操作失败';
                    }
                }
                break;
            case 'info':
                $goodsid = input('post.goodsid/d');
                if (!$goodsid || !$goods = Goods::findByGoodsid($goodsid, false)) {
                    $result['message'] = '商品不存在';
                } else {
                    $model = explode('|', $goods['model']);
                    $goods['input'] = $model[0];
                    unset($model[0]);
                    $goods['input_more'] = implode('|', $model);
                    $_blacklist = '';
                    if ($blacklist = explode('|', $goods['blacklist'])) {
                        foreach ($blacklist as $v) {
                            $_blacklist .= $v . "\n";
                        }
                    }
                    $goods['blacklist'] = $_blacklist;
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'info' => $goods
                    ];
                }
                break;
            case 'save':
                $goodsid = input('post.goodsid/d');
                $data = [
                    'name' => input('post.name'),
                    'gid' => input('post.gid/d'),
                    'desc' => input('post.desc'),
                    'price' => input('post.price'),
                    'unit' => input('post.unit/d'),
                    'did' => input('post.did/d'),
                    'default' => intval(input('post.default/d')),
                    'is_repeat' => input('post.is_repeat') ? 1 : 0,
                    'is_tk' => 0,
                    'api_status' => input('post.api_status/d'),
                    'post' => input('post.post'),
                    'min' => input('post.min/d'),
                    'max' => input('post.max/d'),
                    'rate' => input('post.rate/d'),
                    'max_num' => input('post.max_num/d')
                ];
                $data['rate'] = ($data['rate'] > 1) ? $data['rate'] : 1;
                if (!$data['did']) {
                    $data['post'] = null;
                }
                $rule = [
                    'name|商品名称' => 'require|min:2',
                    'price|销售价格' => 'require|float|egt:0',
                    'unit|每份数量' => 'require|number|egt:1',
                ];
                $validate = $this->validate($data, $rule);
                if (true !== $validate) {
                    $result['message'] = $validate;
                } else {
                    $input = input('post.input');
                    $input = $input ? $input : 'QQ号码';
                    $input .= '|' . input('post.input_more');

                    $input = explode('|', $input);
                    $model = [];
                    foreach ($input as $v) {
                        $v = trim($v);
                        if ($v) {
                            $model[] = $v;
                        }
                    }
                    if (count($model) < 1 || count($model) > 6) {
                        $result['message'] = '下单参数个数必须在1-6之间';
                    } else {
                        $model = implode('|', $model);
                        $data['model'] = $model;

                        //下单黑名单
                        $blacklist = input('post.blacklist');
                        $blacklist = explode("\n", $blacklist);
                        $_blacklist = [];
                        foreach ($blacklist as $v) {
                            $v = trim($v);
                            if (strlen($v) > 2) {
                                $_blacklist[] = $v;
                            }
                        }
                        $data['blacklist'] = implode('|', $_blacklist);

                        if ($goodsid) {
                            $ret = Goods::updateByGoodsid($goodsid, $data);
                            if ($ret === true) {
                                $result = [
                                    'code' => 0,
                                    'message' => '修改成功',
                                ];
                            } elseif (is_string($ret)) {
                                $result['message'] = $ret;
                            } else {
                                $result['message'] = '修改失败，请稍后再试';
                            }
                        } else {
                            $ret = Goods::add($data);
                            if (is_numeric($ret)) {
                                $result = [
                                    'code' => 0,
                                    'message' => '添加成功',
                                ];
                            } elseif (is_string($ret)) {
                                $result['message'] = $ret;
                            } else {
                                $result['message'] = '添加失败，请稍后再试';
                            }
                        }
                    }
                }
                break;
            case 'goodsAll':
                $select = Goods::selectAll(true);
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'list' => $select
                ];
                break;
            case 'list':
                $select = Goods::search();
                if (false === $select) {
                    $result['message'] = '获取商品失败，请稍后再试';
                } elseif (is_string($select)) {
                    $result['message'] = $select;
                } else {
                    foreach ($select['list'] as $k => $v) {
                        unset($v['model']);
                        $select[$k] = $v;
                    }
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'total' => $select['total'],
                        'list' => $select['list']
                    ];
                }
                break;
            case 'groupAll':
                $select = Group::selectAll(true);
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'list' => $select
                ];
                break;
            case 'groupList':
                $select = Group::search();
                if (false === $select) {
                    $result['message'] = '获取分组失败，请稍后再试';
                } elseif (is_string($select)) {
                    $result['message'] = $select;
                } else {
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'total' => $select['total'],
                        'list' => $select['list']
                    ];
                }
                break;
        }

        return $result;
    }

    public function payOrder($action)
    {
        $result = [
            'code' => -1,
            'message' => '无此操作'
        ];
        switch ($action) {
            case 'list':
                $select = PayOrder::search();
                if (false === $select) {
                    $result['message'] = '获取订单失败，请稍后再试';
                } elseif (is_string($select)) {
                    $result['message'] = $select;
                } else {
                    $result = [
                        'code' => 0,
                        'message' => 'success',
                        'total' => $select['total'],
                        'list' => $select['list']
                    ];
                }
                break;
        }

        return $result;
    }

    public function sys($action)
    {
        $result = [
            'code' => -1,
            'message' => '无此操作'
        ];
        switch ($action) {
            case 'clone':
                $key = trim(input('post.key'));
                $domain = trim(input('post.domain'));
                if (strlen($key) != 32) {
                    $result['message'] = '密匙不正确[1]';
                } else {
                    $url = "http://{$domain}" . url('IndexAjax/getCloneGoods');
                    $post = "key={$key}";
                    $ret = getCurl($url, $post);
                    if (!$ret = json_decode($ret, true)) {
                        $result['message'] = '克隆通讯失败[2]';
                    } elseif ($ret['code'] !== 0) {
                        $result['message'] = $ret['message'];
                    } else {
                        model('group')->where('gid', '>', 0)->delete();
                        model('goods')->where('goodsid', '>', 0)->delete();
                        model('group')->insertAll($ret['group']);
                        model('goods')->insertAll($ret['goods']);
                        $result = [
                            'code' => 0,
                            'message' => '克隆成功'
                        ];
                    }
                }
                break;
            case 'cloneKey':
                if (Config::changeClone()) {
                    $result = [
                        'code' => 0,
                        'message' => '更换密匙成功'
                    ];
                } else {
                    $result['message'] = '更换失败，请稍候再试';
                }
                break;
            case 'count':
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'count' => [
                        'order' => [
                            'all' => model('order')->where('pay_status', 1)->count('id'),
                            'status0' => model('order')->where('pay_status', 1)->where('status', 0)->count('id'),
                            'today' => model('order')->where('pay_status', 1)->where('to_days(add_time) = to_days(now())')->count('id')
                        ],
                        'web' => [
                            'all' => model('web')->count('zid'),
                            'today' => model('web')->where('to_days(add_time) = to_days(now())')->count('zid')
                        ],
                        'tx' => model('txRecord')->where('status', 0)->count('id'),
                        'pay' => [
                            'all' => sprintf('%.2f', model('payOrder')->where('status', 1)->sum('rmb')),
                            'today' => sprintf('%.2f', model('payOrder')->where('status', 1)->where('to_days(add_time) = to_days(now())')->sum('rmb')),
                            'yesterday' => sprintf('%.2f', model('payOrder')->where('status', 1)->where('to_days(now())-to_days(add_time)=1')->sum('rmb')),
                            'alipay' => sprintf('%.2f', model('payOrder')->where('status', 1)->where('pay_type', 'alipay')->sum('rmb')),
                            'wxpay' => sprintf('%.2f', model('payOrder')->where('status', 1)->where('pay_type', 'wxpay')->sum('rmb')),
                            'qqpay' => sprintf('%.2f', model('payOrder')->where('status', 1)->where('pay_type', 'qqpay')->sum('rmb')),
                        ]

                    ]
                ];
                break;
            case 'config':
                $sql = "INSERT INTO " . config('database')['prefix'] . "config SET `vkey`=?,`value`=? ON DUPLICATE KEY UPDATE `value`=?";
                foreach ($_POST as $k => $value) {
                    Db::execute($sql, [$k, $value, $value]);
                }
                $result = [
                    'code' => 0,
                    'message' => '保存成功'
                ];
                break;
        }

        return $result;
    }

    private function checkLogin()
    {
        if (!Config::checkLogin()) {
            $this->error('请先登录', url('index/adminLogin'));
        }
    }

    protected function _initialize()
    {
        parent::_initialize();
        $this->checkLogin();
    }

}