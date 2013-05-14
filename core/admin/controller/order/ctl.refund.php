<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_refund extends objectPage{

    var $workground = 'order';
    var $object = 'trading/refund';
    var $finder_filter_tpl='order/refund/finder_filter.html';
    var $deleteAble = true;  //屏蔽删除

    function _detail(){
        return array('show_detail'=>array('label'=>__('退款单信息'),'tpl'=>'order/refund/detail.html'));
    }

    function show_detail($nID){
        $oRefund=&$this->system->loadModel('trading/refund');
        $aDetail=$oRefund->detail($nID);
        $oPayment=&$this->system->loadModel('trading/payment');

        $o = &$this->system->loadModel('admin/operator');
        $aOp = $o->instance($aDetail['send_op_id'],'username');
        $aDetail['send_op_id'] = $aOp['username'];

        $o = &$this->system->loadModel('member/member');
        $aMember = $o->getFieldById($aDetail['member_id']);
        $aDetail['member_id'] = $aMember['uname'];
        $this->pagedata['detail']=$aDetail;
    }

    function edit(){
        $oRefund=&$this->system->loadModel('trading/refund');
        if($oRefund->edit($_POST)){
            $this->splash('success','index.php?ctl=order/refund&act=index',__('修改成功'));
        }else{
            $this->splash('failed','index.php?ctl=order/refund&act=index',__('修改失败'));
        }

    }

}
?>
