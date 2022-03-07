<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
@header("Content-type:text/html;charset=utf-8");

session_start();

// [ 应用入口文件 ]
if (!file_exists(__DIR__ . '/ds/application/index/database.php')) {
    require_once __DIR__ . '/ds/install/index.php';
    exit();
}

// 定义网站目录
define('WEB_ROOT', __DIR__);

// 定义应用目录
define('APP_PATH', __DIR__ . '/ds/application/');
// 加载框架引导文件
require __DIR__ . '/ds/thinkphp/start.php';
