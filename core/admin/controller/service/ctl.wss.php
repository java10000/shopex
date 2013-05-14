<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_wss extends adminPage {
    var $workground ='analytics';
    var $WSS_REG_DOMAIN = 'http://wss.cnzz.com/user/companion/shopex.php?';
    var $WSS_LOGIN_DOMAIN = 'http://wss.cnzz.com/user/companion/shopex_login.php?';
    var $WSS_IFRAME_DOMAIN = 'http://wss.cnzz.com/oem/udmin.php?';
    var $JS = "<script src='http://pw.cnzz.com/c.php?id=###&l=2' language='JavaScript' charset='gb2312'></script>";
    var $aError = array('-1'=>'验证KEY错误','-2'=>'域名长度错误','-3'=>'域名输入错误','-4'=>'域名开通错误','-5'=>'IP限制');
    var $path = array(array('text'=>'统计报表'));

    /**
     * construct the wss
     */
    function ctl_wss(){
        parent::adminPage();
        $this->ENCODESTR='A34dfwfF';
    }
    /**
    * show the wss
    */
    function show(){
        $this->path[] = array('text'=>__('统计功能配置'));
        $this->pagedata['action']=$this->getWss();
        if($this->getWss()){
           $this->pagedata['url']= $this->apply();
           $this->pagedata['wss_id']= $this->getUserName();
           $this->pagedata['wss_password']= $this->getPassword();
        }
        if($this->getShowIndex()){
            $this->pagedata['str']= __('关闭统计功能');
        }else{
            $this->pagedata['str']= __('开启统计功能');
        }
        $this->page('service/wss.html');
    }
    /**
     * register the wss
     */
    function register(){
        set_time_limit(0);
        $net = &$this->system->loadModel('utility/http_client');
        $result=$net->get($this->getLoginDomain());
        $r=$this->response($result);
        if($r){
            $this->splash('failed','index.php?ctl=service/wss&act=show',__('申请统计失败:').$this->aError[$r]);
              exit;
        }
        if($this->setStatus($result)){;
            $this->splash('success','index.php?ctl=service/wss&act=show','申请统计成功');  
        }else{
            $this->splash('failed','index.php?ctl=service/wss&act=show','申请统计失败:参数错误');
            exit;
        }
    }

    /**
     * clear the wss
     */
    function clear(){
        $this->system->setConf('shopex.wss.enable',0);
        $this->splash('success','index.php?ctl=service/wss&act=show',__('清除统计成功'));
    }
    /**
    * login to the wss
    */
    function getShowIndex(){
        return $this->system->getConf('shopex.wss.show');
    }

    /**
    * set the wss show index
    */
    function setShowIndex(){
        if($this->getShowIndex()){
            $this->system->setConf('shopex.wss.show',0);
            $str = __('关闭');
        }else{
            $this->system->setConf('shopex.wss.show',1);
            $str = __('开启');
            $this->setJs();
        }
        $this->splash('success','index.php?ctl=service/wss&act=show',$str.__('前台统计成功'));
    }

    function apply(){
        return $this->url= $this->WSS_LOGIN_DOMAIN.'site_id='.$this->getUserName().'&password='.$this->getPassword();
    }

    function setJs(){
        $content=str_replace('###',$this->getUserName(),$this->JS);
        $this->system->setConf('shopex.wss.js',$content);
    }
    /**
     * get username for the wss
     */
    function getUserName(){
        return $this->system->getConf('shopex.wss.username');
    }
    /**
     * get username for the wss
     */
    function getPassword(){
        return $this->system->getConf('shopex.wss.password');
    }
    /**
    * set status for the wss
    */
    function setStatus($r){
        $tmp = explode('@',$r);
        if($tmp[0]<0){
            return false;
        }
        $this->system->setConf('shopex.wss.username',$tmp[0]);
        $this->system->setConf('shopex.wss.password',$tmp[1]);
        $this->system->setConf('shopex.wss.enable',1);
        return true;
    }
    /**
     * get error for the wss
     */

    function response($result){
        if(isset($this->aError[$result])){
            return $result;
        }else{
            return false;
        }
    }
    /**
     * get login domain
     */
    function getLoginDomain(){
        $domain = HTTP_HOST;
        $key = md5($domain.$this->ENCODESTR);
          return $this->WSS_REG_DOMAIN.'domain='.$domain.'&key='.$key;
    }
    /**
     * check wss register or apply
     */
    function getWss(){
        return $this->system->getConf('shopex.wss.enable');
    }
    function logininx(){
        $this->path[] = array('text'=>__('访问统计'));
        if($this->getWss()){
            $this->pagedata['url'] = $this->apply();
            $this->page('service/wssframe.html');
        }else{
            $this->show();
        }
    }
    function welcome(){
        $this->page('service/welcome.html');
    }
}
?>
