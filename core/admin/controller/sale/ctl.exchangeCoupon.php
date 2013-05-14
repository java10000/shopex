<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_exchangeCoupon extends objectPage{

    var $workground = 'sale';
    var $object = 'trading/exchangeCoupon';
    var $finder_action_tpl = 'sale/coupon/exchange/finder_action.html'; //默认的动作html模板,可以为null
    var $noRecycle = true;
    var $filterUnable = true;

    function _detail(){
        return array('show_detail'=>array('label'=>__('退款单信息'),'tpl'=>'sale/coupon/exchange/addExchange.html'));
    }
    function show_detail($cpnsId){
        $oCoupon = $this->system->loadModel('trading/coupon');
        $aList = $oCoupon->getUserCouponArr();
        $this->pagedata['cpns_list'] = $aList;
        if ($cpnsId) {
            $this->pagedata['cpns'] = $oCoupon->getCouponById($cpnsId);
        }else{
            $this->pagedata['cpns']['cpns_id'] = $aList[0][0]['cpns_id'];
        }
    }
    function showAddExchange($cpnsId=null){
        $oCoupon = $this->system->loadModel('trading/coupon');
        $aList = $oCoupon->getUserCouponArr();
        $this->pagedata['cpns_list'] = $aList;
        if ($cpnsId) {
            $this->pagedata['cpns'] = $oCoupon->getCouponById($cpnsId);
        }else{
            $this->pagedata['cpns']['cpns_id'] = $aList[0][0]['cpns_id'];
        }
        $this->page('sale/coupon/exchange/addExchange.html');
    }

    function addExchange(){
        $this->begin('index.php?ctl=sale/exchangeCoupon&act=index');
        if(empty($_POST['cpns_id']) || $_POST['cpns_id']=='undefined'){
            $this->end(false, __("优惠券名称不能为空"), 'index.php?ctl=sale/exchangeCoupon&act=index');
        }
        $oExchangeCoupon = &$this->system->loadModel('trading/exchangeCoupon');
        if(!$oExchangeCoupon->saveExchange($_POST)) {
            $this->end(false, $oExchangeCoupon->message, 'index.php?ctl=sale/exchangeCoupon&act=index');
        }else{
            $this->end(true, '保存成功', 'index.php?ctl=sale/exchangeCoupon&act=index');
        }
    }
}
?>
