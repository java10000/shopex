<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
/**
 * API 模块部份
 * @package
 * @version 3.0: 
 * @copyright 2003-2011 ShopEx
 * @author 张君华
 * @license Commercial
 */

class api_3_0_server extends shop_api_object {
    
    //绑定关系
    function add_mobile_shop_info($data){
        $this->system->setConf('m_certificate.mobile_node_id',$data['node_id']);
        $this->system->setConf('m_certificate.mobile_certi_id',$data['certi_id']);
        $this->system->setConf('m_certificate.mobile_api_url',$data['api_url']);
        $this->system->setConf('m_certificate.mobile_shop_name',$data['shop_name']);
        $this->system->setConf('m_certificate.mobile_token',$data['token']);
        
        $platform = $this->system->loadModel('system/platform');
        $info=$platform->apply_node_bind();
        $this->api_response('true','',$info);
    }
}