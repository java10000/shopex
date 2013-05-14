<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
require_once('objectPage.php');
class ctl_appmgr extends adminPage{

    var $workground = 'tools';

    function index(){
        $appmgr = $this->system->loadModel('system/appmgr');
        $apps = $appmgr->getList();
        $this->pagedata['install_url'] = constant('APP_ONLINE_URL');
        $aTmp = array();
        foreach($apps as $k=>$app){
            $file = BASE_DIR.'/plugins/app/'.$app['plugin_package'].'/setting.php';
            if(file_exists($file)){
                if($app['status']=='used'){
                    $setting = array();
                    require($file);
                    foreach($setting as $key=>$value){
                        if(!$this->system->getConf('app.'.$app['plugin_ident'].'.'.$key)){
                            $app['unset_setting'] = 1;
                        }
                    }
                }
                $app['has_setting'] = 1;
            }

            $app['plugin_struct'] = unserialize($app['plugin_struct']);
            if($app['plugin_ident']){
                $aTmp[] = array('app_key'=>$app['plugin_ident'],'version'=>$app['plugin_version']);
            }
            $apps[$k] = $app;
        }
        if(count($aTmp)>0){
            $update_times = $this->getAppUpdateTime($aTmp);
            foreach($apps as $k=>$app){
                if($update_times[$app['plugin_ident']])
                    $apps[$k]['update_time'] = $update_times[$app['plugin_ident']];
                else
                    $apps[$k]['update_time'] = datetotimestamp('2011-11-16');
            }
        } else {
            foreach($apps as $k=>$app){
                $apps[$k]['update_time'] = datetotimestamp('2009-11-16');
            }
        }
        $this->pagedata['apps'] = &$apps;
        
        $this->page('system/appmgr/index.html');
    }

    function install_update(){
        echo "<script>new Request().post('index.php?ctl=system/appmgr&act=appupdate_count',{app_key:'".$_GET['app_ident']."',version:'".$_GET['app_version']."'});</script>";
        $this->pagedata['update_url'] = 'index.php?ctl=system/appmgr&act=download_update_app&app_status='.$_GET['app_status'].'&url='.$_GET['url'];
        $this->page('system/appmgr/update.html');
    }

    function download_update_app(){
        if(isset($_GET['url'])){
            include(CORE_DIR.'/admin/controller/service/ctl.download.php');
            $download = new ctl_download();
            $_POST = array(
                'download_list'=>array($_GET['url']),
                'succ_url'=>'http://'.HTTP_HOST.dirname(PHP_SELF)
                .'/index.php?ctl=system/appmgr&act=do_update&app_status='.$_GET['app_status']
            );

            $download->start();
        }
        exit;
    }

    function do_update(){
        $task = HOME_DIR.'/tmp/'.$_GET['download'];
        $temp_mess = file_get_contents($task.'/task.php');
        $down_data = unserialize($temp_mess);
        if($url = $down_data['download_list'][0]){
            $filename = substr($url,strrpos($url,"/")+1);
            $file_path = $task.'/'.$filename;
            $dir_name = substr($filename,0,strrpos($filename,"."));
            if(file_exists($file_path)){
                $appmgr = $this->system->loadModel("system/appmgr");
                if(!$appmgr->instal_ol_app($file_path,$dir_name,$msg,true)){
                    echo $msg;
                    exit;
                }else{
                    if($_GET['app_status']=='used'){
                        $this->install($dir_name,'online','update');
                    }else{
                        $this->install($dir_name,'online');
                    }
                };
            }
        }
    }

    function app_onlince(){
        $this->pagedata['cert_id'] = $this->system->loadModel('service/certificate')->getCerti();
        $this->page('system/appmgr/app_online.html');
    }

    function view($ident){
        $appmgr = $this->system->loadModel('system/appmgr');
        $this->pagedata['app'] = $appmgr->info($ident);
        $this->pagedata['propmap'] = array(
            'ver'=>'版本',
            'website'=>'网址',
            'author'=>'作者',
            'baseurl'=>'访问路径',
            'plugin_path'=>'文件路径',
            );
        $this->display('system/appmgr/detail.html');
    }

    function install_online(){
        if(isset($_GET['url'])){
            include(CORE_DIR.'/admin/controller/service/ctl.download.php');
            $download = new ctl_download();
            $_POST = array(
                'download_list'=>array($_GET['url']),
                'succ_url'=>'http://'.HTTP_HOST.dirname(PHP_SELF)
                .'/index.php?ctl=system/appmgr&act=do_install_online'
            );

            $download->start();
        }
        exit;
    }

    function do_install($ident,$type='offline',$is_update=false){
        $appmgr = $this->system->loadModel('system/appmgr');
        $this->begin('index.php?ctl=system/appmgr&act=index');
        if($appmgr->install($ident,$is_update)){

            echo "<script>new Request().post('index.php?ctl=system/appmgr&act=appkey_count',{app_key:'$ident',type:'".$_GET['operation_type']."',version:'".$_GET['version']."'});</script>";
            $this->clear_all_cache();
            if($type=='online'){
                echo "<script>parent.$('dialogContent').getParent('.dialog').retrieve('instance').close(); W.page('index.php?ctl=system/appmgr&act=index');</script>";
                exit;
            }
            $this->setLoadTpl($ident);
            $this->setApi($appmgr, $ident);
            //检查安装
            $this->end(true,'安装成功','index.php?ctl=system/appmgr&act=index','');
        }else{
            $this->end(false,'安装失败');
        }
    }

    function install($ident,$type='offline',$is_update=false){
        $appmgr = $this->system->loadModel('system/appmgr');
        $app = &$appmgr->load($ident);

        if($app->depend){
            if($err_str = $appmgr->getappByident($app->depend)){
                $this->pagedata['error_msg'] = $err_str;
            };
        }
        $this->pagedata['table_prefix'] = 'sdb_'.$ident.'_';

        include('ctlmap.php');
        $map = array();
        $system_ctl_map = &$this->system->getConf('system.ctlmap');
        foreach($app->ctl_mapper() as $k=>$v){
            if($system_ctl_map[$k]){
                list($pkg,$class,$method)= explode(':',$system_ctl_map[$k]);
                $stopapp[$pkg] = $pkg;
            }
            $map[] = isset($ctlmap[$k])?$ctlmap[$k]:$k;
        }

        foreach($app->listener() as $eventOrg=>$handle){
            if($eventOrg=='*'){
                $listen_event[] = '所有';
            }else{
                list($mdl,$event) = explode(':',$eventOrg);
                $mdl = &$this->system->loadModel($mdl,false);
                if( method_exists($mdl,'events') &&
                    ($events = $mdl->events()) && $events[$event] ) {
                    $listen_event[] = $mdl->name.$events[$event]['label'];
                }else{
                    $listen_event[] = $eventOrg;
                }
            }
        }

        if($type=='online') $this->pagedata['online'] = true;
        $this->pagedata['listen_event'] = &$listen_event;
        $this->pagedata['ctl_mapper'] = $map;
        $this->pagedata['app_ident'] = $ident;
        $this->pagedata['stopapp'] = $appmgr->getNameByIdents($stopapp);
        $this->pagedata['app'] = get_object_vars($app);
        $this->pagedata['app_tables'] = $app->dbtables();

        if($is_update){
            $this->pagedata['update'] = 'yes';
            if($diff_tables = $appmgr->get_app_diff($ident)){
                $this->pagedata['app_diff_tables'] = $diff_tables;
            }
        }
        $this->page('system/appmgr/install.html');
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
                if(!$appmgr->instal_ol_app($file_path,$dir_name,$msg)){
                    echo $msg;
                    exit;
                }else{
                    $this->install($dir_name,'online');
                };
            }
        }
    }

    function uninstall($ident){
        $appmgr = $this->system->loadModel('system/appmgr');
        $this->pagedata['table_prefix'] = 'sdb_'.$ident.'_';
        $app = &$appmgr->load($ident,'app');

        include('ctlmap.php');
        $map = array();
        foreach($app->ctl_mapper() as $k=>$v){
            $map[] = $ctlmap[$k];
        }
        $this->pagedata['ctl_mapper'] = $map;

        $this->pagedata['app'] = get_object_vars($app);
        $certi = $this->system->loadModel("service/certificate");
        $certi_id =  $certi->getCerti();
        $this->pagedata['purl'] = "http://feedback.ecos.shopex.cn/uninstall.php?user_certi_id=".$certi_id.'&app_key='.$ident.'&version='.$app->ver;
        $this->pagedata['app_tables'] = $app->dbtables();
        $this->page('system/appmgr/uninstall.html');
    }

    function do_uninstall($ident){
        $appmgr = $this->system->loadModel('system/appmgr');
        $this->begin('index.php?ctl=system/appmgr&act=index');
        if($appmgr->uninstall($ident)){
            echo "<script>new Request().post('index.php?ctl=system/appmgr&act=appkey_count',{app_key:'$ident',type:'".$_GET['operation_type']."',version:'".$_GET['version']."'});</script>";
            $this->clear_all_cache();
            $this->setLoadTpl($ident,'cel');//检查安装
            $this->setApi($appmgr, $ident,'cel');
            $this->end(true,'软件卸载成功','index.php?ctl=system/appmgr&act=index','');
        }else{
            $this->end(false,'软件卸载失败');
        }
    }

    function enable($ident){
        $appmgr = $this->system->loadModel('system/appmgr');
        $this->begin('index.php?ctl=system/appmgr&act=index');
        $app_model = $appmgr->load($ident);
        if(method_exists($app_model,'enable')){
            $app_model->enable();
        }
        echo "<script>new Request().post('index.php?ctl=system/appmgr&act=appkey_count',{app_key:'$ident',type:'".$_GET['operation_type']."',version:'".$_GET['version']."'});</script>";
        $this->setLoadTpl($ident,'set');//检查安装
        $this->setApi($appmgr, $ident);
        $this->clear_all_cache();
        $this->end($appmgr->enable($ident));
    }

    function disable($ident){
        $appmgr = $this->system->loadModel('system/appmgr');
        $this->begin('index.php?ctl=system/appmgr&act=index');
        $app_model = $appmgr->load($ident);
        if(method_exists($app_model,'disable')){
            $app_model->disable();
        }
        echo "<script>new Request().post('index.php?ctl=system/appmgr&act=appkey_count',{app_key:'$ident',type:'".$_GET['operation_type']."',version:'".$_GET['version']."'});</script>";
        $this->setLoadTpl($ident,'cel');//检查安装
        $this->setApi($appmgr, $ident,'cel');
        $this->clear_all_cache();
        $this->end($appmgr->disable($ident));
    }
    function setting($ident){
        $appmgr = $this->system->loadModel('system/appmgr');
        $app_model = &$appmgr->load($ident);
        $app = $appmgr->info($ident);
        if(method_exists($app_model,'setting_load')){
            $app_model->setting_load();
        }
        foreach($app['setting'] as $k=>$v){
            $tmp['app.'.$ident.'.'.$k] = $v;
        }
        $app['setting'] = $tmp;
        $this->pagedata['app'] = $app;
        $this->pagedata['base_url'] = $this->system->base_url();
        echo "<script>new Request().post('index.php?ctl=system/appmgr&act=appkey_count',{app_key:'$ident',type:'".$_GET['operation_type']."',version:'".$_GET['version']."'});</script>";
        $this->display('system/appmgr/setting.html');
    }
    function setting_save($ident){
        $appmgr = $this->system->loadModel('system/appmgr');
        $app = &$appmgr->load($ident);
        if(method_exists($app,'setting_save')){
            $app->setting_save();
        }else{
            $setting = $_POST['setting'];
                foreach($setting as $key=>$val){
                    $this->system->setConf($key,$val);
                }
            }
        echo "保存成功";
    }

    function dofeedback($app_id){
        $certi = $this->system->loadModel("service/certificate");
        $certi_id =  $certi->getCerti();
        echo '<iframe src="http://feedback.ecos.shopex.cn/index.php?certi_id='.$certi_id.'&app_key='.$app_id.'" width=500 height=350/>';
    }

    function viewUpdateInfo(){
        $return = $this->sendRequest('app.get_appinfo',array('app_key'=>$_GET['app_ident'],'version'=>$_GET['app_version']));
        $updateInfo = "";
        if($return['result'] == 'succ'){
            $this->pagedata['updateInfos'] = json_decode($return['result_msg'],1);
        }
        $this->pagedata['download_url'] = $_GET['download_url'];
        $this->pagedata['app_status'] = $_GET['app_status'];
        $this->pagedata['plugin_ident'] = $_GET['app_ident'];
        $this->pagedata['app_version'] = $_GET['app_new_version'];
        $this->display('system/appmgr/view_update_info.html');
    }

    function appkey_count(){
        $cet_ping = ping_url("http://esb.shopex.cn/api.php");
        if(!strstr($cet_ping,'HTTP/1.1 200 OK')){
            return;
        }
        $certi = $this->system->loadModel("service/certificate");
        $certi_id =  $certi->getCerti();
        $this->sendRequest("app.count_appkey",array('certi_id'=>$certi_id,'app_key'=>$_POST['app_key'],'type'=>$_POST['type'],'version'=>$_POST['version']));
    }

    function appupdate_count(){
        $cet_ping = ping_url("http://esb.shopex.cn/api.php");
        if(!strstr($cet_ping,'HTTP/1.1 200 OK')){
            return;
        }
        $certi = $this->system->loadModel("service/certificate");
        $certi_id =  $certi->getCerti();
        $this->sendRequest("app.count_appupdate",array('certi_id'=>$certi_id,'app_key'=>$_POST['app_key'],'version'=>$_POST['version']));
    }

    function getAppUpdateTime($aData){
        $cet_ping = ping_url("http://esb.shopex.cn/api.php");
        if(!strstr($cet_ping,'HTTP/1.1 200 OK')){
            return false;
        }
        $return = $this->sendRequest("app.get_appkey_time",array('data'=>json_encode($aData)));
        if($return['result'] == "succ")
            return json_decode($return['result_msg'],1);
        else
            return false;
    }

    function sendRequest($service,$aData){
        $oApiClient = $this->system->loadModel("service/apiclient");
        $oApiClient->url="http://esb.shopex.cn/api.php";
        return $oApiClient->native_svc($service,$aData);
    }
    //检查是否是模板app
    function setLoadTpl($ident,$type='set'){
        $this->setConfApp($ident, $type, 'set.app.list');
    }
    //设置加载app
    function setConfApp($app_name,$type,$confKey){
        $loadTplInfo = $this->system->getConf($confKey);
        if($loadTplInfo){
            $loadTplInfo = unserialize($loadTplInfo);
        }
        if($type=='set'){
            $loadTplInfo[$app_name]= $app_name;
        }elseif($type=='cel'){
            unset($loadTplInfo[$app_name]);
        }
        if(is_array($loadTplInfo)){
            $this->system->setConf($confKey,serialize($loadTplInfo));
        }
    }
    //设置扩展api
    function setApi($obj_appmgr, $ident, $type="set"){
        $app = $obj_appmgr->load($ident);
        if(method_exists($app, "get_api_methods")){
            $this->setConfApp($ident, $type, 'extends_api.app.list');
        }
    }

}