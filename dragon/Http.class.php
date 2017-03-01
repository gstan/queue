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

class Http {

    public $response;
    public $server;

    /**
     *@brief 实例化server
     *@param
     *@return
     */
    public function __construct($server) {
        $this->response = new Response();
        $this->server = $server;
        $this->assign($this->response);
    }
    /**
     *@brief 运行
     *@param
     *@return
     */
    public function run() {
		Dispatcher::instance()->dispatch($this);
    }
    /**
     *@brief 将swoole扩展产生的请求对象数据赋值给框架的Request对象
     * @param Swoole\Request $response
     * @return
     */
    public function assign(Response $response)  {
        if (isset($this->server->request->get)) {
            $response->get = $this->server->request->get;
        }
        if (isset($this->server->request->post)) {
            $response->post = $this->server->request->post;
        }
        if (isset($this->server->request->server)) {
            foreach($this->server->request->server as $key => $value) {
                $response->server[strtoupper($key)] = $value;
            }
            $response->remote_ip = $this->server->request->server['remote_addr'];
        }
        $response->header = $this->server->request->header;
        $response->setGlobal();
        $response->serverResponse = $this->server->response;
    }
}
