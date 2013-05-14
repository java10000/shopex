<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_shopbbs extends objectPage {

    var $workground = 'member';
    var $object = 'resources/shopbbs';

    function _detail(){
        return array('show_detail'=>array('label'=>__('留言信息'),'tpl'=>'member/shopbbs/msg_items.html'));
    }

    function show_detail($msg_id){
        $objMsgbox = &$this->system->loadModel('resources/shopbbs');
        $aMsg = $objMsgbox->getFieldById($msg_id);
        $this->pagedata['message'] = $aMsg;
        $this->pagedata['revert'] = $objMsgbox->getMsgReply($msg_id);
        $objMsgbox->setReaded($msg_id);

        $member = &$this->system->loadModel('member/member');
        $this->pagedata['member'] = $member->getFieldById($aMsg['from_id']);
        $lv=&$this->system->loadModel('member/level');
        if ($this->pagedata['member']['member_lv_id']){
            $row = $lv->getFieldById($this->pagedata['member']['member_lv_id']);
            $this->pagedata['member']['levelname'] = $row['name'];
        }
        $tree = $member->getContactObject($aMsg['from_id']);
        $this->pagedata['tree'] = $tree;
    }

    function revert(){
        $this->begin('index.php?ctl=member/shopbbs&act=detail&p[0]='.$_POST['for_id']);
        $oMsg = &$this->system->loadModel('resources/shopbbs');
        $_POST['from_id'] = $this->system->op_id;
        $this->end($oMsg->revert($_POST),__('回复成功'));
    }

    function delete(){
        if($_REQUEST['f_id']){
            $this->begin('index.php?ctl=member/shopbbs&act=detail&p[0]='.$_REQUEST['f_id']);
        }else{
            $this->begin('index.php?ctl=member/shopbbs&act=index');
        }
        $oMsg = &$this->system->loadModel('resources/shopbbs');
        if(is_array($_REQUEST['msg_id']))
            foreach($_REQUEST['msg_id'] as $id){
                $oMsg->toRemove($id);
            }
        $this->end(true,__('操作成功'));
    }
    function setting(){
        $this->path[] = array('text'=>__('留言设置'));
        $comment = &$this->system->loadModel('comment/comment');
        $aOut = $comment->getSetting('msg');
        if(!$aOut['verifyCode']['msg']){
            $aOut['verifyCode']['msg']='off';
        }
        $aOut['verifyLCode']['msg'] = array('on'=>__('开启'), 'off'=>__('关闭'));
        $this->pagedata['setting']=$aOut;
        $this->page('member/shopbbs/setting.html');
    }
    function toSetting(){
        $comment = &$this->system->loadModel('comment/comment');
        $comment->setSetting('msg', $_POST);
        $this->settingEdit();
        $this->splash('success','index.php?ctl=member/shopbbs&act=setting',__('保存成功!'));
    }

    function settingEdit(){

        foreach($_POST['setting'] as $k=>$v){
            if(!$this->system->setConf($k,$v)){
                trigger_error($k.__('设置错误'),E_USER_ERROR);
                return false;
            }
        }
        return true;
    }
}
