<?php
include_once CORE_DIR.'/api/shop_api_object.php';

class api_payment extends shop_api_object {

    // $payment http://open.shopex.cn/apidocs/data_structures/94/10/8.htm
    function dopay($payment){
        if ( +$payment['money'] <= 0 ) {
            $this->api_response('fail','data fail',null,'支付金额不得小于零');
        }
        
        $order = $this->db->selectrow('SELECT order_id,member_id,status,pay_status,payed,total_amount,cur_rate
            FROM sdb_orders WHERE order_id='.$this->db->quote($payment['order_id']));
        
        if ( !$order ) {
            $this->api_response('fail','data fail',null,'订单不存在');
        }
        if ( PAYMENT_PAY_ALL === +$order['pay_status'] ) {
            $this->api_response('fail','data fail',null,'重复支付');
        }
        if ( 'active' != $order['status'] ) {
            $this->api_response('fail','data fail',null,'非活动订单');
        }
        
        // 检查支付方式
        $payment_cfg = $this->db->selectrow('SELECT id,pay_type,custom_name FROM sdb_payment_cfg WHERE id='.intval($payment['payment']));
        if( (-1 !== +$payment['payment']) && !$payment_cfg ) {
            $this->api_response('fail','data fail',null,'支付方式不存在或已禁用');
        }
        
        // 如果是预存款支付则检查预存款额度
        if ( 'deposit' == $payment['pay_type'] ) {
            // 检查会员
            $buyer = $this->db->selectrow('SELECT member_id,uname FROM sdb_members WHERE uname='.$this->db->quote($payment['member_id']));
            if ( !$buyer || ($order['member_id'] != $buyer['member_id'])) {
                $this->api_response('fail','data fail',null,'会员不存在或订单不属于该会员');
            }
            $advanceMdl = $this->system->loadModel('member/advance');
            if ( !$advanceMdl->checkAccount($buyer['member_id'],$payment['money'],$message) ) {
                $this->api_response('fail','data fail',null,'支付失败：'.$message);
            }
        }
        
        $payed = $this->db->select('SELECT SUM(money) payed FROM sdb_payments WHERE status="succ" AND order_id ='.$this->db->quote($payment['order_id']));
        $refund = $this->db->select('SELECT SUM(money) refund FROM sdb_refunds WHERE status="sent" AND order_id ='.$this->db->quote($payment['order_id']));
        $payed_ = $payment['money'] + $payed[0]['payed'] - $refund[0]['refund'];
        
        if ( +$payed_ > +$order['total_amount'] ) {
            $this->api_response('fail','data fail',null,'支付金额大于订单总金额');
        } elseif( +$payed_ == +$order['total_amount'] ) {
            $pay_status = PAYMENT_PAY_ALL;
        } elseif ( 0 == +$payed_ ) {
            $pay_status = PAYMENT_PAY_NOTYET;
        } else {
            $pay_status = PAYMENT_PAY_PART;
        }
        
        $orderMdl = $this->system->loadModel('trading/order');
        if ( PAYMENT_PAY_ALL === $pay_status ) {
            $orderMdl->toCoupon($order);  // 给优惠券
            $orderMdl->toPoint($order);  // 给积分
            $orderMdl->toExperience($order);
        }
        
        // 创建支付单
        $paymentMdl = $this->system->loadModel('trading/payment');
        $payment_ = array(
            'payment_id'=>$paymentMdl->gen_id(),
            'order_id'=>$payment['order_id'],
            'trade_no'=>$payment['trade_no'],
            'member_id'=>$buyer['member_id'],
            'status'=>'succ',
            
            'pay_account'=>$buyer['uname'],
            'pay_type'=>$payment['pay_type'],
            
            'payment'=>+$payment['payment'],
            'paymethod'=>$payment['paymethod'],
            'account'=>$payment['account'],
            'bank'=>$payment['bank'],
            'money'=>$payment['money'],
            'currency'=>$payment['currency'] ? $payment['currency'] : $order['currency'],
            'cur_money'=>$payment['money']*$order['cur_rate']/100,
            
            't_begin'=>$payment['t_begin'] ? $payment['t_begin']:NOW,
            't_end'=>$payment['t_end'] ? $payment['t_end']:NOW,
            'memo'=>'OME发起支付',
            'disabled'=>'false',
            'trade_no'=>$payment['trade_no'], //交易流水单号
            'ip'=>$payment['ip'],
        );
        $rs = $this->db->exec('SELECT * FROM sdb_payments WHERE 0');
        $sql = $this->db->getInsertSql($rs, $payment_);
        if ( !$this->db->exec($sql) ) {
            $this->api_response('fail','system error',null,'支付失败');
        }
        
        // 如果是预存款支付则减去预存款
        if( 'deposit' == $payment_['pay_type'] ){
            if ( !$advanceMdl->deduct($buyer['member_id'],$payment_['money']) ) {
                $this->db->exec('DELETE FROM sdb_payments WHERE payment_id='.$payment_['payment_id']);
                $this->api_response('fail','system error',null,'扣预存款失败');
            }
        }
        
        // 更新订单支付状态
        $now = NOW;
        $this->db->exec("UPDATE sdb_orders SET pay_status='$pay_status',payed=$payed_,last_change_time=$now WHERE order_id=".$this->db->quote($payment_['order_id']));
        
        // 订单日志
        $orderMdl->_info['order_id'] = $payment_['order_id'];
        $orderMdl->addLog("订单支付{$parent_['money']}成功",null,null,'付款');
        
        // 触发事件推送到ome
        $eventdata = array(
            'order_id'=>$payment_['order_id'],'pay_status'=>(string)$pay_status,
            'pay_type'=>$payment_['pay_type'],'money'=>$payment_['money'],
        );
        $orderMdl->fireEvent('payed',$eventdata);
        
        $this->api_response('true','','支付单创建成功等待同步');
    }
    
    // ome 同步支付方式
    function init_payment_cfg(){
        $payment_cfg = $this->db->select('SELECT id,pay_type,custom_name FROM sdb_payment_cfg WHERE disabled = "false"');
        foreach( (array)$payment_cfg as $val ) {
            switch( true ) {
                default: $payout_type = 'online'; break;
                case 'deposit' == $val['pay_type']:
                    $payout_type = 'deposit'; break;
                case 'offline' == $val['pay_type']:
                    $payout_type = 'offline'; break;
            }
            
            $cfgs[] = array(
                'payout_type'=>$payout_type,
                'payment_name'=>$val['custom_name'],
                'payment_id'=>$val['id'],
            );
        }
        $this->api_response('true','',$cfgs);
    }
}
