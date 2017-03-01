<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Message.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-22
*   描    述：
*
============================================*/

namespace Lib;
use Lib\Log;
use Config\RedisConfig;
use Lib\HttpClient;


class Message {

    protected $message;
    protected $queueInfo;
    protected $server;
    protected $isProcess;
    protected $redisHashData;
    /**
     *@brief 构造函数
     *@param
     *@return
     */
    public function __construct($message, $queueInfo, $server, $isProcess = false) {
        $this->message = $message;
        $this->queueInfo = $queueInfo;
        $this->server = $server;
        $this->isProcess = $isProcess;
    }

    /**
     *@brief 推送逻辑
     *@param
     *@return
     */
    public function push() {
        try {
            $postData['message'] = $this->message['message'];
            $postData['msg_id'] = $this->message['msg_id'];

            $client = new HttpClient($this->queueInfo['push_address']);
            $client->post($postData, [$this, 'onPostReceiver']);
        }
        catch(\Exception $e) {
            Log::instance()->writeException($e, __METHOD__);
        }
    }
    /**
     *@brief postReceiver
     *@param
     *@return
     */
    public function onPostReceiver($response) {
        try {
            $log = $this->isProcess . ':' .  $this->message['msg_id'] . ' onPostReceiver:' . $response['http_code'];
            Log::instance()->write($log);
            if ($this->isProcess && $response['http_code'] == 200) {
                $msgKey = $this->queueInfo['queue_name'] . '_' . $this->message['msg_id'];
                if (!empty($this->message['time_id'])) {
                    //swoole_timer_clear($this->message['time_id']);
                }
                return $this->server->getRedis()->hDel(RedisConfig::QUEUE_DATA_KEY, $msgKey, [$this, 'onTimeRedisDelReceiver']);
            }
            if ($this->isProcess) {
                return true;
            }
            if ($response['http_code'] == 200) {
                return true;
            }
            //记录一个redis
            $msgKey = $this->queueInfo['queue_name'] . '_' . $this->message['msg_id'];
            $this->redisHashData = array(
                'msg_id' => $this->message['msg_id'],
                'message' => $this->message['message'],
                'queue_name' => $this->queueInfo['queue_name'],
                'push_address' => $this->queueInfo['push_address'],
                'retry_interval' => $this->queueInfo['retry_interval'],
                'timeout' => $this->queueInfo['timeout'],
                'expire' => $this->queueInfo['expire'],
            );
            $strHsetData = json_encode($this->redisHashData);
            $this->server->getRedis()->hSet(RedisConfig::QUEUE_DATA_KEY, $msgKey, $strHsetData, [$this, 'pushHSetReceiver']);
        }
        catch(\Exception $e) {
            Log::instance()->writeException($e, __METHOD__);
        }
    }
    /**
     *@brief push操作的hSet操作,
     *@param
     *@return
     */
    public function pushHSetReceiver($result) {
        try {
            if ($result) {
                $log = $this->isProcess . ":pushHsetReceiver success:" . $this->message['msg_id'];
            }
            else {
                $log = $this->isProcess . ":pushHsetReceiver failed:" . $this->message['msg_id'];
                Log::instance()->write($log, LOG_WARNING);
            }
            $timeId = swoole_timer_tick($this->queueInfo['retry_interval']*1000, [$this, 'onTime'], array());
            Log::instance()->write($this->message['msg_id'] . ",timeId" . $timeId);
        }
        catch(\Exception $e) {
            Log::instance()->writeException($e, __METHOD__);
        }
    }
    /**
     *@brief 定时器
     *@param
     *@return
     */
    public function onTime($timeId, $param) {
        try {
            //发生消息
            $log = $this->isProcess . ':onTime,timeId:' . $timeId . ',message:' . $this->redisHashData['msg_id'];
            Log::instance()->write($log);
            //在redis里记录timeid
            if (empty($this->redisHashData['time_id'])) {
                $this->redisHashData['time_id'] = $timeId;
                $msgKey = $this->redisHashData['queue_name'] . '_' . $this->redisHashData['msg_id'];
                $strHsetData = json_encode($this->redisHashData);
                $this->server->getRedis()->hSet(RedisConfig::QUEUE_DATA_KEY, $msgKey, $strHsetData, [$this, 'onTimeHSetReceiver']);
            }
            $postData['message'] = $this->redisHashData['message'];
            $postData['msg_id'] = $this->redisHashData['msg_id'];
            $client = new HttpClient($this->redisHashData['push_address']);
            $client->post($postData, [$this, 'onTimerPostReceiver']);
        }
        catch(\Exception $e) {
            Log::instance()->writeException($e, __METHOD__);
        }
    }
    /**
     *@brief 设置redis值的time_id
     *@param
     *@return
     */
    public function onTimeHSetReceiver($result, $success) {
        $prefix =  $this->redisHashData['msg_id'] . ':time_id:' . $this->redisHashData['time_id'];
        if ($success) {
            $log = $prefix . ' set success';
        }
        else {
            $log = $prefix . ' set fail';
        }
        Log::instance()->write($log);
    }

    /**
     *@brief 定时器推送
     *@param
     *@return
     */
    public function onTimerPostReceiver($response) {
        try {
            $log = $this->isProcess . ':' . $this->redisHashData['msg_id'] . ',onTimerPostReceiver:' . $response['http_code'];
            Log::instance()->write($log);
            if ($response['http_code'] == 200) {
                $msgKey = $this->redisHashData['queue_name'] . '_' . $this->redisHashData['msg_id'];
                $log = $this->isProcess . ":clear time, timeId" . $this->redisHashData['time_id'];
                Log::instance()->write($log);
                swoole_timer_clear($this->redisHashData['time_id']);
                $this->server->getRedis()->hDel(RedisConfig::QUEUE_DATA_KEY, $msgKey, [$this, 'onTimeRedisDelReceiver']);
            }
            return false;
        }
        catch(\Exception $e) {
            Log::instance()->writeException($e, __METHOD__);
        }
    }
    /**
     *@brief 记录redis删除hDel的日志
     *@param
     *@return
     */
    public function onTimeRedisDelReceiver($result) {
        if ($result) {
            $log = $this->isProcess . ':' . $this->message['msg_id'] .  ' hdel success' ;
            Log::instance()->write($log);
            return true;
        }
        $log = $this->isProcess . ':' . $this->message['msg_id'] . ':hdel failed';
        Log::instance()->write($log, LOG_WARNING);
        return false;
    }

}
