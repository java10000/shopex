<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_operator extends objectPage{

    var $workground ='setting';
    var $finder_action_tpl = 'admin/finder_action.html';
    var $finder_filter_tpl = 'admin/finder_filter.html';
    var $finder_default_cols = '_cmd,username,name,lastlogin,department';
    var $object = 'admin/operator';
    var $finderVar = 'OperatorMgr';
    var $noRecycle = true;
    var $filterUnable = true;

    function ctl_operator(){
        parent::objectPage();
        if(!$this->system->op_is_super){
              $this->system->responseCode(403);
              exit;
        }
    }

    /**
     * recycle
     * 重写父类objectPage中的recycle
     * 增加当前管理员不能删除自身功能
     * @access public
     *
     */
    function recycle()
    {
        foreach ($_POST['op_id'] as $a)
        {
            if ($a == $this->system->op_id)
            {
                $tmp = 'self';
                break;
            }
        }
        if ($tmp != '')
        {
            echo __('当前管理员不能删除自身！请重新选择！');
            exit;
        }
        else
        {
            parent::recycle();
        }
    }


    /**
     * edit
     *
     * @access public
     * @return array
     */
    function edit($nOpId){
        if($nOpId){
            $operator = $this->model->instance($nOpId);
            $this->pagedata['roles'] = $this->model->getUsedRoles($nOpId);
            $this->path[] = array('text'=>__('编辑 ').$operator['username']);
        }else{
            $this->path[] = array('text'=>__('添加管理员'));
            $operator['super'] = 0;
            $operator['status'] = 1;
        }

        $admin_role = &$this->system->loadModel('admin/adminroles');
        $this->pagedata['adminroles'] = $admin_role->getList('role_id,role_name');
        $operator['select_super'] = array('0'=>__('普通管理员'), '1'=>__('超级管理员'));
        $operator['select_status'] = array('1'=>__('启用'),'0'=>__('禁用'),);

        $this->pagedata['operator'] = $operator;
        $this->page('admin/op_edit.html');
    }


    function save(){
        $this->begin('index.php?ctl='.$_GET['ctl'].'&act=index');
        $_POST['roles'] = $_POST['roles']?$_POST['roles']:array();
        if($_POST[$this->model->idColumn]){
            if($_POST['changepwd']){
                if($_POST['userpass_comfirm'] != $_POST['userpass']){
                    trigger_error(__('两次密码输入不一致'),E_USER_ERROR);
                }
            }else{
                unset($_POST['userpass']);
            }
            
            $this->end($this->model->update($_POST,array($this->model->idColumn=>$_POST[$this->model->idColumn])));
        }else{
            if($_POST['userpass_comfirm'] != $_POST['userpass']){
                trigger_error(__('两次密码输入不一致'),E_USER_ERROR);
            }
            if($this->model->count(array('username'=>$_POST['username']))!=0){
                $this->end(false,__('用户名已存在'));
            }
            unset($_POST[$this->model->idColumn]);
            $this->end($this->model->insert($_POST));
        }
    }

    function delete(){
        if($_POST['op_id']){
            foreach($_POST['op_id'] as $k=>$v){
                if($v==$this->system->op_id){
                    echo __('管理员不能删除自己的账号，');
                    unset($_POST['op_id'][$k]);
                    if(count($_POST['op_id'])==0){
                        echo __('操作失败');
                        return;
                    }
                }
            }
        }else{
             echo __('管理员无法删除自身，');
        }
        if($this->model->delete($_POST,$this->system->op_id)){
            echo __('选定记录已删除成功!');
        }else{
            echo __('选定记录无法删除!');
        }
    }

}
?>
