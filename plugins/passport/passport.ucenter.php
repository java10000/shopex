<?PHP
    class passport_ucenter extends modelFactory{
         var $passport_name = "UCenter 1.0/1.5";
         var $passport_memo = "Ucenter1.0/1.5整合";
         var $_config = null;
         var $tmpl = "passport_ucenter.html";//加载对应的模板文件
         var $forward=0;
         var $charset;
         var $name = "ucenter";
         function setconfig($config){
            $this->_config = $config;
         }
         function verifylogin($login,$passwd){

         }
         function decode($responseData){
            
         }
         function getoptions(){
            return array(
                "ucapi"=>array('label'=>'UCenter URL：','type'=>'input','required'=>'true'),
                "uckey"=>array('label'=>'UCenter 通信密钥：','type'=>'input'),
                "ucappid"=>array('label'=>'UCenter 应用ID：','type'=>'input'),
                "ucserver"=>array('label'=>'UCenter 数据库服务器：(不带http://前缀)','type'=>'input'),
                "ucdbuser"=>array('label'=>'UCenter 数据库用户名：','type'=>'input','required'=>'true'),
                "ucdbpass"=>array('label'=>'UCenter 数据库密码：','type'=>'input'),
                "ucdbname"=>array('label'=>'UCenter 数据库名：','type'=>'input','required'=>'true'),
                "ucprefix"=>array('label'=>'UCenter 表名前缀：','type'=>'input'),
                "encoding"=>array('label'=>'UCenter系统编码：','type'=>'select','options'=>array('utf8' => '国际化编码(utf-8)','gbk' => '简体中文','big5' => '繁体中文','en' => '英文')),
                "ucdbcharset"=>array('label'=>'UCenter数据库编码：','type'=>'select','options'=>array('utf8'=>'UTF8','gbk'=>'GBK'))
            );
         }
         
         function checkuser($username){
             $this->getDefineVar();
             @include_once(CORE_DIR.'/lib/uc_client/client.php');
             if (is_object($this->charset)){
                 $username = $this->charset->utf2local($username,"zh");
             }
             $ucc=uc_user_checkname($username);
             return $ucc; 
         }
         function regist_user($username,$password,$email){
              $this->getDefineVar();
              @include_once(CORE_DIR.'/lib/uc_client/client.php');
              if (is_object($this->charset)){
                 $username = $this->charset->utf2local($username,"zh");
                 $password = $this->charset->utf2local($password,"zh");
              }
              $urg=uc_user_register($username,$password,$email);
              return $urg;
         }
         function regist($userId,$rurl){
            return true;
         }
         function logout($userId,$url){
            $this->getDefineVar();
            @include_once(CORE_DIR.'/lib/uc_client/client.php');
            $logoutinfo=uc_user_synlogout($userId);
            return $logoutinfo;
         }
         function check_login($username,$password){
            $this->getDefineVar();
            @include_once(CORE_DIR.'/lib/uc_client/client.php');
            if (is_object($this->charset))
                $username = $this->charset->utf2local($username,"zh");
            $logres=uc_user_login($username,$password);
            return $logres;
         }  
         function login($userId,$url){
            $this->getDefineVar();
            @include_once(CORE_DIR.'/lib/uc_client/client.php');
            $loginfo = uc_user_synlogin($userId);
            return $loginfo;
         }
         function get_user($username){
            $this->getDefineVar();
            @include_once(CORE_DIR.'/lib/uc_client/client.php');
            if (is_object($this->charset))
                $username = $this->charset->utf2local($username,"zh");
            $userinfo=uc_get_user($username);
            return $userinfo;
         }
         function getDefineVar(){
            $pobj = $this->system->loadmodel('member/passport');
            $data = $pobj->getOptions('ucenter');              
            define('UC_CONNECT', 'mysql');                               
            define('UC_DBHOST', $data['ucserver']['value']);
            define('UC_DBUSER', $data['ucdbuser']['value']);
            define('UC_DBPW',   $data['ucdbpass']['value']);
            define('UC_DBNAME', $data['ucdbname']['value']);
            define('UC_DBCHARSET', $data['ucdbcharset']['value']);
            define('UC_DBTABLEPRE', '`'.$data['ucdbname']['value']."`.".$data['ucprefix']['value']);
            define('UC_DBCONNECT', 0);
            define('UC_KEY', $data['uckey']['value']);
            define('UC_API', $data['ucapi']['value']);
            define('UC_CHARSET', $data['encoding']['value']);
            $tmp=parse_url($data['ucapi']['value']); 
            if (preg_match('/([0-9]{1,3}\.){3}/',$tmp['host'])){
                define('UC_IP', $tmp['host']);
            }
            else{
                define('UC_IP', gethostbyname($tmp['host']));
            }
            define('UC_APPID', $data['ucappid']['value']);//$data['ucserver']['value']);
            define('UC_PPP', $data['ucserver']['value']);
            if (strtoupper(UC_DBCHARSET)<>"UTF8"){
                $this->charset=$this->system->loadModel('utility/charset'); 
            }
         }
         function implodeUserToUC(){
             $this->getDefineVar();
             @include_once(CORE_DIR.'/lib/uc_client/client.php');
             $mem = $this->system->loadModel('member/member');
             $this->charset = $this->system->loadModel('utility/charset');
             $data=$mem->getUserForBBS();
             if (is_array($data)){
                 if (UC_DBCHARSET=="gbk"){
                     foreach($data as $key => $val){
                        $data[$key]['uname'] =  $this->charset->utf2local($val['uname'],"zh");
                     }
                 }
                 uc_user_allmerge($data,$uidGroup);
                 if(is_array($uidGroup)){
                    $account=$this->system->loadModel('member/account');
                    $account->UpdateForeignId($uidGroup);
                 }
             }
         }
         function edituser($uname,$oldpass,$newpass,$email,$ignore=0){
             $this->getDefineVar();
             @include_once(CORE_DIR.'/lib/uc_client/client.php');
             if (is_object($this->charset))
                $uname = $this->charset->utf2local($uname,"zh");
             return uc_user_edit($uname,$oldpass,$newpass,'',$ignore);
         }
         function checkuserregister($uname,$passwd,$email,&$uid,&$message){
             $isuser=$this->checkuser($uname);
             if ($isuser=='-3'){
                $message=__('您开启了UCenter整合，且UCenter中存在该用户名');
             }
             else{
                $uid = $this->regist_user($uname,$passwd,$email);
                switch ($uid){
                    case -1:
                        $message=__('无效的用户名');
                        break;
                    case -2:
                        $message=__('用户名不允许注册');
                        break;
                    case -3:
                        $message=__('已经存在一个相同的用户名');
                        break;
                    case -4:
                        $message=__('无效的email地址');
                        break;
                    case -5:
                        $message=__('邮件不允许');
                        break;
                    case -6:
                        $message=__('该邮件地址已经存在');
                        break;
                    default:
                        break;
                }
             }
             return true;
         }
         function checkusername($uname='',$passwd='',$forward=''){
            $logres = $this->check_login($uname,$passwd);
            //list($uid, $uname, $passwd, $email, $repeat) = $logres;
            return $logres;
         }
    }
?>