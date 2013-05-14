<?php
   $app_error=array(
            'dealer_member_not_exists'=>array('no'=>'b_member_001','debug'=>'','level'=>'error','desc'=>'经销商所对应的会员记录无效','info'=>''),
            'member invalid'=>array('no'=>'b_member_002','debug'=>'','level'=>'error','desc'=>'会员用户名密码错误或者不存在','info'=>''),
            'license invalid'=>array('no'=>'b_member_003','debug'=>'','level'=>'error','desc'=>'错误格式的license'),
            'license exist'=>array('no'=>'b_member_004','debug'=>'','level'=>'error','desc'=>'license已经存在'),  
            'dealer_member_lv_not_exists'=>array('no'=>'b_member_005','debug'=>'','level'=>'error','desc'=>'经销商所对应的会员级别不存在','info'=>'')
    );
   return $app_error;
?>