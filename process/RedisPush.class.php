<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：RedisPush.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-22
*   描    述：
*
============================================*/

namespace Process;
use Lib\Log;
use Config\RedisConfig;
use Lib\Message;

ini_set("memory_limit", "1024M");
class RedisPush {

    //每次轮休时间
    const SLEEP_TIME = 90000;//90S
    //每次加载redis配置的时间
    const LOAD_CONFIG = 300000;//300s
    /**
     *@brief 构造函数
     *@param
     *@return
     */
    public function __construct($worker, $server) {
        $this->worker = $worker;
        $this->server = $server;
    }

    /**
     *@brief 负责消息推送
     *@param
     *@return
     */

    public function run() {
        try {
            //获取redis里的消息数据
            $this->server->getRedis()->hvals(RedisConfig::QUEUE_DATA_KEY, [$this, 'hValsDataReceiver']);
            //获取redis里配置数据
            $this->server->getRedis()->hvals(RedisConfig::QUEUE_CONFIGUE_KEY, [$this, 'hValsConfigReceiver']);
        }
        catch(\Exception $e) {
            Log::instance()->writeException($e, __METHOD__);
        }
    }

    /**
     *@brief 从redis获取数据
     *@param
     *@return
     */
    public function hValsDataReceiver($redisData) {
        try {
            if (empty($redisData)) {
		        Log::instance()->write("hValsDataReceiver empty");
                $timeId = swoole_timer_after(self::SLEEP_TIME, [$this, 'onTimeData']);
		        Log::instance()->write("hValsDataReceiver empty, timeId:" . $timeId);
                return false;
            }
            foreach($redisData as $value) {
                $arrMessage = json_decode($value, true);
                $queueInfo = array(
                    'push_address' => $arrMessage['push_address'],
                    'queue_name' => $arrMessage['queue_name'],
                    'retry_interval' => $arrMessage['retry_interval'],
                    'timeout' => $arrMessage['timeout'],
                    'expire' => $arrMessage['expire'],
                );
                $message = array(
                    'msg_id' => $arrMessage['msg_id'],
                    'message' => $arrMessage['message'],
                    'time_id' => $arrMessage['time_id'],
                );
                //发一个异步消息
                $message = new Message($message, $queueInfo, $this->server, true);
                $message->push();
            }
            unset($redisData);
	        Log::instance()->write("hValsDataReceiver start onTimeData");
            $timeId = swoole_timer_after(self::SLEEP_TIME, [$this, 'onTimeData']);
            Log::instance()->write("hValsDataReceiver start onTimeData,timeId:" . $timeId);
        }
        catch(\Exception $e) {
            Log::instance()->writeException($e, __METHOD__);
        }
    }
    /**
     *@brief 加载redis的配置
     *@param
     *@return
     */
    public function hValsConfigReceiver($redisData) {
        if (empty($redisData)){
            swoole_timer_after(self::LOAD_CONFIG, [$this, 'onTimeConfig']);
            return false;
        }
        foreach($redisData as $config) {
            $arrConfig = json_decode($config, true);
            $queueName = $arrConfig['queue_name'];
            \Lib\Conf::writeConf($queueName, $arrConfig);
        }
        unset($redisData);
        swoole_timer_after(self::LOAD_CONFIG, [$this, 'onTimeConfig']);
    }
    /**
     *@brief redis获取数据
     *@param
     *@return
     */
    public function onTimeData() {
        Log::instance()->write("onTimeData start");
        $this->server->getRedis()->hvals(RedisConfig::QUEUE_DATA_KEY, [$this, 'hValsDataReceiver']);
    }
    /**
     *@brief redis获取数据
     *@param
     *@return
     */
    public function onTimeConfig() {
        Log::instance()->write("onTimeConfig start");
        $this->server->getRedis()->hvals(RedisConfig::QUEUE_CONFIGUE_KEY, [$this, 'hValsConfigReceiver']);
    }
}
