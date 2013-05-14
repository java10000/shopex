<?php defined('CORE_DIR') || exit('入口错误'); 

class ctl_order extends shopPage{

    var $noCache = true;

    function create(){
        $this->begin($this->system->mkUrl('cart', 'checkout'));
        $this->_verifyMember(false);
        foreach($_POST['minfo'] as $k=>$v){
            foreach($v as $a=>$b){
                $_POST['minfo'][$k][$a]['value'] = strip_tags($b['value']);
            }
        }
        foreach($_POST['delivery'] as $kec=>$kev){
            $_POST['delivery'][$kec] = strip_tags($kev);
        }
        $order = &$this->system->loadModel('trading/order');
        $oCart = &$this->system->loadModel('trading/cart');
        $oCart->checkMember($this->member);
        if($_POST['isfastbuy']){
            $cart = $oCart->getCart('all',$_COOKIE['Cart_Fastbuy']);
        }else{
            $cart = $oCart->getCart('all');
        }

        // 校验购物车有没有发生变化
        $md5_cart_info = $_POST['md5_cart_info'];
        if ($md5_cart_info != $oCart->md5_cart_objects($cart)){
            trigger_error(__('对不起，购物车内容发生变化，请重新结算！'),E_USER_ERROR);
        }
        
        
       if($_POST['delivery']['ship_addr_area']!=''){    
            $_POST['delivery']['ship_addr'] = str_replace('^\s+|\s+$','',$_POST['delivery']['ship_addr_area'].$_POST['delivery']['ship_addr']);
        }
        
        if ( ! $this->member ) {
            $this->member = array('member_lv_id'=>constant('MLV_NOLOGIN'));
        }
        $orderid = $order->create($cart, $this->member,$_POST['delivery'],$_POST['payment'],$_POST['minfo'],$_POST);
        if($orderid){
            if($_POST['fromCart'] && !$_POST['isfastbuy']){
                $oCart->removeCart();
            }
        }else{
            trigger_error(__('对不起，订单创建过程中发生问题，请重新提交或稍后提交'),E_USER_ERROR);            
        }

        $this->system->setcookie('ST_ShopEx-Order-Buy', md5($this->system->getConf('certificate.token').$orderid));
        $account=$this->system->loadModel('member/account');
        $account->fireEvent('createorder',$this->member,$this->member['member_id']);
        $this->end_only(true, __('订单建立成功'), $this->system->mkUrl('order', 'index', array($orderid)));
        
        $GLOBALS['pageinfo']['order_id'] = $orderid;
        $this->redirect('order','index',array($orderid));
    }

    function index($order_id, $selecttype=false){
        $this->customer_template_type='order_index';
        if($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->system->getConf('certificate.token').$order_id)){
            $this->splash('failed','index.php',__('订单无效！'));
        }
        
        $objOrder = &$this->system->loadModel('trading/order');
        $aOrder = $objOrder->load($order_id);
//        $GLOBALS['pageinfo'] = $aOrder;
        $aOrder['member_id'] = is_null($aOrder['member_id'])?false:$aOrder['member_id'];
        $this->_verifyMember($aOrder['member_id']);

        $aOrder['cur_money'] = ($aOrder['amount']['total'] - $aOrder['amount']['payed']) * $aOrder['cur_rate'];
        $this->pagedata['order'] = $aOrder;
        
        if(!$this->pagedata['order']){
            $this->system->error(404);
            exit;
        }
        
        if($selecttype){
            $selecttype = 1;
//            $shipping = &$this->system->loadModel('trading/delivery');
//            $this->pagedata['delivery'] = $shipping->checkDlTypePay($this->pagedata['order']['shipping']['id'], $this->pagedata['order']['shipping']['area']);
            $payment = &$this->system->loadModel('trading/payment');
            $payments = $payment->getByCur($this->pagedata['order']['currency']);
            foreach($payments as $key => $val){
                $payments[$key]['money'] = $objOrder->chgPayment($order_id,$val['id'],$aOrder['amount']['total']-$aOrder['amount']['payed'],1);
                $payments[$key]['config']=unserialize($val['config']);
            }
            $payment = $this->system->loadModel('trading/payment');
            $payment->showPayExtendCon($payments,$aOrder['pay_extend']);
            $this->pagedata['payments'] = $payments;
            if ($payments){
                foreach($payments as $key => $val){
                    if(!$aOrder['member_id'] && $val['pay_type'] == 'deposit'){
                        unset($this->pagedata['payments'][$key]);
                        continue;
                    }
                }
            }
        }else{
            $selecttype = 0;
        }
        $this->pagedata['order']['selecttype'] = $selecttype;
        $this->pagedata['order']['paytype'] = strtoupper($this->pagedata['order']['paytype']);
        $objCur = &$this->system->loadModel('system/cur');
        $aCur = $objCur->getDefault();
        $this->pagedata['order']['cur_def'] = $aCur['cur_code'];
        $this->pagedata['base_url'] = $this->system->base_url();
        /**检查支付方式是否有二级内容,如快钱直连的银行****/
        $payment=$this->system->loadModel('trading/payment');
        $payment->OrdMemExtend($aOrder,$extendInfo);
        if ($extendInfo)
            $this->pagedata['extendInfo']=$extendInfo;
        /*************************************************/
        
        $this->output();
    }
    
    function detail($order_id, $selecttype=false){
        $this->customer_template_type='order_detail';
        if($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->system->getConf('certificate.token').$order_id)){
            $this->splash('failed','index.php',__('订单无效！'));
        }
        $objOrder = &$this->system->loadModel('trading/order');
        $aOrder = $objOrder->load($order_id);
        $aOrder['member_id'] = is_null($aOrder['member_id'])?false:$aOrder['member_id'];
        $this->_verifyMember($aOrder['member_id']);
        $aOrder['cur_money'] = ($aOrder['amount']['total'] - $aOrder['amount']['payed']) * $aOrder['cur_rate'];
        if($aOrder['member_id']){
            $member = &$this->system->loadModel('member/member');
            $aMember = $member->getFieldById($aOrder['member_id'], array('email'));
            $aOrder['receiver']['email'] = $aMember['email'];
        }
        $shiparea = explode( ':', $aOrder['receiver']['area'] );
        $aOrder['shipping']['area'] = $shiparea[1];
        $this->pagedata['order'] = $aOrder;
        if(!$this->pagedata['order']){
            $this->system->error(404);
            exit;
        }
        $gItems = $objOrder->getItemList($order_id);
        foreach($gItems as $key => $item){
            $gItems[$key]['addon'] = unserialize($item['addon']);
            if($item['minfo'] && unserialize($item['minfo'])){
                $gItems[$key]['minfo'] = unserialize($item['minfo']);
            }else{
                $gItems[$key]['minfo'] = array();
            }
        }
        $this->pagedata['order']['items'] = $gItems;
        $this->pagedata['order']['giftItems'] = $objOrder->getGiftItemList($order_id);
        
        if($selecttype){
            $selecttype = 1;
//            $shipping = &$this->system->loadModel('trading/delivery');
//            $this->pagedata['delivery'] = $shipping->checkDlTypePay($this->pagedata['order']['shipping']['id'], $this->pagedata['order']['shipping']['area']);
            $payment = &$this->system->loadModel('trading/payment');
            $this->pagedata['payments'] = $payment->getByCur($this->pagedata['order']['currency']);
        }else{
            $selecttype = 0;
        }
        $this->pagedata['order']['selecttype'] = $selecttype;
        
        $objCur = &$this->system->loadModel('system/cur');
        $aCur = $objCur->getDefault();
        $this->pagedata['order']['cur_def'] = $aCur['cur_code'];
        $this->output();
    }
}
?>