<?php
include_once CORE_DIR.'/api/shop_api_object.php';

class api_delivery extends shop_api_object {
    
    // 创建发货单、退货单
    function create($data){
        $ac_type = 'return' == $data['type'] ? 'return':'delivery';
        
        $order_id = $data['order_id'];
        
        $order = $this->db->selectrow('SELECT status,ship_status FROM sdb_orders WHERE order_id='.$this->db->quote($order_id));
        if ( !$order || 'active' != $order['status'] ) {
            $this->api_response('fail','data fail',null,'订单不存在或不再活动');
        }
        if ( ('delivery'==$ac_type) && (SHIPMENT_SHIPOUT === +$order['ship_status']) ) {
            $this->api_response('fail','data fail',null,'订单已全部发货');
        }
        if ( ('return'==$ac_type) && (SHIPMENT_RETURN === +$order['ship_status']) ) {
            $this->api_response('fail','data fail',null,'订单已退货');
        }
        
        if ( !$delivery_id = $data['delivery_id'] ) {
            $this->api_response('fail','data fail',null,'缺少发货/退货单');
        }
        $delivery_ = $this->db->selectrow('SELECT delivery_id FROM sdb_delivery WHERE delivery_id='.$delivery_id);
        if ( $delivery_ ) {
            $this->api_response('fail','data fail',null,'发货单/退货单已存在');
        }
        
        if ( !$member=$this->db->selectrow('SELECT member_id,uname FROM sdb_members WHERE uname='.$this->db->quote($data['member_id'])) ) {
            //$this->api_response('fail','data fail',null,'会员不存在');
        }
        
        if ( !$items = json_decode($data['delivery_item'],true) ) {
            $this->api_response('fail','data fail',null,'没有发货/退货商品');
        } unset($data['delivery_item']);
        
        foreach( $items as $item ) {
            $bn = $item['product_bn'];
            if ( 'goods' == $item['item_type'] ) {
                // delivery
                if ( !$product = $this->db->selectrow('SELECT product_id FROM sdb_products WHERE bn='.$this->db->quote($bn)) ) {
                    $this->api_response('fail','data fail',null,'没有发货/退货商品:'.$bn);
                }
                $product_id = $product['product_id'];
                $item_type = 'goods';
            } elseif ( 'gift' == $item['item_type'] ) {
                if ( !$gift = $this->db->selectrow('SELECT gift_id FROM sdb_gift WHERE gift_bn='.$this->db->quote($bn)) ) {
                    $this->api_response('fail','data fail',null,'没有发货/退货赠品:'.$bn);
                }
                $product_id = $gift['gift_id'];
                $item_type = 'gift';
            }
            
            // 生成发货/退货记录 sdb_delivery_item
            $log = array(
                'delivery_id'=>$delivery_id,
                'item_type'=>$item_type,
                'product_id'=>$product_id,
                'product_bn'=>$bn,
                'product_name'=>$item['product_name'],'number'=>$item['number'],
            );
            $this->db->insert('sdb_delivery_item', $log);
        }
        
        $ship_area = "mainland:{$data['ship_state']}/{$data['ship_city']}";
        if ( $data['ship_district'] ) {
            $ship_area .= "/{$data['ship_district']}";
        }
        // 生成发货/退货单
        $delivery = array(
            'delivery_id'=>$delivery_id,
            'order_id'=>$order_id,
            'member_id'=>$member['member_id'],
            'status'=>'progress',
            'logi_no'=>$data['logi_no'],
            'logi_id'=>$data['logi_id'],
            'logi_name'=>$data['logi_name'],
            
            'ship_name'=>$data['ship_name'],
            'ship_mobile'=>$data['ship_mobile'],
            'ship_tel'=>$data['ship_tel'],
            'ship_zip'=>$data['ship_zip'],
            'ship_addr'=>$data['ship_addr'],
            'delivery'=>$data['delivery'],
            'ship_area'=>$ship_area,
            'type'=>$ac_type,
            't_begin'=>$data['t_begin'] ? $data['t_begin'] : NOW,
        );
        $this->db->insert('sdb_delivery',$delivery);
        
        if ( 'return' == $ac_type ) { // 退货改为只打一次会话
            $update = array(
                'delivery_id'=>$delivery_id,'ship_status'=>'succ',
            );
            return $this->update($update);
        }
        
        $this->api_response('true',null,'发货单创建成功');
    }
    
    // 仅修改发货单：物流公司,物流单号,发货状态
    function update($data) {
        if ( !$delivery_id = $data['delivery_id'] ) {
            $this->api_response('fail','data fail',null,'缺少发货单号');
        }
        $delivery_ = $this->db->selectrow("SELECT order_id,type,status FROM sdb_delivery WHERE disabled='false' AND delivery_id=".$this->db->quote($delivery_id));
        if ( !$delivery_ ) {
            $this->api_response('fail','data fail',null,'发货单不存在');
        }
        
        if( !$status = strtolower($data['ship_status']) ) { // 只修改物流单号
            $logi_id = $this->db->quote($data['logi_id']);
            $logi_no = $this->db->quote($data['logi_no']);
            $logi_name = $this->db->quote($data['logi_name']);
            $this->db->query("UPDATE sdb_delivery SET logi_id=$logi_id,logi_no=$logi_no,logi_name=$logi_name WHERE delivery_id=".$this->db->quote($delivery_id));
            $this->api_response('true',null,'修改物流信息成功');
        }
        
        if ( !in_array($status, array('succ','cancel','progress')) ) {
            $this->api_response('fail','data fail',null,'发货状态必须是:succ,cancel,progress');
        }
        
        if ( 'progress' == $status ) {
            $this->api_response('true',null,'成功');
        }
        
        $order_id = $delivery_['order_id'];
        
        if ( 'cancel' == $status ) {
            if ( 'succ' == $delivery_['status'] ) {
                $this->api_response('fail','data fail',null,'发货单已发货');
            }
            if ( 'cancel' == $delivery_['status'] ) {
                $this->api_response('fail','data fail',null,'发货单已取消');
            }
            $this->db->query('UPDATE sdb_delivery SET status="cancel" WHERE delivery_id='.$this->db->quote($delivery_id));
            $this->api_response('true',null,'发货单取消成功');
        }
        
        // 修改 sdb_order_items
        $tmp = $this->db->select('SELECT item_id,product_id,nums,sendnum,addon FROM sdb_order_items WHERE sendnum<nums AND order_id='.$this->db->quote($order_id));
        for($i=count($tmp);$i>0;$items_[]=$tmp[$i-1],$i--); unset($tmp);
        
        $items = $this->db->select('SELECT product_id,number,item_type FROM sdb_delivery_item WHERE delivery_id='.$this->db->quote($delivery_id));
        if ( !$items ) {
            $this->api_response('fail','system error',null,'发货单数据丢失');
        }
        foreach( $items as $item ) {
            $product_id = +$item['product_id'];
            $sendnum_ = ('return'==$delivery_['type']) ? -$item['number']:+$item['number'];
            $converted = false; // 记录 $sendnum_ 正负号的转变 以终止循环
            if ( 'gift' == $item['item_type'] ) {
                $this->db->query("UPDATE sdb_gift_items SET sendnum=sendnum+$sendnum_ WHERE gift_id=$product_id AND order_id=".$this->db->quote($order_id));
            } else { // 普通商品的发货要考虑配件和捆绑
                foreach( $items_ as $tmp_ ) {
                    if ( (0 == $sendnum_) || $converted ) break;

                    if ( $product_id != $tmp_['product_id'] ) { // 配件 捆绑
                        $adjs[$tmp_['item_id']] = $tmp_['item_id']; continue;
                    } else {
                        unset($adjs[$tmp_['item_id']]);
                    }
                    
                    if ( $sendnum_ >= 0 ) { // 发货
                        $sendnum__ = min($sendnum_,$tmp_['nums']-$tmp_['sendnum']);
                        $converted = ($sendnum_ < 0);
                    } else { // 退货
                        $sendnum__ = max($sendnum_,0-$tmp_['sendnum']);
                        $converted = ($sendnum_ > 0);
                    }
                    
                    $this->db->query("UPDATE sdb_order_items SET sendnum=sendnum+$sendnum__ WHERE item_id={$tmp_['item_id']}");
                }
                // 普通商品发货后sendnum_仍有余 则考虑 配件和捆绑商品的发货
                if ( (0 == $sendnum_) || $converted || !$adjs ) continue;
                foreach( $adjs as $adj ) {
                    if ( !$adj = (int)$adj ) continue;
                    //unserialize($tmp_['addon']);
                    $fuzzy = ($sendnum_ > 0) ? 'nums':0; // 'nums'这里对应表的`nums`列
                    $this->db->query("UPDATE sdb_order_items SET sendnum=$fuzzy WHERE item_id=$adj");
                }
            }
        }
        
        // 检查是否部分发货 如果订单cancel则要保证跟新 sdb_order_items
        if ( $this->db->select('SELECT 1 FROM sdb_order_items WHERE nums-sendnum>0 AND order_id='.$this->db->quote($order_id)) ||
            $this->db->select('SELECT 1 FROM sdb_gift_items WHERE nums-sendnum>0 AND order_id='.$this->db->quote($order_id)) ) {
            $ship_status = SHIPMENT_SHIPOUT_PART;
        } else {
            $ship_status = SHIPMENT_SHIPOUT;
        }
        
        $now = NOW;
        // 发货单发货
        $this->db->query("UPDATE sdb_delivery SET status='succ',t_end=$now WHERE delivery_id=".$this->db->quote($delivery_id));
        
        // 更新订单发货状态
        $this->db->query("UPDATE sdb_orders SET ship_status='$ship_status',last_change_time=$now WHERE order_id=".$this->db->quote($order_id));
        
        if($ship_status == 1){//完全发货
            $objPayment = $this->system->loadModel('trading/payment');
            $objPayment->sync_ship_status_to_taobao($order_id);
        }
        
        $orderMdl = $this->system->loadModel('trading/order');
        // 订单日志
        $orderMdl->_info['order_id'] = $order_id;
        $orderMdl->addLog('发货',null,null,'订单发货');
        
        $this->system->loadModel('trading/order')->fireEvent('editorder',array('order_id'=>$order_id));
        
        $this->update_order($order_id);//add by tt
        
        $this->api_response('true',null,'发货成功');
    }
    
    /**
     * 重新验证订单发货状态
     * @param unknown_type $order_id
     */
    function update_order($order_id){
        //获取订单发货/退货商品信息
        $delivery_pdc_list = array();
        $delivery_gift_list = array();
        $return_pdc_list = array();
        $sql = "SELECT  sd.type,sdi.*  FROM sdb_delivery sd
                   LEFT JOIN sdb_delivery_item sdi  ON(sd.delivery_id=sdi.delivery_id)
                   WHERE sd.order_id ='".$order_id."'";
        $delivery_item_list = $this->db->select($sql);
        if($delivery_item_list){
            foreach($delivery_item_list as $k =>$item_info){
                if($item_info['type']=='delivery'){
                    if($item_info['item_type']=='gift'){
                        //品用ID号记
                        $delivery_gift_list[$item_info['product_bn']]=isset($delivery_gift_list[$item_info['product_bn']]) ? $delivery_gift_list[$item_info['product_bn']] + $item_info['number'] : $item_info['number'];
                    }else{
                        $delivery_pdc_list[$item_info['product_bn']]=isset($delivery_pdc_list[$item_info['product_bn']]) ? $delivery_pdc_list[$item_info['product_bn']] + $item_info['number'] : $item_info['number'];
                    }
                }elseif($item_info['type']=='return'){
                    $return_pdc_list[$item_info['product_bn']]=isset($return_pdc_list[$item_info['product_bn']]) ? $return_pdc_list[$item_info['product_bn']] + $item_info['number'] : $item_info['number'];
                }
            }
        }
        
        //获取订单商品信息，统计捆绑，配件商品数量
        $pdc_list = array();
        $adj_pdc = array();
        $order_item_list = $this->db->select("SELECT  *  FROM sdb_order_items WHERE order_id ='".$order_id."' ORDER BY is_type");
        if($order_item_list){
            foreach( $order_item_list as $k => $item_info ) {
                if($item_info['is_type']=='goods'){
                    $pdc_list[$item_info['bn']] = $item_info['nums'];
                }
                //获取捆绑信息
                $addon_info = unserialize($item_info['addon']);
                if(!empty($addon_info['adjinfo']) && strpos($addon_info['adjinfo'], "|")!==false){
                    $adj_tmp = explode("|", trim($addon_info['adjinfo'],'|'));
                    foreach($adj_tmp as $a_k=>$adj_info){
                        $tmp =  explode("_", $adj_info);  //例 251_0_2  product_id+0+nums
                        $adj_pdc[$tmp[0]] = $tmp[2];
                    }
                }
                if($adj_pdc){
                    $sql = "SELECT product_id,bn FROM sdb_products WHERE product_id in(".implode(",",array_keys($adj_pdc)).")";
                    $adj_bn_list = $this->db->select($sql);
                    if($adj_bn_list){
                        foreach($adj_bn_list as $p_k =>$pdc_info){
                            $pdc_list[$pdc_info['bn']] = isset($pdc_list[$pdc_info['bn']]) ? $pdc_list[$pdc_info['bn']] + $adj_pdc[$pdc_info['product_id']] : $adj_pdc[$pdc_info['product_id']];
                        }
                    }
                }
            }
        }else{
            $this->api_response('fail','data fail',null,'订单号不存在');
        }
        
        //获取订单赠品信息
        $gift_list = array();
        $gift_ids = array();//记录赠品bn对应的ID
        $sql = "SELECT  g.gift_bn,gi.*  FROM sdb_gift_items gi
                   LEFT JOIN sdb_gift g ON(g.gift_id=gi.gift_id)
                   WHERE gi.order_id ='".$order_id."'";
        $gift_item_list = $this->db->select($sql);
        if($gift_item_list){
            foreach($gift_item_list as $k => $gift_item_info){
                $gift_list[$gift_item_info['gift_bn']] = $gift_item_info['nums'];
                $gift_ids[$gift_item_info['gift_bn']]  = $gift_item_info['gift_id'];//记录赠品bn对应的ID
            }
        }
        
        $ship_status='';
        $delivery_nums = array_sum($delivery_pdc_list) +array_sum($delivery_gift_list) ;//订单已发货出量
        $return_nums = array_sum($return_pdc_list);//订单退货数量
        $pdc_nums = array_sum($pdc_list);//订单商品总数
        if( $return_nums>0 && ($return_nums < $pdc_nums)){
            $ship_status = SHIPMENT_RETURN_PART;
        }elseif($return_nums>0 && ($return_nums >= $pdc_nums)){
            $ship_status = SHIPMENT_RETURN;
        }elseif($delivery_nums>0 && ($delivery_nums < $pdc_nums)){
            $ship_status = SHIPMENT_SHIPOUT_PART;
        }elseif($delivery_nums>0 && ($delivery_nums>=$pdc_nums)){
            $ship_status =SHIPMENT_SHIPOUT;
        }
        if($ship_status){
            $now=NOW;
            // 更新订单发货状态
            $this->db->query("UPDATE sdb_orders SET ship_status='$ship_status',last_change_time=".$now." WHERE order_id=".$this->db->quote($order_id));
        }
        
        //处理gift_item发货数量
        if($gift_item_list){
            foreach($gift_item_list as $k => $gift_item_info){
                if( isset($delivery_gift_list[$gift_item_info['gift_bn']]) ) {
                    $num = $delivery_gift_list[$gift_item_info['gift_bn']]>$gift_item_info['nums'] ? $gift_item_info['nums'] : $delivery_gift_list[$gift_item_info['gift_bn']] ; //计算发货数量
                    $this->db->query("UPDATE sdb_gift_items SET sendnum=".$num." WHERE gift_id='".$gift_ids[$gift_item_info['gift_bn']]."' AND order_id=".$this->db->quote($order_id));
                    $delivery_gift_list[$gift_item_info['gift_bn']] -= $num;
                }elseif(isset($delivery_pdc_list[$gift_item_info['gift_bn']]) ){
                    $num = $delivery_pdc_list[$gift_item_info['gift_bn']]>$gift_item_info['nums'] ? $gift_item_info['nums'] : $delivery_pdc_list[$gift_item_info['gift_bn']] ; //计算发货数量
                    $this->db->query("UPDATE sdb_gift_items SET sendnum=".$num." WHERE gift_id='".$gift_ids[$gift_item_info['gift_bn']]."' AND order_id=".$this->db->quote($order_id));
                    $delivery_pdc_list[$gift_item_info['gift_bn']] -= $num;
                }
            }
        }
        
        //处理order_item发货数量
        if($order_item_list){
            //逐个验证商品是否发货
            foreach( $order_item_list as $k => $item_info ) {
                $is_send = true;
                //获取捆绑信息
                $addon_info = unserialize($item_info['addon']);
                if(!empty($addon_info['adjinfo']) && strpos($addon_info['adjinfo'], "|")!==false){
                    $adj_tmp = explode("|", trim($addon_info['adjinfo'],'|'));
                    foreach($adj_tmp as $a_k=>$adj_info){
                        $tmp =  explode("_", $adj_info);  //例 251_0_2  product_id+0+nums
                        $adj_pdc[$tmp[0]] = $tmp[2];
                    }
                }
                if($adj_pdc){
                    $sql = "SELECT product_id,bn FROM sdb_products WHERE product_id in(".implode(",",array_keys($adj_pdc)).")";
                    $adj_bn_list = $this->db->select($sql);
                    if($adj_bn_list){
                        foreach($adj_bn_list as $p_k =>$pdc_info){
                            //检查发货数量
                            if(isset($delivery_pdc_list[$pdc_info['bn']]) ){
                                if($delivery_pdc_list[$pdc_info['bn']] < $adj_pdc[$pdc_info['product_id']]){
                                    $is_send = false;
                                }else{
                                    $delivery_pdc_list[$pdc_info['bn']] -= $adj_pdc[$pdc_info['product_id']];
                                }
                            }
                        }
                    }
                }

                if($item_info['is_type']=='goods'){
                    if(isset($delivery_pdc_list[$item_info['bn']]) && $is_send==true ){
                        if($delivery_pdc_list[$item_info['bn']] >= $item_info['nums']){
                            $num = $delivery_pdc_list[$item_info['bn']]>$item_info['nums'] ? $item_info['nums'] : $delivery_pdc_list[$item_info['bn']] ; //计算发货数量
                            $this->db->query("UPDATE sdb_order_items SET sendnum=".$num." WHERE bn='".$item_info['bn']."' AND order_id=".$this->db->quote($order_id));
                            $delivery_pdc_list[$item_info['bn']] -= $num;
                        }
                    }
                }else if($item_info['is_type']=='pkg'){
                    if($is_send==true ){
                        $this->db->query("UPDATE sdb_order_items SET sendnum=nums WHERE bn='".$item_info['bn']."' AND order_id=".$this->db->quote($order_id));
                    }
                }                
            }
        }
        
    }
    
}
