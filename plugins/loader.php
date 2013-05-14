<?php
ob_start();
if(include(dirname(__FILE__).'/../config/config.php')){
    ob_end_clean();
    if(!defined('CORE_INCLUDE_DIR')){
        define('CORE_INCLUDE_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/include_v5':'/include'));
    }
    require(CORE_INCLUDE_DIR.'/shopCore.php');
    require_once(CORE_DIR.'/func_ext.php');
    class pluginCore extends shopCore{
        function run(){}
    }

    $system = new pluginCore(array());
}else{
    header('HTTP/1.1 503 Service Unavailable',true,503);
    die('<h1>Service Unavailable</h1>');
}
?>
