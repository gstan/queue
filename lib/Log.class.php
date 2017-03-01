<?php
namespace Lib;
/**
 * 日志类，负责记录各种日志
 * author gstan
 * email 474703907@qq.com
 */

class Log {

	private $logfile; //日志的详细文件名
	private $logdir;//日志的目录
    private $warningFile;

    /**
     *@brief 单例模式
     *@param
     *@return
     */
	public static function instance($filename = LOG_SERVICE) {
		static $singletons = array();
		!isset($singletons[$filename]) && $singletons[$filename] = new Log($filename);
		return $singletons[$filename];
		
	}

    /**
     *@brief 构造函数
     *@param
     *@return
     */
	public function __construct($filename) {
		try{
			$this->logdir = LOG_PATH .	$filename  . '/';
			$this->logfile = $this->logdir . $filename . '.log';
            $this->warningFile = $this->logfile . '.wf';
			//如果还没存在这个日志,创建这个日志的目录
			if (!is_dir($this->logdir)) {
				 system("mkdir -p " . $this->logdir . ";chmod -R 777 " . $this->logdir);
			}
		}
		catch(\Exception $e) {

		}
	}
	/**
	 *@brief  日志写入
     *@param str:日志内容 level:错误级别
     *@return
	 */
	public function write($str, $level = LOG_NORMAL) {
        $str = Date("Y-m-d H:i:s") . "\t" . $str;
		//判断日志等级,输出堆栈信息
		if ($level >= LOG_WARNING) {
            /*
			$traces = debug_backtrace();
            $msg = "";
			foreach($traces as $trace)
			{
				if(isset($trace['file'],$trace['line'])) {
					$msg .= "\tin ".$trace['file'].' ('.$trace['line'].')' ;
				}
			}
			$str .= $msg;
             */
		}
        $str .= "\n";
        if ($level == LOG_NORMAL) {
		    file_put_contents($this->logfile, $str, FILE_APPEND);
        }
        else {
		    file_put_contents($this->warningFile, $str, FILE_APPEND);
        }
		//如果需要日志收集
		if (LOG_COLLECT) {
			
		}
	}
    /**
     *@breif 写exception
     *@param
     *@return
     */
    public function writeException($e, $log) {
        $errorNo = $e->getCode();
        $errorMsg = $e->getMessage();
        $arr = array(
            'errorNo' => $errorNo,
            'errorMsg' => $errorMsg,
        );
        $str = json_encode($arr);
        $log = $log . ' '.  $str;
        self::write($log,LOG_WARNING);
    }

}

