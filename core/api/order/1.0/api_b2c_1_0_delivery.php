<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2c_1_0_delivery extends shop_api_object {

    function getColumns(){
        $columns=array(
            'delivery_id'=>array('type'=>'int'),
            'order_id'=>array('type'=>'int'),
            'member_id'=>array('type'=>'int'),
            'money'=>array('type'=>'decimal'),
            'type'=>array('type'=>'string'),
            'is_protect'=>array('type'=>'string'),
            'delivery'=>array('type'=>'string'),  
            'logi_id'=>array('type'=>'string'),
            'logi_name'=>array('type'=>'string'),
            'logi_no'=>array('type'=>'string'),
            'ship_name'=>array('type'=>'string'),
            'ship_area'=>array('type'=>'string'),
            'ship_addr'=>array('type'=>'string'),
            'ship_zip'=>array('type'=>'string'),
            'ship_tel'=>array('type'=>'string'),
            'ship_mobile'=>array('type'=>'string'),  
            'ship_email'=>array('type'=>'string'),
            't_begin'=>array('type'=>'int'),
            't_end'=>array('type'=>'int'),
            'op_name'=>array('type'=>'string'),
            'status'=>array('type'=>'string'),
            'memo'=>array('type'=>'string'),
            'disabled'=>array('type'=>'string')
        );
        return $columns;
    }
    function insert_delivery($data){
        $aData=array(
            'order_id'=>$data['order_id'],
            'member_id'=>$data['member_id'],
            'money'=>$data['money'],
            'type'=>$data['type'],
            'is_protect'=>$data['is_protect'],
            'delivery'=>$data['delivery'],
            'logi_id'=>$data['logi_id'],
            'logi_name'=>$data['logi_name'],
            'logi_no'=>$data['logi_no'],
            'ship_name'=>$data['ship_name'],
            'ship_area'=>$data['ship_area'],
            'ship_addr'=>$data['ship_addr'],
            'ship_zip'=>$data['ship_zip'],
            'ship_tel'=>$data['ship_tel'],
            'ship_mobile'=>$data['ship_mobile'],
            'ship_email'=>$data['ship_email'],
            't_begin'=>$data['t_begin'],
            't_end'=>$data['t_end'],
            'op_name'=>$data['op_name'],
            'status'=>$data['status'],
            'memo'=>$data['memo'],
            'disabled'=>$data['disabled'],
            'replacement'=>$data['replacement'],
            'return_id'=>$data['return_id']
        );
        $objShipping = &$this->system->loadModel('trading/delivery');
        $aData['delivery_id'] = $data['delivery_id']?$data['delivery_id']:$objShipping->getNewNumber($data['type']);
        $sql = "SELECT delivery_id FROM sdb_delivery WHERE delivery_id='".$aData['delivery_id']."' AND order_id='".$data['order_id']."'";
        $row = $this->db->selectrow($sql);
        
        if($row){
            $rs = $this->db->exec("SELECT * FROM sdb_delivery WHERE delivery_id='".$aData['delivery_id']."'");
            $aData = addslashes_array($aData);
            $sql = $this->db->getUpdateSQL($rs,$aData);
            if(!$this->db->exec($sql)){
                $this->api_response('fail','sql exec error',$sql);
            }
        }else{
            $rs = $this->db->query('select * from sdb_delivery where 0=1');
            $aData = addslashes_array($aData);
            $sql = $this->db->getInsertSQL($rs,$aData);
            if(!$this->db->exec($sql)){
                $this->api_response('fail','sql exec error',$sql);
            }else{
                if($data['delivery_item']){
                    $delivery_item=json_decode(stripslashes($data['delivery_item']),true);
                    foreach($delivery_item as $key=>$value){
                        $aData=array(
                            'delivery_id'=>$value['delivery_id'],
                            'item_type'=>$value['item_type'],
                            'product_id'=>$value['product_id'],
                            'product_bn'=>$value['product_bn'],
                            'product_name'=>$value['product_name'],
                            'number'=>$value['number']
                        );
                        $rs = $this->db->query('select * from sdb_delivery_item where 0=1');
                        $aData = addslashes_array($aData);
                        $sql = $this->db->getInsertSQL($rs,$aData);
                        $this->db->exec($sql);
                        $dc_data=$this->db->selectrow("SELECT sendnum FROM sdb_order_items WHERE product_id =".$value['product_id']);
                        if($data['type']=='delivery'){
                            $bData['sendnum'] = $dc_data['sendnum']+$value['number'];
                        }else{
                            $bData['sendnum'] = $dc_data['sendnum']-$value['number'];
                        }
                        $b_rs = $this->db->query("SELECT sendnum FROM sdb_order_items WHERE product_id =".$value['product_id']);
                        $esql = $this->db->getUpdateSQL($b_rs,$bData);
                        $this->db->exec($esql);
                    }
                }                
            }
        }
        $this->api_response('true',false,null);
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

    /**
     * 发货时添上订单操作记录
     * add by hujianxin
     *
     * @param bigint $dealer_order_id
     * @param int $delivery_id，
     * @return boolean
     */
    function _add_order_log($dealer_order_id,$delivery_id){
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
        
        if(!empty($behavior)){
            $message = "订单<!--order_id=".$dealer_order_id."&delivery_id=".$delivery_id."&ship_status=".$ship_status."-->".$message_part1;
            if(!empty($delivery_info['logi_name'])){
                $message .= "，物流公司：".$delivery_info['logi_name'];
            }
            if(!empty($delivery_info['logi_no'])){
                $message .= "，物流单号：".$delivery_info['logi_no'];
            }
            
            $return1 = $this->_add_log($dealer_order_id,$message,$behavior);
            
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
    function _add_log($order_id,$message,$behavior){
        $rs = $this->db->query('select * from sdb_order_log where 0=1');
        $sql = $this->db->getInsertSQL($rs,array(
            'order_id'=>$order_id,
            'op_id'=>NULL,
            'op_name'=>NULL,
            'behavior'=>$behavior,
            'result'=>'success',
            'log_text'=>addslashes($message),
            'acttime'=>time()
        ));
        return $this->db->exec($sql);
    }
    
    /**
     * 写入发货单
     *
     * @param int $supplier_orderid po单单号
     * @param array $data
     *                 array(
     *                     'dealer_order_id' => xxx,     
     *                     'money' => xxx,
     *                     'type' => return/delivery,
     *                     'is_protect' => true/false,
     *                     'delivery' => xxx,
     *                     'logi_name' => xxx,
     *                     'logi_no' => xxx,
     *                     'ship_name' => xxx,
     *                     'ship_area' => xxx,
     *                     'ship_addr' => xxx,
     *                     'ship_zip' => xxx,
     *                     'ship_tel' => xxx,
     *                     'ship_mobile' => xxx,
     *                     'ship_email' => xxx,
     *                     'ship_tel' => xxx,
     *                     't_begin' => xxx,
     *                     't_end' => xxx,
     *                     'status' => xxx,
     *                     'memo' => xxx,
     *                     'struct' => array(
     *                         'dealer_bn' => xxx,
     *                         'item_type' => xxx,
     *                         'product_bn' => xxx,
     *                         'product_name' => xxx,
     *                         'number' => xxx,
     *                       )
     *                   )
     * @return 设置发货成功
     */
    function ww($error_info){
        return false;
        if(is_array($error_info)){
            $error_info = print_r($error_info, true);
        }
        //error_log(date("Y:m:d:H:i:s").$error_info."\n", 3, "/home/bryant/errors.log");
        error_log(date("Y:m:d:H:i:s").$error_info."\n", 3, HOME_DIR."/bryant/errors.log");            
    }
    
    function add_delivery_bill($input_data){
        $this->ww('test');
        $supplier_id = $input_data['supplier_id'];        
        $data = json_decode($input_data['data'], true);

        $delivery_no = $this->_get_new_number('delivery');
        $_delivery_items = $data['struct'];

        $this->ww($_delivery_items);

        $dealer_orderid = $data['dealer_order_id'];
        unset($data['struct']);

        $this->ww($_delivery_items);
/*

        foreach($_delivery_items as $_items){
            $_sql = sprintf('select nums-sendnum as sub_num from sdb_orders where bn=\'%s\' and order_id=%s', $_items['dealer_bn'], $_items['dealer_order_id']);
            $_arr_tmp = $this->db->selectrow($_sql);
            if($_arr_tmp['sub_num']-$_items['number']>0){
                $ship_status = 2;//部分发货
            }
            }*/
        $this->ww($ship_status);
        
        $_sql = sprintf('select member_id from sdb_orders where order_id=%s', $dealer_orderid);
        $this->ww($_sql);

        if ($_order_data = $this->db->selectrow($_sql)){
            $data['member_id'] = $_order_data['member_id'];
            $data['type'] = 'delivery';
            $data['op_name'] = 'admin';
            $data['order_id'] = $dealer_orderid;
            $data['logi_id'] = null;
            $data['delivery_id'] = $delivery_no;
            
            $rs = $this->db->query('SELECT * FROM sdb_delivery WHERE 0=1');
            $data = addslashes_array($data);
            $_sql = $this->db->GetInsertSQL($rs, $data);
            if (!$this->db->exec($_sql)){
                $this->ww($_sql);
                $this->api_response('fail','data fail',null,'发货单插入失败');
            }else{
                foreach($_delivery_items as $_item){
                    $_data = array(
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
                        $this->ww($_sql);
                        $this->api_response('fail','data fail',$result,'发货单插入失败');
                    }
                    //更新order_items 订单发货数量

                    $_sql = sprintf('update sdb_order_items set sendnum=sendnum+%d where order_id=%s and bn=\'%s\'',$_item['number'], $dealer_orderid, $_item['dealer_bn']);
                    $this->db->exec($_sql);
                    $this->ww($_sql);
                }
            }
        }else{
//            $this->api_response('fail','data fail',null,'订单不存在');
            $this->api_response('true',false,null);
        }
        $this->ww('successed');

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
        $this->ww($_sql);        
        if (!$this->db->exec($_sql)){
            $this->api_response('fail','data fail',null,'更新订单发货状态失败');
        }
        //更新订单操作记录，add by hujianxin
        $this->_add_order_log($dealer_orderid,$delivery_no);
        
        $this->api_response('true',false,null);
    }
    /**
     * 写入退货货单
     *
     * @param int $supplier_orderid po单单号
     * @param array $data
     *                 array(
     *                     'dealer_order_id' => xxx,     
     *                     'money' => xxx,
     *                     'type' => return/delivery,
     *                     'is_protect' => true/false,
     *                     'delivery' => xxx,
     *                     'logi_name' => xxx,
     *                     'logi_no' => xxx,
     *                     'ship_name' => xxx,
     *                     'ship_area' => xxx,
     *                     'ship_addr' => xxx,
     *                     'ship_zip' => xxx,
     *                     'ship_tel' => xxx,
     *                     'ship_mobile' => xxx,
     *                     'ship_email' => xxx,
     *                     'ship_tel' => xxx,
     *                     't_begin' => xxx,
     *                     't_end' => xxx,
     *                     'status' => xxx,
     *                     'memo' => xxx,
     *                     'struct' => array(
     *                         'dealer_bn' => xxx,
     *                         'item_type' => xxx,
     *                         'product_bn' => xxx,
     *                         'product_name' => xxx,
     *                         'number' => xxx,
     *                       )
     *                   )
     * @return 设置发货成功
     */

    function add_reship_bill($input_data){
        $supplier_id = $input_data['supplier_id'];        
        $data = json_decode($input_data['data'], true);
//        error_log(print_r($data, true), 3, "/home/bryant/errors.log");
//        error_log(print_r(json_decode($data,true), true), 3, "/home/bryant/errors.log");        
        $delivery_no = $this->_get_new_number('delivery');
        $_delivery_items = $data['struct'];
        $dealer_orderid = $data['dealer_order_id'];
        unset($data['struct']);
/*
        $aShipStatus = $status = array(0=>'未发货',
                                       1=>'已全部发货',
                                       2=>'部分发货',
                                       3=>'部分退货',
                                       4=>'已全部退货' );

*/  
        $ship_status = 5;//已全部退货
        foreach($_delivery_items as $_items){
            $_sql = sprintf('select sendnum from sdb_orders where bn=\'%s\'', $_items['dealer_bn']);
            $_arr_tmp = $this->db->selectrow($_sql);
            if ($_arr_tmp['sendnum']>$_items['number']){
                $ship_status = 3;//部分退货
            }
        }        
        
        $_sql = sprintf('select member_id from sdb_orders where order_id=%s', $dealer_orderid);
        if ($_order_data = $this->db->selectrow($_sql)){
            $data['member_id'] = $_order_data['member_id'];
            $data['type'] = 'return';
            $data['op_name'] = 'admin';
            $data['order_id'] = $dealer_orderid;
            $data['logi_id'] = null;
            $data['delivery_id'] = $delivery_no;            
            
            $rs = $this->db->query('SELECT * FROM sdb_delivery WHERE 0=1');
            $data = addslashes_array($data);
            $_sql = $this->db->GetInsertSQL($rs, $data);
            if (!$this->db->exec($_sql)){
                $this->api_response('fail','data fail',null,'退货单插入失败');
            }else{
                foreach($_delivery_items as $_item){
                    $_data = array(
                        'delivery_id' => $delivery_no,
                        'product_bn' => $_item['dealer_bn'],
                        'item_type' => $_item['item_type'],
                        'product_name' => $_item['product_name'],//todo 用本地的货品名称
                        'number' => $_item['number'],
                        );
                    $rs = $this->db->query('SELECT * FROM sdb_delivery_item WHERE 0=1');
                    $_data = addslashes_array($_data);
                    $_sql = $this->db->GetInsertSQL($rs, $_data);
                    $this->ww($_sql);
                    if (!$this->db->exec($_sql)){
                        $this->api_response('fail','data fail',$result,'退货单插入失败');
                    }
                    //更新order_items 订单发货数量
                    $_sql = sprintf('update sdb_order_items set sendnum=sendnum-%d where order_id=%s and bn=\'%s\'',$_item['number'], $dealer_orderid, $_item['dealer_bn']);                    
                    $this->db->exec($_sql);
                    $this->ww($_sql);
                }
            }
        }else{
            //$this->api_response('fail','data fail',null,'订单不存在');
            $this->api_response('true',false,null);            
        }

        $ship_status = 4;//全部发货
        $_order_items = $this->db->select('select sendnum from sdb_order_items where order_id='.$dealer_orderid);
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
        $this->ww($_sql);

        if (!$this->db->exec($_sql)){
            $this->api_response('fail','data fail',null,'更新退货单状态失败');
        }
        
        //更新订单操作记录，add by hujianxin
        $this->_add_order_log($dealer_orderid,$delivery_no);
        
        $this->api_response('true',false,null);
    }
}