<?php
/**
 * api 工具
 * @package
 * @version 1.0:
 * @copyright 2003-2009 ShopEx
 * @author dreamdream
 * @license Commercial
 */
class api_utility{

    var $host; //要坊问的 API host
    var $file; //要坊问的 API 路径
    var $port=80; //端口
    var $tolken; //服务器的会话KEY
    var $error;//error
    var $error_no;//error
    var $debug;//debug
    var $gzip=false;
    var $send_type='json';
    var $application_error=null;
    var $call_func = null;
    var $call_params = array();
    
    function api_utility($host,$file,$port,$tolken){
       if(!$this->system){
            $this->system = &$GLOBALS['system'];
       }
       $host=strtolower($host);
       
       $api_error = set_error_handler(array(&$this,"apiErrorHandle"));
       if(substr(0,4)!='http'){
            $host='http://'.$host;
       }
       $host_info=parse_url($host);
       if($host_info['path']=='/' || !$host_info['path']){
           $this->host=$host_info['host'];
           $this->file=$file;
       }else{
           $this->host=$host_info['host'];
           if($host_info['path']{(strlen($host_info['path'])-1)}!='/'){
                $this->file=$host_info['path'].$file;
           }else{
                $this->file=substr($host_info['path'],0,-1).$file;
           }
       }
       $this->tolken=$tolken;
       restore_error_handler();
    }

    /**
    * 数据加密
    * @param Array 被加密的源数据值
    * @return Array 数据加上加完密的值
    */
    function encode($vars){
        if(constant('DEBUG_API')){
            $vars['api_debug'] = 1;
        }
        ksort($vars);
        $verify='';
        foreach($vars as $key=>$v){
            $verify.=$v;
        }
        $vars['ac']=Md5($verify.$this->tolken);
        return $vars;
    }


    function apiErrorHandle($errno, $errstr, $errfile, $errline){
        switch ($errno) {
            case E_USER_ERROR:
                $this->error='user error:'.$errstr;
                //$this->error_handle('system error','user error:'.$errstr);
                break;
            case E_USER_WARNING:
                $this->error='user warning:'.$errstr;
                break;
            case E_ERROR:
                $this->error='error:'.$errstr;
                break;
            default:
                $this->error='system error'.$errstr;
                break;
        }
        return false;
        /*
        switch ($errno) {
          case E_USER_ERROR:
            $this->api_post_send('system error','user error:'.$errstr);
            break;
          case E_USER_WARNING:
            $this->api_post_send('system error','user warning:'.$errstr);
            break;
          case E_USER_NOTICE:
            $this->api_post_send('system error','user notice:'.$errstr);
            break;
          default:
            $this->api_post_send('system error',$errstr);
            break;
        }*/
    }

    /**
    * 发送接口
    * @param String 发送的数据源
    * @param String 发送方式get/post
    * @author DreamDream
    * @return Bool 成功/失败
    */
    function send($vars,$method="post"){
        if($vars['return_data']){
            $this->send_type=$vars['return_data'];
        }
        $vars=$this->data_encode($vars);

        $vars=$this->encode($vars);
        $sender_vars="";

        foreach($vars as $key=>$value){
            $sender_vars.=$key.'='.rawurlencode($value).'&';
        }
        $sender_vars=substr($sender_vars,0,-1);
        if($method=='post'){
            return $this->data_resume($this->api_post_send($sender_vars),$vars['return_data']);
        }
        if($method=='get'){
            return $this->data_resume($this->api_get_send($sender_vars),$vars['return_data']);
        }
    }

    /**
    * post 发送
    * @param String 发送的数据源
    * @return String 接收到的返回值
    */
    function api_post_send($vars){
        $process = fsockopen($this->host, $this->port, $errno, $errstr, 10);
        if (!$process) {
            return false;
        }

        $post .= "POST ".$this->file." HTTP/1.1\r\n";
        $post .= "Host: ".$this->host.":".$this->port."\r\n";
        $post .= "Content-Type: application/x-www-form-urlencoded\r\n";
        if(function_exists('gzuncompress')){
            $post .= "Content-Encoding: gzip \r\n";
        }
        $post .= "Content-Length: ". strlen($vars) ."\r\n";
        $post .= "Connection: Close\r\n\r\n";
        $post .= $vars;
        
        if(constant('DEBUG_API')){
            error_log($vars."\n",3,HOME_DIR."/logs/api.log");
        }
        
        fwrite($process, $post);
        while( !feof($process) )
        {
            $buf = trim( fgets( $process, 1024 ) );
            if(!$header_checked && !preg_match('/HTTP+[\s\S]+200/',$buf)){
                $this->error='HTTP status is not 200;'.$buf;
                return false;
            }else{
                $header_checked=true;
            }
            if(!$this->gzip && preg_match('/gzip/',$buf)){
                $this->gzip=true;
            }

            if( $buf == "" )
            {
                break;
            }
            $tmp_buf_key_value = explode( ": ", $buf );
            if( $tmp_buf_key_value[0] == "Content-Length" ) // ?
            {
                $clen = $tmp_buf_key_value[1];
            }
            else if( $tmp_buf_key_value[0] == "Transfer-Encoding" && $tmp_buf_key_value[1] == "chunked" )
            {
                $clen = -1;
            }
            else if($clen == 0)
            {
                $clen = "NO_SEARCH_LEN";
            }
        }

        $return_data = "";
        if( $clen == "NO_SEARCH_LEN" )
        {
            while( !feof($process) )
            {
                $return_data .= trim( fgets( $process, 1024 ) );
            }
        }
        else if( $clen == 0 )
        {
        }
        else if( $clen <= -1 )
        {
            $loop_times1 = 0;
            while( 1 )
            {
                $clen = trim( fgets($process, 128) );
                if( !$clen )
                {
                    $clen = trim( fgets($process, 128) );
                    if(!$clen){
                        break;
                    }
                }
                $clen = base_convert($clen, 16, 10);
                if( $clen > 0 )
                {
                    $tmp_data = "";
                    $loop_times2 = 0;
                    while( 1 )
                    {
                        $need_len = $clen - strlen( $tmp_data );
                        if( $need_len <= 0 )
                        {
                            break;
                        }
                        $tmp_data .= @fread( $process, $need_len );
                        if( $loop_times2++ >= 10000 )
                        {
                            break;
                        }
                    }
                    $return_data .= $tmp_data;
                }
                if( $loop_times1++ >= 1000 )
                {
                    break;
                }
            }
        }
        else
        {
            $return_data = "";
            $loop_times2 = 0;
            while( 1 )
            {
                $need_len = $clen - strlen( $return_data );
                if( $need_len <= 0 )
                {
                    break;
                }
                $return_data .= @fread( $process, $need_len );
                if( $loop_times2++ >= 10000 )
                {
                    break;
                }
            }
        }
        fclose($process);
        if($this->gzip && function_exists('gzuncompress')){
            return @gzuncompress($return_data);
        }else{
            return $return_data;
        }
    }
    /**
    * get 发送
    * @param String 发送的数据源
    * @return String 接收到的返回值
    */
    function api_get_send($vars){
        $process = fsockopen($host, 80, $errno, $errstr, 10);
        if (!$process) {
            return false;
        }
        $get = "GET ".$this->file." HTTP/1.1\r\n";
        $get .= "Host: ".$this->host.":".$this->port."\r\n";
        $get .= "Connection: Close\r\n\r\n";
        fwrite($process, $get);
        while (!feof($process)) {
            $result=fread($process, 1024);
        }
        fclose($process);
        return $result;
    }



    function data_resume($data,$code='json'){

        switch($code){
            case 'raw':
                return $data;
            break;
            case 'json':

                $result=json_decode($data,true);
                //$this->_empty_varfy($result);
            break;
            case 'soap':


            break;

            default:
                $xml=$this->system->loadModel('utility/xml');
                $result=$xml->xml2array($data,'shopex');

        }
        $this->error_no=$result['msg'];
        
        if(constant('DEBUG_API')){
            if($result['request_info']){
                $this->debug = $result['request_info'];
            }
            error_log(print_r($result,true)."\n\n",3,HOME_DIR."/logs/api.log");
        }
        
        $this->application_error_handle($result);
        if($result['result']=='success') {
            return $result['info'];
        }else if($result['result']=='fail') {
            $this->error=$result['info'];
        }else if(!is_array($result)) {
            $this->error='data invalid';
            $this->error_no='0x017';
        }
        // 统一触发平台api错误 wubin 2009-11-09 15:21
        $this->trigger_all_errors($this->call_func,$this->call_params);
        return false;
    }
    
    function _empty_varfy(&$data){
        foreach(array($data) as $key=>$value){
            if(is_array($value)){
                $this->_empty_varfy($value);
            }else if(is_null($data[$key])){
                $data[$key]="";
            }
        }
        return $data;
    }
    
    function application_error_handle($result){
        if(is_array($result['application_error'])){
            foreach($result['application_error'] as $key=>$value){
                $this->application_error[]=$result['application_error'][$key];
                if($result['application_error'][$key]['level']=='error'){
                    $error=true;
                }
            }
            if($error){
                $this->error='data error';
                return false;
            }
        }
        return true;
    }
    
    function data_encode($data){
        $this->en_prase_data($data);
        $xml=$this->system->loadModel('utility/xml');
        foreach($data as $key=>$value){
            if(is_array($value)){
                if($this->send_type=='json'){
                    $data[$key]=json_encode($value);
                }
                if($this->send_type=='xml'){
                    $result=$xml->array2xml($value,'shopex');
                    $data[$key]=$result;
                }
            }
        }
        return $data;
    }
    
    function en_prase_data(&$data){
        foreach((array)$data as $key=>$value){
            if(preg_match("/^[\x7f-\xff]/", $key)){
                $data['shop_prase_name']=$key;
                $data['shop_prase_content']=$value;
            }
            if(is_array($value)){
                $this->en_prase_data($value);
            }
        }
        return $data;
    }
    
    function de_prase_data(&$data){
        foreach((array)$data as $key=>$value){
            if($key=='shop_prase_name'){
                $data[$data['shop_prase_name']]=$data[$data['shop_prase_content']];
            }
            if(is_array($value)){
                $this->de_prase_data($value);
            }
        }
    }
    
    function data_decode(&$data){
        foreach($data as $key=>$value){
            if($value{0}=='{'){
                $data[$key]=json_decode($value);
            }
        }
        $this->de_prase_data($data);
    }

    /**
     * 获取api数据
     *
     * @param string $api_name，api接口名
     * @param string $api_version，api的版本
     * @param array $send，api请求的参数
     * @param string $return_data，API的返回类型 xml/json
     * @param boolean $license，是否要发送license，默认要发送
     * @param boolean $cache，是否要缓存数据到本类对象中，默认不开
     *
     * @return mixed
     */
    function _getApiData($act,$api_version,$send=array(),$license=true,$return_data='json',$send_data='json'){
        if($license){
            $license=$this->system->getConf('certificate.id');
            if($license){
                $send=array_merge(array('certificate_id'=>$license),(array)$send);
            }
        }
        $send=array_merge(array('act'=>$act,'api_version'=>$api_version,'send_data'=>$send_data,'return_data'=>$return_data),(array)$send);
        return $this->send($send);
    }

    /**
     * 获取api数据
     *
     * @param string $api_name，api接口名
     * @param string $api_version，api的版本
     * @param array $params，api请求的参数
     * @param string $return_data，API的返回类型 xml/json
     * @param boolean $license，是否要发送license，默认要发送
     * @param boolean $cache，是否要缓存数据到本类对象中，默认不开
     *
     * @return mixed
     */
    function getApiData($api_name,$api_version,$params,$license=true,$cache=false,$return_data='json',$send_data='json'){
        if ($this->system->getConf('site.api.maintenance.is_maintenance') && floatval(API_VERSION)>=2){
            $this->error_no = '0x019';
            $this->error = $this->system->getConf('site.api.maintenance.notify_msg');
            trigger_error($this->error, E_USER_ERROR);
            return false;
        }
        $this->error_no=null;
        $this->error=null;
        if($cache){
            $key = md5($api_name.$api_version.json_encode($params));
            if(isset($this->api_data[$key])){
                return $this->api_data[$key];
            }else{
                $this->api_data[$key] = $this->_getApiData($api_name,$api_version,$params,$license,$return_data,$send_data);
                return $this->api_data[$key];
            }
        }else{
            return $this->_getApiData($api_name,$api_version,$params,$license,$return_data);
        }
    }

    /**
     * trigger所有api 错误
     * 对于系统级错误和应用级错误error级别直接 trigger_error E_USER_ERROR
     *
     * @author bryant
     * @date 2009-05-31
     * @param int $call_func, 错误处理回调函数
     * @param int $call_params, api 错误处理回调函数参数
     */
    function trigger_all_errors($call_func=null, $call_params=array()){
        $errorStr = '';
        $callback = false;
        $error_type = E_USER_ERROR;
        if($this->error_no=='0x003'){
            $errorStr = $this->error.'::';
            $callback = true;
        }else if($this->error_no=='0x015') { // b2b端正在做还原备份 wubin 2009-11-09 15:35
            $errorStr = $this->error_no;
            $callback = true;
        }else if($this->error_no=='0x016') { // 修改更新权限同步时的错误 yanglish
            $error_type = E_USER_WARNING;
        }else if($this->error_no!=null){
            $errorStr = 'sys:'.$this->error;
            $callback = true;
        }

        //应用级错误没有编号,返回成功
        if($this->application_error){
            foreach($this->application_error as $array_error){
                if($array_error['level']=='error'){
                    $errorStr = 'sys:'.$array_error['desc'];
                    $callback = true;
                    break;
                }
            }
        }
        
        if($call_func && $errorStr && $callback===true){
            @call_user_func_array($call_func, $call_params);
            $error_type = E_USER_WARNING;
        }
        if($errorStr){
            trigger_error($errorStr, $error_type);            
        }
    }
}
?>