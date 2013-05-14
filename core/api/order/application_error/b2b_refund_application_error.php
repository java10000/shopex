<?php
   $app_error=array(
            'can_not_create_refund'=>array('no'=>'b_refund_001','debug'=>'','level'=>'error','desc'=>'退款单不能正常生成','info'=>''),
            'refund_is_out_of_order_price'=>array('no'=>'b_refund_002','debug'=>'','level'=>'error','desc'=>'退款金额不在订单已支付金额范围','info'=>''),
            'not_refund_money'=>array('no'=>'b_refund_003','debug'=>'','level'=>'error','desc'=>'没有退款金额','info'=>'')
    );
   return $app_error;
?>