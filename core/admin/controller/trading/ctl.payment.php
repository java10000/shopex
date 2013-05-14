<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_payment
 *
 * @uses pageFactory
 * @package
 * @version $Id: ctl.payment.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Likunpeng <leoleegood@zovatech.com>
 * @license Commercial
 */
include_once('objectPage.php');
class ctl_payment extends objectPage {

    var $workground ='setting';
    var $finder_action_tpl = 'payment/finder_action.html';
    var $object = 'trading/paymentcfg';
    var $editMode = true;
    var $allowExport = false;

    var $disableGridEditCols = "id";
    var $disableColumnEditCols = "id";
    var $disableGridShowCols = "id";
    var $filterUnable = true;
    /**
    * main
    *
    * @access public
    * @return void
    */

    function _detail(){
        return array('show_detail'=>array('label'=>__('支付配置'),'tpl'=>'payment/pay_edit.html'));
    }

    function index(){
        $appmgr = &$this->system->loadModel('system/appmgr');
        $client = &$this->system->loadModel('service/apiclient');

        $client->key = SDS_API_KEY;
        $client->url = SDS_API;

        $payment = $client->native_svc("payment.get_all_payments");

        if($payment['result'] == 'succ'){
            $allApp = $appmgr->getPaydata($payment['result_msg']);
            file_put_contents(HOME_DIR.'/sendtmp/allApp.log',serialize($allApp));
        }

        $allApp = file_exists(HOME_DIR.'/sendtmp/allApp.log')
                ? file_get_contents(HOME_DIR.'/sendtmp/allApp.log')
                : file_get_contents(HOME_DIR.'/sendtmp/defaultApp.log');
        $allApp = unserialize($allApp);
        if ( !$allApp ) unset($allApp);

        $useApp = file_exists(HOME_DIR.'/sendtmp/useApp.log')
                ? file_get_contents(HOME_DIR.'/sendtmp/useApp.log') : '';
        $useApp = unserialize($useApp);
        if ( !$useApp ) unset($useApp);

        $this->pagedata['allNum'] = count($allApp);
        $this->pagedata['useNum'] = count($useApp);
        $this->pagedata['allPay'] = $allApp;
        $this->pagedata['usePay'] = $useApp;
        $this->page('payment/pay_index.html');
    }

    function show_detail($id){
        $oPay = &$this->system->loadModel('trading/payment');
        $aPay = $oPay->getPaymentById($id);
        $this->pagedata['pay'] = $aPay;
        $this->pagedata['pay_info'] = $this->_getPayOpt($aPay['pay_type'], $aPay['custom_name'], $aPay['fee'], $aPay['config'],1);
        $oPlu = $oPay->loadMethod($aPay['pay_type']);
        if($oPlu){
            $this->pagedata['html'] =  $oPlu->infoPad();
        }
        $this->pagedata['pay_id'] = $id;
        $this->pagedata['order'] = $aPay['orderlist'];
        $this->pagedata['old_pay_type'] = $aPay['pay_type'];
        $this->pagedata['pay_des'] = $aPay['des'];
        $this->pagedata['pay_name'] = $aPay['custom_name'];
        $this->pagedata['paylist'] = $oPay->getPluginsArr(true);
    }

    /**
    * main
    *
    * @access public
    * @return void
    */
    function getPayList(){
        $this->path[] = array('text'=>__('支付方式'));
        $oPay = &$this->system->loadModel('trading/payment');
        $this->pagedata['items'] = $oPay->getMethods();
        $this->page('payment/pay_list.html');
    }

    function _getHtmlString($key,&$val,$rs=array(),&$eventScripts){
        switch($val['type']){
            case 'string':
                $aTemp=array('labelName'=>$val['label'],'params'=>array('type'=>'text','name'=>$key,'value'=>$rs[$key]?$rs[$key]:'','size'=>30),'desc'=>$val['desc']);
                break;
            case 'select':
                foreach($val['options'] as $k=>$v){
                    $tOptions[$k] = $v;
                    if($rs[$key] == $k){
                        $selOption = $k;
                    }
                }
                $aTemp=array('labelName'=>$val['label'],'params'=>array('type'=>'select','name'=>$key,'value'=>$selOption,'options'=>$tOptions),'desc'=>$val['desc']);
                break;
            case 'number':
                $aTemp=array('labelName'=>$val['label'],'params'=>array('type'=>'text','name'=>$key,'value'=>$rs[$key]?$rs[$key]:''),'desc'=>$val['desc']);
                break;
            case 'file':
                $aTemp=array('labelName'=>$val['label'],'params'=>array('type'=>'file','name'=>$key,'value'=>$selOption,'options'=>$tOptions),'desc'=>$val['desc']);
                break;
            case "radio":
                foreach($val['options'] as $k => $v){
                    $checked="";
                    if ($rs[$key]==$k)
                        $checked="checked";
                    $tOptions[$k]=$v;
                    if ($rs[$key]==$k)
                        $selOption=$k;
                }

                if ($val['extendcontent']){
                    unset($extendContent);
                    foreach($val['extendcontent'] as $ck => $cv){
                        $scripts.="<script>";
                        if (isset($rs[$key]))
                           if ($rs[$key])
                               $scripts.="$('".$cv['property']['extconId']."').show();";
                           else
                               $scripts.="$('".$cv['property']['extconId']."').hide();";
                        else{
                            if ($cv['property']['display'])
                                $scripts.="$('".$cv['property']['extconId']."').show();";
                            else
                                $scripts.="$('".$cv['property']['extconId']."').hide();";
                        }
                        $scripts.="</script>";
                        $i=0;
                        $type=$cv['property']['type'];
                        $name=$cv['property']['name'];
                        $size=$cv['property']['size']?$cv['property']['size']:4;
                        unset($extendContent);
                        foreach($cv['value'] as $csk => $csv){
                            unset($checked);
                            if (!$rs)
                                $checked='checked=true';
                            if (in_array($csv['value'],$rs[$name]))
                                $checked='checked=true';
                            $csv['imgname'] = $csv['imgname']?"<img src=".$this->system->base_url()."plugins/payment/images/".$csv['imgname'].">":$csv['name'];
                            $val['extendcontent'][$ck]['value'][$csk]['imgname']=$csv['imgname'];
                            $val['extendcontent'][$ck]['value'][$csk]['checked']=$checked;
                        }
                    }

                }
                $aTemp=array('labelName'=>$val['label'],'params'=>array('type'=>'radio','name'=>$key,'value'=>$selOption,'options'=>$tOptions),'extendContent'=>$val['extendcontent']);
                if ($val['event']){
                    $aTemp['params']['onclick']=$val['event'].'(this);';
                }
                if ($val['eventscripts']){
                    $eventScripts=$val['eventscripts'].$scripts;
                }
                break;
            default:
                $aTemp=array('labelName'=>$val['label'],'params'=>array('type'=>'text','name'=>$key,'value'=>$rs[$key]?$rs[$key]:''));
                break;
        }
        return $aTemp;
    }

    /**
    * savePayment
    *
    * @access public
    * @return void
    */
    function savePayment($ident){
        $this->begin('index.php?ctl=trading/payment&act=index');
        $payMdl = &$this->system->loadModel('trading/payment');

        $_POST['pay_type'] = $paytype = substr($ident, 4);
        
        //有文件上传
        if ( $_FILES ) {
            $sfileMdl = &$this->system->loadModel("system/sfile");
            foreach($_FILES as $key => $val){
                $_POST[$key]=$val['name'];

                $sfileMdl->UploadPaymentFile($val,$paytype);//上传支付相关文件
            }
        }

        if( !$payMdl->updatePay($_POST) ) {
            $this->end(false, __('保存失败！'));
        }

        $this->end(true, __('保存成功！'));
    }

    function disable($id){
        $payment = $this->system->loadModel('trading/payment');
        $this->begin('index.php?ctl=trading/payment&act=index');
        $this->clear_all_cache();
        $this->end($payment->deletePay($id));
    }
    /**
    * addpayment
    *
    * @access public
    * @return void
    */
    function addPayment(){
        $this->begin('index.php?ctl=trading/payment&act=index');
        $oPay = &$this->system->loadModel('trading/payment');
        if ($_POST['paymethod']==1)
            $_POST['fee'] = $_POST['fee'] / 100;
        if ($_FILES){//是否有文件上传
            $file=&$this->system->loadModel("system/sfile");
            foreach($_FILES as $key => $val){
                if (intval($val['size'])>0){
                    $_POST[$key]=$val['name'];
                    switch ($_POST['pay_type']){
                        case "ICBC"://工商银行
                            if ($key=="keyFile"){//商户私钥文件
                                if(substr($val['name'],strrpos($val['name'],".")+1,strlen($val['name']))!="key"){
                                    trigger_error(__('文件格式有误,请上传key格式文件'),E_USER_ERROR);
                                    exit;
                                }
                            }
                            elseif ($key == "certFile"||$key =="icbcFile"){//商户公钥文件
                                if(substr($val['name'],strrpos($val['name'],".")+1,strlen($val['name']))!="crt"){
                                    trigger_error(__('文件格式有误,请上传crt格式文件'),E_USER_ERROR);
                                    exit;
                                }
                            }
                            break;
                        case "HYL"://广东好易联
                            if ($key == "keyFile"){//私钥文件
                                if(substr($val['name'],strrpos($val['name'],".")+1,strlen($val['name']))!="pem"){
                                    trigger_error(__('文件格式有误,请上传pem格式文件'),E_USER_ERROR);
                                    exit;
                                }
                            }
                            elseif ($key == "certFile"){//公钥文件
                                if(substr($val['name'],strrpos($val['name'],".")+1,strlen($val['name']))!="cer"){
                                    trigger_error(__('文件格式有误,请上传cer格式文件'),E_USER_ERROR);
                                    exit;
                                }
                            }
                            break;
                        case "skypay":
                            if ($key=="keyFile" || $key =="certFile"){//私钥文件
                                if(substr($val['name'],strrpos($val['name'],".")+1,strlen($val['name']))!="key"){
                                    trigger_error(__('文件格式有误,请上传key格式文件'),E_USER_ERROR);
                                    exit;
                                }
                            }
                            break;
                        default:
                            break;

                    }
                    $file->UploadPaymentFile($val,$_POST['pay_type']);//上传支付相关文件
                }
            }
        }
        $this->end($oPay->insertPay($_POST,$msg),$msg);
    }

    /**
    * addpayment
    *
    * @access public
    * @return void
    */
    function delPayment($sId){
        $this->begin('index.php?ctl=trading/payment&act=index');
        $oPay = &$this->system->loadModel('trading/payment');
        $this->end($oPay->deletePay($sId),__('删除成功！'));
    }
    /**
    * editPayment
    *
    * @access public
    * @return void
    */
    function editPayment($ident){
        $this->path[] = array('text'=>__('编辑支付方式'));
        $payMdl = &$this->system->loadModel('trading/payment');
        $paytype = substr($ident,4);
        
        if( !$payPlugin = $payMdl->loadMethod($paytype) ) return;

        if ( !$conf = $payMdl->getPaymentByIdent($paytype) ) return;

        $this->pagedata['pay_ident'] = $ident;
        $this->pagedata['html'] =  $payPlugin->infoPad();
        
        $this->_getPayOpt($paytype,$conf['custom_name'],$conf['fee'],$conf['config'],1);

        $this->pagedata['conf'] = $conf;

        $this->page('payment/pay_new.html');
    }

    /**
    * detailPayment
    *
    * @access public
    * @return void
    */
    function detailPayment($id){
        $this->path[] = array('text'=>__('支付方式配置'));
        $oPay = &$this->system->loadModel('trading/payment');
        //$oPay->getPluginsArr(true);
        $aPay = $oPay->getPaymentById($id);
        $this->pagedata['pay'] = $aPay;
        $this->pagedata['pay_info'] = $this->_getPayOpt($aPay['pay_type'], $aPay['custom_name'], $aPay['fee'], $aPay['config']);
        $this->pagedata['pay_id'] = $id;
        $this->pagedata['order'] = $aPay['orderlist'];
        $this->pagedata['old_pay_type'] = $aPay['pay_type'];
        $this->pagedata['pay_des'] = $aPay['des'];
        $this->pagedata['pay_name'] = $aPay['custom_name'];
        $this->pagedata['paylist'] = $oPay->getPluginsArr(true);
        $this->page('payment/pay_edit.html');
    }

    /**
    * getPayOpt
    *
    * @access public
    * @return void
    */
    function getPayOpt($sType, $sPayName=''){
        header('Content-Type: text/html;charset=utf-8');
        if(!$sType){
            echo ' ';
        }else{
            echo $this->_getPayOpt($sType, $sPayName);
        }
    }

    function _getPayOpt($sType, $sPayName='', $nFee='', $config='',$fetch=0){
        $oPay = &$this->system->loadModel('trading/payment');
        if ( !$oPlu = $oPay->loadMethod($sType) ) return;

        if($aThisPayCur = $oPay->getSupportCur($oPlu)){
            if($aThisPayCur['DEFAULT']){

                $curName = __('商店默认货币');
            }else{
                $oCur = &$this->system->loadModel('system/cur');
                $aCurLang = $oCur->getSysCur();
                if($aThisPayCur['ALL']){
                    $aThisPayCur = $aCurLang;
                }
                foreach($aThisPayCur as $k=>$v){
                    $curName .= $aCurLang[$k].",&nbsp;";
                    $curName=$curName?rtrim($curName,',&nbsp;'):'';
                }
            }
        }

        $aTemp = unserialize($config);
        if($aTemp){
            foreach($aTemp as $key=>$val){
                if ($key<>'method'&&$key<>'fee')
                   $aPay[$key]=$val;
            }
        }
        $aField = $oPlu->getfields();
        foreach($aField as $key=>$val){
            $PayPlugItem[] = $this->_getHtmlString($key,$val,$aPay,$eventScripts);
        }

        if ($aTemp['method']==1||!isset($aTemp['method']))
            $check1='checked';
        elseif ($aTemp['method']==2)
            $check2='checked';
        $this->pagedata['sPayName'] = $sPayName;
        $this->pagedata['curName']  = $curName;
        $this->pagedata['PayPlugItem'] = $PayPlugItem;

        $this->pagedata['fee'] = $nFee;
        $this->pagedata['checked']=array($check1,$check2);
        $this->pagedata['eventScripts'] = $eventScripts;
        $this->pagedata['hiddenmethod'] = $aTemp['method'];
    }

    function do_install($ident,$type='offline',$is_update=false){
        // 存在本地目录则从本地安装, 否则从服务器下载安装
        if( is_dir(PLUGIN_DIR.'/app/'.$ident) ) {
            $this->install_app($ident);    
        } else {
            $this->install_online($ident);
        } 
    }

    function install_online($ident,$url){
        $url = SDS_PAYMENT_INSTALLONLINE_URI.$ident.'.tar';
        include(CORE_DIR.'/admin/controller/service/ctl.download.php');
        $download = new ctl_download();
        $_POST = array(
            'download_list'=>array($url),
            'succ_url'=>'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])
            .'/index.php?ctl=trading/payment&act=do_install_online'
        );

        $download->set = 'true';
        $download->start();
    }

    function install_app($ident){
        $appmgr = $this->system->loadModel('system/appmgr');
        $refesh = &$this->system->loadModel('system/addons');
        $payment = $this->system->loadModel('trading/payment');
        if($appmgr->install($ident,'1')){
            $allApp = file_exists(HOME_DIR.'/sendtmp/allApp.log')
                ? file_get_contents(HOME_DIR.'/sendtmp/allApp.log')
                : file_get_contents(HOME_DIR.'/sendtmp/defaultApp.log');
            $allApp = unserialize($allApp);

            $useApp = file_exists(HOME_DIR.'/sendtmp/useApp.log')
                ? file_get_contents(HOME_DIR.'/sendtmp/useApp.log')
                : '';
            $useApp = unserialize($useApp);

            foreach( $useApp as $v ) {
                $useApp_[$v['pay_ident']] = $v['pay_ident'];
            }

            foreach($allApp as $key=>$val){
                if( !isset($useApp_[$ident]) && $val['pay_ident'] == $ident){
                    $val['disabled'] = 'true';
                    $useApp[] = $val;
                }
            }

            file_put_contents(HOME_DIR.'/sendtmp/useApp.log',serialize($useApp));

            if(!$_SESSION['updatePayment']){
                $plugin = $appmgr->getAppName($ident);
                $data['custom_name'] = $plugin['plugin_name'];
                $data['pay_type'] = substr($ident,4);
                $data['disabled'] = 'true';
                
                $payment->insertPaymentApp($data, $err);
            }

            unset($_SESSION['updatePayment']);

            $this->clear_all_cache();

            echo'<script>W.page(\'index.php?ctl=trading/payment&act=index\',{onComplete:function(){$(\'main\').setStyle(\'width\',window.mainwidth);}})</script>';
        }else{
            $this->end(false,'安装失败');
        }
    }

    function do_install_online(){
        $task = HOME_DIR.'/tmp/'.$_GET['download'];
        $temp_mess = file_get_contents($task.'/task.php');
        $down_data = unserialize($temp_mess);
        if($url = $down_data['download_list'][0]){
            $filename = substr($url,strrpos($url,"/")+1);
            $file_path = $task.'/'.$filename;
            $dir_name = substr($filename,0,strrpos($filename,"."));
            if(file_exists($file_path)){
                $appmgr = $this->system->loadModel("system/appmgr");
                $appmgr->instal_ol_app($file_path,$dir_name,$msg,true);
                $this->install_app($dir_name);
            }
        }
    }

    function updateNewPayment($ident){
        if( !isset($ident) ) return;

        $_SESSION['updatePayment'] = true;
        $this->install_online($ident);
    }

    function disApp($ident){
        $this->begin('index.php?ctl=trading/payment&act=index');

        $paymentObj = &$this->system->loadModel('trading/payment');
        $this->end($paymentObj->disApp($ident), __('修改成功！'));
    }

    function startApp($ident){
        $this->begin('index.php?ctl=trading/payment&act=index');

        $paymentObj = &$this->system->loadModel('trading/payment');
        $this->end($paymentObj->startApp($ident), __('修改成功！'));
    }

    function deletePayment($ident){
        $oPayment = $this->system->loadModel('trading/payment');

        $this->begin('index.php?ctl=trading/payment&act=index');

        // 中心登记
        $this->sendRequestAsync($ident,'delete');

        // 本地删除
        $oPayment->deletePayment($ident);
        
        // 删除目录
        !is_dir(PLUGIN_DIR."/app/".$ident) || deleteDir(PLUGIN_DIR."/app/".$ident);

        $this->clear_all_cache();

        $this->end(true,'操作成功');
    }

    function sendRequestAsync($ident,$operation_type,$version=""){
        $cet_ping = ping_url("http://esb.shopex.cn/api.php");
        if(!strstr($cet_ping,'HTTP/1.1 200 OK')){
            return;
        }
        if(!$version){
            $oAppmgr = $this->system->loadModel("system/appmgr");
            $appInfo = $oAppmgr->getPluginInfoByident($ident);
            $version = $appInfo['plugin_version'];
        }
        echo "<script>new Request().post('index.php?ctl=trading/payment&act=sendDataToCenter',{pay_ident:'$ident',version:'$version',operation_type:'$operation_type'});</script>";
    }

    function sendDataToCenter(){
        $oApiClient = $this->system->loadModel("service/apiclient");
        $oApiClient->url="http://esb.shopex.cn/api.php";
        $return = $oApiClient->native_svc("payment.count_payment",array('certi_id'=>$this->system->getConf('certificate.id'),'pay_key'=>$_POST['pay_ident'],'version'=>$_POST['version'],'type'=>$_POST['operation_type'],'time'=>time()));
    }
}

?>
