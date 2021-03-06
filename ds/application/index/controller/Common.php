<?php
/**
 * Created by PhpStorm.
 * User: 梓晨<1251251214@qq.com>
 * Date: 2017/9/4
 * Time: 12:54
 */

namespace app\index\controller;


use app\index\model\Config;
use app\index\model\Web;
use think\Controller;
use think\Loader;
use think\Request;

class Common extends Controller
{
    protected $returnType = 'html';


    private function loadSysConfig()
    {
        $config = Config::selectAll();
        foreach ($config as $v) {
            config("sys_{$v['vkey']}", $v['value']);
        }
    }

    private function loadWebInfo()
    {
        $domain = $_SERVER['HTTP_HOST'];
        if (!$web = Web::findByDomain($domain)) {
            $this->error('此站点未开通');
        } elseif ($web['status'] === 0) {
            $this->error('此站点已关闭');
        }
        $web = $web->getData();
        foreach ($web as $k => $v) {
            config("web_{$k}", $v);
        }
        define('ZID', $web['zid']);

        //默认logo
        if (!trim(config('web_default_image'))) {
            config('web_default_image', '/static/common/images/logo_big.png');
        }
        if (!trim(config('web_bg_image'))) {
            config('web_bg_image', '/static/common/images/bg.jpg');
        }
    }

    protected function error($msg = '', $url = '', $data = -1, $wait = 3, array $header = [])
    {
        if ($this->returnType == 'json') {
            exit(json_encode([
                'code' => $data,
                'message' => $msg,
                'url' => $url
            ], JSON_UNESCAPED_UNICODE));
        } else {
            parent::error($msg, $url ? $url : 'index', $data, $wait, $header);
            exit();
        }
    }

    protected function checkKind($kind = 0)
    {
        if (config('web_kind') < $kind) {
            $this->error('此版本站点无此功能');
        }
    }

    protected function _initialize()
    {
        $controller = Loader::parseName(Request::instance()->controller());
        parent::_initialize(); // TODO: Change the autogenerated stub
        if (Request::instance()->header("X-PJAX")) {
            config('default_ajax_return', 'html');
            define('PJAX', true);
        } else {
            define('PJAX', false);
        }
        if ($controller !== 'admin' && $controller !== 'admin_ajax') {
            $this->loadWebInfo();
        } else {
            config('web_name', '系统管理后台');
        }
        $this->loadSysConfig();
    }
}