<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Errno.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-14
*   描    述：
*
============================================*/
namespace Config;

class Errno {

    const FILE_NOT_FOUND = 100000;
    const FILE_NOT_FOUND_MSG = '无法找到文件';

    const PARAM_ERROR = 100001;
    const PARAM_ERROR_MSG = '参数错误';

    const SING_ERROR = 100002;
    const SING_ERROR_MSG =  '签名错误';

    const QUEUE_HAS_REG = 200000;
    const QUEUE_HAS_REG_MSG =  '队列已经被注册';

    const QUEUE_REG_FAIL = 200001;
    const QUEUE_REG_FAIL_MSG = '队列注册失败';

    const QUEUE_NOT_EXITS = 200002;
    const QUEUE_NOT_EXITS_MSG = '队列未注册';

    const INTO_QUEUE_FAIL = 200003;
    const INTO_QUEUE_FAIL_MSG = '入队列失败';

    const POP_QUEUE_FAIL = 200004;
    const POP_QUEUE_FAIL_MSG = 'pop队列失败';


}
