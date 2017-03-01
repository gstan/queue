<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Config.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-14
*   描    述：
*
============================================*/
//******路径定义********
define("CONF_PATH", ROOT_PATH . "/conf/");
define('DRAGON_PATH', ROOT_PATH . "/dragon/");
define('MODEL_PATH',  ROOT_PATH . "/model/");
define('LIB_PATH', ROOT_PATH . "/lib/");
define('COMMON_PATH', ROOT_PATH . "/common/");
define('MODULES_PATH', ROOT_PATH . "/modules/");
define('LOG_PATH', ROOT_PATH . '/../log/');
//*****页面相关**********
define('DEFAULT_MODULES', 'welcome');//默认路径
define('DEFAULT_METHOD', 'run');//默认方法
//*****日志相关*********
define('LOG_RECORD', 1); //是否记录日志
define('LOG_COLLECT', 0); //是否采用日志收集,默认不收集
define('LOG_FATAL', 2);//错误日志
define('LOG_WARNING', 1); //警告日志
define('LOG_NORMAL', 0);//普通日志
define('LOG_DB', "db_error.log");//数据库异常sql
define('LOG_SERVER', 'server');//服务器日志
define('LOG_ACCESS', 'access');//请求日志
define("LOG_SERVICE", 'queue');//业务日志
