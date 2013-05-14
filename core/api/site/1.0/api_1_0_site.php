<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_1_0_site extends shop_api_object {
    var $max_number=100;
    
    function search_site_information(){
        $result['site_name']=$this->system->getConf('system.shopname');
        $result['site_desc']=$this->system->getConf('system.shopname');
        $result['site_address']=$this->system->getConf('store.address');
        $result['site_phone']=$this->system->getConf('store.telephone');
        $result['site_zip_code']=$this->system->getConf('store.zip_code');
        $this->api_response('true',false,$result);
    }
    function template_info(){
        $theme_id=$this->system->getConf('system.ui.current_theme');
        $result['templateid']=$theme_id;
        $o_theme=$this->system->loadModel('system/template');
        $them_info=$o_theme->getThemeInfo($theme_id);
        $result['templatename']=$them_info['name'];
        $this->api_response('true',false,$result);
    }
}