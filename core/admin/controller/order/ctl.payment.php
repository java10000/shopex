<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_payment
 * 收款单
 *
 * @uses adminPage
 * @package
 * @version $Id: ctl.payment.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
include_once('objectPage.php');
class ctl_payment extends objectPage{

    var $workground = 'order';
    var $object = 'trading/payment';
    var $finder_filter_tpl='order/payment/finder_filter.html';
    var $deleteAble = true;  //屏蔽删除

    function _detail(){
        return array('show_detail'=>array('label'=>__('收款单信息'),'tpl'=>'order/payment/detail.html'));
    }

    function show_detail($nID){
        $oPayment=&$this->system->loadModel('trading/payment');
        $aDetail=$oPayment->getById($nID);

        $o = &$this->system->loadModel('admin/operator');
        $aOp = $o->instance($aDetail['op_id'],'username');
        $aDetail['op_id'] = $aOp['username'];

        $o = &$this->system->loadModel('member/member');
        $aMember = $o->getFieldById($aDetail['member_id']);
        $aDetail['member_id'] = $aMember['uname'];

        $this->pagedata['detail']=&$aDetail;
    }

    function edit(){
        $oPayment=&$this->system->loadModel('trading/payment');
        if($oPayment->edit($_POST)){
            $this->splash('success','index.php?ctl=order/payment&act=index',__('修改成功'));
        }else{
            $this->splash('failed','index.php?ctl=order/payment&act=index',__('修改失败'));
        }

    }

}
?>