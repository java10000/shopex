<?php
class commodity_radar_public extends ShopObject {
    
    var $url = 'http://service.shopex.cn/';//外网
    //var $url = "http://service.ex-sandbox.com/";//内网
    var $certi_id = '';
    var $token = '';
    var $product_key = VERIFY_APP_ID;
    
    function commodity_radar_public(){
      $this->system = &$GLOBALS['system'];
      $this->db = $this->system->database();
      $this->certi_id = $this->system->getConf('certificate.id');
      $this->token = $this->system->getConf('certificate.token');
    }
    
    function make_ac($params,$token="GMGetToken"){
        $str  = '';
        ksort($params);
        foreach($params as $key => $value){
            $str.= $value;
        }
        $res = md5($str.$token);
        
        return  $res;
    }
    
   
    function assemble($params){
        if(!is_array($params))  return null;
        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }
        return $sign;
    }
    
    function get_sign($data){
        $token = $this->get_token();
        $rs = strtoupper(md5(strtoupper(md5($this->assemble($data))).$token));
        
        return $rs;
    }
    
    function verify_license(){
        if ($this->get_token()){
            return true;
        }
        return false;
    }
    
    function get_token($type='remote'){
        if ($type == 'remote'){
            $data = array(
                'certi_app'         => 'certi.get_token',
                'certificate_id'    => $this->certi_id,
            );
            
            $data['certi_ac']   = $this->make_ac($data);
            $net = &$this->system->loadModel('utility/http_client');
            $net_result = $net->post($this->url,$data);
            $rs = json_decode($net_result,true);
            if ($rs['res'] == 'succ'){
                $this->remote_token = $rs['info']['token'];
                return $this->remote_token;
            }
        }else if ($type == 'local'){
            return $this->token;
        }
        return false;
    }
}