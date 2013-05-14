<?php
class passport_phpwind7 extends modelFactory {

    var $passport_name = "PhpWind论坛V7.0";
    var $passport_memo = "此功能可进行第三方系统（譬如论坛系统）用户同步与登录同步设置（Phpwind论坛最新版本已内置本系统接口，其它系统请根据文档自行编写）；";
    var $_config = null;
    var $forward = 0;
    var $name = "phpwind7";
    function setConfig($config) {
        $this->_config = $config;
    }
  
    function verifylogin($login,$passwd){

    }

    function decode($responseData){

    }

    function getoptions(){
        return array(
            'URL'=>array('label'=>'论坛系统URL：','type'=>'input','required'=>'true'),
            'PrivateKey'=>array('label'=>'连接私钥：','type'=>'input'),
            'encoding'=>array('label'=>'论坛系统编码：','type'=>'select','options'=>array('utf8' => '国际化编码(utf-8)','zh' => '简体中文','big5' => '繁体中文','en' => '英文')),
            'conntype'=>array('label'=>'本网站作为：','type'=>'radio','options'=>array('service'=>'服务器端','client'=>'客户端'))
        );
    }

    function login($userId, $rurl) {
        $oMember = $this->system->loadModel('member/member');
        $aMember = $oMember->getFieldById($userId);
        $username = $aMember['uname'];

        if (true || $SITE_EODING=="UTF-8"){
            if($this->_config['encoding']!='utf8'){
                $charset = $this->system->loadModel('utility/charset');
                $username = $charset->utf2local($username,$this->_config['encoding']);
            }
        }

        $member = array(
            'username'    => $username,
            'password'    => $aMember['password'],
            'email'        => $aMember['email'], 
            'time'        => time()
        );
        $userdb_encode='';
        foreach($member as $key=>$val){
            $userdb_encode .= $userdb_encode ? "&$key=$val" : "$key=$val";
        }    
        $key = $this->_config['PrivateKey'];
        $this->db_hash = $key;
        $userdb_encode=str_replace('=','',$this->_StrCode($userdb_encode));
        $forward = $rurl;
        $forward = str_replace('-','~',$forward);
        $Eforward = str_replace(array("\0","%00","\r"),'',$forward);
        $Eforward = preg_replace(
            array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'),
            array('','&amp;'),
            $Eforward
        );
        $verify = md5("login$userdb_encode$Eforward$key");
        $shop_loginapi_url = substr($this->_config['URL'],-1)=="/"?$this->_config['URL']."passport_client.php":$this->_config['URL'];
        header('Location: '.$shop_loginapi_url.'?action=login&userdb='.rawurlencode($userdb_encode).'&forward='.rawurlencode($forward).'&verify='.$verify);    
        exit;
    }
    
    function regist($userId,$rurl) {
        $oMember = $this->system->loadModel('member/member');
        $aMember = $oMember->getFieldById($userId);
        $username = $aMember['uname'];
        if($this->_config['encoding']!='utf8'){
            $charset = $this->system->loadModel('utility/charset');
            $username = $charset->utf2local($username,$this->_config['encoding']);
        }
        $member = array(    
            'username'    => $username,
            'password'    => $aMember['password'],
            'email'        => $aMember['email'], 
            'time'        => $aMember['regtime']
        );
        $userdb_encode='';
        foreach($member as $key=>$val){
            $userdb_encode .= $userdb_encode ? "&$key=$val" : "$key=$val";
        }    
        $key = $this->_config['PrivateKey'];
        $this->db_hash=$key;
        $userdb_encode=str_replace('=','',$this->_StrCode($userdb_encode));
        $shop_loginapi_url = substr($this->_config['URL'],-1)=="/"?$this->_config['URL']."passport_client.php":$this->_config['URL'];
        $this->setPlugCookie(1);
        if (!empty($rurl)){
            //$rurl.="index.php?ctl=passport&act=create";
            $rurl.='?passport-create';
        }
        else{//从pw过来
            $this->setPlugCookie(1);
            if (strstr($this->system->mkUrl('passport','create'),"?"))
                $rurl = substr($this->system->mkUrl('passport','create'),0,strrpos($this->system->mkUrl('passport','create'),"?")+1)."ctl=passport&act=create";
            else
                $rurl = substr($this->system->mkUrl('passport','create'),0,strrpos($this->system->mkUrl('passport','create'),"/")+1)."?ctl=passport&act=create";
           $rurl.="&plugUrl=".$this->_config['URL'];
        }
        $forward = $rurl; 
        $Eforward = str_replace(array("\0","%00","\r"),'',$forward);
        $Eforward = preg_replace(
            array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'),
            array('','&amp;'),
            $Eforward
        );
        $verify = md5("login".$userdb_encode.$Eforward.$key);
        header('Location: '.$shop_loginapi_url.'?action=login&userdb='.rawurlencode($userdb_encode).'&forward='.rawurlencode($forward).'&verify='.$verify); 
        exit;
    }        

    function logout($userId,$rurl) {
        $key = $this->_config['PrivateKey'];
        $this->db_hash=$key;
        $userdb_encode=str_replace('=','',$this->_StrCode($userdb_encode));
        $forward = $rurl;
        $verify = md5
            ("quit$userdb_encode$forward$key");
        $shop_loginapi_url = substr($this->_config['URL'],-1)=="/"?$this->_config['URL']."passport_client.php":$this->_config['URL'];
        header('Location: '.$shop_loginapi_url.'?action=quit&forward='.$forward."&verify=$verify");
        exit;
    }

    function _StrCode($string,$action='ENCODE'){
        $key    = substr(md5($_SERVER["HTTP_USER_AGENT"].$this->db_hash),8,18);
        $string    = $action == 'ENCODE' ? $string : base64_decode($string);
        $len    = strlen($key);
        $code    = '';
        for($i=0; $i<strlen($string); $i++){
            $k        = $i % $len;
            $code  .= $string[$i] ^ $key[$k];
        }
        $code = $action == 'DECODE' ? $code : base64_encode($code);
        return $code;
    }
    
    function ClientUserAction($action,$userdb,$forward=''){
        parse_str($this->StrCode($userdb,'DECODE'),$userdb);
        if ($this->_config['encoding']!="utf8"){
           $charset = $this->system->loadModel('utility/charset');
           foreach($userdb as $key => $val){
              $userdb[$key] = $charset->local2utf($val,$this->_config['encoding']);
           }
        }
        $account = $this->system->loadModel('member/account');
        if ($action=="login"){
            $account->PlugUserRegist($userdb);
            if ($forward){
                header('Location:'.$forward);
            }
            else{
                $this->setPlugCookie(1);
                if ($_COOKIE['FromPlace']){
                    setcookie("FromPlace",'');
                    $rdUrl="./?passport-create.html";
                }
                else{
                    $rdUrl = $this->system->base_url()."/?ctl=passport&act=create&plugUrl=".$this->_config['URL'];
                } 
                header("Location:".$rdUrl);
            }
        } 
        else{
            $account->PlugUserExit();
            header("Location:".$forward);
        }
        exit;
    }  
    function StrCode($string,$action='ENCODE'){
        $this->db_hash = $this->_config['PrivateKey'];
        $action != 'ENCODE' && $string = base64_decode($string);
        $code = '';
        $key  = substr(md5($_SERVER['HTTP_USER_AGENT'].$this->db_hash),8,18);
        $keylen = strlen($key); $strlen = strlen($string);
        for ($i=0;$i<$strlen;$i++) {
            $k        = $i % $keylen;
            $code  .= $string[$i] ^ $key[$k];
        }
        return ($action!='DECODE' ? base64_encode($code) : $code);
    }
    function ServerClient($action){
        setCookie('FromPlace','shopEx');
        if ($this->_config['conntype']=="client"){
            switch ($action){
                case "signup":
                    $toUrl='register.php';
                    break;
                case "login":
                    $toUrl='login.php';
                    break;                    
                case "logout":                  
                    $toUrl='login.php?action=quit';
                    break;
                case "security":
                    $toUrl="profile.php?action=modify";
                    break;
            }
            $url = substr($this->_config['URL'],-1)=="/"?$this->_config['URL']:$this->_config['URL']."/";
            $url.=$toUrl;
            header("Location:".$url);
            exit;
        }
    }
    function getPlugCookie(){
        $account = $this->system->loadModel('member/account');
        return $account->getPlugCookie('CType');
    }
    function setPlugCookie($val){
        $account = $this->system->loadModel('member/account');
        if ($val) 
            $account->setPlugCookie('CType','phpwind');
        else
            $account->setPlugCookie('CType','');
    }
}
?>