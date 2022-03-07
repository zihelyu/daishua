<?php

namespace app\index\controller;

use app\index\model\Config;
use app\index\model\Order;
use think\Request;

class Admin extends Common
{
    public function update()
    {
        $url = get_curl("http://auth.hongml.com/bin/klds_update.php?url=".$_SERVER['HTTP_HOST']."&authcode=".config("sk.authCode")."&ver=".config("version.versionCode"));
        $json = json_decode($url,true);
        if($json['code']==-1){
            $code=-1;
            $msg = $json['msg'];
        }else{
            $code=1;
            $msg = $json['msg'];
            $uplog = $json['uplog'];
            $this->assign('uplog',$uplog);
            //$file = $json['file'];
        }
        if(request()->IsPost()){
            $RemoteFile = $json['file'];
            $ZipFile = "Archive.zip";
            copy($RemoteFile,$ZipFile) or die("无法下载更新包文件！");
            if (zipExtract($ZipFile,WEB_ROOT)) {
            if(function_exists("opcache_reset"))@opcache_reset();
            $rs = "程序更新成功";
            unlink($ZipFile);
            }
            else {
            $rs = "无法解压文件";
            if (file_exists($ZipFile))
            unlink($ZipFile);
            }
            $this->assign('rs',$rs);
        }
        $this->assign('msg',$msg);
        
        $this->assign('code',$code);
        $this->assign('webTitle', '更新程序');
        return $this->fetch();
    }
    public function clone_goods()
    {
        $this->assign('webTitle', '一键克隆');
        return $this->fetch();
    }

    public function dj_list()
    {
        $this->assign('webTitle', '对接配置');
        return $this->fetch();
    }

    public function rmb_tx()
    {
        $this->assign('webTitle', '提现管理');
        return $this->fetch();
    }

    public function rmb_record()
    {
        $this->assign('webTitle', '余额明细');
        return $this->fetch();
    }

    public function export()
    {
        $ids = input('get.ids');
        if (is_numeric($ids) && $ids < 0) {
            $list = Order::selectByGoodsidAndStatus(abs(intval($ids)), 0);
            Order::updateByGoodsidAndStatus(abs(intval($ids)), 0, ['status' => 90]);
        } else {
            $ids = explode(',', $ids);
            $list = Order::selectByIds($ids);
        }
        if (count($list) > 0) {
            $txt = '';
            foreach ($list as $v) {
                $i = 1;
                for ($i = 1; $i <= 6; $i++) {
                    if (strlen($v['value' . $i]) > 2) {
                        $txt .= $v['value' . $i] . '----';
                    } else {
                        break;
                    }
                }
                $txt .= $v['num'] * $v['unit'] . "\r\n";
            }
            Header("Content-type: application/octet-stream");
            Header("Content-Disposition: attachment; filename=Order_" . date("Y-m-d H:i:s") . ".txt");
            exit($txt);
        } else {
            $this->error('没有要导出的数据');
        }
    }

    public function web_add()
    {
        $this->assign('webTitle', '添加分站');
        return $this->fetch();
    }

    public function web_list()
    {
        $this->assign('webTitle', '分站列表');
        return $this->fetch();
    }

    public function order_list()
    {
        $this->assign('webTitle', '订单列表');
        return $this->fetch();
    }

    public function goods_list()
    {
        $this->assign('webTitle', '商品列表');
        return $this->fetch();
    }

    public function goods()
    {
        $this->assign('webTitle', '商品添加/修改');
        return $this->fetch();
    }

    public function goods_group()
    {
        $this->assign('webTitle', '商品分组');
        return $this->fetch();
    }

    public function config()
    {
        $this->assign('webTitle', '系统配置');
        return $this->fetch();
    }

    public function pay_list()
    {
        $this->assign('webTitle', '支付订单');
        return $this->fetch();
    }

    public function pay_config()
    {
        $this->assign('webTitle', '支付配置');
        return $this->fetch();
    }

    public function index()
    {
        $this->assign('version', config('version'));
        $this->assign('webTitle', '管理首页');
        return $this->fetch();
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
        $this->assign('webTitle', '管理后台');
        $this->checkLogin();
    }

}
