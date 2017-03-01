<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Pull.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-22
*   描    述：
*
============================================*/

namespace Modules\Message;

class Pull extends \Dragon\CModule {

    protected $queueInfo;
    protected $timeId;//定时器id

    /**
     *@brief 入口
     *@param
     *@return
     */
    public function run() {
        try {
            $this->init();
            $this->queueInfo = \Lib\Conf::getConf($_REQUEST['queue_name']);
            if (empty($this->queueInfo)) {
                return $this->response->setErrorResponse(\Config\Errno::QUEUE_NOT_EXITS, \Config\Errno::QUEUE_NOT_EXITS_MSG);
            }
            //checkSign
            $bool = $this->checkSign($this->queueInfo['secret_key']);
            //签名错误
            if (!$bool) {
                 return $this->response->setErrorResponse(\Config\Errno::SING_ERROR, \Config\Errno::SING_ERROR_MSG);
            }
            $redisKey = \Config\RedisConfig::QUEUE_PREFIX . $this->queueInfo['queue_name'];
            //pop一个数据
            $this->httpServer->getRedis()->lPop($redisKey, [$this, 'lPopReceive']);
        }
        catch(\Exception $e) {
            return $this->response->setExceptionResponse($e);
        }
    }
    /**
     *@brief lpop一个数据
     *@param
     *@return
     */
    public function lPopReceive($redisData, $success) {
        try {
            if (empty($success)) {
                $this->response->setErrorResponse(\Config\Errno::POP_QUEUE_FAIL, \Config\Errno::POP_QUEUE_FAIL_MSG);
                return false;
            }
            if (empty($redisData)) {
                $queueInfo = array(
                    'message' => array(),
                );
            }
            else {
                $queueInfo['message'] = json_decode($redisData);
            }
            $this->response->setSuccessResponse($queueInfo);
        }
        catch(\Exception $e) {
            return $this->response->setExceptionResponse($e);
        }
    }
    /**
     *@brief 校验参数
     *@param
     *@return
     */
    public function checkSign($secretKey) {
        $needSign = md5($_REQUEST['queue_name'] . $_REQUEST['timestamp'] . $secretKey);
        if ($needSign == $_REQUEST['sign']) {
            return true;
        }
        return false;
    }
    /**
     *@brief 初始化
     *@param
     *@return
     */
    public function init() {
        $mustParam = array('queue_name', 'timestamp', 'sign');
        foreach($mustParam as $param) {
            if (empty($_REQUEST[$param])) {
                return $this->response->throwError(\Config\Errno::PARAM_ERROR, $param . \Config\Errno::PARAM_ERROR_MSG);
            }
        }
    }
}
