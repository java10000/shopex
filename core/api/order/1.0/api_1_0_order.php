<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
/**
 * API order模块部份
 * @package
 * @version 1.0:
 * @copyright 2003-2008 ShopEx
 * @author dreamdream
 * @license Commercial
 */

class api_1_0_order extends shop_api_object {
    var $select_limited=100;
    /**
    * 订单部份开放的字段，包括字段类型
    * @author DreamDream
    * @return 开放的字段相关信息
    */
    function getColumns(){
        $columns=array(
            'order_id'=>array('type'=>'int'),
            'status'=>array('type'=>'string'),
            'is_delivery'=>array('type'=>'string'),
            'memo'=>array('type'=>'string'),
            'last_change_time'=>array('type'=>'int'),
            'shipping'=>array('type'=>'string'),
            'memo'=>array('type'=>'string'),
            'pay_status'=>array('type'=>'string'),
            'createtime'=>array('type'=>'int'),
            'product_info'=>array('type'=>'string','join'=>true),
            'gift_info'=>array('type'=>'string','join'=>true),
            'ship_info'=>array('type'=>'string','join'=>true),
            'order_source'=>array('type'=>'string','join'=>true),
            'payment'=>array('type'=>'string'),
            'shipping'=>array('type'=>'string','name'=>'shipping_id'),
            'payment_number'=>array('type'=>'string','join'=>true),
            'cost_freight'=>array('type'=>'float'),
            'ship_name'=>array('type'=>'string'),
            'ship_addr'=>array('type'=>'string'),
            'ship_zip'=>array('type'=>'string'),
            'ship_tel'=>array('type'=>'string'),
            'ship_email'=>array('type'=>'string'),
            'ship_mobile'=>array('type'=>'string'),
            'final_amount'=>array('type'=>'string'),
            'total_amount'=>array('type'=>'string'),
            'ship_area'=>array('type'=>'string'),
            'ship_status'=>array('type'=>'string'),
            'member_id'=>array('type'=>'string')
        );
        return $columns;
    }
    function set_order_status($data,$innerloader=false){
        if(!$data['status']){
            $this->api_response('fail','data fail',$data,'status not exists');
        }
        if(!($rs=$this->db->exec('select status,last_change_time,acttime from sdb_orders where order_id='.$data['order_id']))){
            $this->api_response('fail','data fail',$data,'can not find the order');
        }

        $aData=array(
            'status'=>$data['status'],
            'last_change_time'=>time(),
            'acttime'=>time()
        );

        $sql=$this->db->getUpdateSQL($rs,$aData);

        if(!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }

        if(!$innerloader){

            return $this->api_response('true');
        }

        return true;
    }
    function set_pay_status($data,$innerloader=false){

        if($data['pay_status']<0){
            $this->api_response('fail','data fail',$data,'pay status not exists');
        }
        if(!($rs=$this->db->exec('select pay_status,last_change_time,acttime from sdb_orders where order_id='.$data['order_id']))){
            $this->api_response('fail','data fail',$data,'can not find the order');
        }
        $aData=array(
            'pay_status'=>$data['pay_status'],
            'last_change_time'=>time(),
            'acttime'=>time()
        );
        $sql=$this->db->getUpdateSQL($rs,$aData);

        if(!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }
        if(!$innerloader){
            return $this->api_response('true');
        }
        return true;
    }
    function set_ship_status($data,$innerloader=false){
        if($data['ship_status']<0){
            $this->api_response('fail','data fail',$data,'user status not exists');
        }
        if(!($rs=$this->db->exec('select ship_status,last_change_time,acttime from sdb_orders where order_id='.$data['order_id']))){
            $this->api_response('fail','data fail',$data,'can not find the order');
        }
        $aData=array(
            'ship_status'=>$data['ship_status'],
            'last_change_time'=>time(),
            'acttime'=>time()
        );
        $sql=$this->db->getUpdateSQL($rs,$aData);
        if(!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }
        if(!$innerloader){
            return $this->api_response('true');
        }
        return true;
   }
   function search_aftermarket_list($data){
        if($data['last_modify_st_time']=='0'){
            $result=$this->db->selectrow('select count(*) as counts from sdb_return_product where (( add_time>='.intval($data['last_modify_st_time']).' and add_time<'.intval($data['last_modify_en_time']).') or (add_time is null)) and disabled="false"');
        }else{
            $result=$this->db->selectrow('select count(*) as counts from sdb_return_product where add_time>='.intval($data['last_modify_st_time']).' and add_time<'.intval($data['last_modify_en_time']).' and disabled="false"');
        }
        $where[]='add_time>='.intval($data['last_modify_st_time']).' and add_time<'.intval($data['last_modify_en_time']).' and disabled="false"';
        $where=parent::_filter($where,$data);
        $result['data_info']=$this->db->select('select * from sdb_return_product'.$where);
        foreach($result['data_info'] as $key=>$value){
            if($value['member_id']){
                $member_name=$this->db->selectrow('select uname from sdb_members where member_id='.$value['member_id']);
                $result['data_info'][$key]['member_info']=$member_name['uname'];
            }
        }
        $this->api_response('true',false,$result);

    }
    function set_aftermarket_status($data){
        if(!($rs=$this->db->exec('select status,comment,process_data,memo,money,center_return_id from sdb_return_product where return_id='.$data['return_id']))){
            $this->api_response('fail','data fail',$data,'can not return product');
        }

        $aData=array(
            'status'=>$data['status']
        );
        if($data['comment']){
            $aData['comment']=$data['comment'];
        }
        if($data['process_data']){
            $aData['process_data']= stripslashes($data['process_data']);
        }
        if($data['memo']){
            $aData['memo']=$data['memo'];
        }
        if($data['money']){
            $aData['money']=$data['money'];
        }
        if($data['center_return_id']){
            $aData['center_return_id']=$data['center_return_id'];
        }

        $sql=$this->db->getUpdateSQL($rs,$aData);
        if(!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }

        $data['return_items'] = unserialize(stripslashes($data['return_items']));
        if($data['return_items']){
            $this->db->exec('delete from sdb_return_product_items where return_id='.$data['return_id']);
            foreach ($data['return_items'] as $item){
                $item_re = $this->db->exec('select * from sdb_return_product_items where 1=0');
                $item_sql = $this->db->getInsertSQL($item_re,$item);
                if($item_sql){
                    $this->db->exec($item_sql);
                }
            }
        }

        $this->api_response('true');
    }

    function set_aftermarket_status_c($data){
        foreach ($data as $k=>$v){
            if(($k == 'product_data' || $k == 'process_data') && $v){
                $aData[$k] = stripslashes($data[$k]);
            }
            if($k == 'image_file' && $v){
                $aData[$k] = $data[$k];
            }
            if($k != 'act' && $k != 'api_version' && $k != 'return_items' && $k != 'product_data' && $k != 'process_data' && $k != 'image_file' && $v){
                $aData[$k] = $data[$k];
            }
        }
        $rs=$this->db->selectrow('select return_id from sdb_return_product where center_return_id='.$data['center_return_id']);
        if($rs){
            $rss = $this->db->exec('select * from sdb_return_product where return_id='.$rs['return_id']);
            $sql=$this->db->getUpdateSQL($rss,$aData);
            if($sql){
                if(!$this->db->exec($sql)){
                    $this->api_response('fail','db error',$data);
                }else{
                    $return_id = $rs['return_id'];
                }
            }else{
                $return_id = $rs['return_id'];
            }
        }else{
            $rss = $this->db->exec('select * from sdb_return_product where 1=0');
            $sql = $this->db->getInsertSQL($rss,$aData);
            if(!$this->db->exec($sql)){
                $this->api_response('fail','db error',$data);
            }else{
                $return_id = $this->db->lastInsertId();
            }
        }

        $data['return_items'] = unserialize(stripslashes($data['return_items']));
        if($data['return_items']){
            $this->db->exec('delete from sdb_return_product_items where return_id='.$return_id);
            foreach ($data['return_items'] as $item){
                $item['return_id'] = $return_id;
                $item_re = $this->db->exec('select * from sdb_return_product_items where 1=0');
                $item_sql = $this->db->getInsertSQL($item_re,$item);
                if($item_sql){
                    $this->db->exec($item_sql);
                }
            }
        }

        $this->api_response('true');
    }

    function set_order_status_center($data){

        if(!($rs=$this->db->exec('select ship_name,ship_area,ship_addr,ship_zip,ship_tel,ship_time,ship_mobile from sdb_orders where order_id='.$data['order_id']))){
            $this->api_response('fail','data fail',$data,'can not find the order');
        }

        if(!($rs2=$this->db->exec('select payment_id from sdb_payments where order_id='.$data['order_id'].' order by t_begin desc limit 1'))){
            $this->api_response('fail','data fail',$data,'can not find the payment');
        }

        /*$aData=array(
            'ship_name'=> $data['ship_name'],
            'ship_area'=> $data['ship_area'],
            'ship_addr'=> $data['ship_addr'],
            'ship_zip'=> $data['ship_zip'],
            'ship_tel'=> $data['ship_tel'],
            'ship_time'=> $data['ship_time'],
            'ship_mobile'=> $data['ship_mobile'],
        );*/


        //$sql=$this->db->getUpdateSQL($rs,$aData);

        if($sql&&!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }

        if($data['status']){
            $this->set_order_status($data,true);
        }


        if(isset($data['pay_status'])&&intval($data['pay_status'])>=0){
            $this->set_pay_status($data,true);
            
            if(intval($data['pay_status'])==1){
                $this->set_payment_status($data,true);
                if(isset($data['pay_type']) && $data['pay_type'] == 'recharge'){
                    $this->add_member_advance($data,true);
                }
            }
        }
        if(isset($data['ship_status'])&&intval($data['ship_status'])>=0){
            $this->set_ship_status($data,true);
        }
        $result['data_info']['order_id']= $data['order_id'];
        $result['data_info']['last_change_time']= time();
        $this->api_response('true',false,$result);
    }
    /**
    * 查找订单详细信息
    * @param 要查找的订单信息
    * @author DreamDream
    * @return 订单详细信息
    */
    function search_order_detail($data){
        if(!($result['data_info']=$this->db->selectrow('select * from sdb_orders where order_id='.$data['order_id']))){
            $this->api_response('fail','data fail',$data);
        }
        if(isset($result['data_info']['ship_area'])){
            list($pkg,$regions,$region_id) = explode(':',$result['data_info']['ship_area']);
            $result['data_info']['ship_area']=$regions;
        }
        if(isset($result['data_info']['payment'])){
            $payment=$this->db->selectrow('select custom_name from sdb_payment_cfg where id='.$result['data_info']['payment']);
            $result['data_info']['payment']=$payment['custom_name'];
            unset($payment);
        }


        if($data['columns_join']['product_info']){
            $tmp_data = $this->db->select('select a.addon,a.score,a.amount as num,b.name as goods_name,a.item_id,b.goods_id,sendnum,a.name as product_name,a.product_id,a.bn,dly_status as product_status,a.price as product_price,nums as product_number from sdb_order_items a join sdb_products b on a.product_id = b.product_id where order_id='.$data['order_id']);
            foreach($tmp_data as $key=>$value){
                $result['data_info']['product_info'][$key]['items'][0] =$value;
            }
        }
        $result['data_info']['pay_time'] ="";
        $result['data_info']['orders_number'] =count($result['data_info']['product_info']) ;
        foreach($result['data_info']['product_info'] as $key=>$value){

            foreach($value['items'] as $p=>$t){
                $result['data_info']['product_info'][$key]['items'][$p]['type'] = 'product';
                $result['data_info']['product_info'][$key]['item_id'] = $t['item_id'];
                $result['data_info']['product_info'][$key]['goods_name'] = $t['goods_name'];
                $result['data_info']['product_info'][$key]['goods_id'] = $t['goods_id'];
                $result['data_info']['product_info'][$key]['price'] = $t['product_price'];
                unset($result['data_info']['product_info'][$key][$p]['item_id']);
                unset($result['data_info']['product_info'][$key][$p]['product_price']);
                $mes_g = unserialize($t['addon']);
                if($mes_g['adjinfo']){
                   $viop =explode("|",$mes_g['adjinfo']);
                   $num = count($result['data_info']['product_info'][$key]['items']);
                   foreach($viop as $vv=>$vt){
                       if($vt){
                           $rpcid =explode("_",$vt);
                           if($rpcid[2]){
                               $result['data_info']['product_info'][$key]['items'][$num]['product_id'] =$rpcid[0];
                               $result['data_info']['product_info'][$key]['items'][$num]['sendnum'] =$rpcid[2];
                               $result['data_info']['product_info'][$key]['items'][$num]['type'] ='adjunt';
                           }
                       }
                   }
                }

               $result['data_info']['product_info'][$key]['items_num'] = count($result['data_info']['product_info'][$key]['items']);
            }
           $result['data_info']['product_info'][$key]['items'][$p]['total_price'] = floatval($t['product_price']) * floatval($t['product_number']);
            unset($result['data_info']['product_info'][$key]['items'][$p]['addon']);
        }
        $result['data_info']['pay_type'] = 'other';
        if($result['data_info']['order_refer'] == 'lakala'){
            $payment = $this->db->selectrow('select pay_type from sdb_payments where order_id='.$data['order_id'].' order by t_begin desc');
            if($payment && $payment['pay_type'] == "recharge"){
                $result['data_info']['pay_type'] = 'recharge';
            }
            unset($payment);
        }
        $this->api_response('true',false,$result);
    }
    /**
    * 查找订单列表
    * @param 要查找的订单信息
    * @author DreamDream
    * @return 订单列表信息
    */
    /**
    * 查找订单列表
    * @param 要查找的订单信息
    * @author DreamDream
    * @return 订单列表信息
    */
    function search_order_list($data){
        if($data['status']){
            $status=' and status="'.$data['status'].'"';
        }
        $result=$this->db->selectrow('select count(*) as counts from sdb_orders where last_change_time>='.intval($data['last_modify_st_time']).' and last_change_time<'.intval($data['last_modify_en_time']).$status);

        $where=$this->_filter($data);
        if(isset($data['page_no'])&&isset($data['page_size'])){
            if($data['page_no']===1){
                $p_min = 0;
                $p_max = $data['page_size'];
            }else{
                $p_min = $data['page_size']*$data['page_no'];
                $p_max = ($data['page_no']+1)*$data['page_size'];
            }
        }else{
            $p_min=0;
            $p_max=20;
        }
        //$where .= "LIMIT ".$p_min.','.$p_max;
        $result['data_info']=$this->db->select('select '.implode(',',$data['columns']).' from sdb_orders '.$where);
        foreach($result['data_info'] as $key=>$value){

            if(isset($result['data_info'][$key]['ship_area'])){
                $result['data_info'][$key]['ship_area_code']=$result['data_info'][$key]['ship_area'];
                list($pkg,$regions,$region_id) = explode(':',$result['data_info'][$key]['ship_area']);
                $result['data_info'][$key]['ship_area']=$regions;
            }
            if(isset($result['data_info'][$key]['payment'])){
                $payment=$this->db->selectrow('select custom_name from sdb_payment_cfg where id='.$result['data_info'][$key]['payment']);
                $result['data_info'][$key]['payment']=$payment['custom_name'];
                unset($payment);
            }
            if(isset($result['data_info'][$key]['shipping'])){
                $shopping=$this->db->selectrow('select dt_name from sdb_dly_type where dt_id='.$result['data_info'][$key]['shipping']);
                $result['data_info'][$key]['shipping']=$shopping['dt_name'];
                unset($shopping);
            }
            //$data['columns_join']['product_info']=true;
            if($data['columns_join']['product_info']){

               $productInfo=$this->db->select('select sendnum,name as product_name,product_id,bn,dly_status as product_status,price as product_price,nums as product_number, addon as product_addon from sdb_order_items where order_id='.$result['data_info'][$key]['order_id']);
                foreach($productInfo as $p_key=>$p_value){
                    $productInfo[$p_key]['product_addon']=unserialize($productInfo[$p_key]['product_addon']);
                }
                $result['data_info'][$key]['product_info']=$productInfo;
            }

            if($data['columns_join']['gift_info']){
                $result['data_info'][$key]['gift_info']=$this->db->select('SELECT * FROM sdb_gift_items WHERE order_id ='.$result['data_info'][$key]['order_id']);
            }

            if($result['data_info'][$key]['member_id']){
                $meber_name=$this->db->selectrow('select uname,name,area,tel,email,zip,addr from sdb_members where member_id='.$result['data_info'][$key]['member_id']);
                $result['data_info'][$key]['buyer_name']=$meber_name['uname'];
                $result['data_info'][$key]['member_info']=$meber_name;
            }
        }
        $this->api_response('true',false,$result);
    }

    function get_order_log($data){
        if($data['order_id']){
            $result['data_info']=$this->db->select('select * from sdb_order_log where order_id='.$data['order_id']);
            $this->api_response('true',false,$result);
        }else{
            $this->api_response('fail','data fail',$data);
        }
    }
    function get_order_message($data){
        if($data['last_modify_en_time'] && $data['last_modify_st_time']){
            $result=$this->db->selectrow('select count(*) as counts from sdb_message where date_line>='.$data['last_modify_st_time'].' and date_line<'.$data['last_modify_en_time'].' and rel_order>0');
            $result['data_info']=$this->db->select('select * from sdb_message where date_line>='.$data['last_modify_st_time'].' and date_line<'.$data['last_modify_en_time'].' and rel_order>0');
            $this->api_response('true',false,$result);
        }else{
            $this->api_response('fail','data fail',$data);
        }
    }
    function set_order_message($data){
        $aData=array(
            'for_id'=>$data['for_id'],
            'msg_from'=>$data['msg_from'],
            'from_id'=>$data['from_id'],
            'from_type'=>$data['from_type'],
            'to_id'=>$data['to_id'],
            'to_type'=>$data['to_type'],
            'unread'=>$data['unread'],
            'folder'=>$data['folder'],
            'email'=>$data['email'],
            'tel'=>$data['tel'],
            'subject'=>$data['subject'],
            'message'=>$data['message'],
            'rel_order'=>$data['rel_order'],
            'date_line'=>$data['date_line'],
            'is_sec'=>$data['is_sec'],
            'del_status'=>$data['del_status'],
            'disabled'=>$data['disabled'],
            'msg_ip'=>$data['msg_ip'],
            'msg_type'=>$data['msg_type']
        );
        $rs = $this->db->query("SELECT * FROM sdb_message WHERE 0=1");
        $sql = $this->db->getInsertSql($rs,$aData);
        if(!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }else{
            $this->api_response('true');
        }
    }
    function get_order_promotion($data){
        if($data['order_id']){
            $result['data_info']=$this->db->select('select * from sdb_order_pmt
            where order_id='.$data['order_id']);
            $this->api_response('true',false,$result);
        }else{
            $this->api_response('fail','data fail',$data);
        }
    }

    /**
    * 订单模块的过滤赛选器
    * @param 赛选条件
    * @author DreamDream
    * @return 过滤过的筛选条件
    */
    function _filter($filter){

        $where = array();
        if(isset($filter['last_modify_st_time'])){
            $where[]=' last_change_time >='.intval($filter['last_modify_st_time']);
        }
        if(isset($filter['last_modify_en_time'])){
            $where[]=' last_change_time <'.intval($filter['last_modify_en_time']);
        }
        if(isset($filter['status'])){
             $where[]=' status ="'.$filter['status'].'"';
        }
        if(isset($filter['order_id'])){
             $where[]=' order_id IN ('.$filter['order_id'].')';
        }
        return parent::_filter($where,$filter);

    }

    function create_order($data){
        $curr_time = time();
        $data['acttime'] = $curr_time;
        $data['createtime'] = $curr_time;
        $data['last_change_time'] = $curr_time;
        $aRs = $this->db->query("SELECT * FROM sdb_orders WHERE 0=1");
        $sSql = $this->db->getInsertSql($aRs,$data);
        $this->db->exec($sSql);
        $order_id = $this->db->lastinsertid();
        foreach(json_decode($data['orders']['order'],true) as $key =>$value){
            $value['order_id'] = $order_id;
            $aRs = $this->db->exec("SELECT * FROM sdb_order_items WHERE 0=1");
            $sSql = $this->db->getInsertSql($aRs,$value);
            $this->db->exec($sSql);
            $order_item_id[] = $this->db->lastinsertid();
        }
        $result['data_info'] = array('order_id'=>$order_id,'last_modified'=>time(),'order_item_id'=>$order_item_id);
        $this->api_response('true',false,$result);
    }

    function update_order_remark($data){
        $adata = array("mark_text"=>$data['mark_text']);
        $res = $this->db->exec("SELECT * FROM sdb_orders WHERE order_id = ".$data['order_id']);
        $sql = $this->db->getUpdateSQL($res,$adata);
        $this->db->exec($sql);
        $result['data_info'] = array('order_id'=>$data['order_id'],'last_modified'=>time());
        $this->api_response('true',false,$result);
    }


    function update_order($data){
        unset($data['pay_status']);
        unset($data['shio_status']);
        unset($data['status']);
        if($row=$this->db->selectrow("SELECT * FROM sdb_orders WHERE order_id=".$data['order_id'])){
            if($row['pay_status']!=0&&$row['ship_status']!=0&&$row['status']!='active'){
                $this->api_response('fail','The order is lock',$result);
            }
        }
        $res = $this->db->exec("SELECT * FROM sdb_orders WHERE order_id=".$data['order_id']);
        $sql = $this->db->getUpdateSQL($res,$data);
        $this->db->exec($sql);
        if($data['orders']['order'])$this->db->exec("DELETE FROM sdb_order_items WHERE order_id=".$data['order_id']);
        foreach(json_decode($data['orders']['order'],true) as $key =>$value){
            $aRs = $this->db->exec("SELECT * FROM sdb_order_items WHERE item_id=".$value['item_id']);
            if($value['item_id']){
                $sSql = $this->db->getUpdateSQL($aRs,$value);
                $this->db->exec($sSql);
                $order_item_id[] = $value['item_id'];
            }else{
                $sSql = $this->db->getInsertSql($aRs,$value);
                $this->db->exec($sSql);
                $order_item_id[] = $this->db->lastinsertid();
            }
        }
        $result['data_info'] = array('order_id'=>$data['order_id'],'item_id'=>$order_item_id);
        $this->api_response('true',false,$result);
    }

    function get_payments($data){
        $result['data_info'] = $this->db->selectrow("SELECT * FROM sdb_payments WHERE order_id = ".$data['order_id']);
        if(!$result['data_info']){
            $this->api_response('fail','has no payments',$result);
        }
        $this->api_response('true',false,$result);
    }
    function get_refunds($data){
        $result['data_info'] = $this->db->selectrow("SELECT * FROM sdb_refunds WHERE order_id = ".$data['order_id']);
        if(!$result['data_info']){
            $this->api_response('fail','has no refunds',$result);
        }
        $this->api_response('true',false,$result);
    }
    function get_shippings($data){
        $result['data_info'] = $this->db->selectrow("SELECT * FROM sdb_delivery WHERE type = 'delivery' AND order_id = ".$data['order_id']);
        if(!$result['data_info']){
            $this->api_response('fail','has no shippings',$result);
        }
        $tmp_data = explode(":",$result['data_info']['ship_area']);
        $result['data_info']['ship_area'] = $tmp_data[1];
        $result['data_info']['ship_items'] =  $this->db->select("SELECT * FROM sdb_delivery_item WHERE delivery_id = ".$result['data_info']['delivery_id']);
        $this->api_response('true',false,$result);
    }
    function get_returns($data){
        $result['data_info'] = $this->db->selectrow("SELECT * FROM sdb_delivery WHERE type = 'return' AND order_id = ".$data['order_id']);
        if(!$result['data_info']){
            $this->api_response('fail','has no returns',$result);
        }
        $tmp_data = explode(":",$result['data_info']['ship_area']);
        $result['data_info']['ship_area'] = $tmp_data[1];
        $this->api_response('true',false,$result);
    }

    function set_payment_status($data, $innerloader=false){
        if(!($payment=$this->db->selectrow('select payment_id from sdb_payments where order_id='.$data['order_id'].' order by t_begin desc'))){
            $this->api_response('fail','data fail',$data,'can not find the payment');
        }

        if(!($rs=$this->db->exec('select status,t_end from sdb_payments where payment_id='.$payment['payment_id']))){
            $this->api_response('fail','data fail',$data,'can not find the payment');
        }

        $aData=array(
            'status'=>'succ',
            't_end'=>time()
        );

        $sql=$this->db->getUpdateSQL($rs,$aData);
        if(!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }

        if(!$innerloader){

            return $this->api_response('true');
        }

        return true;
    }

    function add_member_advance($data, $innerloader=false){
        if(!($payment=$this->db->selectrow('select payment_id, paymethod, member_id, cur_money, status from sdb_payments where order_id='.$data['order_id'].' order by t_begin desc'))){
            $this->api_response('fail','data fail',$data,'can not find the order');
        }
        $member_id = intval($payment['member_id']);
        if(!($rs = $this->db->exec('SELECT advance FROM sdb_members WHERE member_id='.$member_id))){
            $this->api_response('fail','data fail',$data,'can not find the member');
        }

        $member = $this->db->getRows($rs,1);

        $member_advance = $payment['cur_money']+$member[0]['advance'];

        $aData=array(
            'advance'=>$member_advance,
        );
        $sql=$this->db->getUpdateSQL($rs,$aData);

        if(!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }

        $row = $this->db->selectrow("SELECT SUM(advance) as sum_advance FROM sdb_members");
        $shop_advance = $row['sum_advance'];
        $rs2 = $this->db->exec('select * from sdb_advance_logs where 0=1');
        $sql = $this->db->getInsertSQL($rs2,array(
            'member_id'=>$member_id,
            'money'=>$payment['cur_money'],
            'mtime'=>time(),
            'message'=>"网店订单预存款充值：支付单号{".$payment['payment_id']."}",
            'payment_id'=>$payment['payment_id'],
            'order_id'=>$data['order_id'],
            'paymethod'=>$payment['paymethod'],
            'memo'=>"在线充值",
            'import_money'=>$payment['cur_money'],
            'explode_money'=>0,
            'member_advance'=>$member_advance,
            'shop_advance'=>$shop_advance
           ));
        if(!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }

        if(!$innerloader){
            return $this->api_response('true');
        }
        return true;
    }

}