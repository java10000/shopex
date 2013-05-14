<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_printer_center extends objectPage{

    var $workground = 'order';
    var $object='trading/dly_centers';
    var $finder_action_tpl = 'order/dly_center_action.html';

    function printer_select(){
        $path="/pxml";
        $handle=opendir('../../pxml');
        $ct=array();
        while(false !==($file=readdir($handle))){
            if(preg_match('/.*\\.xml+/',$file)){
             //echo $file;
             $ct[]=$file;
            }

        }
        $this->pagedata['pdate'] = $ct;

        $this->display('order/printertest.html');
    }
    function add_center(){
        $this->page('order/dly_center_editor.html');
    }

    function save_data(){
        $this->begin('index.php?ctl=order/delivery_centers&act=index');
        $this->end( $this->model->insert($_POST),__('发货信息添加成功'));
    }
}
?>
