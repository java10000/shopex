<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_roles extends objectPage{

    var $workground = 'setting';
    var $finder_action_tpl = 'admin/roles_action.html';
    var $finder_default_cols = '_cmd,role_name,role_memo';
    var $object = 'admin/adminroles';
    var $filterUnable = true;

    function add(){
        $this->pagedata['actions'] = $this->model->getAllActions();
        $this->page('admin/roles_item.html');
    }

    function edit($role_id){
        $this->pagedata['actions'] = $this->model->getAllActions();
        $this->pagedata['role'] = $this->model->instance($role_id);
        $this->pagedata['role']['actions'] = array_flip($this->pagedata['role']['actions']);
        $this->page('admin/roles_item.html');
    }

}
?>
