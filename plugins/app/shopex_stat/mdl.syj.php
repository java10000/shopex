<?php

include_once 'shopObject.php';
include_once 'config.php';

class mdl_syj extends shopObject{

    // 开通生意经
    function open_service(&$err){
        $certiMdl = $this->system->loadModel('service/certificate');
        $params = array(
            'method' => 'smb.reg',            
            'sign_method' => 'md5',
            'v' => '1.0',
            'format' => 'json',
            'timestamp' => time(),
            'license_id' => $certiMdl->getCerti(),
            'expire_time' => mktime()+10*24*3600,
        );
        $token = $certiMdl->getToken();
        if ( !$params['license_id'] ) {
            $err = '开通生意经需要您的网店部署在公网环境并且具备合法的<a href="?ctl=service/certificate&act=showIndex">Shopex证书</a>';
            return false;
        }
        $params['sign'] = $this->_create_sign($params,$token);
        
        $httpMdl = $this->system->loadModel('utility/http_client');
        if ( !$result = $httpMdl->post(STAT_COMMIT_URL, $params) ) {
            $err = '与服务：'.STAT_COMMIT_URL.'通信错误'; return false;
        }
        $results = json_decode($result,true);
        if('fail' == $results['rsp']){
            $err =$results['err_msg'] ;
        }
        return $results;
    }
    
    function account($account=false) {
        if ( $account && is_array($account) ) {
            $this->system->setConf('app.shopex_stat.uid',$account['uid']);
            $this->system->setConf('app.shopex_stat.token',$account['token']);
            return true;
        }
        
        $account = array(
            'uid'=>$this->system->getConf('app.shopex_stat.account.uid'),
            'token'=>$this->system->getConf('app.shopex_stat.account.uid'),
        );
        if ( !$account['uid'] || !$account['token'] ) {
            return false;
        }
        
        return $account;
    }
    
    function _create_sign($paramArr, $token) {
        $sign = '';
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '' && $key != 'sign') {
                $sign .= $key . $val;
            }
        }
        $sign = strtoupper(md5($sign . $token));
        return $sign;
    }
    
    function _state_encode($state_array) {
        return str_replace(array('+', '/', '='), array('_', '^', '~'), base64_encode(json_encode($state_array)));
    }
    
    function _state_decode($state) {
        $state = urldecode($state);
        return json_decode(base64_decode(str_replace(array('_', '^', '~'), array('+', '/', '='), $state)), TRUE);
    }
}
