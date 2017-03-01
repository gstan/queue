<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：HttpServer.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-14
*   描    述：
*
============================================*/

namespace Dragon;
use Lib\Log;
use Lib\RedisClient;
use Lib\TimeHelper; 
use Config\RedisConfig;
use Process\RedisPush;

class Server {

    public $server;
    public $request;
    public $response;
    public $redisHelper;

    /**
     *@brief 实例化server
     *@param
     *@return
     */
    public function __construct($config) {
        $this->setting = $config;
    }
    /**
     * 修改进程名称
     * @param $name 进程名称
     * @return bool
     * @throws \Exception
     */
    private function setProcessName($name) {
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($name);
            return true;
        }
        if (function_exists('swoole_set_process_name')) {
            swoole_set_process_name($name);
            return true;
        } 
        return false;
    }
    /**
     *@brief 
     *@param
     *@return
     */
    public function run() {
        $this->server = new \swoole_http_server($this->setting['host'], $this->setting['port']);
        $this->server->set($this->setting);
        $this->connectRedis();
        $process = new \swoole_process(function($worker) {
            $this->workProccess($worker);
        });
        $process->start();
        $callFunction  = [
            'start',
            'managerStart',
            'workerStart',
            'request',
            //'task',
            //'finish',
            'workerStop',
            'shutdown',
            'close',
            'workerError',
        ];
        //事件回调函数绑定
        foreach ($callFunction as $function) {
            $onFunction = 'on' . ucfirst($function);
            if (method_exists($this, $onFunction)) {
                $this->server->on($function, [$this, $onFunction]);
            }
        }
        $this->server->start();
    }
    /**
     *@brief 主进程开始
     *@param 记录
     *@return
     */
    public function onStart($server) {
        $log = "swoole_http_server master start";
        Log::instance(LOG_SERVER)->write($log);
        //记录进程id,脚本实现自动重启
        $pid = "{$this->server->master_pid}\n{$this->server->manager_pid}";
        file_put_contents($this->setting['pid'], $pid);
        $this->setProcessName($this->setting['process_name'] . '-master');
    }
    /**
     *@brief close函数
     *@param
     *@return
     */
    public function onClose($server, $fd, $id) {
        $log = "on Close,from_id:" . $id;
        Log::instance(LOG_SERVER)->write($log);
    }

    /**
     *@brief work进程错误的回调
     *@param
     *@return
     */
    public function onWorkerError($server, $workId, $pid, $exitCode) {
        $log = 'on WorkerError, work_id:' . $workId . ",pid:" . $pid . ',exit_code:' . $exitCode;
        Log::instance(LOG_SERVER)->write($log);
    }
    /**
     *@brief  管理进程启动开始
     * @param $server
     * @return
     */
    public function onManagerStart($server)
    {
        $log = "swoole_http_server manager worker start";
        Log::instance(LOG_SERVER)->write($log);
        $this->setProcessName($this->setting['process_name'] . '-manager');
    }
    /**
     * @brief 服务关闭
     * @param
     * @return
     */
    public function onShutdown() {
        unlink($this->setting['pid']);
        $log = ' swoole_http_server shutdown';
        Log::instance(LOG_SERVER)->write($log);
    }
    /**
     * @biref 加载业务脚本常驻内存 http://wiki.swoole.com/wiki/page/p-event/onWorkerStart.html
     * @param $server
     * @param $workerId
     */
    public function onWorkerStart($server, $workerId) {
        if ($workerId >= $this->setting['worker_num']) {
            $this->setProcessName($server->setting['process_name'] . '-task');
        } else {
            $this->setProcessName($server->setting['process_name'] . '-work');
        }
    }
    /**
     * @brief worker进程停止
     * @param $server
     * @param $workerId
     */
    public function onWorkerStop($server, $workerId)
    {
        $log = "swoole_http_server {$server->setting['process_name']}, worker:{$workerId} shutdown";
        Log::instance(LOG_SERVER)->write($log);
    }

	/**
	*@brief request数据
	*@param
	*@return
	*/
	public function onRequest(\swoole_http_request $request, \swoole_http_response $response) {
        try {
            $this->request = $request;
            $this->response = $response;
            $http = new Http($this);
            $http->run();
        }
        catch(\Exception $e) {
            $this->response->status(500);
            $this->response->end('faild');
        }
	}
	/**
	*@brief 获取request数据
	*@param
	*@return
	*/
    public function getRequest() {
        return $this->request;
    }
	/**
	*@brief 获取response数据
	*@param
	*@return
	*/
    public function getResponse() {
        return $this->response;
    }
	/**
	*@brief 获取redis句柄
	*@param
	*@return
	*/
    public function getRedisHelper() {
        return $this->redisHelper;
    }
    /**
     *@brief 自定义进程
     *@param
     *@return
     */
    public function workProccess($worker) {
        //$this->connectRedis();
        $this->setProcessName($this->setting['process_name'] . '-process');
        $processHelper = new RedisPush($worker, $this);
        $processHelper->run();
    }
    /**
     *@brief 获取redis句柄
     *@param
     *@return
     */
    public function connectRedis() {
        $idc = CURRENT_IDC;
        if (empty($idc)) {
            $idc = 'default';
        }
        $config = RedisConfig::$server[$idc];
        if (is_string($config)) {
            $newIdc = $config;
            $config = RedisConfig::$server[$newIdc];
        }
        $i = 0;
        foreach($config as $con) {
            $this->redisHelper[$i++] = new RedisClient($con['ip'], $con['port']);
        }
    }
    /**
     *@brief 获取一个redis链接 
     *@param
     *@return
     */
    public function getRedis() {
        $num = count($this->redisHelper);
        $key = rand(0, $num - 1);
        return $this->redisHelper[$key];
    }
}
