<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_debug extends adminPage{
    var $workground ='tools';

    function index(){
        $this->path[] = array('text'=>__('网店暂停营业'));
        $oDebug=&$this->system->loadModel('system/debug');
        $this->pagedata['shopurl']=$this->system->base_url();
        $this->pagedata['systemShopMode']=!is_file(HOME_DIR.'/notice.html');
        if(!$this->pagedata['systemShopMode']){
            $this->pagedata['announcement'] = file_get_contents(HOME_DIR.'/notice.html');
        }
        $this->pagedata['shop_mode']=$systemShopMode;
        $this->page('system/debug/debug.html');
    }

    function editShopMode(){
        $this->begin('index.php?ctl=system/debug&act=index');
        $oDebug=&$this->system->loadModel('system/debug');

        if($_POST['shop_mode']){
            $this->end($oDebug->stopShopMode($_POST['announcement']));
        }else{
            $this->end($oDebug->startShopMode());
        }
    }

    function fix_database(){
        $this->begin('index.php?ctl=system/debug&act=check_database');
        $schema=&$this->system->loadModel('utility/schemas');
        $db = &$this->system->database();
        $dbtables = $schema->get_system_schemas();
        foreach($dbtables as $tbname=>$struct){
            if($diffsql = $schema->diff($tbname,$struct)){
                foreach($diffsql as $sql){
                    $db->exec($sql);
                }
            }
        }
        $this->end(true,__('数据修复成功'));
    }
    function check_database(){
        $schema=&$this->system->loadModel('utility/schemas');
        $ret = array();
        $db = &$this->system->database();
        $dbtables = $schema->get_system_schemas();
        foreach($dbtables as $tbname=>$struct){
            if($diff = $schema->diff($tbname,$struct)){
                $ret[$db->prefix.$tbname] = $diff;
            }
        }
        $this->pagedata['diff'] = &$ret;
        $this->page('system/debug/databasecheck.html');
    }

    function clear(){
        if(constant('SAAS_MODE')){
            exit;
        }
        $this->page('system/debug/clear.html');
    }

    function cleardata(){
        if(constant('SAAS_MODE')){
            exit;
        }
        $this->begin('index.php?ctl=system/debug&act=clear');
        $operator = &$this->system->loadModel('admin/operator');
        if($operator->tryLogin($_POST)){
            $clr = &$this->system->loadModel('system/debug');
            $clr->clearData();
            $this->end(true,__('数据成功清除'));
        }else{
            $this->end(false,__('录入的用户名或密码不正确'));
        }

    }
}
?>
