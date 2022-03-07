<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/17
 * Time: 15:49
 */

namespace app\index\controller;


use app\index\model\Goods;
use app\index\model\Group;
use app\index\model\Order;
use app\index\model\RmbRecord;
use app\index\model\TxRecord;
use app\index\model\User;
use app\index\model\Web;
use app\index\model\WebGoods;

class HomeAjax extends Ajax
{
    private $userInfo;

    public function count()
    {
        $result = [
            'code' => 0,
            'message' => 'success',
            'count' => [
                'order' => [
                    'all' => model('order')->where('pay_status', 1)->where('zid', ZID)->count('id'),
                    'today' => model('order')->where('pay_status', 1)->where('zid', ZID)->where('to_days(add_time) = to_days(now())')->count('id')
                ],
                'web' => [
                    'all' => model('web')->where('rid', ZID)->count('zid'),
                    'today' => model('web')->where('rid', ZID)->where('to_days(add_time) = to_days(now())')->count('zid')
                ],
                'pay' => [
                    'all' => sprintf('%.2f', model('payOrder')->where('zid', ZID)->where('status', 1)->sum('rmb')),
                    'today' => sprintf('%.2f', model('payOrder')->where('zid', ZID)->where('status', 1)->where('to_days(add_time) = to_days(now())')->sum('rmb')),
                    'yesterday' => sprintf('%.2f', model('payOrder')->where('zid', ZID)->where('status', 1)->where('to_days(now())-to_days(add_time)=1')->sum('rmb')),
                    'alipay' => sprintf('%.2f', model('payOrder')->where('zid', ZID)->where('status', 1)->where('pay_type', 'alipay')->sum('rmb')),
                    'wxpay' => sprintf('%.2f', model('payOrder')->where('zid', ZID)->where('status', 1)->where('pay_type', 'wxpay')->sum('rmb')),
                    'qqpay' => sprintf('%.2f', model('payOrder')->where('zid', ZID)->where('status', 1)->where('pay_type', 'qqpay')->sum('rmb')),
                ]

            ]
        ];
        return $result;
    }

    public function profile()
    {
        $result['code'] = 0;
        $pass = trim(input('post.pass'));
        if (strlen($pass) < 5) {
            $result['message'] = '密码太简单';
        } elseif (User::updateByUid($this->userInfo['uid'], ['sid' => createSid(), 'pass' => encryptPassword($pass)])) {
            $result = [
                'code' => 0,
                'message' => '修改密码成功'
            ];
        } else {
            $result['message'] = '修改密码失败，请稍后再试';
        }
        return $result;
    }

    public function logout()
    {
        if (User::updateByUid($this->userInfo['uid'], ['sid' => createSid()])) {
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
            case 'orderRank':
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'list' => Web::getOrderRank()
                ];
                break;
            case 'payRank':
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'list' => Web::getPayRank()
                ];
                break;
            case 'list':
                $select = Web::search(ZID);
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
            case 'add':
                $this->checkKind(1);
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
                    $web = Web::add($data, $this->userInfo['uid'], true);
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
                    $order = Order::createOrder($goods, $data, true);
                    if (true === $order) {
                        $result = [
                            'code' => 0,
                            'message' => '下单成功',
                        ];
                    } elseif (false === $order) {
                        $result['message'] = "下单失败，请稍后再试";
                    } else {
                        $result['message'] = $order;
                    }
                }
            }
        }
        return $result;
    }

    public function getGoodsList()
    {
        $gid = input('post.gid/d');
        $list = Goods::selectByGid($gid, true);
        foreach ($list as $k => $v) {
            $priceInfo = Goods::getGoodsPriceInfo(ZID, $v, config('web_kind'), true);
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

    public function order($action)
    {
        $result = [
            'code' => -1,
            'message' => '无此操作'
        ];
        switch ($action) {
            case 'list':
                $select = Order::search(ZID);
                if (false === $select) {
                    $result['message'] = '获取订单失败，请稍后再试';
                } elseif (is_string($select)) {
                    $result['message'] = $select;
                } else {
                    $list = $select['list'];
                    foreach ($list as $k => $v) {
                        $v['tc'] = 0;
                        if ($info = json_decode($v['p_info'], true)) {
                            if (isset($info[ZID])) {
                                $v['tc'] = $info[ZID];
                            }
                        }
                        unset($v['p_info']);
                        unset($v['did']);
                        unset($v['dj_error']);
                        $list[$k] = $v;
                    }
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
            case 'priceSet':
                $goodsid = input('post.goodsid/d');
                $price_web = sprintf('%.2f', input('post.price_web'));
                $price_user = sprintf('%.2f', input('post.price_user'));
                if ($price_web < 0) {
                    $result['message'] = '用户加价价格不能小于0';
                } elseif ($price_user < 0) {
                    $result['message'] = '下级分站加价价格不能小于0';
                } elseif (WebGoods::setPrice(ZID, $goodsid, $price_user, $price_web)) {
                    $result = [
                        'code' => 0,
                        'message' => '设置成功',
                    ];
                } else {
                    $result['message'] = '设置失败，请稍后再试';
                }
                break;
            case 'list':
                $select = Goods::search(true);
                if (false === $select) {
                    $result['message'] = '获取商品失败，请稍后再试';
                } elseif (is_string($select)) {
                    $result['message'] = $select;
                } else {
                    foreach ($select['list'] as $k => $v) {
                        $priceInfo = WebGoods::getWebPriceInfo(ZID, $v);
                        $v['price_user'] = $priceInfo['price'];
                        $v['price_web'] = $priceInfo['price_0'];
                        $priceInfo = Goods::getGoodsPriceInfo(ZID, $v, config('web_kind'), true);
                        $v['price'] = $priceInfo['price'];
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
            case 'goodsAll':
                $select = Goods::selectAll(true);
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'list' => $select
                ];
                break;
            case 'groupAll':
                $select = Group::selectAll(true);
                $result = [
                    'code' => 0,
                    'message' => 'success',
                    'list' => $select
                ];
                break;
        }
        return $result;
    }

    public function rmb($action)
    {
        $result = [
            'code' => -1,
            'message' => '无此操作'
        ];
        switch ($action) {
            case 'txRecord':
                $select = TxRecord::search(ZID);
                if (false === $select) {
                    $result['message'] = '获取记录失败，请稍后再试';
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
            case 'tx':
                $rmb = input('post.rmb/d');
                $min = config('sys_tx_min');
                if (!is_numeric($rmb) || $rmb < 1) {
                    $result['message'] = '提现金额不正确';
                } elseif ($rmb < $min) {
                    $result['message'] = "最低提现 {$min} 元";
                } elseif (TxRecord::add($this->userInfo['uid'], $rmb)) {
                    $result = [
                        'code' => 0,
                        'message' => '申请提醒成功，请耐心等待管理员转账！',
                        'rmb' => User::getRmbByUid($this->userInfo['uid'])
                    ];
                } else {
                    $result['message'] = '提现失败，可能余额不足';
                }
                break;
            case 'record':
                $select = RmbRecord::search(ZID);
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
        }
        return $result;
    }

    public function config($action)
    {
        $result = [
            'code' => -1,
            'message' => '无此操作'
        ];
        switch ($action) {
            case 'upload':
                $file = $_FILES['file'];
                if ($file['size'] < 0) {
                    $result['message'] = '上传文件为空';
                } else {
                    if ($file['size'] > 2097152) {
                        $result['message'] = '上传图片不能大于2M';
                    } else {
                        $name = '';
                        if ($_FILES["file"]["type"] == "image/x-icon") {
                            $name = createSid() . '.ico';
                        } elseif ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/png"))) {
                            $name = createSid();
                            if ($_FILES["file"]["type"] == "image/gif") {
                                $name .= ".gif";
                            } elseif ($_FILES["file"]["type"] == "image/png") {
                                $name .= ".png";
                            } else {
                                $name .= ".jpg";
                            }
                        } else {
                            $result['message'] = '只允许上传图片';
                        }
                        if (move_uploaded_file($_FILES["file"]["tmp_name"], WEB_ROOT . '/upload/' . $name)) {
                            $result['code'] = 0;
                            $result['message'] = '上传成功';
                            $result['url'] = "/upload/" . $name;
                        } else {
                            $result['message'] = '上传失败';
                        }
                    }
                }

                break;
            case 'clone':
                $key = trim(input('post.key'));
                $domain = trim(input('post.domain'));
                if (strlen($key) != 32) {
                    $result['message'] = '密匙不正确[1]';
                } else {
                    $url = "http://{$domain}" . url('IndexAjax/getCloneGG');
                    $post = "key={$key}";
                    $ret = getCurl($url, $post);
                    if (!$ret = json_decode($ret, true)) {
                        $result['message'] = '克隆通讯失败[2]';
                    } elseif ($ret['code'] !== 0) {
                        $result['message'] = $ret['message'];
                    } elseif (Web::updateByZid(ZID, $ret['data'])) {
                        $result = [
                            'code' => 0,
                            'message' => '克隆成功'
                        ];
                    } else {
                        $result['message'] = '克隆失败，请稍候再试';
                    }
                }
                break;
            case 'cloneKey':
                if (Web::updateByZid(ZID, ['clone_key' => createSid()])) {
                    $result = [
                        'code' => 0,
                        'message' => '更换密匙成功'
                    ];
                } else {
                    $result['message'] = '更换失败，请稍候再试';
                }
                break;
            case 'tx':
                $data = [
                    input('post.type'),
                    input('post.user'),
                    input('post.name'),
                ];
                if (!in_array($data[0], ['微信', '支付宝', 'QQ'])) {
                    $result['message'] = '请选择正确的收款方式';
                } elseif (strlen($data[1]) < 5) {
                    $result['message'] = '请填写正确的收款账号';
                } else {
                    $tx = implode('|#|', $data);
                    if (User::updateByUid($this->userInfo['uid'], ['tx_info' => $tx])) {
                        $result = [
                            'code' => 0,
                            'message' => '保存成功'
                        ];
                    } else {
                        $result['message'] = '保存失败，请稍后再试';
                    }
                }
                break;
            case 'price':
                $this->checkKind(1);
                $data = [
                    'price_web_0' => sprintf("%.2f", trim(input('post.price_web_0'))),
                    'price_web_1' => sprintf("%.2f", trim(input('post.price_web_1'))),
                ];
                if (Web::updateByZid(ZID, $data)) {
                    $result = [
                        'code' => 0,
                        'message' => '保存成功'
                    ];
                } else {
                    $result['message'] = '保存失败，请稍后再试';
                }
                break;
            case 'gg':
                $data = [
                    'gg_index' => input('post.gg_index'),
                    'gg_notice' => input('post.gg_notice'),
                    'index_foot' => input('post.index_foot'),
                    'index_dialog' => input('post.index_dialog'),
                    'gg_search' => input('post.gg_search'),
                    'gg_web' => input('post.gg_web'),
                ];
                if (Web::updateByZid(ZID, $data)) {
                    $result = [
                        'code' => 0,
                        'message' => '保存成功'
                    ];
                } else {
                    $result['message'] = '保存失败，请稍后再试';
                }
                break;
            case 'set':
                $data = [
                    'name' => trim(input('post.name')),
                    'qq' => input('post.qq'),
                    'title' => input('post.title'),
                    'keywords' => input('post.keywords'),
                    'description' => input('post.description'),
                    'logo' => input('post.logo'),
                    'bg_image' => input('post.bg_image'),
                    'is_tz' => input('post.is_tz/d') ? 1 : 0,
                ];
                !$data['logo'] && $data['logo'] = '/static/index/images/logo.png';
                !$data['bg_image'] && $data['bg_image'] = '/static/index/images/bj.png';
                if (Web::updateByZid(ZID, $data)) {
                    $result = [
                        'code' => 0,
                        'message' => '保存成功'
                    ];
                } else {
                    $result['message'] = '保存失败，请稍后再试';
                }
                break;
        }
        return $result;
    }

    private function checkLogin()
    {
        if (!$this->userInfo = User::getLoginUser()) {
            $this->error('请先登录', url('index/login'));
        }
    }

    protected function _initialize()
    {
        parent::_initialize();
        $this->checkLogin();
    }
}