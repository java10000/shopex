<?php
class messenger_email{

    var $name = '电子邮件'; //名称
    var $iconclass="sysiconBtn email"; //操作区图标
    var $name_show = '发邮件'; //列表页操作区名称
    var $version='$ver$'; //版本
    var $updateUrl='';  //新版本检查地址
    var $isHtml = true; //是否html消息
    var $hasTitle = true; //是否有标题
    var $maxtime = 300; //发送超时时间 ,单位:秒
    var $maxbodylength =300; //最多字符
    var $allowMultiTarget=false; //是否允许多目标
    var $targetSplit = ',';
    var $dataname='email';
    var $debug = false;

    function ready($config){
        $system = &$GLOBALS['system'];
        $this->email = &$system->loadModel('system/email');
        $config['sendway'] = $config['sendway'] ? $config['sendway'] : 'smtp';
        
        if($config['sendway']=='smtp'){
            $this->email->smtp = &$system->loadModel('utility/smtp');;
            if( !$this->email->SmtpConnect($config) ){ return false;}
        }
        return true;
    }

    /**
     * finish 
     * 可选方法，结束发送时触发
     * 
     * @param mixed $config 
     * @access public
     * @return void
     */
    function finish($config){
        if($config['sendway']=='smtp'){
            $this->email->SmtpClose();
        }
    }
    
    function send($to, $subject, $body, $config){
        $system = &$GLOBALS['system'];
        $body = str_replace('|use_reply','',$body);  //所有邮件不需要回复
        $config['sendway']=($config['sendway'])?$config['sendway']:'smtp';
        
        // 缓存禁词
        $blacklist_cache = HOME_DIR.'/cache/blacklistcache.txt';
        if ( !is_file($blacklist_cache) || (filemtime($blacklist_cache) < NOW - 30*24*3600) ) {
            $bl_url = $system->loadModel('system/sms')->get_blacklist();
            $blacklist = file_get_contents($bl_url);
            file_put_contents($blacklist_cache, $blacklist); // 禁词 每行一个
        } else {
            $blacklist = file_get_contents($blacklist_cache);
        }
        
        // 禁词过滤
        $bl=preg_split("/\r\n|\n\r|\n/",$blacklist);
        for ($I=count($bl)-1; $I>=0 && strlen($body)>0; $body = str_replace($bl[$I],'#-#',$body),$I--);
        for ($I=count($bl)-1; $I>=0 && strlen($subject)>0; $subject = str_replace($bl[$I],'#-#',$subject),$I--);
        
        return $system->loadModel('system/email')->send($to,$subject,$body,$config);
    }

    function getOptions(){
        $edm_conf_url = EDM_WEB_API_URL;
        return array(
            'sendway'=>array(
                'label'=>'发送方式','type'=>'radio',
                'options'=>array(
                    'mail'=>"使用本服务器发送",
                    'smtp'=>"使用外部SMTP发送",
                    'edm'=>"使用Shopex-EDM发送(收费)",
                ),
                'value'=>"mail"),
                
            'usermail'=>array('label'=>'发信人邮箱','type'=>'input','value'=>'yourname@domain.com'),
            'smtpserver'=>array('label'=>'smtp服务器地址','type'=>'input','value'=>'mail.domain.com'),
            'smtpport'=>array('label'=>'smtp服务器端口','type'=>'input','value'=>'25'),
            'smtpuname'=>array('label'=>'smtp用户名','type'=>'input','value'=>''),
            'smtppasswd'=>array('label'=>'smtp密码','type'=>'password','value'=>''),
            'edmconfig'=>array('label'=>'配置EDM','type'=>'button','value'=>'配置','onclick'=>"window.location.href=\"index.php?ctl=member/messenger&act=edm\""),
        );
    }
}
