<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_2_0_refund extends shop_api_object {
     var $app_error=array(
            'can_not_create_refund'=>array('no'=>'b_refund_001','debug'=>'','level'=>'error','desc'=>'退款单不能正常生成','info'=>''),
            'refund_is_out_of_order_price'=>array('no'=>'b_refund_002','debug'=>'','level'=>'error','desc'=>'退款金额不在订单已支付金额范围','info'=>''),
            'not_refund_money'=>array('no'=>'b_refund_003','debug'=>'','level'=>'error','desc'=>'没有退款金额','info'=>''),
        );
     /**
     * 创建退款单
     * @param array $aData 
     *
     * @return 创建退款单
     */
    function refund($aOrder,$payMoney,& $obj_order){
        $obj_order->checkOrderStatus('refund', $aOrder);

        $aUpdate['pay_status']= 5;    //预设订单状态
        $aUpdate['payed'] = $aOrder['cost_payment'];    //预设订单支付金额
        if(isset($aOrder['refund_money'])){ 
            if($aOrder['refund_money'] > $payMoney || $aOrder['refund_money'] <= 0){
                $this->add_application_error('refund_is_out_of_order_price');
            }
            
            if($payMoney > $aOrder['refund_money']){
                $aUpdate['pay_status'] = 4;
                $aUpdate['payed'] = $aOrder['payed'] - $aOrder['refund_money'];
            }
            $paymentId = 1;
            $payMethod = '预存款支付';
            $payMoney = $aOrder['refund_money'];
        }else{
            $this->add_application_error('not_refund_money');
        }
        
        $obj_advance = $this->load_api_instance('deduct_dealer_advance','2.0');    
        $obj_advance->checkAccount($aOrder['member_id'], 0);//检查会员帐户,现在退钱走预存款
       
        $aRefund['money'] = $payMoney;
        $aRefund['order_id'] = $aOrder['order_id'];
        $aRefund['pay_type'] = 'deposit';//默认为预存款
        $aRefund['member_id'] = $aOrder['member_id'];
        $aRefund['account'] = $aOrder['account'];
        $aRefund['pay_account'] = $aOrder['pay_account'];
        $aRefund['bank'] = $aOrder['bank'];
        $aRefund['title'] = 'title';
        $aRefund['currency'] = $aOrder['currency'];
        $aRefund['payment'] = $paymentId;
        $aRefund['paymethod'] = $payMethod;
        $aRefund['status'] = 'sent';
        $aRefund['memo'] = '经销商修改订单退款产生';
        $aRefund['refund_id'] = $this->gen_id();
        $aRefund['t_ready'] = time();
        $aRefund['t_sent'] = time();

        //通知平台
        $objPlatform = $this->system->loadModel('system/platform');
        if($objPlatform->tell_platform('refunds',array('refund_id'=>$aRefund['refund_id'],'data'=>$aRefund))=== false){
           $this->api_response('fail','data fail',$result,$objPlatform->getErrorInfo());
        }
            
        
        $rs = $this->db->query('select * from sdb_refunds where 0=1');
        $sql = $this->db->getInsertSQL($rs,$aRefund);
        if(!$this->db->exec($sql)){
            $this->add_application_error('can_not_create_refund');
        }
        
        $obj_order->addLog($aOrder['order_id'],'订单退款'.$payMoney,null, null , '退款');
         
        //更新订单状态以及支付金额     
        $aUpdate['acttime'] = time();
        //$obj_order->update_order($aOrder['order_id'],$aUpdate);
        
        //增加预存款
        $message .= '预存款退款：#O{'.$aOrder['order_id'].'}#';    
        $obj_advance->add($aOrder['member_id'], $payMoney, $message, '', $aOrder['order_id'] ,'' ,'预存款退款');
     
        return $aUpdate;
    }
    
    /**
     * 创建退款单号
     *
     *
     * @return 创建退款单号
     */
    function gen_id(){
        $i = rand(0,9999);
        do{
            if(9999==$i){
                $i=0;
            }
            $i++;
            $refund_id = time().str_pad($i,4,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('select refund_id from sdb_refunds where refund_id =\''.$refund_id.'\'');
        }while($row);
        return $refund_id;
    }
  
  
}