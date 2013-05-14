<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_demo extends adminPage{
    var $workground = 'goods';
    var $requestAddress='http://localhost/platform/';
    var $my_supply_id=2;
    var $totalPage=10;
    var $supplier_id=TEST_SUPPLIER;

    function index() {
        $this->page('demo/list.html',true);
    }
    


    function set(){
        $this->page('demo/set.html',true);        
    }
    function b2c(){
        $this->page('demo/b2c.html',true);        
    }
    function orderlist(){
        $this->page('demo/订单列表.html',true);        
    }
    function orderedit(){
        $this->page('demo/订单详情(采购单操作).html',true);        
    }
    function orderedit2(){
        $this->page('demo/订单详情(缺库存商品下单).html',true);        
    }
     function orderedit3(){
        $this->page('demo/订单详情(采购单修改逻辑).html',true);        
    }

    function order_mai(){
        $this->page('demo/order_mai.html',true);        
    }
    function order(){
        $this->page('demo/order.html',true);        
    }
    function purchase(){
        $this->page('demo/下采购单确认页面.html',true);        
    }
     function purchaselist(){
        $this->page('demo/采购单列表页面.html',true);        
    }
     function purchaseset(){
        $this->page('demo/purchaseset.html',true);        
    }
    function pay(){
        $this->page('demo/支付页面.html',true);        
    }
    function subsist(){
        $this->page('demo/预存款合并付款页面.html',true);        
    }
    function member_newsell(){
        $this->page('demo/member_newsell.html',true);        
    }
    function planlist(){
        $this->page('demo/planlist.html',true);        
    }
    function agentbuy(){
        $this->page('demo/agentbuy.html',true);        
    }    
    function userpurview(){
        $this->page('demo/userpurview.html',true);        
    }    
    function pricectr(){
        $this->page('demo/pricectr.html',true);        
    }    
    function pricectr2(){
        $this->page('demo/pricectr2.html',true);        
    }
    function pricectr3(){
        $this->page('demo/pricectr3.html',true);        
    }
    function addWholesale(){
        $this->page('demo/addWholesale.html',true);        
    }
    function doSelectRule1(){
        $this->page('demo/doSelectRule1.html',true);        
    }
    function doSelectRule2(){
        $this->page('demo/doSelectRule2.html',true);        
    }
    function doSelectRule3(){
        $this->page('demo/doSelectRule3.html',true);        
    }
    function doWriteRule(){
        $this->page('demo/doWriteRule.html',true);        
    }
    function saveRule1(){
        $this->page('demo/saveRule1.html',true);        
    }
    function saveRule2(){
        $this->page('demo/saveRule2.html',true);        
    }
    function viewallRule(){
        $this->page('demo/viewallRule.html',true);        
    }

    function productedit(){
        $this->page('demo/productedit.html',true);        
    }
    function productedit2(){
        $this->page('demo/productedit2.html',true);        
    }
    function returngoods(){
        $this->page('demo/returngoods.html',true);        
    }
    function afterservice(){
        $this->page('demo/afterservice.html',true);        
    }
    function registertype(){
        $this->page('demo/registertype.html',true);        
    }
    function memberlist(){
        $this->page('demo/memberlist.html',true);        
    }
    function member_editsell(){
        $this->page('demo/member_editsell.html',true);        
    }
    function noticeDemo(){
        $this->page('demo/noticeDemo.html',true);        
    }
    function freeze(){
        $this->page('demo/freeze.html',true);        
    }
    function freeze2(){
        $this->page('demo/freeze2.html',true);        
    }
    function setTab(){
        $this->page('demo/setTab.html',true);        
    }
    //pass
    function test_inquiry(){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
/*        $_result = $oOrderPo->inquiry($this->supplier_id,'20090525070839',array(
                               '00086lG49B771B4BB4E0-1' => 3,
                               '00086lG49B771B4BB4E0-2' => 4,
                               '00086lG49B771B4BB4E0-3' => 5,
                               '00086lG49B771B4BB4E0-4' => 2,                               
                               ));
*/
        /*
        $_result = $oOrderPo->inquiry($this->supplier_id,'20090525070839',array(
                               'G49B771B4BB4E0-1' => 3,
                               'G49B771B4BB4E0-2' => 4,
                               'G49B771B4BB4E0-3' => 5,
                               'G49B771B4BB4E0-4' => 2,                               
                               ));*/
        $_result = $oOrderPo->inquiry($this->supplier_id,'20090525070839',array(
                               'G49Basdf771B4BB4E0-1' => 3,
                               'G49B771Bdsa4BB4E0-2' => 4,
                               'G49B771B4BBasdf4E0-3' => 5,
                               'G49B771B4BB4E0-4' => 2,                               
                               ));
        echo '--';
          
            
        var_dump($_result);
    }

    //pass
    function test_createPo(){
        
        $_info = array(
            'shipping' => 'shipping',
            'shipping_area' => 'shipping_area',
            'ship_name' => 'ship_name',
            'ship_area' => 'ship_area',
            'ship_addr' => 'ship_addr',
            'ship_zip' => 'ship_zip',
            'ship_tel' => 'ship_tel',
            'ship_email' => 'ship_email',
            'ship_time' => 'ship_time',
            'ship_mobile' => 'ship_mobile',
            'is_tax' => 'false',
            'tax_company' => '',
            'is_protect' => 'true',
            'currency' => 'CNY',
            'member_memo' => 'asdfsadf',
            'sender_info' => 'kk',//发货人信息
            );

        $_items = array(
            0 => array(
                'dealer_bn' => '00086lG49B771B4BB4E0-1',
                'supplier_bn' => 'G49B771B4BB4E0-1',
                'price' => '339',
                'nums' => '3'
                ),
            1 => array(
                'dealer_bn' => '00086lG49B771B4BB4E0-2',
                'supplier_bn' => 'G49B771B4BB4E0-2',
                'price' => '279',
                'nums' => '4'
                ),
            2 => array(
                'dealer_bn' => '00086lG49B771B4BB4E0-3',
                'supplier_bn' => 'G49B771B4BB4E0-3',
                'price' => '339',
                'nums' => '5'
                ),
            3 => array(
                'dealer_bn' => '00086lG49B771B4BB4E0-4',
                'supplier_bn' => 'G49B771B4BB4E0-4',
                'price' => '279',
                'nums' => '2'
                ));
            
            
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->createPo($this->supplier_id, '20090525070839', $_info, $_items);

        var_dump($_result);
    }

    //pass
    function test_getPoListByOrderId(){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->getPoListByOrderId('20090525070839');
//        $_result = $oOrderPo->getPoListByOrderId('111236');        
        var_dump($_result);
        
//            function getPoListByOrderId($orderid, $format='simple'){
        
    }

    //todo
    function test_modifyOrder(){
/*
     *                          array( 
     *                              0 => array( 
     *                                   'dealer_bn' => xxx, //b2c端bn号
     *                                   'supplier_bn' => xxx, //b2c端bn号     
     *                                   'price' => xxx ,//b2c端价格
     *                                   'po_price' => xxx ,//采购单价格
     *                                   'nums' => xxx
     *                                   'product_id' => xxx),
     *                               ...
     *                          )
     */  
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_items = array(
            0 => array(
                'dealer_bn' => '00086lG49B771B4BB4E0-1',
                'supplier_bn' => 'G49B771B4BB4E0-1',
                'price' => 339,
                'po_price' => 339,                
                'nums' => 13,
                'product_id' => 1008                
                ),
            1 => array(
                'dealer_bn' => '00086lG49B771B4BB4E0-2',
                'supplier_bn' => 'G49B771B4BB4E0-2',
                'price' => 279,
                'po_price' => 279,                                
                'nums' => 14,
                'product_id' => 1009                                
                ),
            2 => array(
                'dealer_bn' => '00086lG49B771B4BB4E0-3',
                'supplier_bn' => 'G49B771B4BB4E0-3',
                'price' => 339,
                'po_price' => 339,                
                'nums' => 10,
                'product_id' => 1010
                
                ),
            3 => array(
                'dealer_bn' => '00086lG49B771B4BB4E0-4',
                'supplier_bn' => 'G49B771B4BB4E0-4',
                'price' => 279,
                'po_price' => 279,                
                'nums' => 3,
                'product_id' => 1011                                                
                ));
        $_item = json_decode('{"0008sbG49B771B4BB4E0-1":{"dealer_bn":"0008sbG49B771B4BB4E0-1","supplier_bn":"G49B771B4BB4E0-1","price":"339.000","nums":"15"},"0008s5G49B771B4BB4E0-2":{"dealer_bn":"0008s5G49B771B4BB4E0-2","supplier_bn":"G49B771B4BB4E0-2","price":"279.000","nums":"32"},"00087hG49B771B4BB4E0-3":{"dealer_bn":"00087hG49B771B4BB4E0-3","supplier_bn":"G49B771B4BB4E0-3","price":"339.000","nums":"19"},"0008voG49B771B4BB4E0-4":{"dealer_bn":"0008voG49B771B4BB4E0-4","supplier_bn":"G49B771B4BB4E0-4","price":"279.000","nums":"30"}}', true);
//        var_dump($_item);exit;
        $_result = $oOrderPo->modifyOrder('20090525070839',$_items,$this->supplier_id,188);
        var_dump($_result);
        
//        $orderid, $modifyItems, $supplierId=0, $poId=0
    }

    //pass
    function test_getSupplierDomain(){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->getSupplierDomain($this->supplier_id,true);
        var_dump($_result);
    }

    //pass
    function test_getDlyArea(){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->getDlyArea($this->supplier_id);
        var_dump($_result);
    }
    
    //pass
    function test_getDlyCorp($supplierId){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->getDlyCorp($this->supplier_id);
        var_dump($_result);
    }

    //pass
    function test_getDlyType(){
          echo '---';
        var_dump($this->supplier_id);
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->getDlyType($this->supplier_id);
      
        var_dump($_result);
    }

    //pass
    function test_getDlyHarea($supplierId){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->getDlyHarea($this->supplier_id);
        var_dump($_result);
    }
    //pass
    function test_getCurList($supplierId){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->getCurList($this->supplier_id);
        var_dump($_result);
    }    
    
    //pass
    function test_getPaymentCfg($supplierId){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->getPaymentCfg($this->supplier_id);
        var_dump($_result);
    }

    //pass
    function test_pendingPo($supplierId){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->pendingPo('186');
        var_dump($_result);
    }
    
    //pass
    function test_cancelPendingPo($supplierId){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->cancelPendingPo('186');
        var_dump($_result);
    }

    //pass
    function test_payByDeposits(){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->payByDeposits('186', 1, 10);
        var_dump($_result);
    }

    //pass
    function test_getSubRegionsb(){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->getSubRegions(1835307036, 0);
        var_dump($_result);
        
        
    }
    
    function test_getDlyTypeById(){
        $oOrderPo = $this->system->loadModel('purchase/order_po');
        $_result = $oOrderPo->getDlyTypeById($this->supplier_id, 1);
        var_dump($_result);
    }    
}
?>