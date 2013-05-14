<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_magicvars extends objectPage {

    var $workground ='setting';
    var $object = 'system/magicvars';
    var $finder_action_tpl = 'system/magicvars/finder_action.html';
    var $finder_default_cols = '_cmd,var_name,var_title,var_value';
    var $filterUnable = true;

    function ctl_magicvars(){
        parent::objectPage();
        $this->model->filter = 'and var_type= "custom"';
    }

    function var_item($var_id){
        if($var_id){
            $magicvars = &$this->system->loadModel('system/magicvars');

            $this->pagedata['var'] = $magicvars->instance($var_id);
            $this->pagedata['readOnly'] = 'readOnly';
        }

        $this->page('system/magicvars/var_item.html');
    }

    function save(){
        $this->begin("index.php?ctl=system/magicvars");
        $magicvars = &$this->system->loadModel('system/magicvars');
        $var_name = $_POST['var_name'];
        if($var_name1 = substr(substr($var_name,1),0,-1)){
           $message = str_replace('_','',$var_name1);
             if(strlen($message)<2){
               trigger_error('变量名必须为2个字母以上',E_USER_ERROR);
               return false;
             }
        }
        if(!preg_match('/^\w+$/', substr(substr($_POST['var_name'],1),0,-1))){
            trigger_error('变量名非法',E_USER_ERROR);
            return false;
        }
        $_POST['var_type'] = 'custom';
        if(isset($_POST['is_editing'])){
            $this->end($magicvars->update($_POST,array('var_name'=>$_POST['var_name']),$message),$message?$message:__('修改成功'),'index.php?ctl=system/magicvars&act=index');
        }else{
            $this->end($magicvars->insert($_POST,$message),$message?$message:__('保存成功'),'index.php?ctl=system/magicvars&act=index');
        }
    }

}
?>
