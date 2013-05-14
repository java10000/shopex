<?php
ob_start();
if(file_exists('config/config.php')){
    require('config/config.php');
    ob_end_clean();
    if(isset($_GET['dav'])){
        require(CORE_DIR.'/include/shopdav.php');
        $system = new shopdav();
    }else{
        require(CORE_DIR.'/api/shop_api.php');
        $system = new shop_api();
    }
}else{
    header('HTTP/1.1 404 Not Found',true,'404');
}
?>
