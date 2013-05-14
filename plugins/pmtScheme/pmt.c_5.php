<?php
class pmt_c_5{
    var $name = '优惠券规则--购物车中商品总金额大于指定金额，客户可获得一张优惠券';
    var $memo = '购物车的金额大于指定金额，客户可获得一张优惠券';
    var $pmts_solution = array(
        'type'=>'order',
        'condition'=>array(
            array('mLev'),
            array('orderMoney_from'),
            array('orderMoney_to')
        ),                
        'method'=>array(
            array('generateCoupon'),
        )
    );
    var $pmts_type = PMT_SCHEME_COUPON;
}
?>
