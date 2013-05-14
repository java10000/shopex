<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_2_0_sync extends shop_api_object {
    
    function sync_mark(){
        $file = HOME_DIR.'/sync_mark.txt';
        $mess = "sync_mark";
        file_put_contents($file,$mess);
        $result = "file is create!";
        $this->api_response('true',false,$result);
    }
}
?>
