<?php
class entity_order extends entity{

    function &export_sdf_array($id){
        $order = &$this->system->loadModel('trading/order');
        $payment = &$this->system->loadModel('trading/payment');
        
        $order_info = $order->getFieldById($id);
        $item_list['items'] = $order->getItemList($id);
        $payment_list['payment_record'] = $payment->_getByOrderId($id);
        $delievry['delievry'] = $order->_getShipment($id);
        $shipmentItems['shipment_item'] = $order->_getShipmentItem($delievry['delievry']['delivery_id']);
        $delievry['delievry'] = array_merge($delievry['delievry'],$shipmentItems);
        array_push($shipment['shipment'],$delievry['delievry']);
                
        $order_info = array_merge($order_info,$item_list);
        $order_info = array_merge($order_info,$payment_list);
        $order_info = array_merge($order_info,$shipment);

        $j_order_arr = $this->_getOrder($order_info,$this->_map_schema());
        
        return $j_order_arr;
    }
    
    function _map_schema(){        
        $order_schema = array(
            'order_id'=>array('order_id'),
            'title'=>array('tostr'),
            'member_id'=>array('member_id'),
            'createtime'=>array('createtime'),
            
            'shipment_want'=>array('shipping_id','shipping','cost_freight'),
            'payment_want'=>array('payment','cost_payment'),
            'cost'=>array('cost_tax','tax_company'),
            'status'=>array('status'),
            'meta'=>array('itemnum','acttime','is_delivery','acttime','refer_id','refer_url','ip',
            'is_tax','is_protect','cost_protect',
            'cur_rate','score_u','score_g','score_e','advance','discount','use_pmt','total_amount','final_amount',
            'pmt_amount','payed','markstar','memo','print_status','mark_text','disabled',
            'use_registerinfo','mark_type','extend','is_has_remote_pdts','cur_name'),
            
            'consignee'=>array('ship_name','ship_addr','ship_zip','ship_tel','ship_email','ship_mobile','ship_area'),
            
            'order_item'=>array('send','nums','name','product_id','good_id','bn','dly_status','thumbnail_pic','price'),
            
            'payment'=>array('cur_money','pay_type','currency','memo','paycost','paymethod'),
            
            'shipment'=>array('money','delivery','type',),
            'shipment_item'=>array('product_id','product_name','number'),

        );
        return $order_schema;
    }

    function import_sdf_array(&$sdf_array,$type){
        
        $array = array();
        foreach ($sdf_array[$type] as $k=>$v){
            switch($k){
                case 'order_id':
                    $tmp_oid[$k] = $v;
                    $array = array_merge($array,$tmp_oid);
                break;
                case 'currency':
                    $tmp_curr[$k]=$v;
                    $array = array_merge($array,$tmp_curr);
                break;
                case 'title':
                    $tmp_title['tostr'] = $v;
                    $array = array_merge($array,$tmp_title);
                break;
                case 'last_modified':
                    $tmp_lastm['last_change_time'] = $v;
                    $array = array_merge($array,$tmp_lastm);
                break;
                
                case 'member_id':
                    $tmp_mid[$k] = $v;
                    $array = array_merge($array,$tmp_mid);
                break;
                
                case 'cost':
                    foreach($v as $tax_k=>$tax_v){
                        $tmp_tax['tax_company'] = $v['title'];
                        $tmp_tax['cost_tax'] = $v['value'];
                    }
                    $array = array_merge($array,$tmp_tax);
                break;
                
                case 'createtime':
                    $tmp_createtime[$k] = $v;
                    $array = array_merge($array,$tmp_createtime);
                break;
                
                case 'status':
                    $tmp_status[$k] = $v;
                    $array = array_merge($array,$tmp_status);
                break;
                
                case 'meta':
                if(count($v)>0){
                    foreach($v as $meta_k=>$meta_v){
                        $tmp_m[$meta_v['key']] = $meta_v['value'];
                    }
                    $array = array_merge($array,$tmp_m);
                }
                break;
                
                case 'shipment_want':
                    foreach($v as $s_want_v){
                        $tmp_swant['shipping_id'] = $v['id'];
                        $tmp_swant['cost_freight'] = $v['cost'];
                        $tmp_swant['shipping'] = $v['value'];    
                    }
                    $array = array_merge($array,$tmp_swant);
                break;
                
                case 'payment_want':
                    foreach($v as $p_want_v){
                        $tmp_p['cost_payment'] = $v['cost'];
                        $tmp_p['payment'] = $v['id'];
                    }
                    $array = array_merge($array,$tmp_p);
                break;
                case 'consignee':
                    foreach($v as $ck=>$cv){
                        $tmp_c['ship_'.$ck]=$cv;
                    }
                    $array = array_merge($array,$tmp_c);
                    
                break;
                
                case 'order_item':
                $items['items'] = array();
                    foreach($v as $order_k=>$order_v){
                        foreach($order_v as $ok=>$ov){
                            unset($order_v['url']);
                            if($ok=='shipped'){
                                $tmp_oi['sendnum']=$ov;
                            }elseif($ok=='quantity'){
                                $tmp_oi['nums'] = $ov;
                            }elseif($ok=='product_name'||$ok=='product_price'||$ok=='product_status'){
                                $tmp_oi['name'] = $order_v['product_name'];
                                $tmp_oi['price'] = $order_v['product_price'];
                                $tmp_oi['dly_status'] = $order_v['product_status'];
                            }else{
                                $tmp_oi[$ok] = $ov;
                            }
                        }
                        array_push($items['items'],$tmp_oi);
                    }
                    $array = array_merge($array,$items);
                break;
                
                case 'payment':
                    $payment['payment_record'] = array();
                    foreach($v as $pay_k=>$pay_v){
                        $tmp_pay['paymethod'] = $pay_v['cost']['title'];
                        $tmp_pay['pay_type'] = $pay_v['cost']['type'];
                        $tmp_pay['paycost'] = $pay_v['cost']['value'];
                        $tmp_pay['currency'] = $pay_v['money']['currency'];
                        $tmp_pay['cur_money'] = $pay_v['money']['value'];
                        array_push($payment['payment_record'],$tmp_pay);
                        
                    }
                    $array = array_merge($array,$payment);
                break;
                
                case 'shipment':
                   $ship['shipment'] = array();
                   
                    foreach($v as $ship_k=>$ship_v){
                        $tamp = array();
                        $shipment['shipment_item'] = array();
                        foreach($ship_v as $sa=>$sv){
                            if($sa=='cost'){
                                $tmp_ship['delivery'] = $sv['title'];
                                $tmp_ship['type'] = $sv['type'];
                                $tmp_ship['money'] = $sv['value'];
                            }elseif($sa == 'ship_item'){
                                foreach($sv as $sitem_k=>$sitem_v){
                                    $sitem['product_id'] = $sitem_v['product_id'];
                                    $sitem['num'] = $sitem_v['quantity'];
                                    $sitem['product_name'] = $sitem_v['value'];
                                    array_push($shipment['shipment_item'],$sitem);
                                }
                            }    
                        }
                        $tamp = array_merge($tamp,$tmp_ship);
                        $tamp = array_merge($tamp,$shipment);
                        array_push($ship['shipment'],$tamp);
                    }
                    $array = array_merge($array,$ship);
                break;
                
                case 'event':
                    $order_log['order_log'] = array();
                    foreach($v as $ek=>$ev){
                        $tmp_ol['acttime'] = $ev['time'];
                        $tmp_ol['log_text'] = $ev['value'];
                        array_push($order_log['order_log'],$tmp_ol);
                    }
                    $array = array_merge($array,$order_log);
                break;
            }
        }
        
        $items_data = $array['items'];
        $payment_data = $array['payment_record'];
        $shipment_data = $array['shipment'];
        $o_log = $array['order_log'];
        
        $new_payment = &$this->system->loadModel('trading/payment');
        
        unset($array['items']);
        unset($array['payment_record']);
        unset($array['shipment']);
        unset($array['order_log']);
        $new_order_id = date("Ymd").rand(100000,999999);
        $array['order_id'] = $new_order_id;
        $foreign['order_id'] = $new_order_id;
        //unset($array['order_id']);
        $aRs = $this->db->query("SELECT * FROM sdb_orders WHERE 0");
        $aRs_payment = $this->db->query("SELECT * FROM sdb_payments WHERE 0");
        $aRs_oitems = $this->db->query("SELECT * FROM sdb_order_items WHERE 0");
        $aRs_olog = $this->db->query("SELECT * FROM sdb_order_log WHERE 0");
        
        
        $sSql = $this->db->getInsertSql($aRs,$array);
        $this->db->exec($sSql);
        foreach($o_log as $log_k=>$log_v){
            $log_v = array_merge($log_v,$foreign);
            $sSql_olog = $this->db->getInsertSql($aRs_olog,$log_v);
            $this->db->exec($sSql_olog);
        }
        foreach($payment_data as $payment_log){
            $new_payment_id['payment_id'] = $new_payment->gen_id();
            $payment_log = array_merge($payment_log,$new_payment_id);
            $payment_log = array_merge($payment_log,$foreign);
            $sSql_payment = $this->db->getInsertSql($aRs_payment,$payment_log);
            $this->db->exec($sSql_payment);
        }
        
        
        foreach($items_data as $items_list){
            $items_list = array_merge($items_list,$foreign);
            unset($items_list['thumbnail_pic']);
            $sSql_items = $this->db->getInsertSql($aRs_oitems,$items_list);
            $this->db->exec($sSql_items);
        }
        return $new_order_id;
    }

    function _getOrder($arr,$map_arr){
        $array['order'] = array();

        $order_id = $this->_getOrderId($arr,$map_arr);
        $currency = $this->_getCurrency($arr);
        $title = $this->_getTitle($arr,$map_arr);
        $member_id = $this->_getOrderMemberId($arr,$map_arr);
        $createtime = $this->_getOrderCreatetime($arr,$map_arr);
        $shipment_want = $this->_getShipmentWant($arr,$map_arr);
        $payment_want = $this->_getPaymentWant($arr,$map_arr);
        $status = $this->_getOrderStatus($arr,$map_arr);
        $event = $this->_getEvent($arr);
        $consignee = $this->_getConsignee($arr,$map_arr);
        $order_item = $this->_getOrderItems($arr,$map_arr);
        $meta = $this->_getOrderMeta($arr,$map_arr);
        $payment = $this->_getPayment($arr,$map_arr);
        $shipment = $this->_getShipment($arr,$map_arr);
        $url = $this->_getUrl($arr);
        $cost = $this->_getCost($arr,$map_arr);
        $last_modified = $this->_getLastmodified($arr);
        

        $array['order'] = array_merge($array['order'],$order_id);
        $array['order'] = array_merge($array['order'],$currency);
        $array['order'] = array_merge($array['order'],$member_id);
        $array['order'] = array_merge($array['order'],$title);
        $array['order'] = array_merge($array['order'],$createtime);
        $array['order'] = array_merge($array['order'],$meta);
        $array['order'] = array_merge($array['order'],$shipment_want);
        $array['order'] = array_merge($array['order'],$payment_want);
        $array['order'] = array_merge($array['order'],$status);
        $array['order'] = array_merge($array['order'],$event);
        $array['order'] = array_merge($array['order'],$consignee);
        $array['order'] = array_merge($array['order'],$order_item);
        $array['order'] = array_merge($array['order'],$payment);
        $array['order'] = array_merge($array['order'],$shipment);
        $array['order'] = array_merge($array['order'],$url);
        $array['order'] = array_merge($array['order'],$cost);
        $array['order'] = array_merge($array['order'],$last_modified);

        return $array;

    }
    function _getCurrency($arr){
        foreach($arr as $k=>$v){
            if($k=='currency'){
                $currency[$k] = $v;
            }
        }
        return $currency;
    }
    function _getTitle($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['title'])){
                $title['title'] = $v;
            }
        }
        return $title;
    }
    function _getLastmodified($arr){
        foreach($arr as $k=>$v){
            $last_modified['last_modified'] = $arr['last_change_time'];
        }
        return $last_modified;
    }
    function _getUrl($arr){
        $url['url'] = 'http://127.0.0.1/485/src/shopadmin/index.php?ctl=order/order&act=showEdit&p[0]='.$arr['order_id'];
        return $url;
    }
    function _getCost($arr,$map_arr){
        foreach($arr as $k=>$v){
            $cost['title'] = $arr['tax_company'];
            $cost['value'] = $arr['cost_tax'];
        }
        $tmp['cost'] = array();
        $tmp['cost'] = array_merge($tmp['cost'],$cost);
        return $tmp;
    }

    function _getOrderMeta($arr,$map_arr){
        $meta_arr['meta'] = array();
        if(is_array($arr)){
            foreach($arr as $k=>$v){
                if(in_array($k,$map_arr['meta'])){
                    $tmp = array('key'=>$k,'value'=>$v);
                    array_push($meta_arr['meta'],$tmp);
                }
            }
        }
        return $meta_arr;
    }

    function _getOrderId($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['order_id'])){
                $orderId[$k]=$v;
            }
        }
        return $orderId;
    }

    function _getOrderMemberId($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['member_id'])){
                $orderMemberId[$k]=$v;
            }
        }
        return $orderMemberId;
    }

    function _getOrderCreatetime($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['createtime'])){
                $createtime[$k]=$v;
            }
        }
        return $createtime;
    }

    function _getOrderStatus($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['status'])){
                $status[$k]=$v;
            }
        }
        return $status;
    }

    function _getConsignee($arr,$map_arr){
        $consignee = array();
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['consignee'])){
                $tmp[substr_replace($k,'',0,5)] = $v;
            }
        }
        $consignee['consignee'] = array_merge($consignee,$tmp);
        return $consignee;
    }
    function _getOrderItems($arr,$map_arr){
        $order_item = array();
        $objOrderItem['order_item'] = array();
        foreach($arr as $k=>$v){
            if($k=='items'){
                foreach ($v as $kk=>$vv){
                    foreach($vv as $key=>$value){
                        if(in_array($key,$map_arr['order_item'])){
                            if($key=='send'){
                                $tmp['shipped'] = $value;
                            }elseif($key == 'nums'){
                                $tmp['quantity'] = $value;
                            }elseif($key=='name'){
                                $tmp['product_name'] = $value;
                            }elseif($key == 'dly_status'){
                                $tmp['product_status'] = $value;
                            }elseif($key=='price'){
                                $tmp['product_price'] = $value;
                            }elseif($key=='thumbnail_pic'){
                                $height_row = $this->db->selectrow('SELECT src_size_height FROM sdb_gimages WHERE goods_id=\''.$vv['goods_id'].'\' AND thumbnail=\''.$value.'\'');
                    $width_row = $this->db->selectrow('SELECT src_size_width FROM sdb_gimages WHERE goods_id=\''.$vv['goods_id'].'\' AND thumbnail=\''.$value.'\'');
                                $tmp['thumbnail'] = array('width'=>$width_row['src_size_width'],'height'=>$height_row['src_size_height'],'value'=>$value);
                            }else{
                                $tmp[$key] = $value;
                                $tmp['url'] = 'http://127.0.0.1/shopex485/src/shopAdmin/index.php?ctl=order/order&act=showEdit&p[0]='.$arr['order_id'];
                            }
                        }
                    }
                    array_push($order_item,$tmp);
                }
            }
        }
        $objOrderItem['order_item'] = array_merge($objOrderItem['order_item'],$order_item);
        return $objOrderItem;
    }

    function _getShipmentWant($arr,$map_arr){
        $shipment_want['shipment_want'] = array();
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['shipment_want'])){
                if($k=='cost_freight'){
                    $tmp['cost'] = $v;
                }elseif($k=='shipping_id'){
                    $tmp['id'] = $v;
                }else{
                    $tmp['value'] = $v;
                }
            }
        }
        $shipment_want['shipment_want'] = array_merge($shipment_want['shipment_want'],$tmp);
        return $shipment_want;
    }
    function _getPaymentWant($arr,$map_arr){
        $payment_want['payment_want'] = array();
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['payment_want'])){
                if($k=='cost_payment'){
                    $tmp['cost'] = $v;
                }elseif($k=='payment'){
                    $tmp['id'] = $v;
                    $rs = $this->db->select('SELECT pay_type FROM sdb_payment_cfg WHERE id =\''.$v.'\'');
                    $tmp['value'] = $rs[0]['pay_type'];
                }
            }
        }
        $payment_want['payment_want'] = array_merge($payment_want['payment_want'],$tmp);
        return $payment_want;
    }
    function _getPayment($arr,$map_arr){
        $payment['payment'] = array();
        $temp_payment = array();
        foreach($arr as $k=>$v){
            if($k=='payment_record'){
                foreach($v as $kk=>$vv){
                    foreach($vv as $key=>$value){
                        if(in_array($key,$map_arr['payment'])){
                            $tmp['cost'] = array('title'=>$vv['paymethod'],'type'=>$vv['pay_type'],'value'=>$vv['paycost']);
                            $tmp['money'] = array('title'=>'人民币','currency'=>$vv['currency'],'value'=>$vv['cur_money']);
                            //$tmp['event'] = array()
                        }
                    }
                    array_push($temp_payment,$tmp);
                }
            }
        }
        $payment['payment'] = array_merge($payment['payment'],$temp_payment);
        return $payment;
    }

    function _getShipment($arr,$map_arr){
        $ship_item['ship_item'] = array();
        $shipment['shipment'] = array();
        foreach($arr as $k=>$v){
            if($k=='shipment'){
                foreach($v as $kk=>$vv){
                    foreach($vv as $key=>$value){
                        if(in_array($key,$map_arr['shipment'])){
                            $tamp['cost'] = array('title'=>$vv['delivery'],'type'=>$vv['type'],'value'=>$vv['money']);
                        }elseif($key=='shipment_item'){
                            foreach($value as $a=>$b){
                                foreach($b as $aa=>$bb){
                                    $tmp['shipitem'] = array('product_id'=>$b['product_id'],'quantity'=>$b['num'],'value'=>$b['product_name']);
                                }
                                array_push($ship_item['ship_item'],$tmp['shipitem']);
                            }
                        }
                    }
                    $temp_shipment = array_merge($tamp,$ship_item);
                    array_push($shipment['shipment'],$temp_shipment);
                    $ship_item['ship_item'] = array();
                }
            }
        }
        return $shipment;
    }
    
    function _getEvent($arr){
        $orderlog_list = $this->db->select('SELECT log_text,acttime FROM sdb_order_log WHERE order_id =\''.$arr['order_id'].'\'');
        $order_log['event'] = array();
        foreach($orderlog_list as $k=>$v){
            $tmp_log['time'] = $v['acttime'];
            $tmp_log['value'] = $v['log_text'];
            array_push($order_log['event'],$tmp_log);
        }
        return $order_log;
    }
    
}

?>