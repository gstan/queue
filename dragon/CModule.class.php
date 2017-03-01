<?php
namespace Dragon;
/**
 * 所有的Action的基类，主要复杂初始化smarty
 * author gstan
 * date 2013-02-07
 * 474703907@qq.com
 */
abstract class CModule {

    public $httpServer;
    public $response;
    /**
     *@brief 构造函数
     *@param 
     *@return
     */
	public function __construct(Http $http) {
        $this->httpServer = $http->server;
        $this->response = $http->response;
    }


	//必须实现的方法
	abstract function run() ;
//    abstract function response();
}
