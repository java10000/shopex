<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_custommessage extends objectPage{
    var $deleteAble = false;
    var $workground ='site';
    var $object = 'content/custommessage';
    var $finder_default_cols = '_cmd,var_title,var_remark';
    var $filterUnable = true;
    var $allowExport = false;




    function var_item($var_id){
        if($var_id){
            $magicvars = &$this->system->loadModel('system/magicvars');
            $this->pagedata['var'] = $magicvars->instance($var_id);
        }
        $this->page('content/custommessage/edit.html');
    }

    function save(){
        $this->begin("index.php?ctl=system/custommessage");
        $magicvars = &$this->system->loadModel('system/custommessage');
        $_POST['var_type'] = 'system';

        if(isset($_POST['is_editing'])){
            $this->end($magicvars->update($_POST,array('var_name'=>$_POST['var_name'])),__('修改成功'),'index.php?ctl=content/custommessage&act=index');
        }else{
            $this->end($magicvars->insert($_POST),__('保存成功'),'index.php?ctl=content/custommessage&act=index');
        }
    }
}