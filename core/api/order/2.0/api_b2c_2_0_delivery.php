<?php
include_once(CORE_DIR.'/api/shop_api_object.php');

class api_b2c_2_0_delivery extends shop_api_object {

    function notify_delivery($input_data){
        set_error_handler(array(&$this,'_err_handler'));        
        $token = $this->system->getConf('certificate.token');
        $supplier_id = $input_data['supplier_id'];
        
        $dealer_orderid = $input_data['dealer_order_id'];

        $_arr_delivery_ids = array();        
        $_sql = sprintf('select supplier_delivery_id from sdb_delivery where order_id=%s', $dealer_orderid);
        if ($_arr_delivery_ids = $this->db->select($_sql)){
            $_arr_delivery_ids = array_item($_arr_delivery_ids, 'supplier_delivery_id');
        }
        
        if(empty($_arr_delivery_ids)){
            $_arr_delivery_ids = array(0); //todo:临时解决问题，需要把php4环境下的json_encode(array())不正确的情况解决掉
        }
        
        $_send = array(
            'id' => $supplier_id,
            'dealer_order_id' => $dealer_orderid,
            'exists' => $_arr_delivery_ids,
            'exists_type' => 'json');
        
        $api_utility = $this->system->api_call(PLATFORM,PLATFORM_HOST,PLATFORM_PATH,PLATFORM_PORT,$token);
        $delivery_items = $api_utility->getApiData('getLogisticsList',API_VERSION,$_send,true,true,'json');
//        exit;
        $api_utility->trigger_all_errors();

        $this->ww($delivery_items);        
//        $this->ww($_send);
        
        if(!$delivery_items){
            $this->api_response('true');            
        }
        
        //获取order信息
        $_sql = sprintf('select member_id from sdb_orders where order_id=%s', $dealer_orderid);
        if ($_order_data = $this->db->selectrow($_sql)){
            $_order_info['member_id'] = $_order_data['member_id'];
            $_order_info['supplier_id'] = $supplier_id;
            $_order_info['order_id'] = $dealer_orderid;
        }else{
            $this->api_response('fail','data fail',null,'无此订单');
        }

        foreach($delivery_items as $delivery_item){
            if ($delivery_item['type'] == 'delivery'){
                $this->_delivery($_order_info, $delivery_item);
            }else if ($delivery_item['type'] == 'return'){
                $this->_return($_order_info, $delivery_item);
            }
        }
        $this->api_response('true');        
    }

    function _get_new_number($type){
        if ($type == 'return'){
            $sign = '9'.date("Ymd");
        }else{
            $sign = '1'.date("Ymd");
        }
        $sqlString = 'SELECT MAX(delivery_id) AS maxno FROM sdb_delivery WHERE delivery_id LIKE \''.$sign.'%\'';
        $aRet = $this->db->selectrow($sqlString);
        if(is_null($aRet['maxno'])) $aRet['maxno'] = 0;
        $maxno = substr($aRet['maxno'], -6) + 1;
        if ($maxno==1000000){
            $maxno = 1;
        }
        return $sign.substr("00000".$maxno, -6);
    }

    function _add_ship_bill($order_info, $delivery_item, $type='delivery'){
        $dealer_orderid = $order_info['order_id'];                
        $delivery_no = $this->_get_new_number($type);
        $_delivery_goods_items = $delivery_item['struct'];
        unset($delivery_item['struct']);
        unset($delivery_item['order_id']);

        $_data = array();
        $_data = array_merge((array)$delivery_item, (array)$order_info);

        $_data['supplier_delivery_id'] = $delivery_item['delivery_id'];
        $_data['delivery_id'] = $delivery_no;
        $_data['op_name'] = 'admin';            
        $_data['logi_id'] = null;
        unset($_data['disabled']);        
        $order_id = $order_info['order_id'];
            
        $rs = $this->db->query('SELECT * FROM sdb_delivery WHERE 0=1');
        $_data = addslashes_array($_data);
        $_sql = $this->db->GetInsertSQL($rs, $_data);

        if ($this->db->exec($_sql)){
            foreach($_delivery_goods_items as $_item){
                $_data = array(
                    'product_id'=>$_item['product_id'],
                    'delivery_id' => $delivery_no,
                    'product_bn' => $_item['dealer_bn'],
                    'item_type' => $_item['item_type'],
                    'product_name' => $_item['product_name'],//todo 用本地的货品名称
                    'number' => $_item['number'],
                    );
                $rs = $this->db->query('SELECT * FROM sdb_delivery_item WHERE 0=1');
                $_data = addslashes_array($_data);                                
                $_sql = $this->db->GetInsertSQL($rs, $_data);
                if (!$this->db->exec($_sql)){
                    $this->api_response('fail','data fail',$result,'发货单/退货单商品清单插入失败');
                }
                //更新order_items 订单发货数量
                $_sql = sprintf('update sdb_order_items set sendnum=sendnum+%d where order_id=%s and bn=\'%s\'',
                                (($type=='delivery')? 1 : -1) * $_item['number'],
                                $dealer_orderid,
                                $_item['dealer_bn']);
                $this->db->exec($_sql);
            }
        }else{
            $this->api_response('fail','data fail',null,'发货单/退货单插入失败');
        }
        //更新订单操作记录，add by hujianxin

        return $delivery_no;

    }

    function _delivery($order_info, $delivery_item){
        $dealer_orderid = $order_info['order_id'];        
        $supplier_id = $order_info['supplier_id'];
        $delivery_no = $this->_add_ship_bill($order_info, $delivery_item, 'delivery');
        $ship_status = 1;//全部发货
        $_order_items = $this->db->select('select nums,sendnum from sdb_order_items where order_id='.$dealer_orderid);
        if(is_array($_order_items)){
            foreach($_order_items as $_item){
                if($_item['nums']>$_item['sendnum']){
                    $ship_status = 2;
                    break;
                }
            }
        }
        
        $_data = array('ship_status' => $ship_status);
        $rs = $this->db->exec('SELECT * FROM sdb_orders WHERE order_id='.$dealer_orderid);
        $_sql = $this->db->getUpdateSQL($rs,$_data);
        if (!$this->db->exec($_sql)){
            $this->api_response('fail','data fail',null,'更新订单发货状态失败');
        }
        $this->_add_order_log($supplier_id,$dealer_orderid,$delivery_no);        

    }

    function _return($order_info, $delivery_item){
        $dealer_orderid = $order_info['order_id'];        
        $supplier_id = $order_info['supplier_id'];
        $delivery_no = $this->_add_ship_bill($order_info, $delivery_item, 'return');        
        $ship_status = 4;//全部发货
        $_order_items = $this->db->select('select sendnum from sdb_order_items where order_id='.$dealer_orderid);
        $this->ww($_order_items);
        if(is_array($_order_items)){
            foreach($_order_items as $_item){
                if($_item['sendnum']>0){
                    $ship_status = 3;
                    break;
                }
            }
        }        
        
        $_data = array('ship_status' => $ship_status);
        $rs = $this->db->exec('SELECT * FROM sdb_orders WHERE order_id='.$dealer_orderid);
        $_sql = $this->db->getUpdateSQL($rs,$_data);

        if (!$this->db->exec($_sql)){
            $this->api_response('fail','data fail',null,'更新退货单状态失败');
        }
        $this->_add_order_log($supplier_id,$dealer_orderid,$delivery_no);
    }

    function _add_order_log($supplier_id,$dealer_order_id,$delivery_id){
        $message_part1 = "";
        $message = "";
        $behavior = "";
        $order_info = $this->db->selectrow("SELECT ship_status FROM sdb_orders WHERE order_id=".$dealer_order_id);
        $ship_status = $order_info['ship_status'];
        
        $delivery_info = $this->db->selectrow("SELECT logi_name,logi_no FROM sdb_delivery WHERE delivery_id=".$delivery_id);
        
        if($ship_status == "1"){   //全部发货
            $message_part1 = "发货完成";
            $behavior = "发货";
        }else if($ship_status == "2"){    //部分发货
            $message_part1 = "已发货";
            $behavior = "发货";
        }else if($ship_status == "3"){  //部分退货
            $message_part1 = "已退货";
            $behavior = "退货";
        }else if($ship_status == "4"){   //全部退货
            $message_part1 = "退货完成";
            $behavior = "退货";
        }        

        $this->ww('delivery1');        
        if(!empty($behavior)){
            $message = "订单<!--order_id=".$dealer_order_id."&delivery_id=".$delivery_id."&ship_status=".$ship_status."-->".$message_part1;
            if(!empty($delivery_info['logi_name'])){
                $message .= "，物流公司：".$delivery_info['logi_name'];
            }
            if(!empty($delivery_info['logi_no'])){
                $message .= "，物流单号：".$delivery_info['logi_no'];
            }
            $this->ww('delivery2');
            $return1 = $this->_add_log($supplier_id,$dealer_order_id,$message,$behavior);
            
            return $return1;
        }else{
            return false;
        }
    }

    /**
     * 写入order log
     *
     * @param int $order_id
     * @param string $message
     * @param string $behavior
     * @return boolean
     */
    function _add_log($supplier_id,$order_id,$message,$behavior){
        $supplier_info = $this->db->selectrow("SELECT supplier_brief_name FROM sdb_supplier WHERE supplier_id=".$supplier_id);
        $supplier_name = $supplier_info['supplier_brief_name']?$supplier_info['supplier_brief_name']:"供应商";
        $rs = $this->db->query('select * from sdb_order_log where 0=1');
        $sql = $this->db->getInsertSQL($rs,array(
            'order_id'=>$order_id,
            'op_id'=>NULL,
            'op_name'=>$supplier_name,
            'behavior'=>$behavior,
            'result'=>'success',
            'log_text'=>addslashes($message),
            'acttime'=>time()
        ));
        $this->ww($sql);
        return $this->db->exec($sql);
    }
    

    function _err_handler($errno, $errstr, $errfile, $errline){
        if($errno == E_USER_ERROR || $errno == E_ERROR || $errno == E_USER_WARNING){
            $this->ww(array($errstr,$errfile,$errline));
        }
        return true;
    }
    
    function ww($error_info){
        return false;
        if(is_array($error_info)){
            $error_info = print_r($error_info, true);
        }
        error_log(date("Y:m:d:H:i:s").$error_info."\n", 3, HOME_DIR."/logs/sy.log");           
    }
    
}

