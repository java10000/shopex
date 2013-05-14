<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_payment
 *
 * @uses pageFactory
 * @package
 * @version $Id: ctl.passport.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author bryant <bryant@shopex.cn>
 * @license Commercial
 */
class ctl_passport extends adminPage {
    var $workground ='setting';
    
    function getPassportList(){
         $oPassport = &$this->system->loadModel('member/passport'); 
         $this->pagedata['items'] = $oPassport->getList();
        $this->path[] = array('text'=>__('登录整合'));
        $this->page('passport/passport_list.html');
    }
    
    function savePassport(){
        $this->begin('index.php?ctl=system/passport&act=getPassportList');
        $oPassport = &$this->system->loadModel('member/passport');
        $this->end($oPassport->savePassport($_POST), __('保存成功！'));
    }

    /**
    * detailPassport
    *
    * @access public
    * @return void
    */
    function detailPassport($type){
        $oPassport = &$this->system->loadModel('member/passport');
        $this->pagedata['options'] = $oPassport->getOptions($type);
        $this->pagedata['passport_type'] = $type;
        $this->pagedata['params'] = $oPassport->getParams($type,false);
        $this->pagedata['passport_ifvalid'] = ($oPassport->getCurrentPlugin()==$type)?'true':'false';
        var_dump($this->pagedata['params']['tmpl']);
        $this->page('passport/passport_edit.html');
    }
}

