<?php
namespace Dragon;
/**
 * url路由转发
 * author gstan
 * date 2013-02-005
 * email 474703907@qq.com
 */
use Dragon\CModule;
use Config\Errno;

class Dispatcher {

	private $url;
	private $modules;
	private $action;
	private $path;

	/**
     * @brief 单例模式
     * @param
     * @return
	 */
	public static function instance() {
		static $singleton = null;
		!isset($singleton) && $singleton = new Dispatcher();
		return $singleton;
	}
	
	/**
     * @brief 构造函数
     * @param
     * @return
	 */
	public function __construct() {
	}
	/**
     * @brief 路由分发
     * @param
     * @return
	 */
	public function dispatch(Http $http) {
        try {
            $url = preg_replace("/(.+)(\?.*)/i", "\\1", $http->server->request->server['request_uri']);

            $args = explode("/", $url);
            
            $this->modules = isset($args[1]) ? $args[1] : null;
            //如果不存在的连接都到默认的连接
            if (empty($this->modules)) {
                $this->modules = DEFAULT_MODULES;
            }
            $this->path = MODULES_PATH . $this->modules;
            if (!is_dir($this->path)) {
                $this->modules = DEFAULT_MODULES;
                $this->path = MODULES_PATH . $this->modules;
            }
            $this->action = isset($args[2]) ? $args[2] : null;
            if (empty($this->action)) {
                $this->action = $this->modules;
            }
            //默认执行run函数
            $func = DEFAULT_METHOD;
            $tmpAction = ucwords($this->action);
            $this->action = "Modules\\" .  ucwords($this->modules) . "\\" . $tmpAction;
            $file = $this->path . "/" . $tmpAction . ".class.php";
            if (!file_exists($file)) {
                return $http->response->throwError(Errno::FILE_NOT_FOUND, Errno::FILE_NOT_FOUND_MSG);
            }
            $class = new $this->action($http);
            $class->$func();
        }
        catch(Exception $e) {
            return $http->response($swoole->response, $e);
        }
	}
}
