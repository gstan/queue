<?php
/*========================================
*   Copyright (C) 2016 All rights reserved.
*   
*   文件名称：Conf.class.php
*   创 建 者：tanguoshuai
*   创建日期：2016-12-23
*   描    述：
* 读取配置
============================================*/
namespace Lib;

class Conf {

    /**
     *@brief 获取配置queue_nj/queue_name
     *@param
     *@return
     */
    public static function getConf($item) {
        $path = CONF_PATH;
        $arrItem = explode("/", $item);
        if (empty($arrItem)) {
            return false;
        }
        $itemNum = count($arrItem);
        if ($itemNum > 3) {
            return false;
        }
        $filePath = $path . $arrItem[0] . '.ini';
        if (!file_exists($filePath)) {
            return false;
        }
        //先获取到文件数据
        $config = parse_ini_file($filePath, true);
        array_shift($arrItem);
        $conf = self::getData($config, $arrItem);
        return $conf;
    }

    /**
     *@brief 获取配置数据
     *@param
     *@return
     */
    public static function getData($config, $item) {
        if (empty($item)) {
            return $config;
        }
        $key = array_shift($item);
        //说明递归结束
        if (empty($item) && isset($config[$key])) {
            return $config[$key];
        }
        if (empty($item) && !isset($config[$key])) {
            return false;
        }
        if (isset($config[$key]) && is_array($config[$key])) {
            $config = $config[$key];
            self::getData($config, $item);
        }
        return false;
    }
        
    /**
     *@brief 写配置数据
     *@param
     *@return
     */
    public static function writeConf($file, $arrData) {
        $str = "";
        foreach($arrData  as $key => $value) {
            $str .= $key . '=' . $value . "\n";
        }
        $path = CONF_PATH;
        $filePath = $path . $file . '.ini';
        file_put_contents($filePath, $str);
    }
}
