DROP TABLE IF EXISTS `klds_config`;
CREATE TABLE `klds_config` (
  `vkey` varchar(50) NOT NULL,
  `value` text,
  PRIMARY KEY (`vkey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `klds_config` VALUES ('price_web_0', '10');
INSERT INTO `klds_config` VALUES ('price_web_1', '20');
INSERT INTO `klds_config` VALUES ('domains', 'haicheng.com');
INSERT INTO `klds_config` VALUES ('gg_web_admin', '<div class=\'text-success text-center\'>欢迎搭建分站</div>');
INSERT INTO `klds_config` VALUES ('tx_rate', '0.05');
INSERT INTO `klds_config` VALUES ('tx_min', '10');
INSERT INTO `klds_config` VALUES ('v_sid', '28e09c91038701be28b639f86693ffaa');
INSERT INTO `klds_config` VALUES ('v_user', 'admin');
INSERT INTO `klds_config` VALUES ('v_pass', '4d3ea8f0d1aa6fa07b6c0a5375645c48');
INSERT INTO `klds_config` VALUES ('price_web_1_0', '1');
INSERT INTO `klds_config` VALUES ('price_web_1_1', '3');
INSERT INTO `klds_config` VALUES ('clone_key', '');
DROP TABLE IF EXISTS `klds_dj`;
CREATE TABLE `klds_dj` (
  `did` int(11) NOT NULL AUTO_INCREMENT,
  `kind` varchar(255) NOT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`did`)
) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `klds_goods`;
CREATE TABLE `klds_goods` (
  `goodsid` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `status` int(2) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `desc` text,
  `price` decimal(10,2) NOT NULL,
  `model` varchar(1024) NOT NULL,
  `is_repeat` tinyint(2) NOT NULL DEFAULT '0',
  `is_tk` tinyint(2) NOT NULL DEFAULT '0',
  `unit` int(11) unsigned NOT NULL DEFAULT '1',
  `default` int(11) unsigned NOT NULL DEFAULT '0',
  `min` int(11) unsigned NOT NULL DEFAULT '1',
  `max` int(11) unsigned NOT NULL DEFAULT '999999999',
  `rate` int(11) unsigned NOT NULL DEFAULT '1',
  `max_num` int(11) unsigned NOT NULL DEFAULT '0',
  `did` int(11) NOT NULL DEFAULT '0',
  `post` varchar(255) DEFAULT NULL,
  `api_status` tinyint(2) NOT NULL DEFAULT '0',
  `blacklist` text,
  `add_time` datetime DEFAULT NULL,
  PRIMARY KEY (`goodsid`)
) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `klds_group`;
CREATE TABLE `klds_group` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8;
INSERT INTO `klds_group` VALUES ('1000', '默认分组', '1', '1000');
DROP TABLE IF EXISTS `klds_order`;
CREATE TABLE `klds_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zid` int(11) NOT NULL,
  `goodsid` int(11) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `value1` varchar(255) NOT NULL,
  `value2` varchar(255) DEFAULT NULL,
  `value3` varchar(255) DEFAULT NULL,
  `value4` varchar(255) DEFAULT NULL,
  `value5` varchar(255) DEFAULT NULL,
  `value6` varchar(255) DEFAULT NULL,
  `unit` int(11) NOT NULL DEFAULT '1',
  `num` int(11) NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `p_info` varchar(255) DEFAULT NULL,
  `tk` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pay_status` tinyint(2) NOT NULL DEFAULT '0',
  `add_time` datetime DEFAULT NULL,
  `dj_id` int(11) NOT NULL DEFAULT '0',
  `dj_error` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `klds_pay_order`;
CREATE TABLE `klds_pay_order` (
  `oid` varchar(32) NOT NULL,
  `id` int(11) NOT NULL,
  `zid` int(11) NOT NULL,
  `rmb` decimal(10,2) DEFAULT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `pid` varchar(32) DEFAULT NULL,
  `pay_type` varchar(255) DEFAULT NULL,
  `pay_time` datetime DEFAULT NULL,
  `add_time` datetime DEFAULT NULL,
  PRIMARY KEY (`oid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `klds_rmb_record`;
CREATE TABLE `klds_rmb_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `action` varchar(255) DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `now` varchar(255) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `add_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `klds_tx_record`;
CREATE TABLE `klds_tx_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `rmb` decimal(10,2) NOT NULL,
  `sxf` decimal(10,2) unsigned NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `finish_time` datetime DEFAULT NULL,
  `add_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8;;
DROP TABLE IF EXISTS `klds_user`;
CREATE TABLE `klds_user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `sid` varchar(32) NOT NULL,
  `rmb` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `tx_info` varchar(255) DEFAULT NULL,
  `add_time` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;
INSERT INTO `klds_user` VALUES ('100', 'admin', '4d3ea8f0d1aa6fa07b6c0a5375645c48', 'f0c13baf5a175784469a931364b14a64', '1000.00', '1', null, '2017-09-18 15:22:24');
DROP TABLE IF EXISTS `klds_web`;
CREATE TABLE `klds_web` (
  `zid` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL,
  `kind` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `qq` varchar(255) NOT NULL DEFAULT '10000',
  `domain` varchar(255) NOT NULL,
  `extra_domain` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `clone_key` varchar(255) DEFAULT NULL,
  `is_tz` tinyint(2) NOT NULL DEFAULT '0',
  `add_time` datetime DEFAULT NULL,
  `gg_index` text,
  `gg_search` text,
  `gg_web` text,
  `gg_notice` text,
  `index_foot` text,
  `index_dialog` text,
  `logo` varchar(255) DEFAULT NULL,
  `bg_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`zid`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;
INSERT INTO `klds_web` (`zid`, `rid`, `uid`, `kind`, `name`, `status`, `qq`, `domain`, `extra_domain`, `title`, `keywords`, `description`, `add_time`, `gg_index`, `gg_search`, `gg_web`, `gg_notice`, `index_foot`, `index_dialog`, `logo`, `bg_image`, `clone_key`, `is_tz`) VALUES ('100', '0', '100', '2', '梓晨代刷系统', '1', '1844623143', '127.0.0.1', '', '梓晨代刷系统', '梓晨代刷系统', '梓晨代刷系统', '2017-09-18 17:24:05', '<center><div class=\"list-group-item reed\"><span class=\"\"></span> <font color=\"#000000\"><span class=\"label label-default\">支持：</span> <span class=\"label label-info\">QQ钻</span> <span class=\"label label-primary\">名片赞</span> <span class=\"label label-danger\">空间</span> <span class=\"label label-warning\">全民K歌</span> <span class=\"label label-success\">快手</span><p></p>新用户请仔细阅读下单需知 <a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapseTwo\"><span class=\"glyphicon glyphicon-th-list\"></span>展开/关闭</a><div id=\"collapseTwo\" class=\"panel-collapse collapse\"><p></p><p></p><div class=\"alert alert-info alert-dismissable\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\"></button><p>下单前必看须知：</p>1.会员钻类商品都是24小时内到账<p></p>2.下单前请阅读商品 说明后再购买！<p></p>3.低价名片赞: 下单秒刷/日刷 5 万！<p></p>4.极速名片赞: 下单秒刷/日刷 100万！<p></p>5.快手粉丝: 下单秒刷/慢慢到账！<p></p>6.全民K歌: 信息填对/直接秒刷！<p></p>7.搭建分站: 信息填对/5分钟内自动开通</div></div></font></div></center>', '<div><span class=\"label label-primary\">待处理</span> 说明正在努力提交到服务器！<p></p><p></p><span class=\"label label-success\">已完成</span> 并不是刷完了只是开始刷了！<p></p><p></p><span class=\"label label-warning\">处理中</span> 已经开始为您开单 请耐心等！<p></p><p></p><span class=\"label label-danger\">有异常</span> 下单信息有误 联系客服处理！</div>', '点击下方按钮系统全自动为你开通分站，无需手工搭建，更不需要建站技术即可拥有自己的平台。', '<div id=\"demo-acc-faq\" class=\"panel-group accordion\"><div class=\"panel panel-trans pad-top\"><a href=\"#demo-acc-faq1\" class=\"text-semibold text-lg text-main\" data-toggle=\"collapse\" data-parent=\"#demo-acc-faq\">为什么下单很久了都没有开始刷呢？</a><div id=\"demo-acc-faq1\" class=\"mar-ver collapse in\">由于本站采用全自动订单处理，难免会出现漏单，部分单子处理时间可能会稍长一点，不过都会完成，最终解释权归本站所有。超过24小时没处理请联系客服！</div></div><div class=\"panel panel-trans pad-top\"><a href=\"#demo-acc-faq2\" class=\"text-semibold text-lg text-main\" data-toggle=\"collapse\" data-parent=\"#demo-acc-faq\">空间人气下单方法讲解</a><div id=\"demo-acc-faq2\" class=\"mar-ver collapse\">1.下单前：空间必须是所有人可访问,必须自带1~4条原创说说!<br>2.代刷期间，禁止关闭访问权限，或者删除说说，删除说说的一律由自行负责，不给予补偿。</div></div><div class=\"panel panel-trans pad-top\"><a href=\"#demo-acc-faq3\" class=\"text-semibold text-lg text-main\" data-toggle=\"collapse\" data-parent=\"#demo-acc-faq\">说说赞相关下单方法讲解</a><div id=\"demo-acc-faq3\" class=\"mar-ver collapse\">1.下单前：空间必须是所有人可访问,必须自带1条原创说说!转发的说说不能刷！<br>2.在“QQ号码”栏目输入QQ号码，点击下面的获取说说ID并选择你需要刷的说说的ID，下单即可。<br>3.代刷期间，禁止关闭访问权限，或者删除说说，删除说说的一律由自行负责，不给予补偿。</div></div><div class=\"panel panel-trans pad-top\"><a href=\"#demo-acc-faq4\" class=\"text-semibold text-lg text-main\" data-toggle=\"collapse\" data-parent=\"#demo-acc-faq\">全民Ｋ歌下单方法讲解</a><div id=\"demo-acc-faq4\" class=\"mar-ver collapse\">1.打开你的全名k歌<br>2.复制你全名k歌里面的需要刷的歌曲链接<br>3.例如：你歌曲链接是：<font color=\"#ff0000\">https://kg.qq.com/node/play?s= <font color=\"green\">881Zbk8aCfIwA8U3</font> &g_f=personal</font><br>4.然后把s=后面的 <font color=\"green\">881Zbk8aCfIwA8U3</font> 链接填入到歌曲ID里面，然后提交购买。</div></div><div class=\"panel panel-trans pad-top\"><a href=\"#demo-acc-faq5\" class=\"text-semibold text-lg text-main\" data-toggle=\"collapse\" data-parent=\"#demo-acc-faq\">快手代刷下单方法讲解</a><div id=\"demo-acc-faq5\" class=\"mar-ver collapse\">1.需要填写用户ID和作品ID，比如<font color=\"#ff0000\">http://www.kuaishou.com/i/photo/lwx?userId= <font color=\"green\">294200023</font> &photoId= <font color=\"green\">1071823418</font></font> (分享作品就可以看到“复制链接”了)<br>2.用户ID就是 <font color=\"green\">294200023</font> 作品ID就是 <font color=\"green\">1071823418</font> ，然后在分别把用户ID和作品ID填上，请勿把两个选项填反了，不给予补单！</div></div><div class=\"panel panel-trans pad-top\"><a href=\"#demo-acc-faq6\" class=\"text-semibold text-lg text-main\" data-toggle=\"collapse\" data-parent=\"#demo-acc-faq\">Q会员/钻下单方法讲解</a><div id=\"demo-acc-faq6\" class=\"mar-ver collapse\">1.下单之前，先确认输的信息是不是正确的，如果密码输错，那就刷不了了，没到账之前不要改密码<br>2.Q会员/钻因为需要人工处理，所以每天不定时开刷，24小时-48小时内到账！</div></div></div>', '<div class=\"panel panel-primary\"><div class=\"panel-heading\"><h3 class=\"panel-title\"><font color=\"#fff\"><i class=\"fa fa-skyatlas\"></i>  <b>站点助手</b></font></h3></div><div class=\"text-center\"><table class=\"table table-bordered\"><tbody><tr><td><button type=\"button\" class=\"btn btn-block btn-warning\"><a href=\"/\" target=\"_blank\" style=\"color:#fff\"><b>QQ代刷网</b></a></button></td><td><button type=\"button\" class=\"btn btn-block btn-danger\"><a href=\"/\" target=\"_blank\" style=\"color:#fff\"><b>QQ代刷网</b></a></button></td></tr></tbody></table></div></div>', NULL, '/static/index/images/logo.png', '/static/index/images/bj.png', '28c54294ef78eb6d7fa4213728c3c4e1', '0');
DROP TABLE IF EXISTS `klds_web_goods`;
CREATE TABLE `klds_web_goods` (
  `zid` int(11) NOT NULL,
  `goodsid` int(11) NOT NULL,
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `price_0` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `klds_web_record`;
CREATE TABLE `klds_web_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kind` tinyint(2) NOT NULL DEFAULT '0',
  `rid` int(11) NOT NULL DEFAULT '0',
  `user` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `qq` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `price_web` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `add_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8;
