<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php

include_once('objectPage.php');
class ctl_memberattr extends ObjectPage {
    var $workground = 'member';
    var $object = 'member/memberattr';


    function showNewMemAttr(){
        $this->path[] = array('text'=>__('添加注册项'));
        $this->page('member/attr/attr_new.html');
    }


    function index(){
        $mematt = &$this->system->loadModel('member/memberattr');
        $tmpdate =$mematt->getList('*',null,0,-1,array('attr_order','asc'));
        $t_num = count($tmpdate);
        for($i=0;$i<$t_num;$i++){
            if($tmpdate[$i]['attr_type'] == 'select'||$tmpdate[$i]['attr_type'] == 'checkbox'){
                $tmpdate[$i]['attr_option'] = unserialize($tmpdate[$i]['attr_option']);
            }
        }
        $this->pagedata['tree'] = $tmpdate;
        $this->page('member/attr/map.html');
    }



    function enableAttrshow($attr_id){
        $this->begin('index.php?ctl=member/memberattr&act=index');
        $memberattr = &$this->system->loadModel('member/memberattr');
        $rst = $memberattr->setVisibility($attr_id,true);
        $this->end($rst,__('已设置显示状态'));
    }

    function disableAttrshow($attr_id){
        $this->begin('index.php?ctl=member/memberattr&act=index');
        $memberattr = &$this->system->loadModel('member/memberattr');
        $rst = $memberattr->setVisibility($attr_id,false);
        $this->end($rst,__('已设置关闭状态'));
    }


    function addMemAttr(){
        $this->begin('index.php?ctl=member/memberattr&act=index');
        switch($_POST['attr_tyname']){
            case __('输入内容不限制'):
            $MemAttr['attr_valtype'] = '';
            $MemAttr['attr_type'] = 'text';
            $MemAttr['attr_group'] = 'input';
            break;
            case __('仅限输入数字'):
            $MemAttr['attr_valtype'] = 'number';
            $MemAttr['attr_type'] = 'text';
            $MemAttr['attr_group'] = 'input';
            break;
            case __('仅限输入字符'):
            $MemAttr['attr_valtype'] = 'alpha';
            $MemAttr['attr_type'] = 'text';
            $MemAttr['attr_group'] = 'input';
            break;
            case __('仅限输入数字和字符'):
            $MemAttr['attr_valtype'] = 'alphaint';
            $MemAttr['attr_type'] = 'text';
            $MemAttr['attr_group'] = 'input';
            break;
            case __('email格式'):
                $MemAttr['attr_valtype'] = 'email';
                $MemAttr['attr_type'] = 'text';
                $MemAttr['attr_group'] = 'input';
                break;
            case __('单选项'):
            $MemAttr['attr_valtype'] = '';
            $MemAttr['attr_type'] = 'select';
            $MemAttr['attr_group'] = 'select';
            break;
            case __('多选项'):
            $MemAttr['attr_valtype'] = '';
            $MemAttr['attr_type'] = 'checkbox';
            $MemAttr['attr_group'] = 'select';
            break;
            case __('日期(年月日)'):
            $MemAttr['attr_valtype'] = '';
            $MemAttr['attr_type'] = 'cal';
            $MemAttr['attr_group'] = 'date';
            break;
            case 'QQ':
            $MemAttr['attr_valtype'] = '';
            $MemAttr['attr_type'] = 'text';
            $MemAttr['attr_group'] = 'contact';
            break;
            case 'MSN':
            $MemAttr['attr_valtype'] = 'email';
            $MemAttr['attr_type'] = 'text';
            $MemAttr['attr_group'] = 'contact';
            break;
            case __('旺旺'):
            $MemAttr['attr_valtype'] = '';
            $MemAttr['attr_type'] = 'text';
            $MemAttr['attr_group'] = 'contact';
            break;
            case 'Skype':
            $MemAttr['attr_valtype'] = 'alphaint';
            $MemAttr['attr_type'] = 'text';
            $MemAttr['attr_group'] = 'contact';
            break;
        }
        
        $member = &$this->system->loadModel('member/member');
        $MemAttrObj = &$this->system->loadModel('member/memberattr');
        $MemAttr['attr_search'] = $_POST['attr_search']?'true':'false';
        $order = $MemAttrObj->getMaxOrder();
        $MemAttr['attr_option'] = $_POST['attr_option']?serialize($_POST['attr_option']):'';
        $MemAttr['attr_tyname'] = $_POST['attr_tyname'];
        $MemAttr['attr_required'] = $_POST['attr_required']?'true':'false';
        $MemAttr['attr_name'] = $_POST['attr_name'];
        $MemAttr['attr_show'] = 'true';
        if($_POST['attr_id'] == ""){
            $MemAttr['attr_order'] = $order[0]['attr_order']+1;
            $flag = $MemAttrObj->save($MemAttr);
            if($flag!=''){
                $this->clear_cache('finder/lister.html#member/member');
                $this->clear_cache('finder/common.html#member/member');
                $this->end(true,__('保存成功！'));
            }else{
                $this->end(false,__('保存失败！'));
            }
        }else{
            if($MemAttrObj->updatememattr($MemAttr,$_POST['attr_id'])){
                $this->clear_cache('finder/lister.html#member/member');
                $this->clear_cache('finder/common.html#member/member');
                $this->end(true,__('编辑成功！'));
            }else{
                $this->end(false,__('编辑失败！'));
            }
        }

    }

    function toRemove($attr_id){
        $this->begin('index.php?ctl=member/memberattr&act=index');
        $MemAttrObj = &$this->system->loadModel('member/memberattr');
        $this->clear_cache('finder/lister.html#member/member');
        $this->clear_cache('finder/common.html#member/member');
        $this->end($MemAttrObj->Remove($attr_id),__('选项删除成功'));
    }

    function edit($attr_id){
        $this->path[] = array('text'=>__('编辑注册项'));
        $Memattr = &$this->system->loadModel('member/memberattr');
        $Mema = $Memattr->getFieldById($attr_id);
        if($Mema['attr_option'] !=''){
            $Mema['attr_option'] = unserialize($Mema['attr_option']);
            $Mema['attr_optionNo1'] = $Mema['attr_option'][0];
            unset($Mema['attr_option'][0]);
        }
        $this->pagedata['memattr'] = $Mema;
        $this->page('member/attr/attr_edit.html');
    }

    function changeorder(){
        $this->begin('index.php?ctl=member/memberattr&act=index');
        $memberattr = &$this->system->loadModel('member/memberattr');
        $order = $_POST['attr_order'];
        $o_num = count($order);
        for($i=0;$i<$o_num;$i++){
            $memberattr->updateorder($i,$order[$i]);
        }
        $this->end(true,__('选项排序更改成功'));
    }
}


?>