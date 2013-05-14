<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_shipping extends objectPage{

    var $workground = 'order';
    var $object='trading/shipping';
    var $deleteAble = true;  //屏蔽删除

    function _detail(){
        return array('show_detail'=>array('label'=>__('发货单信息'),'tpl'=>'order/shipping/detail.html'));
    }

    function show_detail($nID){
        $oDelivery=&$this->system->loadModel('trading/shipping');
        $aDetail=$oDelivery->detail($nID);

        $o = &$this->system->loadModel('member/member');
        $aMember = $o->getFieldById($aDetail['member_id']);
        $aDetail['member_id'] = $aMember['uname'];

        $this->pagedata['detail']=$aDetail;
        $this->pagedata['items'] = $oDelivery->getItemList($nID);
    }

    function edit(){
        $oRefund=&$this->system->loadModel('trading/shipping');
        if($oRefund->edit($_GET)){
            $this->splash('success','index.php?ctl=order/shipping&act=index',__('修改成功'));
        }else{
            $this->splash('failed','index.php?ctl=order/shipping&act=index',__('修改失败'));
        }
    }

    function delete(){
        $oShipping = &$this->system->loadModel('trading/shipping');
        foreach($_POST['delivery_id'] as $v){
            $oShipping->toRemove($v);
        }
        echo __('删除成功！');
    }
}
?>
