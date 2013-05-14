<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_level extends objectPage {

    var $workground = 'member';
    var $finder_action_tpl = 'member/level/finder_action.html'; //默认的动作html模板,可以为null
    var $object = 'member/level';
    var $filterUnable = true;

    function _detail(){
        return array('show_detail'=>array('label'=>__('会员等级详细'),'tpl'=>'member/level/level_edit.html'));
    }

    function showNewMemLv(){
        $this->path[] = array('text'=>__('添加会员等级'));
        $this->pagedata['levelSwitch'] = $this->system->getConf('site.level_switch');
        $this->page('member/level/level_new.html');
    }

    function addMemLevel(){
        if(empty($_POST['lv_name'])){
            $this->splash('failed','index.php?ctl=member/level&act=index',__('请输入等级名称'));
            exit;
        }
        if(empty($_POST['dis_count'])){
            $this->splash('failed','index.php?ctl=member/level&act=index',__('请输入会员折扣率'));
            exit;
        }

        $switch=$this->system->getConf('site.level_switch');
        if($switch)
            $_POST['experience'] = intval($_POST['experience']);
        else
            $_POST['point'] = intval($_POST['point']);
        $oMem = &$this->system->loadModel("member/level");
        $filter['lv_type'] = $_POST['lv_type'];
        $tmpdate = $oMem->getList('*',$filter,0,-1);
        foreach($tmpdate as $k => $v){
            if($_POST['lv_type']!='wholesale'){
                if ($switch){
                    if ($_POST['experience']==$v['experience'])
                        $this->splash('failed','index.php?ctl=member/level&act=index',__('已存在相同经验值的会员等级'));
                }
                else{
                    if ($_POST['point']==$v['point'])
                        $this->splash('failed','index.php?ctl=member/level&act=index',__('已存在相同积分的会员等级'));
                    }
            }
        }
        $_POST['dis_count'] = $_POST['dis_count'] / 100;
        if($oMem->checkLevel($_POST,'INSERT')){
            $this->splash('failed','index.php?ctl=member/level&act=index',__('有同名会员等级存在'));
            exit;
        }
        if($oMem->checkMlevel($_POST,'INSERT')){
            $this->splash('failed','index.php?ctl=member/level&act=index',__('默认级别已经存在'));
            exit;
        }
        $r =  $oMem->insertLevel($_POST);
        $this->splash('success','index.php?ctl=member/level&act=index',__('添加会员等级成功'));
    }

    function show_detail($nLvId){
        $this->path[] = array('text'=>__('会员等级编辑'));
        $oLv = &$this->system->loadModel("member/level");
        $aLv = $oLv->getFieldById($nLvId);
        $aLv['dis_count'] *= 100;
        $switch=$this->system->getConf('site.level_switch');
        $aLv['value'] = $switch?$aLv['experience']:$aLv['point'];
        $this->pagedata['lv'] = $aLv;
        $this->pagedata['levelSwitch'] = $switch;
        $this->pagedata['lv_type']=array('retail'=>__('普通零售会员等级'),'wholesale'=>__('批发代理会员等级'));
    }

    function saveLevelInfo(){
        $oMem = &$this->system->loadModel("member/level");
        $_POST['dis_count'] = $_POST['dis_count'] / 100;
        if($oMem->checkLevel($_POST,'UPDATE')){
            $this->splash('failed','index.php?ctl=member/level&act=detail&p[0]='.$_POST['member_lv_id'],__('有同名会员等级存在'));
            exit;
        }
        if($oMem->checkMlevel($_POST,'UPDATE')){
            $this->splash('failed','index.php?ctl=member/level&act=detail&p[0]='.$_POST['member_lv_id'],__('默认级别已经存在'));
            exit;
        }
        $filter['lv_type'] = $_POST['lv_type'];
        $tmpdate = $oMem->getList('*',$filter,0,-1);
        $switch=$this->system->getConf('site.level_switch');
        if($switch)
            $_POST['experience'] = intval($_POST['experience']);
        else
            $_POST['point'] = intval($_POST['point']);
        foreach($tmpdate as $k => $v){
            if($_POST['member_lv_id']!=$v['member_lv_id']){
                if($_POST['lv_type']!='wholesale'){
                    if ($switch){
                        if($_POST['experience']==$v['experience']){
                            $this->splash('failed','index.php?ctl=member/level&act=index',__('已存在相同经验值的会员等级'));
                        }
                    }else{
                        if($_POST['point']==$v['point']){
                            $this->splash('failed','index.php?ctl=member/level&act=index',__('已存在相同积分的会员等级'));
                        }
                    }
                }
            }
        }
        $r=$oMem->saveLevel($_POST);
        if($r){
            $this->splash('success','index.php?ctl=member/level&act=detail&p[0]='.$_POST['member_lv_id'],__('修改成功'));
        }else{
            $this->splash('failed','index.php?ctl=member/level&act=detail&p[0]='.$_POST['member_lv_id'],__('修改失败'));
        }
    }
    function delete(){
        $oLv = &$this->system->loadModel("member/level");
        $aLvId = $_POST['member_lv_id'];
        if($oLv->delLevel($aLvId)){
            $this->message = array('string'=>__('删除成功！'),'type'=>MSG_OK);
            $this->splash('success','index.php?ctl=member/level&act=index');
        }else{
            $this->message = array('string'=>__('删除失败！'),'type'=>MSG_ERROR);
            $this->splash('success','index.php?ctl=member/level&act=index');
        }
    }
}
?>