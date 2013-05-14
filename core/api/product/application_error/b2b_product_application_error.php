<?php
   $app_error=array(
             'sync_goods_no_exist'=>array('no'=>'b_product_001','debug'=>'','level'=>'error','desc'=>'同步订单商品数据不存在','info'=>''),
            'goods_not_exists'=>array('no'=>'b_product_002','debug'=>'','level'=>'error','desc'=>'订单商品不存在','info'=>''),
            'product_not_exists'=>array('no'=>'b_product_003','debug'=>'','level'=>'error','desc'=>'订单货品不存在','info'=>''),
            'goods_can_not_publish'=>array('no'=>'b_product_004','debug'=>'','level'=>'error','desc'=>'订单商品未发布不能下单','info'=>''),
            'goods_price_is_not_equal_to_the_suppliers_price'=>array('no'=>'b_product_005','debug'=>'','level'=>'error','desc'=>'供应商价格或者库存变动，请重新询价后下单','info'=>''),
            'product_no_store'=>array('no'=>'b_product_006','debug'=>'','level'=>'error','desc'=>'订单货品无库存','info'=>''),
            'product_no_available_store'=>array('no'=>'b_product_007','debug'=>'','level'=>'error','desc'=>'订单货品没有可下单库存','info'=>'')
    );
   return $app_error;
?>