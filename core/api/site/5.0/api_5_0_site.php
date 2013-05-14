<?php

/**
 * API 模块部份
 * @package
 * @version 5.0: 
 * @copyright 2003-2011 ShopEx
 * @author 张君华
 * @license Commercial
 */

class api_5_0_site extends shop_api_object {
    
    //绑定
    function auto_bind($data){
        $ptype = $data['ptype']; // 请求绑定的产品类型
        $this->system->setConf('binding.'.$ptype.'.node_id',$data['node_id']);
        $this->system->setConf('binding.'.$ptype.'.certi_id',$data['certi_id']);
        $this->system->setConf('binding.'.$ptype.'.shop_name',$data['shop_name']);
        
        $target = array('node_id'=>$data['node_id']);
        $platMdl = $this->system->loadModel('system/platform');
        
        // 同意绑定
        $info = $platMdl->agree_node_bind($target);
        
        // 主动申请绑定
        $platMdl->apply_node_bind($target);
        
        $this->api_response('true','',$info);
    }
    
    //回调
    function bind_notify($data) {
        $platMdl = $this->system->loadModel('system/platform');
        $platMdl->agree_accept_notify($target);
    }
}
