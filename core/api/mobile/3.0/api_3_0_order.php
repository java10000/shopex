<?php
/** Zhang Junhua
*/
include_once(CORE_DIR.'/api/shop_api_object.php');

class api_3_0_order extends shop_api_object {
    function api_3_0_order() {
        parent::shop_api_object();
        $this->orderMdl =& $this->system->loadModel('trading/order');
        $this->memberMdl =& $this->system->loadModel('member/member');
        // 定义支付方式映射,支付方式来自中心
        $this->payment_method = array(0=>'-1', 2=>'30');
    }
    
    /**
    *创建订单
    */
    function trade_order_add($data){
        $order_info = json_decode(trim($data['trade_order']),true);
        $order_info['orders'] = json_decode(trim($order_info['orders']),true);
        if ( !$order_info['orders']['order'] ) {
            $this->api_response('fail',false,'订单没有商品');
        }
        
        // 订单状态转换
        $order_status = $this->status2local($order_info['status']);
        $order_paystatus = $this->pay_status2local($order_info['pay_status']);
        $order_shipstatus = $this->ship_status2local($order_info['ship_status']);
        $order_isdelivery = $this->is_delivery_status2local($order_info['is_delivery']);
        
        $order = array_merge($order_status,$order_paystatus,$order_shipstatus,$order_isdelivery);
        $order['order_id'] = $this->orderMdl->gen_id();
        
        // 查看订单是否已经存在
        $order['order_refer_id'] = $order_info['tid'];
        $order_exists = $this->db->selectrow("SELECT 1 FROM sdb_orders WHERE order_refer_id='{$order['order_refer_id']}'");
        if ( $order_exists ) {
            $this->api_response('fail',false,"订单号：{$order['order_refer_id']} 已存在");
        }
        $order['createtime'] = strtotime($order_info['created']);
        $order['last_change_time'] =strtotime($order_info['modified']);
        //$order['is_delivery'] = $order_info['is_delivery'];
        $order['cost_item'] = $order_info['total_goods_fee'];
        $order['total_amount'] = $order_info['total_trade_fee'];
        $order['payed'] = $order_info['payed_fee'];
        $order['currency'] = $order_info['currency'];
        $order['cur_rate'] = $order_info['currency_rate'];
        $order['score_g'] = $order_info['buyer_obtain_point_fee'];
        $order['score_u'] = $order_info['point_fee'];
        $order['shipping_id'] = $order_info['shipping_tid'];
        $order['shipping'] = $order_info['shipping_type'];
        $order['cost_freight'] = $order_info['shipping_fee'];
        $order['is_protect'] = $order_info['is_protect'];
        $order['cost_protect'] = $order_info['protect_fee'];
        $order['payment'] = $this->payment_method[+$order_info['payment_tid']];
        //$order['custom_name'] = $order_info['payment_type'];
        $order['ship_name'] = $order_info['receiver_name'];
        $order['ship_email'] = $order_info['receiver_email'];
        $order['ship_area'] = '';//微商无地区
        $order['ship_addr'] = $order_info['receiver_address'];
        $order['ship_zip'] = $order_info['receiver_zip'];
        $order['ship_mobile'] = $order_info['receiver_mobile'];
        $order['ship_tel'] = $order_info['receiver_phone'];
        $order['ship_time'] = $order_info['receiver_time'];
        
        // 检查用户
        $member = $this->memberMdl->getMemberByUser($order_info['buyer_uname']);
        if ( !$member ) {
            $this->api_response('fail',false,'找不到用户');
        }
        $member_id = $order['member_id'] = $member['member_id'];
        
        // 订单中商品数量不可为零
        foreach ($order_info['orders']['order'] as $k=>$v) {
            $v['items_num'] = (int)$v['items_num'];
            if ( $v['items_num'] <= 0 ) {
                //$this->api_response('fail',false,'订单中商品数量为零');
            }
            $order['itemnum'] += $v['items_num']; 
        }
        $order['weight'] = $order_info['total_weight'];
        $order['tostr'] = $order_info['title'].':';
        $order['order_refer'] = 'mobile'; // 订单来源        
        $order['cost_tax'] = '0';
        $order['is_tax'] = ($order_info['has_invoice']=='true')?'true':'false';  //是否开启发票       
        $order['tax_company'] = $order_info['invoice_title']; //发票内容
        $order['cost_payment'] = $order_info['pay_cost'];
        $order['advance'] = '0';
        $order['discount'] = $order_info['discount_fee'];
        $order['final_amount'] = $order_info['total_trade_fee']-$order_info['discount_fee'];
        $order['markstar'] = 'N';
        $order['memo'] = $order_info['trade_memo'];
        $order['print_status'] = '0';
        $order['disabled'] = 'false';
        //$order['alipay_payid'] = $order_info['buyer_alipay_no'];
        $order['consign_time'] = $order_info['consign_time'];
        $order['pay_time'] = $order_info['pay_time'];

        // 订单中的商品
        foreach ( $order_info['orders']['order'] as $k=>$v ) {
            foreach ( $v['order_items']['item'] as $order_items_k=>$order_items_v ) {
                $product = $this->db->selectrow("SELECT product_id,name,cost,goods_id,bn FROM sdb_products WHERE product_id='{$order_items_v['sku_id']}'");
                $goods = $this->db->selectrow("SELECT type_id FROM sdb_goods WHERE goods_id='{$product['goods_id']}'");
                if ( !$product || !$goods ) {
                    $this->api_response('fail',false,'订单中包含不存在的商品');
                }
                $order_item = array(
                    'name'=>$product['name'],'order_id'=>$order['order_id'],
                    'bn'=>$product['bn'],'type_id'=>$goods['type_id'],'product_id'=>$product['product_id'],
                    'cost'=>'', 'dly_status'=>'storage',
                    'price'=> $order_items_v['price'], 'amount'=> $order_items_v['price']*$order_items_v['num'],
                    'nums' => $order_items_v['num'],'addon'=>'','minfo'=>'',
                );
                $this->orderMdl->addItem($order_item);
                
                //订单列表中用到这个字段表示整个订单的内容概要
                $order['tostr'] .= addslashes($order_item['name'])."({$order_items_v['num']})";
                
                // 后面会用到这里的bn name 所以提前拿出来
                $order_sku[$order_items_v['sku_id']] = array('bn'=>$product['bn'],'name'=>$product['name']);
            }
        }
        
        // 生成订单
        $order_tmp = $this->db->exec("SELECT * FROM sdb_orders WHERE order_id='{$order['order_id']}'",true,true);
        $sql = $this->db->GetUpdateSql($order_tmp,$order,true);
        if ( !$this->db->exec($sql,true,true) ) {
            $this->api_response('fail',false,'创建订单失败');
        }
        
        //订单数量统计
        $statusMdl =& $this->system->loadModel('system/status');
        $statusMdl->add('ORDER_NEW');
        $statusMdl->count_order_to_pay();
        $statusMdl->count_order_new();
        
        // 如果订单已经结束则改变用户积分情况
        if ( 'TRADE_FINISHED' == $order['status'] ) {
            $oMemberPoint=$this->system->loadModel('trading/memberPoint');
            $oMemberPoint->payAllGetPoint($order['member_id'],$order['order_id']);
        }
        
        //给订单添加tag
        $modTag = $this->system->loadModel('system/tag');
        $mobile_tag_id = $modTag->getTagByName('order','无线商城');
        if(!$modTag->getTagRel($mobile_tag_id,$order['order_id'])){
            $modTag->addTag($mobile_tag_id,$order['order_id']);
        }

        //如果订单已经支付则生成收款单
        if( 1 == +$order['pay_status'] ) {
            $paymentMdl =& $this->system->loadModel('trading/payment');
            $bill_exists = !!$paymentMdl->getOrderBillList($order['order_id']);
            if( !$bill_exists ) { // 如果还不存在收款单测生成收款单
                $bill = array(
                    'member_id'=>$order['member_id'],'order_id' => $order['order_id'],
                    't_end'=>strtotime($order_info['created']),'t_begin'=>strtotime($order_info['created']),
                    'bank'=>'alipay','currency'=>'CNY','money'=>$order['total_amount'],'cur_money'=>$order['total_amount'],
                    'payment_id'=>$paymentMdl->gen_id(),'payment'=>$order['payment'],
                    'status'=>'succ','memo'=>'此订单支付来自于移动平台',
                );
                $pay_conf = $paymentMdl->getPaymentById($order['payment']);
                $bill['paymethod'] = $pay_conf['custom_name'];
                $pay_tmp = $this->db->exec("SELECT * FROM sdb_payments WHERE 0",true,true);
                $pay_sql = $this->db->GetInsertSql($pay_tmp,$bill,true);
                $this->db->exec($pay_sql,true,true);
            }
        }

        //如果订单已发货则生成发货单
        if( 1 == +$order['ship_status'] ) {
            $deliveryMdl = $this->system->loadModel('trading/delivery');
            $ship_exists = $this->db->select("SELECT 1 FROM sdb_delivery WHERE order_id='{$order['order_id']}'");
            if( !$ship_exists ) {
                $ship = array(
                    'order_id'=>$order['order_id'],'ship_addr'=>$order['ship_addr'],'ship_zip'=>$order['ship_zip'],
                    'ship_tel'=>$order['ship_tel'],'ship_mobile'>$order['ship_mobile'],'ship_name'=>$order['ship_name'],
                    'ship_area'=>$order['ship_area'],'member_id'=>$order['member_id'],'money'=>$order['cost_freight'],
                    'type' => 'delivery','is_protect' => 'false',
                );
                $ship['delivery_id'] = $deliveryMdl->gen_id();
                $ship['delivery'] = isset($order['shipping'])?$order['shipping']:'不需要物流';
                $ship['logi_id'] = 'other';
                $ship['logi_name'] = isset($order['shipping'])?$order['shipping']:'不需要物流';
                $ship['logi_no'] = $order['shipping_id'];
                $ship['ship_name'] = $order_info['receiver_name'];
                $ship['t_begin'] = strtotime($order_info['created']);

                $ship_tmp = $this->db->exec("SELECT * FROM sdb_delivery WHERE 0",true,true);
                $ship_sql = $this->db->GetUpdateSql($ship_tmp,$ship,true);
                if( !$this->db->exec($ship_sql) ) {
                    $this->api_response('fail',false,'生成物流单失败');
                }
                
                // 生成发货单内货物
                foreach($order_info['orders']['order'] as $orders_k=>$orders_v){
                    foreach($orders_v['order_items']['item'] as $items_k=>$items_v){
                        $ship_item = array(
                            'delivery_id'=>$ship['delivery_id'],'item_type'=>'goods','product_id'=>$items_v['sku_id'],
                            'product_bn'=>$order_sku[$items_v['sku_id']]['bn'],'product_name'=>$order_sku[$items_v['sku_id']]['name'],
                            'number'=>$items_v['num'],
                        );
                        $ship_item_tmp = $this->db->exec("SELECT * FROM sdb_delivery_item WHERE 0",true,true);
                        $ship_item_sql = $this->db->GetUpdateSql($ship_item_tmp,$ship_item,true);
                        $this->db->exec($ship_item_sql,true,true);
                    }
                }
            }
        }

        $this->api_response('true',array('tid'=>$order['order_id'],'order_refer_id'=>$order['order_refer_id']));
    }
    
    /**
     *订单状态转换
     *@author lushengchao
     *@date 2011-8-8
     *@params  $status 订单状态
     */
    function status2local($status){
        $array=array('TRADE_ACTIVE'=>array("status"=>'active',"user_status"=>'null','confirm'=>'N'),
        'TRADE_FINISHED'=>array("status"=>'finish',"user_status"=>'shipped','confirm'=>'Y'),
        'TRADE_CLOSED'=>array("status"=>'dead',"user_status"=>'null','confirm'=>'N'));
        return $array[$status];
    }
    
    /**
     *支付状态转换
     *@author lushengchao
     *@date 2011-8-8
     *@params  $pay_status 订单状态
     */
    function pay_status2local($pay_status){
        $array=array('PAY_NO'=>array("pay_status"=>0),
        'PAY_FINISH'=>array("pay_status"=>1),
        'PAY_TO_MEDIUM'=>array("pay_status"=>2),
        'PAY_PART'=>array("pay_status"=>3),
        'REFUND_PART'=>array("pay_status"=>4),
        'REFUND_ALL'=>array("pay_status"=>5));
        return $array[$pay_status];
    }
    
    /**
     *发货状态转换
     *@author lushengchao
     *@date 2011-8-8
     *@params  $ship_status 订单状态
     */
    function ship_status2local($ship_status){
        $array=array('SHIP_NO'=>array("ship_status"=>0),
        'SHIP_PREPARE'=>array("ship_status"=>0),
        'SHIP_PART'=>array("ship_status"=>2),
        'SHIP_FINISH'=>array("ship_status"=>1),
        'RESHIP_PART'=>array("ship_status"=>3),
        'RESHIP_ALL'=>array("ship_status"=>4));
        return $array[$ship_status];
    }
    
     /**
     *是否实体配送状态转换
     *@author lushengchao
     *@date 2011-8-8
     *@params  $is_delivery 是否实体配送
     */
    function is_delivery_status2local($is_delivery){
        $array=array('true'=>array("is_delivery"=>'Y'),
        'false'=>array("is_delivery"=>'N'));
        return $array[$is_delivery];
    }

    /**
    修改该方法前要确认搞清楚了$data的数据结构
    */
    function trade_order_update($data){
        $order_info = json_decode($data['trade_order'],true);
        if( !$order_info || !$order_info['tid'] ) {
            $this->api_response('fail',false,'缺少有效的订单内容');
        }
        // $order_info['tid'] 是易开店的订单号
        $local_order = $this->orderMdl->getFieldById($order_info['tid'],array('total_amount','member_id','order_id','order_refer_id','payment'));
        if ( !$local_order ) {
            $this->api_response('fail',false,"订单{$order_info['tid']}不存在");
        }
        // 检查 订单-用户 是否匹配
        $member = $this->memberMdl->getMemberByUser($order_info['buyer_uname']);
        if ($member['member_id'] != $local_order['member_id'] ) {
            //$this->api_response('fail',false,'订单中用户名不匹配');
        }
        
        // 更新订单支付状态
        $order = array(
            'pay_status'=>'1','status'=>'active',
            'payed'=>$local_order['total_amount'],'payment'=>$local_order['payment'],
        );
        $order_tmp = $this->db->exec("SELECT * FROM sdb_orders WHERE order_id='{$local_order['order_id']}'",true,true);
        $sql = $this->db->GetUpdateSql($order_tmp,$order,true);
        $this->db->exec($sql,true,true);
        
        //如果订单已经支付则生成收款单
        if( 1 == +$order['pay_status'] ) {
            $paymentMdl =& $this->system->loadModel('trading/payment');
            $bill_exists = !!$paymentMdl->getOrderBillList($order_info['tid']);
            if( !$bill_exists ) { // 如果还不存在收款单测生成收款单
                $bill = array (
                    'member_id'=>$local_order['member_id'],'order_id' => $local_order['order_id'],
                    't_end'=>time(),'t_begin'=>time(),
                    'bank'=>'alipay','currency'=>'CNY','money'=>$local_order['total_amount'],'cur_money'=>$local_order['total_amount'],
                    'payment_id'=>$paymentMdl->gen_id(),'payment'=>$local_order['payment'],
                    'status'=>'succ','memo'=>'此订单支付来自于移动平台',
                );
                $pay_conf = $paymentMdl->getPaymentById($bill['payment']);
                $bill['paymethod'] = $pay_conf['custom_name'];
                $pay_tmp = $this->db->exec("SELECT * FROM sdb_payments WHERE 0",true,true);
                $pay_sql = $this->db->GetInsertSql($pay_tmp,$bill,true);
                $this->db->exec($pay_sql,true,true);
            }
        }
        $this->api_response('true',array('tid'=>$local_order['order_id'],'order_refer_id'=>$local_order['order_refer_id']));
    }
}
