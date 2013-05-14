<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_passport
 *
 * @uses shopPage
 * @package
 * @version $Id: ctl.passport.php 2035 2008-04-28 14:06:13Z alex $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
class ctl_passport extends shopPage{

    var $noCache = true;

    function ctl_passport(&$system){
        parent::shopPage($system);
        $this->header .= "<meta name=\"robots\" content=\"noindex,noarchive,nofollow\" />\n";
        $this->pagedata['redirectInfo'] = '';
        if(isset($_POST['form'])){
            $form = get_magic_quotes_gpc()?stripcslashes($_POST['form']):$_POST['form'];
            $this->pagedata['redirectInfo'].='<input type="hidden" name="form" value="'.htmlspecialchars($form).'" />';
        }if(isset($_REQUEST['url'])){
            $this->pagedata['redirectInfo'].='<input type="hidden" name="url" value="'.htmlspecialchars($_REQUEST['url']).'" />';
        }
        if(constant('DEBUG_JS')){
           $script_dir='script_src';
        }else{
           $script_dir='script';
        }

        $this->pagedata['scriptplus']='<script type="text/javascript" src="statics/'.$script_dir.'/formplus.js"></script>';
    }

    function verifyCode($type=''){
        $oVerifyCode = &$this->system->loadModel('utility/verifyCode');
        $oVerifyCode->type = $type;
        $oVerifyCode->Output(60,16,true);
    }

    function namecheck(){
        $member=&$this->system->loadModel('member/account');
        $name = trim($_POST['name']);
        if($member->check_uname($name,$message)){
                echo '<span class="fontcolorGreen">&nbsp;'.__('可以使用').'</span>';
        }else{
                echo '<span class="fontcolorRed">&nbsp;'.$message.'</span>';
        }
    }
    function showValideCode($tp=''){
        switch ($tp){
            case "login":
                if($this->system->getConf('site.login_valide')){
                    $this->pagedata['valideCode'] = true;
                }
            break;
            case "signup":
                if($this->system->getConf('site.register_valide')){
                    $this->pagedata['valideCode'] = true;
                }
            break;
        }
    }

    // 云登陆回调地址
    function ecopen_login_verify(){
        // 签名验证
        $token = $this->system->getConf('certificate.token');
        ($_GET['sign'] && $this->get_ce_sign($_GET, $token) == $_GET['sign']) || exit("签名错误，云登陆无法完成");

        // 为登陆者创建账户
        $accountMdl = $this->system->loadModel('member/account');
        $mem = $accountMdl->createotherlogin($_GET);

        // 在本系统登陆
        $this->system->setCookie('MEMBER',$mem['secstr'],null);
        $this->system->setCookie('UNAME',$mem['uname'],null);
        $this->system->setCookie('NAME',$mem['name'],null);
        $this->system->setCookie('MLV',$mem['member_lv_id'],null);
        $this->system->setCookie('CUR',$mem['cur'],null);
        $this->system->setCookie('LANG',$mem['lang'],null);
        $this->system->setCookie('LOGIN_TYPE',$mem['open_type'],null);

        // 保存购物车
        $cartMdl = &$this->system->loadModel('trading/cart');
        $cartCookie = $cartMdl->getCart();
        $cartMdl->checkMember($mem);
        $cartDB = $cartMdl->getCart();
        $cartMdl->save('all', $cartMdl->mergeCart($cartCookie,$cartDB));

        exit('<script>window.location=decodeURIComponent(location.hash).substr(1)</script>');
    }

    function get_ce_sign($params,$token){
        $arg="";
        ksort($params);
        reset($params);
        while (list ($key, $val) = each ($params)) {
            if ( 'sign' == $key ) continue;
            
            $arg.=$key."=".urlencode($val)."&";
        }
        return md5(substr($arg,0,count($arg)-2).$token);//去掉最后一个问号
    }

    function index($url){

        $this->pagedata['_MAIN_'] = 'passport/login.html';
        return $this->login($url);
        if(count($_POST['form'])>0 && !$this->member && !$this->system->getConf('security.guest.enabled',false)){
            $this->pagedata['mustMember'] = true;
        }

        $this->showValideCode();
        switch($url){
            case 'checkout':
                $options['url'] = $this->system->mkUrl("cart","checkout");
            break;
        }
        $this->pagedata['options'] = $options;
        $this->output();
    }

    function login($url,$error_msg=''){
        if(isset($_COOKIE['IS_VERIFY'])&&$_COOKIE['IS_VERIFY'] == 'true'){
            $this->system->setCookie('IS_VERIFY', '', time()-1000);
        }else{
            $error_msg='';
        }

        $this->showValideCode('login');
        switch($url){
            case 'checkout':
                $options['url'] = $this->system->mkUrl("cart","checkout");
            break;
        }
        $this->pagedata['LogInvalideCode'] = $this->pagedata['valideCode'];
        $this->title = __('用户登录');

        $this->pagedata['ref_url'] = $this->deal_baseurl(base64_decode($url),1);
        $baseurl='http://'.HTTP_HOST.substr(PHP_SELF, 0, strrpos(PHP_SELF, '/') + 1);

        if(!strpos($baseurl.'?member',$_SERVER['HTTP_REFERER'])&&$url==""){
            $this->pagedata['ref_url'] = $_SERVER['HTTP_REFERER'];
        }
        if(($_SERVER['HTTP_REFERER']==$this->system->mkUrl("passport","login")&&$error_msg=="")||$_SERVER['HTTP_REFERER']==$this->system->mkUrl("passport","sendPSW")||$_SERVER['HTTP_REFERER'] ==$this->system->mkUrl("passport","signup")||$_SERVER['HTTP_REFERER']==""){
            $this->pagedata['ref_url'] = $this->system->mkUrl('member');
        }
        if($error_msg!=''){
            $this->pagedata['err_msg'] = $this->deal_baseurl(base64_decode($error_msg),1,false);
        }
        $this->pagedata['options'] = $options;
        $this->pagedata['forward'] = $_REQUEST['forward'];
        $this->pagedata['loginName'] = $_COOKIE['loginName'];
        $appmgr = $this->system->loadModel('system/appmgr');
        
        if($_GET['mini_passport']){
          $this->pagedata['mini']=1;
            $this->__tmpl = 'passport/index/login.html';
            $login_plugin = $appmgr->getloginplug();
            foreach($login_plugin as $key =>$value){
                $object = $appmgr->instance_loginplug($value);
                if(method_exists($object,'getMiniHtml')){
                    $this->pagedata['mini_login_content'][] = $object->getMiniHtml();
                }
            }

        }else{
            $login_plugin = $appmgr->getloginplug();
            foreach($login_plugin as $key =>$value){
                $object = $appmgr->instance_loginplug($value);
                if(method_exists($object,'getHtml')){
                    $this->pagedata['login_content'][] = $object->getHtml();
                }
            }
        }

        $this->output();
    }

    function signup($url,$forward=''){
        $passport = &$this->system->loadModel('member/passport');
        if ($obj=$passport->function_judge('ServerClient')){
            $type = $obj->ServerClient('signup');
        }
        $this->showValideCode('signup');
        switch($url){
            case 'checkout':
                $options['url'] = $this->system->mkUrl("cart","checkout");
            break;
        }
        $this->title = __('用户注册');
        $this->pagedata['options'] = $options;
        $this->pagedata['forward'] = $_REQUEST['forward'];
        $this->pagedata['SignUpvalideCode'] = $this->pagedata['valideCode'];
        if($_GET['mini_passport']){
            $this->__tmpl = 'passport/index/signup_fast.html';
             $appmgr = $this->system->loadModel('system/appmgr');
            $login_plugin = $appmgr->getloginplug();
            foreach($login_plugin as $key =>$value){
                $object = $appmgr->instance_loginplug($value);
                if(method_exists($object,'getRegistHtml')){
                    $this->pagedata['regist_content'][] = $object->getRegistHtml();
                }
            }
        }

        $this->output();
    }

    function lost($url){
        $this->showValideCode();

        switch($url){
            case 'checkout':
                $options['url'] = $this->system->mkUrl("cart","checkout");
            break;
        }
        $this->pagedata['options'] = $options;
        $this->output();
    }

    function splash($status, $url, $msg, $links='', $wait=3, $json=false){
        if($json){
            echo json_encode(array('status'=>$status, 'url'=>$url, 'msg'=>$msg));
            exit;
        }else{
            parent::splash($status, $url, $msg, $links, $wait);
        }
    }

    function create() {
        $account = &$this->system->loadModel('member/account');
        $passport = &$this->system->loadModel('member/passport');
        
        // 检查验证码
        if( $this->system->getConf('site.register_valide') ) {
            if( $_COOKIE["S_RANDOM_CODE"]!=md5($_POST['signupverifycode']) ) {
                $this->splash('failed','back',__('验证码录入错误，请重新输入'),'','',$_POST['from_minipassport']);
            }
        }

        if( 'agree' != $_POST['license'] ) {
            $this->splash('failed','back',__('同意注册条款后才能注册'),'','',$_POST['from_minipassport']);
        }

        $toolsMdl = $this->system->loadModel('utility/tools');
        if( !$toolsMdl->is_email($_POST['email']) ) {
            $this->splash('failed','back',__('邮件格式不正确'),'','',$_POST['from_minipassport']);
        }

        if(strlen($_POST['passwd']) < 4 || strlen($_POST['passwd']) > 20 ) {
            $this->splash('failed','back',__('密码长度应介于4~20之间'),'','',$_POST['from_minipassport']);
        }

        if( $account->checkMember($_POST) ) {
            $this->splash('failed','back',__('该用户名已经存在'),'','',$_POST['from_minipassport']);
        }

        $o_passwd= $_POST['passwd'];
        $o_email= $_POST['email'];

        if($_REQUEST['forward']) $url = $_REQUEST['forward'].DEFAULT_INDEX;
        else $url = $this->system->request['base_url'].DEFAULT_INDEX;

        if( !$info = $account->create($_POST,$message) ) {
            $this->splash('failed','back',$message,'','',$_POST['from_minipassport']);
        }

        $this->system->setCookie('MEMBER',$info['secstr'],null);
        $this->system->setCookie('UNAME',$info['uname'],null);
        $this->system->setCookie('MLV',$info['member_lv_id'],null);
        $GLOBALS['runtime']['member_lv'] = $info['member_lv_id'];
        $this->system->setCookie('CUR',$info['cur'],null);
        $this->system->setCookie('LANG',$info['lang'],null);
        $oCart = &$this->system->loadModel('trading/cart');
        $cartCookie = $oCart->getCart();
        $oCart->checkMember($info);
        $oCart->memberLogin = true;
        $oCart->save('all', $cartCookie);
        $this->system->setcookie($oCart->cookiesName,'');

        // 注册后立刻进入购物车
        if($_POST['regType'] == 'buy'){
            $this->system->setcookie($oCart->cookiesName,'');
            if($_POST['isfastbuy'])$redi_url = $this->system->mkUrl('cart','checkout',array(1));
            else $redi_url = $this->system->mkUrl('cart','checkout');
            $this->splash('success',$redi_url,__('注册成功，并进入购买'),'',1,$_POST['from_minipassport']);
        } else {
            if ($_REQUEST['forward']){
                $registinfo = $passport->regist($info['member_id'],$url);
                $this->nowredirect('plugin_passport', $registinfo,'',$_POST['from_minipassport']);
            }else{
                if ($_POST['forward']){
                    $url='';
                }
                $registinfo = $passport->regist($info['member_id'],$url);
                //$this->nowredirect('plugin_passport', $registinfo,'',$_POST['from_minipassport']);
            }
        }

        // from_minipassport 弹出窗口注册
        if($_POST['from_minipassport']){
            exit('success');
        }

        $oMem = &$this->system->loadModel('member/member');
        $mematt = &$this->system->loadModel('member/memberattr');
        
        // 用户注册项
        $filter['attr_show'] = 'true';
        $tmpdate =$mematt->getList('*',$filter,0,-1,array('attr_order','asc'));

        for($i=0;$i<count($tmpdate);$i++){
            if($tmpdate[$i]['attr_type'] == 'select'||$tmpdate[$i]['attr_type'] == 'checkbox'){
                $tmpdate[$i]['attr_option'] = unserialize($tmpdate[$i]['attr_option']);
            }
        }
        
        $this->pagedata['passwd'] = $o_passwd;
        $this->pagedata['email'] = $o_email;
        $this->pagedata['tree'] = $tmpdate;
        $this->pagedata['plugUrl'] = $_REQUEST['plugUrl']?$_REQUEST['plugUrl']:$_COOKIE['plugUrl'];
        $this->output();
    }

    function recover(){
        $member=&$this->system->loadModel('member/member');
        $this->pagedata['data']=$member->getMemberByUser($_POST['login']);
        if(empty($this->pagedata['data']['member_id'])){
            $this->splash('failed','back',__('该用户不存在！'));
        }
        if($this->pagedata['data']['disabled'] == "true"){
            $this->splash('failed','back',__('该用户已经放入回收站！'));
        }
        $this->output();
    }

    function sendPSW(){
        $this->begin($this->system->mkUrl('passport','lost'));
        $member=&$this->system->loadModel('member/member');
        $memberObj = &$this->system->loadModel('member/account');
        $data=$member->getMemberByUser($_POST['uname']);
        if(($data['pw_answer']!=$_POST['pw_answer']) || ($data['email']!=$_POST['email'])){
            $this->end(false,__('问题回答错误或当前账户的邮箱填写错误'),$this->system->mkUrl('passport','lost'));
        }

        if( $data['member_id'] < 1 ){
            $this->end(false,__('会员信息错误'),$this->system->mkUrl('passport','lost'));
        }

        $messenger = &$this->system->loadModel('system/messenger');echo microtime()."<br/>";
        $passwd = substr(md5(rand(1000,10000).rand(100,1000).print_r(microtime(),true)),0,6);
        
        //找回密码时，新的加密方法
        $md5_passwd = $memberObj->encrypt_passwd_enhanced($passwd,$_POST['uname'],time());
        $pObj=$this->system->loadModel('member/passport');
        if ($obj=$pObj->function_judge('edituser')){
            $res = $obj->edituser($data['uname'],'',$passwd,$data['email'], '1');
            if ($res>0){
                $member->update(array('password'=>$md5_passwd,'regtime'=>time(),),array('member_id'=>intval($data['member_id'])));
            }
            else{
                trigger_error('输入的旧密码与原密码不符！', E_USER_ERROR);
                return false;
            }
        }else{
            $member->update(array('password'=>$md5_passwd,'regtime'=>time(),),array('member_id'=>intval($data['member_id'])));
        }
        $data['passwd'] = $passwd;
        $memberObj->fireEvent('lostPw',$data,$data['member_id']);

        $this->end(true,__('邮件已经发送'),$this->system->mkUrl('passport','index'));
    }

    /*取消跳转后此方法已失效*/
    function error(){
        $url = $this->system->request['url_prefix'];
        $this->pagedata['nexturl'] = $url;
        $this->header.="\n<meta http-equiv=\"refresh\" content=\"3; url={$url}\">\n";
        $this->output();
    }

    //退出
    function logout() {
        $this->_verifyMember(false);
        
        $this->system->loadModel('member/account')->logout($this->member['member_id']);
        unset($this->member);

        $this->system->location($this->system->base_url());
    }

    function nowredirect($status='success',$url,$msg="",$json=false){
        if($status!='failed'){
            if($json){
                echo json_encode(array('status'=>$status, 'url'=>$url));
            }else{
                header('Location: '.$url);
            }
        }else{
            if($json){
                echo json_encode(array('status'=>'failed', 'msg'=>$msg));
                exit;
            }else{
                $url = $this->system->mkUrl('passport','login',array($url,base64_encode($this->deal_baseurl($msg,false,false))));
                $this->system->setCookie('IS_VERIFY', 'true');
                header('Location: '.$url);
                exit;
            }
        }
    }

    function verify() {
        // 检查验证码
        if( $this->system->getConf('site.login_valide') && $_COOKIE["L_RANDOM_CODE"]!=md5($_POST['loginverifycode']) ) {
            $this->nowredirect('failed',base64_encode($this->deal_baseurl($_POST['ref_url'])),__('验证码录入错误，请重新输入'),$_POST['from_minipassport']);
        }

        $memberObj = &$this->system->loadModel('member/account');
        $this->system->setCookie('loginName',$_POST['login']);

        $this->title = '';
        if( !$info = $memberObj->verifyLogin($_POST['login'],$_POST['passwd'],$message)){
            $this->nowredirect('failed',base64_encode($this->deal_baseurl($_POST['ref_url'])),__('用户名或密码有误，请重新输入'),$_POST['from_minipassport']);
        }

        if(isset($_REQUEST['forward']) && $_REQUEST['forward']){
            $url = $this->deal_baseurl($_REQUEST['forward'].DEFAULT_INDEX);
        }elseif(isset($_POST['ref_url']) && $_POST['ref_url']){
            $url = $this->deal_baseurl($_POST['ref_url']);
        }else{
            $url = $this->system->base_url();
        }

        $cart_count = unserialize($info['addon']);
        $cart_count = count(explode(',',$cart_count['cart']));

        $this->system->setCookie('MEMBER',$info['secstr'],null);
        $this->system->setCookie('UNAME',$info['uname'],null);
        $this->system->setCookie('NAME',$info['name'],null);
        $this->system->setCookie('MLV',$info['member_lv_id'],null);
        $GLOBALS['runtime']['member_lv'] = $info['member_lv_id'];
        $this->system->setCookie('CUR',$info['cur'],null);
        $this->system->setCookie('LANG',$info['lang'],null);
        $this->system->setCookie('CART_COUNT',$cart_count,null);

        // 购物车数量
        $oCart = &$this->system->loadModel('trading/cart');
        $cartCookie = $oCart->getCart();
        $oPassport = &$this->system->loadModel('member/passport');

        //DB 购物车数量
        $oCart->checkMember($info);
        $cartDb = $oCart->getCart();

        if($_COOKIE['CART_COUNT']){ //Cookie 购物车数量
            if($cartDb){
                if($this->system->mkUrl('cart','checkout')==$url){
                    $url = $this->system->mkUrl('cart','index');
                }
                if($_POST['from_minipassport']){
                    $aCart = $oCart->mergeCart($cartCookie, $cartDb);
                    $oCart->save('all',$aCart);
                }
                $this->nowredirect('success',$this->system->mkUrl('cart','merge',array(1)).'?forward='.urlencode($url),'',$_POST['from_minipassport']);
            }else{
                $oCart->save('all', $cartCookie);
                $this->system->setcookie($oCart->cookiesName,'');
                if ($oPassport->forward){
                    $loginfo = $oPassport->login($info['member_id'],$url);
                    $this->nowredirect('plugin_passport', $loginfo,'',$_POST['from_minipassport']);
                }else{
                    $loginfo = $oPassport->login($info['member_id'],$url);
                    $this->nowredirect('plugin_passport', $loginfo,'',$_POST['from_minipassport']);
                }
                $this->nowredirect('success',$url,'',$_POST['from_minipassport']);
            }
        }
        
        if ($oPassport->forward){
            $loginfo = $oPassport->login($info['member_id'],$url);
            $this->nowredirect('plugin_passport', $loginfo,'',$_POST['from_minipassport']);
            $this->header.=$loginfo;
        }else{
            $loginfo = $oPassport->login($info['member_id'],$url);
            $this->nowredirect('plugin_passport', $loginfo,'',$_POST['from_minipassport']);
        }

        $this->nowredirect('success',$url,'',$_POST['from_minipassport']);
    }

    function deal_baseurl($url,$code=false,$allow_url=true){
        $replace_url = str_replace($this->system->base_url(),'',$url);
        $replace_url = preg_replace('/<(script|meta)/i', '<_$1', $replace_url);

        if($code){//编码
          $replace_url = str_replace(array('_',',','~'),array('+','/','='),$replace_url);
        }else{//解码
          $replace_url = str_replace(array('+','/','='),array('_',',','~'),$replace_url);
        }

        if($allow_url){
            return $this->system->base_url().$replace_url;
        }else{
            return $replace_url;
        }
    }//防止域名中带_ , ~被过滤.

    function ssoSignin()
    {
        $oPassport = &$this->system->loadModel('member/passport');
        return $oPassport->ssoSignin();
    }

    function _setLoginCookie($info)
    {
        if($info){
            $option = time()+60*60*24*30;//null;
        }else{
            $option = time() - 1000;
        }
        $this->system->setCookie('MEMBER',$info['secstr'],$option);
        $this->system->setCookie('UNAME',$info['uname'],$option);
        $this->system->setCookie('MLV',$info['member_lv_id'],$option);
        $GLOBALS['runtime']['member_lv'] = $info['member_lv_id'];
        $this->system->setCookie('CUR',$info['cur'],$option);
        $this->system->setCookie('LANG',$info['lang'],$option);
        return true;
    }

    function callback($type)
    {
        $oPassport = &$this->system->loadModel('member/passport');
        $info = $oPassport->ssoSignin($type);
        $this->_succ = true;
        switch($_GET['action']){
            case 'login':
                if(!$info){
                    header("location: ".$_GET['forward']);
                    exit;
                }
                $this->_setLoginCookie($info);
                header("location: ".$_GET['forward']);
                exit;
            case 'logout':
                $this->_setLoginCookie($info);
                header("location: ".$_GET['forward']);
                exit;
        }
    }

    function getMsg()
    {
        return array(
            'login'=>array('succ'=>__('登陆成功'),'fail'=>__('登陆失败')),
            'logout'=>array('succ'=>__('退出成功'),'fail'=>__('退出失败'))
        );
    }

   function _checkusername($uname='',$passwd='',$forward='',$uid='',$email='')
    {
         $mem_info = $this->system->loadModel('member/member');
         $account = &$this->system->loadModel("member/account");
         $data=array(
            "uname"=>$uname,
            "passwd"=>$passwd,
            "email"=>$email
         );
         $oCart = &$this->system->loadModel('trading/cart');
         $cartCookie = $oCart->getCart();
         if($_REQUEST['forward']) $url = $_REQUEST['forward'].DEFAULT_INDEX;
         else $url = $this->system->request['base_url'].DEFAULT_INDEX;

         $row =  $account->getMemberPluginUser($uname);
         if (!$row){
            $row=$account->createUserFromPluin($data,$message,$uid);
         }else{
             if ($row['foreign_id']<>$uid){
                $account->UpdateForeignId(array($row['member_id']=>$uid));
             }
         }

         $oMsg = $this->system->loadModel('resources/msgbox');
         $row['unreadmsg'] = $oMsg->getNewMessageNum($row['member_id']);

        $cart_count = unserialize($row['addon']);
        $cart_count = count(explode(',',$cart_count['cart']));
        $this->system->setCookie('CART_COUNT',$cart_count,null);

         $this->system->setCookie('MEMBER',$row['secstr'],null);
         $this->system->setCookie('UNAME',$row['uname'],null);

         $this->system->setCookie('MLV',$row['member_lv_id'],null);
         $GLOBALS['runtime']['member_lv'] = $row['member_lv_id'];

         $this->system->setCookie('CUR',$row['cur'],null);

         $this->system->setCookie('LANG',$row['lang'],null);

         $oPassport = &$this->system->loadModel('member/passport');
         $row['foreign_id']?$row['member_id']=$row['foreign_id']:'';
         $loginfo = $oPassport->login($row['member_id'],$url);
         $this->header.=$loginfo;
         $tmp_mem = $mem_info->getMemIdByName($uname);
         $row['member_id']= $tmp_mem[0]['member_id'];
         $oCart->checkMember($row);
         if($_POST['ref_url']) $url=$_POST['ref_url'];
         if ($_COOKIE['CART_COUNT']){
            $cartCookie = $oCart->getCart();
                    if($_POST['from_minipassport']){
                        $cartDb =  $oCart->getCart();
                        $oCart->memberLogin = false;
                        $cartCookie =  $oCart->getCart();
                        $aCart =  $oCart->mergeCart($cartCookie, $cartDb);

                        $oCart->memberLogin = true;
                        $oCart->save('all',$aCart);
                        $this->system->setcookie($oCart->cookiesName,'');
                        $oCart->setCartNum($aCart);
                    }
                    $this->nowredirect('success',$this->system->mkUrl('cart','merge',array(1)).'?forward='.urlencode($url),'',$_POST['from_minipassport']);
                    exit;
         }else{
            $oCart->save('all', $cartCookie);
         }
         if($_POST['from_minipassport']){
            return true;
         }else{
            $this->nowredirect('success',$url,'',$_POST['from_minipassport']);
            $this->_succ=true;
         }
    }
}
?>