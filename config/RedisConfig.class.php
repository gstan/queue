<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Redis.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-15
*   描    述：
*
============================================*/

namespace Config;

class RedisConfig {


    const QUEUE_CONFIGUE_KEY = 'RedisConfig:queue_config_key';

    const QUEUE_DATA_KEY = 'RedisConfig:queue_data_key';
    const QUEUE_PREFIX = 'RedisConfig:prefix:';

    public static $server = array(
        'tc' => array(
            array(
                'ip' => '10.67.19.12',
                'port' => '9207',
            ),
            array(
                'ip' => '10.67.48.55',
                'port' => '9207',
            ),
        ),
        'nj' => array(
            array(
                'ip' => '10.205.26.20',
                'port' => '7290',
            ),
            array(
                'ip' => '10.208.146.45',
                'port' => '7290',
            ),
        ),
        'nj03' => array(
            array(
                'ip' => '10.195.107.43',
                'port' => '7290',
            ),
            array(
                'ip' => '10.195.141.40',
                'port' => '7290',
            ),
        ),
    );
}
