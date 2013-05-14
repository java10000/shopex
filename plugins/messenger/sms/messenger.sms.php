<?php
class messenger_sms{
    var $name             = '手机短信'; //名称
    var $iconclass        = "sysiconBtn sms"; //操作区图标
    var $name_show        = '发短信'; //列表页操作区名称
    var $version          = '$ver$'; //版本
    var $updateUrl        = false;  //新版本检查地址
    var $isHtml           = false; //是否html消息
    var $hasTitle         = false; //是否有标题
//    var $maxtitlelength = 300; //最多字符
    var $maxtime          = 300; //发送超时时间 ,单位:秒
    var $maxbodylength    = 300; //最多字符
    var $allowMultiTarget = false; //是否允许多目标
//  var $targetSplit      = ','; //多目标分隔符
    var $withoutQueue     = false;
    var $dataname         = 'mobile';
    var $sms_service_ip   = '124.74.193.222';
    var $sms_service      = 'http://idx.sms.shopex.cn/service.php';

    function messenger_sms(){
        $this->system = &$GLOBALS['system'];
        $this->net=&$this->system->loadModel('utility/http_client');
        $this->sms=&$this->system->loadModel('system/sms');
    }

    function send($to,$message,$config,$sms_type){
        if( false !== strpos($message,'|use_reply') ){
            $message = str_replace('|use_reply','',$message);
            $this->use_reply = 1;
        }
        
        // 缓存禁词
        $blacklist_cache = HOME_DIR.'/cache/blacklistcache.txt';
        if ( !is_file($blacklist_cache) || (filemtime($blacklist_cache) < NOW - 30*24*3600) ) {
            $bl_url = $this->sms->get_blacklist();
            $blacklist = file_get_contents($bl_url);
            file_put_contents($blacklist_cache, $blacklist); // 禁词 每行一个
        } else {
            $blacklist = file_get_contents($blacklist_cache);
        }
        // 禁词过滤
        for ($bl=preg_split("/\r\n|\n\r|\n/",$blacklist),$I=count($bl)-1;
            $I>=0 && strlen($message)>0;
            $message = str_replace($bl[$I],'#-#',$message),$I--);
            
        $content = array(
            'content'=>$message,
            'phones'=>trim(trim($to),','),
        );
        $reply = $this->use_reply ? '|1':''; // 是否允许恢复短信
        $send_type = strpos($content['phones'],',') ? 'fan-out' : 'notice'; // todo 
        return $this->sms->send('['.json_encode($content).']'.$reply,'sms.send',$send_type);
    }
    
    function apply(){
        $certiMdl = $this->system->loadModel('service/certificate');
        $submit_str['certi_id'] = $certiMdl->getCerti();
        $submit_str['ac'] = md5($submit_str['certi_id'].$certiMdl->getToken());
        $submit_str['version']=$this->version;
        $results = $this->net->post($this->sms_service,$submit_str);
        return $results{0} == '0';
    }

    /**
     * ready
     * 可选方法，准备发送时触发
     *
     * @param mixed $config
     * @access public
     * @return void
     */
    function ready($config){
        return $this->apply($this->sms_service,$this->version);
    }

    /**
     * finish
     * 可选方法，结束发送时触发
     *
     * @param mixed $config
     * @access public
     * @return void
     */
    function finish($config){}
    
    function extraVars(){
        return array('outgoingOptions'=>$this->sms->getSmsUrl('sms','accountsList'));
    }
}
