<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Reg.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-15
*   描    述：
*
============================================*/

namespace Modules\Config;

class Reg extends \Dragon\CModule {


    /**
     *@brief run
     *@param
     *@return
     */
    public function run() {
        try {
            $bool = $this->init();
            $queueName = $_REQUEST['queue_name'];
            //判断这个queueName是否存在
            $this->httpServer->getRedis()->hExists(\Config\RedisConfig::QUEUE_CONFIGUE_KEY, $queueName, [$this, 'hExistsReceive']);
        }
        catch(\Exception $e) {
            return $this->response->setExceptionResponse($e);
        }
    }
    /**
     *@brief 判断存在的回调
     *@param
     *@return
     */
    public function hExistsReceive($result) {
        try {
            //存在直接返回
            if ($result) {
                $this->response->throwError(\Config\Errno::QUEUE_HAS_REG, \Config\Errno::QUEUE_HAS_REG_MSG);
                return false;
            }
            $arrData = array(
                'queue_name' => $_REQUEST['queue_name'],
                'queue_create_user' => strval($_REQUEST['queue_create_user']),
                'push_address' => strval($_REQUEST['push_address']),
                'secret_key' => strval($_REQUEST['secret_key']),
                'create_time' => time(),
                'retry_interval' => $_REQUEST['retry_interval'],
                'timeout' => $_REQUEST['timeout'],
                'expire' => $_REQUEST['expire'],
            );
            $strData = json_encode($arrData);
            $this->httpServer->getRedis()->hSet(\Config\RedisConfig::QUEUE_CONFIGUE_KEY, $_REQUEST['queue_name'], $strData, [$this, 'hsetReceive']);
        }
        catch(\Exception $e) {
            return $this->response->setExceptionResponse($e);
        }
    }
    /**
     *@brief redisReceive
     *@param
     *@return
     */
    public function hsetReceive($result) {
        try {
            if (!$result) {
                 return $this->response->throwError(\Config\Errno::QUEUE_REG_FAIL, \Config\Errno::QUEUE_REG_FAIL_MSG);
            }
            $this->response->setSuccessResponse();
        }
        catch(\Exception $e) {
            return $this->response->setExceptionResponse($e);
        }
    }
    /**
     *@brief 初始化
     *@param
     *@return
     */
    public function init() {
        $mustParam = array('queue_name', 'queue_create_user', 'secret_key');
        foreach($mustParam as $param) {
            if (empty($_REQUEST[$param])) {
                return $this->response->throwError(\Config\Errno::PARAM_ERROR, $param . \Config\Errno::PARAM_ERROR_MSG);
            }
        }
        $_REQUEST['retry_interval'] = intval($_REQUEST['retry_interval']);
        if (empty($_REQUEST['retry_interval'])) {
            $_REQUEST['retry_interval'] = 30;
        }
        $_REQUEST['timeout'] = intval($_REQUEST['timeout']);
        if (empty($_REQUEST['timeout'])) {
            $_REQUEST['timeout'] = 10;
        }
        $_REQUEST['expire'] = intval($_REQUEST['expire']);
        if (empty($_REQUEST['expire'])) {
            $_REQUEST['expire'] = 3600;
        }
        return true;
    }

}
