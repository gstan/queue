<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：HttpClient.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-20
*   描    述：
*
============================================*/
namespace Lib;
//官方的不可以用
class HttpClient
{
    protected $config;//配置
    protected $callback;//注册回调
    protected $client;//客户端
    protected $msgId;//消息id

	/**
     * @brief 构造函数
	 *@param
	 *@return
	 */
    public function __construct($url) {
		$arr = parse_url($url);
		$ip = gethostbyname($arr['host']);
		$port = $arr['port'] ? $arr['port'] : 80;
        $this->config = array(
            'ip' => $ip,
            'port' => $port,
            'host' => $arr['host'],
            'url' => $url,
            'uri' => $arr['path'],
        );
    }
	/**
     * @brief post请求
	 *@param
	 *@return
	 */
	public function post($postData, $callback) {
        $this->msgId = $postData['msg_id'];
         \Lib\Log::instance()->write($this->msgId . ' start post');
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $client->on('connect', array($this, 'onConnect'));
        $client->on('error', array($this, 'onError'));
        $client->on('receive', array($this, 'onReceive'));
        $client->on('close', array($this, 'onClose'));
        $client->connect($this->config['ip'], $this->config['port']);
	    $this->client = $client;
        $this->postData = $postData;
        $this->callback = $callback;
        //数据解析暂时不用做
        //如果25s没有回调500
        $this->timeId = swoole_timer_after(25000, [$this, 'onFailTimer']);
        Log::instance()->write($this->msgId . " connect timeId" . $this->timeId);
	}


    /**
     *@brief 连接成功
     *@param
     *@return
     */
    public function onConnect($client) {
        $postValue = http_build_query($this->postData);
        $postLen = strlen($postValue);
        $request = "";
        $request .= "POST {$this->config['uri']} HTTP/1.1\r\n";
		$request .= "Host: {$this->config['host']}\r\n"; 
		$request .= "User-Agent: MovieQueueServer\r\n";
		$request .= "Accept: text/html \r\n"; 
		$request .= "Accept-Language: en-us, en;q=0.50\r\n"; 
		$request .= "Accept-Encoding: gzip, deflate, compress;q=0.9\r\n"; 
		$request .= "Accept-Charset: utf-8, utf-8;q=0.66, *;q=0.66\r\n"; 
		$request .= "Connection: keep-alive\r\n"; 
		$request .= "Cache-Control: max-age=0\r\n"; 
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: {$postLen}\r\n\r\n";
        $request .= $postValue;
        $client->send($request);
    }
    /**
     *@brief 接受消息
     *@param
     *@return
     */
    public function onReceive($client, $data) {
        $bool = swoole_timer_clear($this->timeId);
        Log::instance()->write($this->msgId . ' time_id' . $this->timeId . ',clear timer:' . $bool);
        Log::instance()->write($this->msgId . ' http client on receive');
        $pattrn = "|HTTP/1.1\s+?(\d+)\D*|";
        preg_match($pattrn, $data, $match);
        if (empty($match)) {
            $log = $this->msgId . " get http code failed";
            Log::instance()->write($log,LOG_WARNING);
            $httpCode = 500;
        }
        else {
            $httpCode = $match[1];
            $log =  $this->msgId .  ':' . $this->config['url'] . ' response http_code:' . $httpCode;
            Log::instance()->write($log);
        }
        //数据解析暂时不用做
        $result = array(
            'http_code' => $httpCode,
            'http_data' => '',
        );
        //非200需要放在task任务里进行
        call_user_func($this->callback, $result);
	    $client->close(true);
    }
    /**
     *@brief 错误
     *@param
     *@return
     */
    public function onError($client) {
        Log::instance()->write($this->msgId . ' http client on error');
        //数据解析暂时不用做
        $result = array(
            'http_code' => '500',
            'http_data' => '',
        );
        //非200
        call_user_func($this->callback, $result);
    }
    /**
     *@brief 25s没有回应
     *@param
     *@return
     */
    public function onFailTimer() {
        Log::instance()->write($this->msgId . ' on fail timer');
        //数据解析暂时不用做
        $result = array(
            'http_code' => '500',
            'http_data' => '',
        );
        //非200
        call_user_func($this->callback, $result);
        $this->client->close();
    }

    /**
     *@brief 关闭连接
     *@param
     *@return
     */
    public function onClose($client) {
        Log::instance()->write($this->msgId . ' http client on close');
    }



}
