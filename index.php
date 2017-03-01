<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：index.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-14
*   描    述：
*
============================================*/
use  Dragon\Dragon;
//设置此页面的过期时间(用格林威治时间表示)，只要是已经过去的日期即可。
//设置此页面的最后更新日期(用格林威治时间表示)为当天，可以强制浏览器获取最新资料
define('ROOT_PATH', __DIR__);
//add the dragon framework
require(ROOT_PATH . '/config/Config.class.php');
require(ROOT_PATH . '/config/Idc.class.php');
require(DRAGON_PATH . 'Dragon.class.php');
Dragon::start();





