<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Http.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-15
*   描    述：
*
============================================*/
namespace Lib;

class Http {
    public static $httpCode;
    public static $curlErrno;
    /**
     *@brief 发生get请求
     *@param
     *@return
     */
    public static function post($url, $postData, $timeout = 10) {
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1); //设置POST提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); //提交POST数据
		$result = curl_exec($ch); 
		self::$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        self::$curlErrno = curl_errno($ch);
		//有错误发生
        if (self::$curlErrno != CURLE_OK) { 
			$str = $url . " curl code:" . self::$curlErrno;
			Log::instance()->write($str);
			return  false;
		}
		if (self::$httpCode != 200) {
			return false;
		}
		return $result;
    }

}
