<?php
class pmt_n_6{
    var $name = '促销活动规则--购物车中商品总金额大于指定金额，客户就可得到指定的%off折扣';
    var $memo = '购物车的金额大于指定金额，客户就可得到指定的%off折扣';
    var $pmts_solution = array(
        'type'=>'order',
        'condition'=>array(
            array('mLev'),
            array('orderMoney_from'),
            array('orderMoney_to')
        ),                
        'method'=>array(
            array('discount'),
        )
    );
    var $pmts_type = PMT_SCHEME_PROMOTION;
}
?>
