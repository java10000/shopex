<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_adjunct extends adminPage{

    function addGrp(){
        $this->pagedata['aOptions'] = array('goods'=>__('选择几件商品作为配件'), 'filter'=>__('选择一组商品搜索结果作为配件'));        
        $this->display('product/adjunct/info.html');
        return;
    }

    function doAddGrp(){
        $this->pagedata['adjunct'] =array('name'=>$_POST['name'],'type'=>$_POST['type']);
        $this->pagedata['key'] = time();
        $this->display('product/adjunct/row.html');
    }
}
?>
