<?php
/**
* shop_api
*
*/
require_once(CORE_DIR.'/kernel.php');
require_once(CORE_DIR.'/func_ext.php');

class shop_api extends kernel{
   var $return_data_type=0;//返回值类型 默认字符返回 0,1 返回 xml,2 返回 json
   var $version;

    function shop_api(){
        parent::kernel();

        $this->magic=get_magic_quotes_gpc();

        if ( !$apiAct = $_REQUEST['act'] ) {
            $this->error_handle('missing method');
        }
        
        if ( isset($_REQUEST['appname']) ) {
            $appName = preg_replace('/[^a-z0-1_~]/i', '', $_REQUEST['appname']);
        } elseif ( strpos($apiAct, ':') > 0 ) {// request plugin api
            list($appName, $apiAct) = explode(':', $apiAct);
        } elseif ( 0 === strpos($apiAct,'shopex_') ) { // 照顾"商品助理"
            $appName = 'goodsassistant';
        }
        
        if ( $appName && file_exists(PLUGIN_DIR."/app/{$appName}/api/api_link.php") ) {
            $APIs = include PLUGIN_DIR."/app/{$appName}/api/api_link.php";
        } else {// request traditional api
            $APIs = include CORE_DIR.'/api/include/api_link.php';
        }

        $APIs[$apiAct] || $this->error_handle('missing method');

        if( ($apiVersion = $_REQUEST['api_version']) && $APIs[$apiAct][$apiVersion] ) {
            $api = $APIs[$apiAct][$apiVersion];
        } else {
            $apiVersion = $this->api_version_compare($APIs[$apiAct]);
            $api = $APIs[$apiAct][$apiVersion];
        }
     
        if ( !$APIs[$apiAct][$apiVersion]['n_varify'] && !$this->verify($_POST) ) {
            $this->error_handle('veriy fail');
        }

        if( (!$api) || (!$ctl = $api['ctl']) ) {
            $this->error_handle('service error','service not this method');
        }
      
        include CORE_DIR.'/api/shop_api_object.php';
        if ( $appName ) {
            include PLUGIN_DIR."/app/{$appName}/api/{$apiVersion}/{$ctl}.php";
        } else {
            include CORE_DIR.'/'.dirname($ctl).'/'.$apiVersion.'/'.basename($ctl).'.php';
        }
      
        $ctl = basename($ctl);
        $act = $api['act'];
        if ( (!class_exists($ctl)) || (!$ctlObj = new $ctl) || (!method_exists($ctlObj, $act)) ) {
            $this->error_handle('service error','can not service');
        }

        if($_REQUEST['return_data']){ // 返回数据格式 json xml
            $ctlObj->data_format=strtolower($_REQUEST['return_data']);
        }
        if( false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') ) {
           $ctlObj->gzip=true;
        }

        $ctlObj->verify_data($_POST, $api);
        $ctlObj->$act($_POST);
   }
   
    // get the lastest version if lack the just requested version
    function api_version_compare($data, $version){
        return max(array_keys($data));
    }
   
    function apiErrorHandle($errno, $errstr, $errfile, $errline){
        switch ( $errno ) {
        case E_USER_ERROR:
            $this->error_handle('system error','user error:'.$errstr);
        break;
        case E_USER_WARNING:
            $this->error_handle('system error','user warning:'.$errstr);
        break;
        case E_ERROR:
            $this->error_handle('system error','error:'.$errstr);
        break;
        default: break;
      }
   }
   
   // check $_POST['ac']
    function verify( &$data ){
        if( !$token = $this->getConf('certificate.token') ){
            $this->error_handle('shop error','shop no token');
        }
        $ac = $data['ac'];
        unset($data['ac']);
      
        $data = array_filter( $data, create_function('$d','return null!==$d;') );// never transparent NULL to other language
        ksort($data);
        $tmp = implode('', array_values($data));
        
        return $ac && ($ac === strtolower( md5($tmp.$token) ));
   }
   
   /**
   * http头文件
   * @param 文件类型
   * @param 编码格式
   * @author DreamDream
   */
   function _header($content='text/html',$charset='utf-8'){
      header('Content-type: '.$content.';charset='.$charset);
      header("Cache-Control: no-cache,no-store , must-revalidate");
      $expires = gmdate ("D, d M Y H:i:s", time() + 20);
      header("Expires: " .$expires. " GMT");
   }

   function error_handle($code,$error_info=null) {
      if(!$this->error){
         $this->error=include('include/api_error_handle.php');
      }
      if(!$this->errors){
            $this->errors=include('include/api_error.php');
      }
      $result['msg']=$this->errors[$this->error[$code]['code']];
      $result['result']='fail';
      $result['info']=$error_info?$error_info:$this->error[$code]['code'];
      switch( $_POST['return_data'] ) {
         default:
            $this->system = &$GLOBALS['system'];
            $xml = &$this->system->loadModel('utility/xml');
            echo $xml->array2xml($result,'shopex');
         break;
         case 'json':
            $this->_header();
            echo json_encode($result);
         break;
      }
      exit;
   }

   function mkUrl($ctl,$act='index',$args=null,$extName = 'html'){
      return $this->realUrl($ctl,$act,$args,$extName,$this->request['base_url']);
   }
}
