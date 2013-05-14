<?php
include_once CORE_DIR.'/api/shop_api_object.php';

class api_refund extends shop_api_object {

    function dorefund($refund){
        if ( 0 >= +$refund['money'] ) {
            $this->api_response('fail','data fail',null,'退款金额必须大于零');
        }
        
        // 检查订单存在
        $orderMdl = &$this->system->loadModel('trading/order');
        if ( (!$order_id = $refund['order_id']) || (!$order = $orderMdl->instance($order_id)) ) {
            $this->api_response('fail','data fail',null,'订单号不存在');
        }

        if ( 0 >= +$order['payed'] ) {
            $this->api_response('fail','data fail',null,'订单尚未付款');
        }
        
        // 检查会员
        if( (!$uname=$refund['member_id']) ||
            (!$member = $this->db->selectrow('SELECT member_id FROM sdb_members WHERE uname='.$this->db->quote($uname))) ) {
            //$this->api_response('fail','data fail',null,'会员不存在');
        }
        if ( $member['member_id'] != $order['member_id'] ) {
            //$this->api_response('fail','data fail',null,'会员订单不匹配');
        }
        
        // 检查支付方式
        $pay_type=$refund['pay_type'];
        if ( !$payment_id =$refund['payment'] ){
            $this->api_response('fail','data fail',null,'未指定支付方式');
        }
        $payment_cfg = $this->db->selectrow('SELECT id,custom_name FROM sdb_payment_cfg WHERE id='.intval($payment_id));
        if ( !$payment_cfg ) {
            $this->api_response('fail','data fail',null,'没有对应支付方式');
        }
        
        $paymentMdl = $this->system->loadModel('trading/payment');
        $refund_ = array(
            'refund_id'=>$paymentMdl->gen_id(),
            'order_id'=>$order_id,
            'member_id'=>$order['member_id'],
            'account'=>$refund['account'],
            'bank'=>$refund['bank'],
            'pay_account'=>$refund['pay_account'],
            'currency'=>$refund['currency'],
            'cur_money'=>$refund['money']+$refund['money']*$order['cur_rate'],
            'money'=>$refund['money'],
            'pay_type'=>$pay_type,
            'payment'=>$payment_cfg['id'],
            'paymethod'=>$payment_cfg['custom_name'],
            'ip'=>$refund['ip'],
            't_ready'=>NOW,'t_sent'=>NOW,'t_received'=>NOW,
            
            'status'=>'sent',
            'memo'=>'ome请求退款单',
            'title'=>'title',
            'send_op_id'=>$refund['send_op_id'],
            'trade_no'=>$refund['order_id'],//交易流水单号
        );
        
        $payed_money = +$order['payed'];
        if ( $refund['money'] > $payed_money ) {
            $this->api_response('fail','data fail',null,'退款金额大于订单支付金额');
        } elseif ( $payed_money == $refund['money'] ) {
            $pay_status = PAYMENT_REFUNDS_ALL;
        } elseif ( $payed_money - $refund['money'] >= $order['total_amount'] ) {
            $pay_status = PAYMENT_PAY_ALL;
        } else {
            if ( PAYMENT_PAY_ALL == $order['pay_status'] ||
                PAYMENT_REFUNDS_PART == $order['pay_status'] ) { // 如果对已支付、部分退款的订单退款则是部分退款
                $pay_status = PAYMENT_REFUNDS_PART;
            } else { // 如果对部分支付的订单退款则是部分支付
                $pay_status = PAYMENT_PAY_PART;
            }
        }
        
        if ( 'deposit' == $pay_type ) {
            $advanceMdl = $this->system->loadModel('member/advance');
            // todo:locking
            $advance = +($advanceMdl->get($member['member_id']) + $refund['money']);
            $this->db->exec("UPDATE sdb_members SET advance=$advance WHERE member_id=".intval($member['member_id']));
            $message = '预存款退款：#O{'.$order_id.'}#';
            
            $advanceMdl->log($member['member_id'],$refund['money'],$message,
                $refund_['refund_id'],$refund['order_id'],$refund_['paymethod'],$refund_['paymethod'],$advance);
        }

        $this->db->insert('sdb_refunds',$refund_);
        $now = NOW; $payed_money = $payed_money-$refund['money'];
        $this->db->exec("UPDATE sdb_orders set pay_status ='$pay_status',payed=$payed_money,last_change_time=$now WHERE order_id=".$this->db->quote($order_id));

        $event_data = array(
            'order_id'=>$order_id,
            'refund_id'=>$refund_['refund_id'],
            'ome_refund_id'=>$refund['refund_id'],
        );
        
        $orderMdl->fireEvent('refund',$event_data);
        $orderMdl->fireEvent('editorder',$event_data);
        
        $this->api_response('true',$event_data,'退款单新建成功');
    }
}
