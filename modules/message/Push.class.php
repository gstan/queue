<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Push.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-14
*   描    述：
*
============================================*/
namespace Modules\Message;

class Push extends \Dragon\CModule {

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
            //没有推送地址,说明是拉模式,直接入数据即可
            if (empty($this->queueInfo['push_address'])) {
                $this->doPullWay();
            }
            else {
                $this->doPushWay();
            }
        }
        catch(\Exception $e) {
            return $this->response->setExceptionResponse( $e);
        }
    }
    /**
     *@brief 推模式
     *@param
     *@return
     */
    public function doPushWay() {
        try {
            $msgData = array(
                'msg_id' => $_REQUEST['msg_id'],
                'message' => $_REQUEST['message'],
            );
            $message = new \Lib\Message($msgData, $this->queueInfo, $this->httpServer);
            $message->push();
            //先返回了
            $this->response->setSuccessResponse();
        }
        catch(\Exception $e) {
            return $this->response->setExceptionResponse($e);
        }
    }
    /**
     *@brief 拉取模式,处理
     *@param
     *@return
     */
    public function doPullWay() {
        $msgData = array(
            'msg_id' => $_REQUEST['msg_id'],
            'message' => $_REQUEST['message'],
        );
        $strMsgData = json_encode($msgData);
        $redisKey = \Config\RedisConfig::QUEUE_PREFIX . $this->queueInfo['queue_name'];
        $this->httpServer->getRedis()->rPush($redisKey, $strMsgData, [$this, 'pullWayPushReceive']);
    }
    /**
     *@brief 拉模式入队列的回调
     *@param
     *@return
     */
    public function pullWayPushReceive($result) {
        try {
            if ($result) {
                return $this->response->setSuccessResponse();
            }
            else {
               return $this->response->setErrorResponse(\Config\Errno::INTO_QUEUE_FAIL, \Config\Errno::INTO_QUEUE_FAIL_MSG);
            }
        }
        catch(\Exception $e) {
            return $this->response->setExceptionResponse( $e);
        }
    }

    /**
     *@brief 校验参数
     *@param
     *@return
     */
    public function checkSign($secretKey) {
        $needSign = md5($_REQUEST['queue_name'] . $_REQUEST['msg_id'] . $_REQUEST['message'] . $secretKey);
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
        $mustParam = array('msg_id', 'queue_name', 'message', 'sign');
        foreach($mustParam as $param) {
            if (empty($_REQUEST[$param])) {
                return $this->response->throwError(\Config\Errno::PARAM_ERROR, $param . \Config\Errno::PARAM_ERROR_MSG);
            }
        }
    }

}
