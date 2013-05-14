<?php
/**
 * api 基类
 * @package
 * @version 1.0:
 * @copyright 2003-2009 ShopEx
 * @author dreamdream
 * @license Commercial
 */
class shop_api_object{
    var $data_format='xml';//返回值类型 默认字符返回 0,1 返回 xml,2 返回 json
    var $version='1.0';
    var $columns_split='|';//字段默认分格
    var $select_limited=20;//获取记录的上线
    var $model_instance;
    var $gzip;
    var $application_error=array();
    /**
    * 构造函数
    */
    function shop_api_object(){
        if(!$this->system){
            $this->system = &$GLOBALS['system'];
        }
        if(!$this->db){
            $this->db = $this->system->database();
        }
    }
    /**
    * API 内部调用方法
    * @param string link的方法名称
    * @param string api的版本
    * @return api方法实例
    */

    function load_api_instance($act,$api_version){
        if(!$this->method){
            $this->method=include(CORE_DIR.'/api/include/api_link.php');
        }
        $callmethod=$this->method[$act][$api_version];
        if($ctl=$callmethod['ctl']){
            if(!$this->model_instance[$ctl]){
                include_once(CORE_DIR.'/'.dirname($ctl).'/'.$api_version.'/'.basename($ctl).'.php');
                $ctl=basename($ctl);
                $this->model_instance[$ctl]=new $ctl;
                $this->model_instance[$ctl]->data_format=$this->data_format;
            }
            return $this->model_instance[$ctl];
        }
    }

    /**
    * 数据返回
    * @param array 数据源
    * @author DreamDream
    * @return 将数据按照设定的类型返回
    */

    function return_date($data){
        switch($this->data_format){
            case 'string':
                $result=print_r($data,true);
            break;
            case 'json':
                $this->_header('text/html');
                // 对uf8编号进行unicode处理 b2b 2010-02-26 18:34 wubin
                $data = function_exists('ucs_encode')? ucs_encode($data) : $data;
                $result= json_encode($data);
            break;
            case 'xml':
                $this->_header('text/xml');
                $xml=&$this->system->loadModel('utility/xml');
                //$data['msg'] = $this->error($data['msg']);
                $result= $xml->array2xml($data,'shopex');

            break;
            case 3:

            break;
            case 'soap':

                //soap
            break;
            default:
                 $this->api_response('fail','language error',$data);
            break;

        }
        if($this->gzip && function_exists('gzencode')){
            echo @gzencode($result);
        }else{
            echo $result;
        }
        exit();
    }


    function varify_date_whole(&$data){
        $aData=$this->getColumns();
        foreach($data as $key=>$v){
            if($aData[$key]){
                $result[$key]=$v;
                unset($data[$key]);
            }
        }
        if($data){
            $this->api_response('fail','data fail',$data);
        }
        return $result;
    }

    /**
    * 头文件
    * @param string 文件类型
    * @param string 编码格式
    * @author DreamDream
    */
    function _header($content='text/html',$charset='utf-8'){
        header('Content-type: '.$content.';charset='.$charset);
        if($this->gzip && function_exists('gzencode')){
            header('Content-Encoding: gzip');
        }
        header("Cache-Control: no-cache,no-store , must-revalidate");
        $expires = gmdate ("D, d M Y H:i:s", time() + 20);
        header("Expires: " .$expires. " GMT");
    }


    /**
    * API LOG
    * @param log的信息
    * @param 原始文件
    * @author DreamDream
    */
    function api_error_log($msg,$data){
        $path=HOME_DIR.'/logs';
        $handle=fopen($path.'/'.date("Ymd").'.log','a+');
        $content='data:'.date("Y m d H:i:s").print_r($data,true)."\r\n";
        fwrite($content);
        fclose($handle);
    }

    /**
    * 通用过滤器
    * @param 查询条件
    * @param 过滤器原值
    * @author DreamDream
    * @return 过滤过的筛选条件
    */
    function _filter($where=NULL,$filter){
        $where || ($where = array(1));

        foreach( $where as $i=>$v ) {
            if ( empty($v) ) unset($where[$i]);
        }

        $result = ' WHERE '.implode($where,' AND ');

        if($filter['orderby']){
            $result .= ' ORDER BY '.$filter['orderby'].' DESC';
        }

        ($psize = +$filter['page_size'] ^ +$filter['counts']) || ($psize = 20);
        ($pnumber = +$filter['page_no'] ^ +$filter['pages']) || ($pnumber = 1);
        
        $result .= ' LIMIT '.$psize*($pnumber-1).','.$psize;
        
        return $result;
    }

    function load_model($path,$apiversion='1.0'){
        $file=API_DIR.'/'.dirname($path).'/'.$apiversion.'/model/mdl.'.basename($path).'.php';
        if(file_exists($file)){
            require_once($file);
            $mdl_instalce='mdl_'.basename($path);
            if(!$this->model[$path]){
                $this->model[$path]=new $mdl_instalce;
            }else{
                return $this->model[$path];
            }
        }

    }
    /**
    * 数据校验
    * @param 原数据值
    * @param 必备的数据字段
    * @author DreamDream
    * @return 是否是正确数据
    */
    function verify_data(&$data,&$key_value){
                
        if($key_value['required']){
            foreach($key_value['required'] as $value){
                if(!isset($value)){
                    $this->api_response('fail','data fail');
                }
            }
        }

        if($key_value['columns']){
            if($data['columns']){
                $data['columns']=explode('|',$data['columns']);
                $_tmpcolumns="";
                $columns=$this->getColumns();
                foreach($data['columns'] as $key=>$v){
                    if($columns[$v]){
                        if($columns[$v]['join']){
                            $data['columns_join'][$v]=true;
                            unset($data['columns'][$key]);
                        }
                        if($columns[$v]['name']){
                            $data['columns'][$key]=$columns[$v]['name'].' as '.$v;
                        }
                    }
                }
            }else{
                if(method_exists($this,'getColumns')){
                    foreach((array)$this->getColumns() as $key=>$v){
                        if($v['join']){
                            $data['columns_join'][$key]=true;
                        }else if($v['name']){
                            $data['columns'][]=$v['name'].' as '.$key;
                        }else{
                            $data['columns'][]=$key;
                        }
                    }
                }
            }
            if(is_array($key_value['columns'])){
                foreach($key_value['columns'] as $value){
                    if(!in_array($value,$data['columns'])){
                        $this->api_response('fail','data fail');
                    }
                }
            }
        }
                
        return true;
    }
    /*
    function application_error_handle($code,$debug,$info,$desc){
        if($debug){
            $this->app_error[$code]['debug']=$debug;
        }
        if($info){
            $this->app_error[$code]['info']=$info;
        }
        if($desc){
            $this->app_error[$code]['desc']=$desc;
        }
        return $this->app_error[$code];
    }
    */

    /**
    * API 添加应用级错误
    * @param 错误码
    * @param 调试错误信息，放数据源
    * @param 错误信息
    * @param 错误详细说明
    * @author DreamDream
    * @return 成功数据信息，失败错误信息
    */

    function add_application_error($code,$debug,$info,$desc){
        if($debug){
            $this->app_error[$code]['debug']=$debug;
        }
        if($info){
            $this->app_error[$code]['info']=$info;
        }
        if($desc){
            $this->app_error[$code]['desc']=$desc;
        }
        if($this->app_error[$code]){
            $this->application_error[]=$this->app_error[$code];
            if($this->app_error[$code]['level']=='error'){
               $this->api_response('fail','data fail',null,'application error');
            }
        }

    }
    function api_erro_table($code){
        return $error($code);
    }

    /**
    * API 返回值
    * @param 返回码
    * @param 是否发生错误
    * @param 数据原始值 // $data return value to client @author rocky
    * @author DreamDream
    * @return 成功数据信息，失败错误信息
    */

    function api_response($resCode,$errorCode=false,$data=null,$info=null){
                
        $resposilbe = array(
            'true'=>'success',
            'fail'=>'fail',
            'wait'=>'wait'
        );
        if($errorCode){
            $error = $this->error_code($errorCode);
            if(constant('API_ERROR_LOG')){
                if(constant('API_ERROR_LOG_LEVEL')){
                    if($error['level'] >= API_ERROR_LOG_LEVEL){
                        $this->api_error_log($error,$data);
                    }
                }else{
                    $this->api_error_log($error,$data);
                }
            }
            $result['result'] = $resposilbe[$resCode];
            $result['msg_code'] = (string)$error['code'];
            if($error['code']){
                $result['msg'] = $this->error($error['code']);
            }else{
                $result['msg'] = $errorCode;
            }
            $result['info'] = $info?$info:$errorCode;
        }else{
            foreach($data['data_info'] as $key=>$value){
                if(!$data['data_info'][$key] && $data['data_info'][$key]!=='0' && $data['data_info'][$key]!==0){
                   $data['data_info'][$key]='';
                }
            }
            $result['result']=$resposilbe[$resCode];
            $result['msg']='';
            $result['info']=$data;

        }
        if($this->application_error && is_array($this->application_error)){
            $result['application_error'] = $this->application_error;
        }
        //print_r($result);exit();
        echo $this->return_date($result);
        exit();
    }


    /**
    * API 错误码
    * @param 错误编码
    * @author DreamDream
    * @return 返回错误信息
    */
    function &error_code($code){
        if(!$this->error){
            $this->error=include('include/api_error_handle.php');
        }
        return $this->error[$code];
    }
    
     /**
    * API 错误码
    * @param 错误编码
    * @author DreamDream
    * @return 返回错误说明信息
    */
    function &error($code){
        if(!$this->errors){
            $this->errors=include('include/api_error.php');
        }
        return $this->errors[$code];
    }
    
    function start_db_transaction(){
        
    }
    
    function end_db_transaction(){
        
    }
}
