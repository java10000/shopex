<?php
include_once CORE_DIR.'/api/shop_api_object.php';

class api_matrix extends shop_api_object {
    function matrix_callback() {
        $queue_id = $_GET['queue_id'];
        
        $queueMdl =& $this->system->loadModel('plugins/ome/ome_queue');
        
        $resp = array(
            'msg_id'=>$_POST['msg_id'],
            'rsp'=>$_POST['rsp'],
            'err_msg'=>$_POST['err_msg'],
        );
        $queueMdl->update($queue_id,json_encode($resp),true);
    }
}
