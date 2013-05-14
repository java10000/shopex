<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_specification
 *
 * @uses adminPage
 * @package
 * @version $Id: ctl.specification.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Liujy <ever@zovatech.com>
 * @license Commercial
 */
include_once('objectPage.php');
class ctl_specification extends objectPage{

    var $workground = 'goods';
    var $object = 'goods/specification';
    var $finder_default_cols = '_cmd,bn,spec_name,spec_type,spec_show_type,spec_value';
    var $finder_action_tpl = 'product/spec/finder_action.html';
    var $noRecycle = true;
    var $filterUnable = true;

    function edit($specid=0){
        $storager = &$this->system->loadModel("system/storager");
        $this->path[] = array('text'=>__('规格编辑'));
        if($specid){
            $objSpec = &$this->system->loadModel('goods/specification');
            $aSpec = $objSpec->getFieldById($specid,array('*'));
            $aVal = $objSpec->getValueList($specid);
            $this->pagedata['spec'] = $aSpec;
            $this->pagedata['width'] = $this->system->getConf('spec.image.width');
            $this->pagedata['height'] = $this->system->getConf('spec.image.height');
            $this->pagedata['img_path'] = $storager->getUrl($this->system->getConf('spec.default.pic'));
            $this->pagedata['spec']['vals'] = $aVal;
        }else{
            $this->addspec();
        }
        $this->page('product/spec/detail.html');
    }

    function addspec($specid=0){
        $storager = &$this->system->loadModel("system/storager");
        $this->path[] = array('text'=>__('规格新增'));
        $this->pagedata['width'] = $this->system->getConf('spec.image.width');
        $this->pagedata['height'] = $this->system->getConf('spec.image.height');
        $this->pagedata['img_path'] = $storager->getUrl($this->system->getConf('spec.default.pic'));
        $this->page('product/spec/detail.html');
    }

    function save(){
        $this->begin('index.php?ctl=goods/specification&act=index');
        $objSpec = &$this->system->loadModel('goods/specification');
        $this->end($objSpec->toSave($_POST), __('保存成功!'));
    }

    function selSpec(){
        $objSpec = &$this->system->loadModel('goods/specification');
        $this->pagedata['spec'] = $objSpec->getFieldById( $_POST['spec_id'], array('*') );
        $this->pagedata['spec_value'] = $objSpec->getValueList( $_POST['spec_id'] );
        $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');
        $this->display('product/sel_spec_value.html');
    }

    function check_spec_value_id(){
        $objSpec = &$this->system->loadModel('goods/specification');
        echo $objSpec->check_spec_value_id($_POST['spec_value_id']);
    }
}
