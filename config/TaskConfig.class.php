<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Task.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-16
*   描    述：
*
============================================*/
namespace Config;

class TaskConfig {

    const PUSH_TYPE = 'QueuePush'; //推送任务

    const BASE_TASK = 'RedisPush';//一直读取redis数据，看是否需要推送
}
