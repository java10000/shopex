<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_advance extends shop_api_object {
    var $max_number=100;
    var $app_error=array(
            'predeposits is not enough'=>array('no'=>'b_deduct_dealer_001','debug'=>'','level'=>'warning','info'=>'预存款帐户余额不足','desc'=>'','debug'=>''),
            'fail to update predeposits'=>array('no'=>'b_deduct_dealer_002','debug'=>'','level'=>'warning','info'=>'更新预存款帐户失败','desc'=>'','debug'=>''),
            'payment is not predeposits'=>array('no'=>'b_deduct_dealer_003','debug'=>'','level'=>'warning','info'=>'支付方式不是预存款','desc'=>'','debug'=>'')

    );
    function getColumns(){
        $columns=array(
         
        );
        return $columns;
    }
    
    /**
     * 预存款充值
     * @param $data
     * @return unknown_type
     */
    function advance_add($data){
        $this->add($data['member_id'],$data['money'],$data['message'],$data['payment_id'],$data['order_id'],$data['paymethod'],$data['memo']);
        $this->api_response('true',false,'');
    }
    
     /**
     * add 预存款充值
     * 
     * @param mixed $member_id 
     * @param mixed $money 
     * @param mixed $message 
     * @access public
     * @return void
     */
    function add($member_id,$money,$message, $payment_id='', $order_id='' ,$paymethod='' ,$memo=''){
        $error_msg = '';
        $this->checkAccount($member_id,0,$rows);
        
        $data=array('advance'=>$rows[0]['advance'] + $money);
        $member_advance = $data['advance'];
        $rs = $this->db->exec('SELECT * FROM sdb_members WHERE member_id='.intval($member_id));
        $sql = $this->db->getUpdateSQL($rs,$data);
        
        if($this->db->exec($sql)){
            $this->log($member_id,$money,$message, $payment_id, $order_id ,$paymethod ,$memo ,$member_advance);
        }else{
            $error_msg = '更新预存款帐户失败';
        }
          
        if(!empty($error_msg)){
            $this->api_response('fail','data fail',$result,$error_msg);
        }else{
            return true;
        }
    }
    
    /**
     * 检查会员帐户
     * @param array $act 
     * @param array $aOrder 
     * @return 检查会员帐户
     */
    function checkAccount($member_id,$money=0,&$rows){
        $error_msg = '';
        if($rs = $this->db->exec('SELECT advance,member_id FROM sdb_members WHERE member_id='.intval($member_id))){
            $rows = $this->db->getRows($rs,1);
            if(count($rows)>0){
                if($money>$rows[0]['advance']){
                    $error_msg = '预存款帐户余额不足';
                }
            }else{
                $error_msg = '预存款帐户不存在';
            }
        }else{
            $error_msg = '查询预存款帐户失败';
            return false;
        }
        
        if(!empty($error_msg)){
            $this->api_response('fail','data fail',$result,$error_msg);
        }else{
            return true;
        }
    }
    
      /**
     * 供应商订单用预存款支付
     *
     * @param array $data 
     *
     * @return 供应商订单用预存款支付
     */
   
    function deduct_dealer_advance($data){ 
        $dealer_id = $data['dealer_id'];
        $order_id = $data['order_id'];
        $pay_id = $data['pay_id'];//支付方式ID
        $obj_member = $this->load_api_instance('verify_member_valid','1.0');    
        $obj_member->verify_member_valid($dealer_id,$member);//根据经销商ID验证会员记录有效性
       
        $obj_order = $this->load_api_instance('set_dead_order','1.0');
        $obj_order->verify_order_valid($order_id,$order,'*');//验证订单有效性    
        $obj_order->checkOrderStatus('pay',$order);//检查订单状态是否能够支付
        $obj_order->verify_order_item_valid($order_id,$local_order_item_list);//验证订单订单商品的有效性
        
        $obj_payment_cfg = $this->load_api_instance('search_payment_cfg_list','1.0');    
        $obj_payment_cfg->verify_paymentcfg_advance_valid($pay_id,$local_payment_cfg);//验证支付方式是否是预存款支付
        
        if($local_payment_cfg['pay_type'] !='deposit'){
            $this->api_response('fail','data fail',$result,'支付方式不是预存款');
        }
        
        $last_cost_payment = empty($order['cost_payment']) ? 0 : $order['cost_payment'];//最后次订单支付费用
        $money = $order['total_amount'] - $order['payed'];//取支付金额
        $cost_payment = $local_payment_cfg['fee'] * $money;//当前支付金额支付费
        $money = $money + $cost_payment;//需要支付的金额
        $order['total_amount'] = $order['total_amount'] + $cost_payment;//最新订单总价入库

        $this->advance_is_enough($member['advance'],$money);//预存款是否能足够支付   
       
        $obj_order->verify_payed_valid($order,$money);//检查支付金额是否大于订单总金额    
        
        $order_payment = array('order_id'=>$data['order_id'],
                               'money'=>$money,
                               'paycost'=>$cost_payment
        );
        $order_payment =  array_merge($order_payment,$order);
        $obj_payment = $this->load_api_instance('search_payments_by_order','1.0');
        $payment_id = $obj_payment->create_payment($pay_id,$order_payment,'deposit');//生成支付单
        
        $obj_payment->verify_payment_valid($payment_id,$payment);
        
         //通知平台支付单
        $objPlatform = &$this->system->loadModel('system/platform');
        if($objPlatform->tell_platform('payments',array('pay_id'=>$payment_id)) === false){
            $obj_payment->deletePayment($payment_id);
            $this->api_response('fail','data fail',$result,$objPlatform->getErrorInfo());
        }
          
        $obj_product = $this->load_api_instance('search_product_by_bn','1.0');      
        $obj_product->update_store_by_orderitem($local_order_item_list);//对订单商品进行库存计算
        
        $this->deduct_member_advance($member,$money,$payment_id,$order_id);//从预存款中扣除

        //设置订单支付费用,加上上次的支付费用
        $curr_cost_payment = $last_cost_payment + $cost_payment;
        $obj_order->set_order_payment($order_id,$curr_cost_payment);
        
        $obj_order->changeOrderPayment($order_id,$pay_id);//改变支付方式    
        $obj_order->payed($order,$money);//订单状态更改
        
        $result['data_info'] = $payment;
        $this->api_response('true',false,$result);
    }

    /**
     * 从会员预存款中扣除
     * 
     * @param mixed $member_id 
     * @param mixed $start 
     * @param mixed $end 
     * @access public
     * @return 从会员预存款中扣除
     */
    function deduct_member_advance($member,$money,$payment_id,$order_id){
        $member_id = $member['member_id'];
        $data=array('advance'=>$member['advance']-$money);
        $message = '扣费成功';
        $memo = '扣费成功';
        $paymethod = '预存款支付';
        
        //会员预存款支付
        $member_advance = $data['advance'];
        $rs = $this->db->exec('SELECT * FROM sdb_members WHERE member_id='.intval($member_id));
        $sql = $this->db->getUpdateSQL($rs,$data);
        if(!$sql || $this->db->exec($sql)){
           $this->log($member_id,-$money,$message, $payment_id, $order_id ,$paymethod ,$memo ,$member_advance );        
        }else{
           $this->api_response('fail','data fail',$result,'更新预存款帐户失败');
        }
    }

    /**
     * log 取得记录
     * 
     * @param mixed $member_id 
     * @param mixed $start 
     * @param mixed $end 
     * @access public
     * @return void
     */
    function log($member_id,$money,$message, $payment_id='', $order_id='' ,$paymethod='' ,$memo='' ,$member_advance='' ){
        $shop_advance = $this->getShopAdvance();
        $rs = $this->db->exec('select * from sdb_advance_logs where 0=1');
        $sql = $this->db->getInsertSQL($rs,array(
            'member_id'=>$member_id,
            'money'=>$money,
            'mtime'=>time(),
            'message'=>$message,
            'payment_id'=>$payment_id,
            'order_id'=>$order_id,
            'paymethod'=>$paymethod,
            'memo'=>$memo,
            'import_money'=>($money>0?$money:0),
            'explode_money'=>($money<0?-$money:0),
            'member_advance'=>$member_advance,
            'shop_advance'=>$shop_advance
            ));
        return $this->db->exec($sql);
    }
     
    /* 会员预存款是否足够支付
     * 
     * @param int $member_advance 
     * @param int $money
     * @return 会员预存款是否足够支付
     */
    function advance_is_enough($member_advance,$money){
        if($money > $member_advance){
           $this->api_response('fail','data fail',$result,'预存款帐户余额不足');
        }
        
        return true;
    }
     
     
    function getShopAdvance(){
        $row = $this->db->selectrow("SELECT SUM(advance) as sum_advance FROM sdb_members");
        return $row['sum_advance'];
    }
 
    /**
    * 会员预存款的应用级错误
    * @param string 错误原码
    * @author DreamDream
    * @return array 错误信息
    */
    function application_error($code){
        $error=array(
            'predeposits is not enough'=>array('no'=>'b_advance_001','debug'=>'','level'=>'warning','info'=>'预存款帐户余额不足','desc'=>'','debug'=>''),
            'fail to update predeposits'=>array('no'=>'b_advance_002','debug'=>'','level'=>'warning','info'=>'更新预存款帐户失败','desc'=>'','debug'=>''),
            'payment is not predeposits'=>array('no'=>'b_advance_003','debug'=>'','level'=>'warning','info'=>'支付方式不是预存款','desc'=>'','debug'=>'')
        );
        return $error[$code];
    }

}