<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_frendlink
 *
 * @uses adminPage
 * @package
 * @version $Id: ctl.frendlink.php 1912 2008-04-24 08:21:17Z alex $
 * @copyright 2003-2007 ShopEx
 * @author hujianxin <hjx@shopex.cn>
 * @license Commercial
 */
include_once('objectPage.php');
class ctl_frendlink extends objectPage{

    var $workground = 'site';
    var $finder_action_tpl = 'content/frendlink/finder_action.html';
    var $object = 'content/frendlink';
    var $filterUnable = true;

    function _detail(){
        return array('show_detail'=>array('label'=>__('友情链接'),'tpl'=>'content/frendlink/detail.html'));
    }

    function show_detail($link_id){
        $this->path[] = array('text'=>__('友情链接'));

        $link = &$this->system->loadModel("content/frendlink");
        $linkinfo = $link->getFieldById($link_id,array('*'));
        $this->pagedata['linkInfo'] = $linkinfo;
    }

    function addNew(){
        $this->path[] = array('text'=>__('友情链接编辑'));
        $this->page('content/frendlink/detail.html');
    }

    function save(){
        if($_POST['link_id'] || $_FILES){
            $this->begin("index.php?ctl=content/frendlink&act=detail&p[0]=".$_POST['link_id']);
        }else{
            $this->begin("index.php?ctl=content/frendlink&act=index");
        }
        $link = &$this->system->loadModel("content/frendlink");
        $this->end($link->save($_POST,$msg),$msg);
    }
}
