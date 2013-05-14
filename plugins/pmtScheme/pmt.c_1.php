<?php
class pmt_c_1{
    var $name = '优惠券规则--购物车中商品总金额大于指定金额，赠送某个赠品';
    var $memo = '购物车的金额大于指定金额，赠送某个赠品';
    var $pmts_solution = array(
        'type'=>'order',
        'condition'=>array(
            array('mLev'),
            array('orderMoney_from'),
            array('orderMoney_to')
        ),            
        'method'=>array(
            array('giveGift'),
        )
    );
    var $pmts_type = PMT_SCHEME_COUPON;
}
?>
