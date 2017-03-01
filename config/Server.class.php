<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Server.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-16
*   描    述：
*
============================================*/

return array(
    'host' => '0.0.0.0',
    'port' => '8501',
    //'daemonize' => 1,//用supervise就不需要开这个
    'worker_num' => 64, //所有的核数
    //'task_worker_num' => 1,
    'reactor_num' => 64,//线程的数量,默认为cpu核数
    'log_file' => LOG_PATH . 'swoole.log',
    'process_name' => 'movieQueueServer',
    'pid' => ROOT_PATH . '/var/pid.var',
);
