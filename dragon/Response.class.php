<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Response.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-23
*   描    述：
*
============================================*/

namespace Dragon;

class Response {

    public $http_protocol = 'HTTP/1.1';
    public $http_status = 200;
    public $timeHelper;

    public $head;
    public $cookie;
    public $body;
    public $get = array();
    public $post = array();
    public $request;
    public $server = array();
    public $header;
    public $remote_ip;
    public $serverResponse;


    static $HTTP_HEADERS = array(
        100 => "100 Continue",
        101 => "101 Switching Protocols",
        200 => "200 OK",
        201 => "201 Created",
        204 => "204 No Content",
        206 => "206 Partial Content",
        300 => "300 Multiple Choices",
        301 => "301 Moved Permanently",
        302 => "302 Found",
        303 => "303 See Other",
        304 => "304 Not Modified",
        307 => "307 Temporary Redirect",
        400 => "400 Bad Request",
        401 => "401 Unauthorized",
        403 => "403 Forbidden",
        404 => "404 Not Found",
        405 => "405 Method Not Allowed",
        406 => "406 Not Acceptable",
        408 => "408 Request Timeout",
        410 => "410 Gone",
        413 => "413 Request Entity Too Large",
        414 => "414 Request URI Too Long",
        415 => "415 Unsupported Media Type",
        416 => "416 Requested Range Not Satisfiable",
        417 => "417 Expectation Failed",
        500 => "500 Internal Server Error",
        501 => "501 Method Not Implemented",
        503 => "503 Service Unavailable",
        506 => "506 Variant Also Negotiates",
    );
    /**
     * @brief 将原始请求信息转换到PHP超全局变量中
     * @param
     * @return
     */
    public function setGlobal() {
        $_GET = $this->get;
        $_POST = $this->post;
        $_SERVER = $this->server;
        if (!empty($this->get) && !empty($this->post)) {
            $_REQUEST = array_merge($this->get, $this->post);
        }
        elseif (!empty($this->get)) {
            $_REQUEST = $this->get;
        }
        else {
            $_REQUEST = $this->post;
        }
    }
    /**
     *@brief  构造函数
     *@param
     *@return
     */
    public function __construct() {
        $this->timeHelper = new \Lib\TimeHelper();
        $this->timeHelper->start();
        $this->__init();
    }
    /**
     *@brief 初始化
     *@param
     *@return
     */
    public function __init() {
        $this->head = array(
            'Connection' => 'close',
            'Content-Type' => 'text/html;charset=utf-8',
            'KeepAlive' => 'off',
        );
    }
    /**
     * 设置Http状态
     * @param $code
     */
    public function setHttpStatus($code) {
        $this->http_status = $code;
    }

    /**
     * 设置Http头信息
     * @param $key
     * @param $value
     * @return
     */
    public function setHeaders() {
        $this->serverResponse->status($this->http_status);
        //$this->head['Content-Length'] = strlen($this->body);
        //foreach($this->head as $key => $value) {
            $this->serverResponse->header($key, $value);
            //$headers .= $key. ':' . $value . "\r\n";
       // }
        return $headers;
    }

    public function noCache() {
        $this->head['Cache-Control'] = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0';
        $this->head['Pragma'] = 'no-cache';
    }
	/**
     * 抛出错误异常
     * @param type $errorNo
     * @return
     */
    public function throwError($errorNo, $errorMsg) {
        throw new \Exception($errorMsg, $errorNo);
    }

    /**
     *@brief 设置exception的相应
     *@param
     *@return
     */
    public function setExceptionResponse($e, $arrData = array()) {
        $errorNo = $e->getCode();
        $errorMsg = $e->getMessage();
        $arrResult = compact('errorNo', 'errorMsg');
        $arrResult = array_merge($arrResult, $arrData);
        $strData = json_encode($arrResult);
        $this->body = $strData;
        $this->setHeaders();
        $this->serverResponse->end($strData);
        $this->writeAccessLog();
    }
    /**
     *@breif 设置正常的输出
     *@param
     *@return
     */
    public function setSuccessResponse($data = array()) {
        $arrData['errorNo'] = 0;
        $arrData['errorMsg'] = 'success';
        $arrData = array_merge($arrData, $data);
        $strData = json_encode($arrData);
        $this->body = $strData;
        $this->setHeaders();
        $this->serverResponse->end($strData);
        $this->writeAccessLog();
    }
    /**
     *@brief 设置输出结果
     *@param
     *@return
     */
    public function setResponse($response, $arrData) {
        $strData = json_encode($arrData);
        $this->body = $strData;
        $this->setHeaders();
        $this->serverResponse->end($strData);
        $this->writeAccessLog();
    }
    /**
     *@brief 设置错误的输出结果
     *@param
     *@return
     */
    public function setErrorResponse($errorNo, $errorMsg) {
        $arrData['errorNo'] = $errorNo;
        $arrData['errorMsg'] = $errorMsg;
        $strData = json_encode($arrData);
        $this->body = $strData;
        $this->setHeaders();
        $this->serverResponse->end($strData);
        $this->writeAccessLog();
    }
    /**
     *@brief 记录access日志
     *@param
     *@return
     */
    public function writeAccessLog() {
        $this->timeHelper->stop();
        $used = $this->timeHelper->spent();
        $log = "";
        $log = $_SERVER['REQUEST_URI'] .  "\t" . 
               $_SERVER['REQUEST_METHOD'] . "\t" . 
               $_SERVER['SERVER_PROTOCOL'] . "\t" . 
               $this->http_status . "\t" . 
               $_SERVER['REQUEST_TIME'] . "\t" . 
               $_SERVER['REMOTE_ADDR'] . "\t" . 
               $_SERVER['QUERY_STRING'] . "\t" . 
               $_SERVER['USER-AGENT'] . "\t" . 
               $_REQUEST['msg_id'] . "\t" . 
               $this->body . "\t"  .
               $used ; 
		\Lib\Log::instance(LOG_ACCESS)->write($log);
    }

}
