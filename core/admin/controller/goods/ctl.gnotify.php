<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_gnotify extends objectPage{

    var $workground = 'goods';
    var $finder_action_tpl = 'product/gnotify/finder_action.html';
    var $object = 'goods/goodsNotify';
    var $filterUnable = true;

    function index($operate){
        if($operate=='admin'){
            $this->system->set_op_conf('notifytime',time());
        }
        parent::index();
    }

    function toNotify(){
        if( !$_POST['gnotify_id'] ) {
            echo __("请先从列表中选择需要发送的记录！"); exit;
        }

        $notifyMdl = &$this->system->loadModel('goods/goodsNotify');
        $notifyMdl->toNofity($_POST['gnotify_id']);
        
        echo __("通知邮件已入队列等等发送");
    }
}
