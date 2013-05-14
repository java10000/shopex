<?php
   $app_error=array(
             'predeposits_is_not_enough'=>array('no'=>'b_advance_001','debug'=>'','level'=>'error','desc'=>'预存款帐户余额不足','info'=>''),
            'fail_to_update_predeposits'=>array('no'=>'b_advance_002','debug'=>'','level'=>'error','desc'=>'更新预存款帐户失败','info'=>''),
            'payment_is_not_predeposits'=>array('no'=>'b_advance_003','debug'=>'','level'=>'error','desc'=>'支付方式不是预存款','info'=>''),
            'advance_is_not_exist'=>array('no'=>'b_advance_004','debug'=>'','level'=>'error','desc'=>'预存款帐户不存在','info'=>''),
            'fail_to_select_advance'=>array('no'=>'b_advance_005','debug'=>'','level'=>'error','desc'=>'查询预存款帐户失败','info'=>'')
    );
   return $app_error;
?>