<?php
namespace Dragon;
/**
 * dragon base file
 * author gstan 
 * email 474703907@qq.com
 * date 2013-02-04
 */
use  Lib\Log;

class Dragon {
	
	
	private static $map = array();

	public static function start() {
		//设定错误和异常处理
		register_shutdown_function(array("Dragon\\Dragon", 'fatal'));
		//注册自动加载函数
		spl_autoload_register(array("Dragon\\Dragon", 'autoload'));
        //swoole
        try {
            $config = include ROOT_PATH . '/config/Server.class.php';
            $server = new Server($config);
            $server->run();
        }
        catch(\Exception $e) {

        }
	}

    // 致命错误捕获
    public static function fatal() {
        if ($e = error_get_last()) {
            self::error($e['type'],$e['message'],$e['file'],$e['line']);
        }
    }
    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    public static function error($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_ERROR :
            case E_PARSE :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
            case E_USER_ERROR :
                $errorStr = "$errstr ".$errfile." 第 $errline 行.";
                if(LOG_RECORD) {
                    Log::instance()->write($errorStr, LOG_FATAL);
                }
                break;
            case E_STRICT :
            case E_USER_WARNING :
            case E_USER_NOTICE :
            default :
                $errorStr = "[$errno] $errstr ".$errfile." 第 $errline 行.";
                if(LOG_RECORD) {
                    Log::instance()->write($errorStr, LOG_WARNING);
                }
                break;
        }
    }

	/**
     *@brief 字段加载
     *@param
     *@return
     */
	public static function  autoload($classname) {
		//采用命名空间的
		$path = "";
		$namespace = explode("\\", $classname);
		$class = array_pop($namespace);
		//构造路径
		$path  = implode('/', $namespace);
		$path = ROOT_PATH . "/" . strtolower($path) . "/";
        if (isset(self::$map[$classname])) {
            return true;
        }
        $file = $path . $class . ".class.php";
        if (!file_exists($file)) {
            return false;
        }
        require($file);
        self::$map[$classname] = 1;
	}
}

