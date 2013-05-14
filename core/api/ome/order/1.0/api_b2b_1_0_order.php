<?php
//发货,订单编辑
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_order extends shop_api_object {

    var $error = array('not_active'=>'订单状态未激活',
                   'not_pay'=>'订单未支付',
                   'already_full_refund'=>'订单已全额退款',
                   'already_pay'=>'订单已支付',
                   'go_process'=>'订单支付中',
                   'already_part_refund'=>'订单已部分退款',
                   'no_full_pay'=>'订单未完成支付',
                   'already_shipping'=>'订单已配送',
                   'not_shipping'=>'订单未配送',
                   'must_not_shipping'=>'订单必须未配送',
                   'must_not_pay'=>'订单必须未支付',
                   'is_dead'=>'此订单是死单',
                   'must_not_pending'=>'订单必须是暂停发货',
                   'cancel_order_fail'=>'订单取消失败',
                   
        
    );
    var $app_error=array(
            'payment is out of order amount'=>array('no'=>'b_goods_001','debug'=>'','level'=>'warning','info'=>'支付金额已经超过订单总金额','desc'=>'','debug'=>''),
            'can not find the goods'=>array('no'=>'b_goods_001','debug'=>'','level'=>'warning','info'=>'订单没有对应的商品项','desc'=>'','debug'=>''),
            'can not find the goods'=>array('no'=>'b_goods_001','debug'=>'','level'=>'warning','info'=>'订单ID不能为空','desc'=>'','debug'=>''),
            'can not find the goods'=>array('no'=>'b_goods_001','debug'=>'','level'=>'warning','info'=>'经销商ID不能为空','desc'=>'','debug'=>''),
            'can not find the goods'=>array('no'=>'b_goods_001','debug'=>'','level'=>'warning','info'=>'经销商订单ID不能为空','desc'=>'','debug'=>''),
            'can not find the goods'=>array('no'=>'b_goods_001','debug'=>'','level'=>'warning','info'=>'如果要开票,必须要交税','desc'=>'','debug'=>'')
    );
    function getColumns(){
        $columns=array(
         
        );
        return $columns;
    }
    
    //检测task_id
    function check_task_exists($data){
        $task= array();
        $task_id=$this->db->selectrow('select task_id from sdb_connect_ome_connect_pool where task_id = "'.$data['task'].'"');
        if($task_id['task_id']){
           unset($data);
        }else{
            $task['date_time'] = time();
            $task['task_id'] = $data['task'];
            $aRs = $this->db->query('SELECT * FROM sdb_connect_ome_connect_pool WHERE 0=1');
            $sSql = $this->db->getInsertSql($aRs,$task);
            $this->db->exec($sSql);
        }
        
    }
    /**
     * 设置订单失效
     *
     * @param array $data 
     *
     * @return 设置订单失效
     */
    function set_ome_dead_order($data,$order_refund){
        $this->check_task_exists($data);
        $order_id = $data['order_id'];
        $this->verify_order_valid($order_id,$local_new_version_order,'*');//验证订单有效性

        $this->checkOrderStatus('cancel',$local_new_version_order);//检查订单状态

        $this->verify_order_item_valid($order_id,$local_order_item_list);//验证订单订单商品的有效性

        $obj_product = $this->load_api_instance('search_product_by_bn','1.0');
        
        //解冻上次订单商品库存
        foreach($local_order_item_list as $k=>$local_order_item){
            $obj_product->update_product_store($local_order_item['bn'],$local_order_item['nums'],'unfreeze');
        }
        
         //通知平台
       // $objPlatform = $this->system->loadModel('system/platform');
       // if($objPlatform->tell_platform('invalid_order',array('id'=>$order_id))=== false){
       //     $this->api_response('fail','data fail',$result,$objPlatform->getErrorInfo());
       // }

        $this->db->exec('update sdb_orders set status="dead" where order_id='.$order_id);

        $this->api_response('true','data succ',$result,'取消订单成功');
    }


    /**
     * 设置取消暂停发货
     *
     * @param array $data 
     *
     * @return 设置取消订单失效
     */
    function set_cancel_stop_shipping($data){
        $order_id = $data['order_id'];
        $this->verify_order_valid($order_id,$order,'*');//验证订单有效性
        $this->checkOrderStatus('cancel_stop_shipping',$order);//检查订单状态
        
        //通知平台
        $objPlatform = $this->system->loadModel('system/platform');
        if($objPlatform->tell_platform('cancel_stop_shipping',array('id'=>$order_id))=== false){
            $this->api_response('fail','data fail',$result,$objPlatform->getErrorInfo());
        }
        
        $this->db->exec('update sdb_orders set status="active" where  version_id=0 and order_id='.$order_id);
        $this->api_response('true',false,$result);
    }
    
    /**
     * 设置暂停发货
     *
     * @param array $data 
     *
     * @return 设置暂停发货
     */
    function set_stop_shipping($data){
        $order_id = $data['order_id'];
        $this->verify_order_valid($order_id,$order,'*');//验证订单有效性
        $this->checkOrderStatus('stop_shipping',$order);//检查订单状态
        
        //通知平台
        $objPlatform = $this->system->loadModel('system/platform');
        if($objPlatform->tell_platform('stop_shipping',array('id'=>$order_id))=== false){
            $this->api_response('fail','data fail',$result,$objPlatform->getErrorInfo());
        }
        
        $this->db->exec('update sdb_orders set status="pending" where version_id=0 and  order_id='.$order_id);
        $this->api_response('true',false,$result);
    }
    
    /* 设置订单disable(暂时内部用)
     *
     * @param int $order_id 
     * @param enum $disable 
     * 
     * @return 设置订单disable
     */
    function set_disable($order_id,$disable){
        $this->db->exec('update sdb_orders set disabled="'.$disable.'" where version_id=0 and  order_id='.$order_id);
    }

    /**
     * 修改订单信息接口
     *
     * @param array $data 
     *
     * @return 修改订单信息
     */
    function change_ome_order_info($data){
        $this->check_task_exists($data);
        $data['consignee'] = json_decode($data['consignee'],true);
        $data['order_item'] = json_decode($data['order_item'],true);
        $data['shipping'] = json_decode($data['shipping'],true);
        $data['payment_type'] = json_decode($data['payment_type'],true);
        $order_id = $data['order_id'];

        $this->verify_order_valid($order_id,$local_order,'*');//验证订单有效性

       if(is_string($data['member_id'])&&$data['member_id']!=''){
           $get_member_id = $this->db->selectrow('select member_id from sdb_members where uname = "'.$data['member_id'].'"');
            
           if($get_member_id['member_id']){
              $data['member_id'] = $get_member_id['member_id'];
           }else{
               $this->api_response('fail','data error',null,'用户名不存在');
           }
        }else{
           $data['member_id'] = NULL;
        }

        if(isset($data['is_tax'])){//如果远程要存在加税，就按远程走
            $local_order['is_tax'] = $data['is_tax'];
        }
        
        if(isset($data['is_protect'])){//如果远程要存在保价，就按远程走
            $local_order['is_protect'] = $data['is_protect'];
        }
        
        if(isset($data['tax_company'])){//如果远程要存在发票，就按远程走
            $local_order['tax_company'] = $data['tax_company'];
        }

        $data['ship_area'] = $this->getRegion($data['consignee']);

        $local_order = $this->organize_order_data($local_order,$data);//组织订单数据


        $data_order_item = $data['order_item']; 

            $this->update_order($order_id,$local_order);
            
            $this->addLog($order_id,'远程订单商品修改',null, null , '订单商品修改');  
                 
            $this->api_response('true','data true',$result,'订单商品修改成功');

    }
    

   /*生成买家留言接口*/
   function set_ome_message($data){

       $this->check_task_exists($data);
       $aData = array(
         'rel_order'=>$data['rel_order'],
         'date_line'=>$data['date_line'],
         'msg_from'=>$data['msg_from'],
         'subject'=>$data['subject'],
         'message'=>htmlspecialchars($data['message']),
       );
         $aData['from_type'] = 1;
         $aData['unread'] = 1;
         $aData['floder'] ='inbox';

         $order_id = $this->db->selectrow('SELECT order_id FROM sdb_orders where order_id = '.$aData['rel_order']);
       if($order_id['order_id']){
            $aRs = $this->db->query('SELECT * FROM sdb_message WHERE 0=1');
            $sSql = $this->db->getInsertSql($aRs,$aData);
            $this->db->exec($sSql);
            $this->api_response('true','data succ',$result,'订单留言更新成功');
       }else{
            $this->api_response('true','data succ',$result,'订单号不存在');
       }

    }

    /*生成配送地址接口*/
    function set_ome_ship_addr($data){

       $this->check_task_exists($data);
       $aData = array(
         'order_id'=>$data['order_id'],
         'ship_name'=>$data['ship_name'],
         'ship_state' => $data['ship_state'],
         'ship_city' => $data['ship_city'],
         'ship_district' => $data['ship_district'],
         'ship_addr'=>$data['ship_addr'],
         'ship_zip'=>$data['ship_zip'],
         'ship_tel'=>$data['ship_tel'],
         'ship_email'=>$data['ship_email'],
         'ship_time'=>$data['ship_time'],//送货时间
         'ship_mobile'=>$data['ship_mobile'],
       );
       $order_id = $this->db->selectrow('SELECT order_id FROM sdb_orders where order_id = '.$aData['order_id']);
       unset($aData['order_id']);
      
       if($order_id['order_id']){
            $save['ship_area'] = $this->getRegion($data);
            $save['ship_name'] = $aData['ship_name'];
            $save['ship_addr'] = $aData['ship_addr'];
            $save['ship_zip'] = $aData['ship_zip'];
            $save['ship_tel'] = $aData['ship_tel'];
            $save['ship_email'] = $aData['ship_email'];
            $save['ship_time'] = $aData['ship_time'];
            $save['ship_mobile'] = $aData['ship_mobile'];
            $aRs = $this->db->query('SELECT * FROM sdb_orders WHERE order_id ='.$order_id['order_id']);
            $sSql = 'update sdb_orders set ship_area = "'.$save['ship_area'].'",ship_name = "'.$save['ship_name'].'",ship_addr = "'.$save['ship_addr'].'",ship_zip = "'.$save['ship_zip'].'",ship_tel = "'.$save['ship_tel'].'",ship_tel = "'.$save['ship_tel'].'",ship_email = "'.$save['ship_email'].'",ship_time = "'.$save['ship_time'].'",ship_mobile = "'.$save['ship_mobile'].'" WHERE order_id ='.$order_id['order_id'];

            $this->db->exec($sSql);
            $this->api_response('true','data succ',$result,'订单配送信息修改成功');
       }else{
            $this->api_response('true','data succ',$result,'订单号不存在');
       }


   }

   function getRegion($data){
        if((empty($data['ship_district']))||($data['ship_district']=='')){
            $region_id=$this->db->selectrow("select region_id from sdb_regions where local_name ='".$data['ship_city']."'");
            $data['area'] = 'mainland:'.$data['ship_state'].'/'.$data['ship_city'].':'.$region_id['region_id'];
        }else{
         $region_id=$this->db->selectrow("select region_id from sdb_regions where local_name ='".$data['ship_district']."'");
            $data['area'] = 'mainland:'.$data['ship_state'].'/'.$data['ship_city'].'/'.$data['ship_district'].':'.$region_id['region_id'];
      }

      return $data['area'];
   }

    /*生成订单备注接口*/
    function set_ome_mark($data){

        $this->check_task_exists($data);
        $aData = array(
         'order_id'=>$data['order_id'],
         'mark_text'=>$data['mark_text'],
         'mark_type'=>$data['mark_type'],

       );
       $order_id = $this->db->selectrow('SELECT order_id FROM sdb_orders where order_id = '.$aData['order_id']);
       unset($aData['order_id']);
       if($order_id['order_id']){
            $aRs = $this->db->query('SELECT * FROM sdb_orders WHERE order_id ='.$order_id['order_id']);
            $sSql = $this->db->getUpdateSQL($aRs,$aData);
            $this->db->exec($sSql);
            $this->api_response('true','data succ',$result,'订单备注修改成功');
       }else{
            $this->api_response('true','data succ',$result,'订单号不存在');
       }

   }

    /*检查订单一系列状态*/
   function order_status($data,$local_order){
        if($local_order['ship_status']!= $data['ship_status']){
            $this->api_response('true','data true',$result,'订单已发货');
        }   
   }
    /**
     * 生成订单接口
     *
     * @param array $data 
     *
     * @return 生成订单接口
     */
    function generate_order_record($data){
        $order = json_decode($data['order'],true);
        $order_id = $order['order_id'];
        $dealer_id = $order['dealer_id'];
        $dealer_order_id = $order['dealer_order_id'];
        
        if(empty($order_id)){
           $this->api_response('fail','data fail',$result,'订单ID不能为空');
        }
        
        if(empty($dealer_id)){
           $this->api_response('fail','data fail',$result,'经销商ID不能为空');
        }
        
        if(empty($dealer_order_id)){
           $this->api_response('fail','data fail',$result,'经销商订单ID不能为空');
        }
         
        $this->verify_order_exist($order_id);
        $obj_member = $this->load_api_instance('verify_member_valid','1.0');
        $obj_member->verify_member_valid($dealer_id,$member);
        
        $arr_order_item = $order['items'];
        unset($order['items']);
        
        $obj_product = $this->load_api_instance('search_product_by_bn','1.0');
        $obj_product->filter_product_invalid($member,$arr_order_item,$filter_order_item);

        $arr_order_item = $filter_order_item;//过滤完毕
       
        $order = $this->organize_order_data($order,$arr_order_item);//组织订单数据
        
        $order['use_registerinfo'] = 'true';
        $order['member_id'] = $member['member_id'];
        
        //下单引发库存变化
        /*$objGoodsStatus = $this->system->loadModel('trading/goodsstatus');
        $op_goods_id = array();
        foreach($arr_order_item as $order_item){
            $op_goods_id[] = $order_item['goods_id'];
        }
        $objGoodsStatus->batchEditStart($op_goods_id,array('updateAct'=>'store'));*/
        
        //过滤商品带引号
        $obj_tools = $this->load_api_instance('get_http','1.0');
        $order = $obj_tools->addslashes_array($order);
        
        $this->create_order($order,$insert_order);
        $this->create_order_item($order['order_id'],$arr_order_item,$insert_order_item);
        
        //$objGoodsStatus->batchEditEnd(); //下单引发库存变化
        
        $this->addLog($order_id,'远程订单创建',null, null , '订单创建');
        
        $insert_order['items'] = $insert_order_item;
        $insert_order['effect_time'] = $insert_order['effect_time'] - $insert_order['createtime'];
        
        $insert_order = $obj_tools->stripslashes_array($insert_order);//去斜杠
         
        $insert_order = $this->filter_order_output($insert_order);
        $result['data_info'] = $insert_order;
        $this->api_response('true',false,$result);
    }    
    
    /**
     * 生成订单时，根据传递的参数生成订单数据
     * @param array $aOrder 
     * item_type  'goods' 普通商品
                  'gift' 赠品
                  'adjunct' 配件
                  'pkg' 捆绑商品
     * @return array
     */
    function organize_order_data($aOrder,$aOrderItem){

        if(!empty($aOrderItem['tax_company']) && 'true' != $aOrderItem['is_tax']){
           $this->api_response('fail','data fail',$result,'如果要开票,必须要交税');
        }

        //edit order products begin 
        $order_items = $this->db->select('select * from sdb_order_items where order_id ='.$aOrder['order_id']);
        foreach($order_items as $order_key => $order_val){
           $local_bn[] = $order_val['bn'];
        }
        //校验订单中商品是否有效
        $return_product = $this->check_goods_vaild($aOrderItem['order_item'],$aOrder['order_id']);
        $res_bn = '';
        foreach($return_product as $return_key => $return_val){
            if($return_val!==NULL){
                $res_bn = implode('、',$return_val).'、';
            }
        }
        if($res_bn!=''){
            //$res_bn = substr($res_bn,0,strlen($res_bn)-1);
            $this->api_response('fail',false,'该订单下'.$res_bn.'不存在.','该订单下'.$res_bn.'不存在.');
        }
        
        $weight = 0;//重量
        $tostr = '';//商品描述
        $itemnum = 0;//数量
        $cost_item = 0;//商品总价
        $order_tostr = '';
        foreach($aOrderItem['order_item'] as $order_item){
            if(($order_item['del_status'] == 'normal')&&($order_item['is_type'] != 'gift')){
                $tostr .= $order_item['name'].',';
                $itemnum += $order_item['nums'];
                $cost_item += $order_item['amount'];
                $order_tostr .= $order_item['name'].'('.$order_item['nums'].'),';
            }
        }

        $this->db->exec('UPDATE sdb_orders SET tostr = \''.$order_tostr.'\' WHERE order_id ='.$aOrder['order_id']);

        if(!empty($tostr)){
            $tostr = substr($tostr,0,strlen($tostr)-1);
        }

        $adj_order_items = $this->db->select('select * from sdb_order_items where order_id ='.$aOrder['order_id'].' and is_type = "goods"');

        foreach($adj_order_items as $adj_order_key => $adj_order_val){
           $adj_local_bn[] = $adj_order_val['bn'];
        }

     foreach($aOrderItem['order_item'] as $order_item){
//pkg adjunct gift goods
       switch($order_item['is_type']){
          case 'goods':
           $this->edit_order_type($order_item,'goods',$local_bn,'',$aOrder['order_id']);
          break;
          case 'gift':
           $this->edit_order_type($order_item,'gift',$local_bn,'',$aOrder['order_id']);
          break;
          case 'adjunct':
           $this->edit_order_type($order_item,'adjunct',$local_bn,$adj_local_bn,$aOrder['order_id']);
          break;
          case 'pkg':
           $this->edit_order_type($order_item,'pkg',$local_bn,'',$aOrder['order_id']);
          break;
       }
    }
    //edit order products end 
        $aOrder['weight'] = $aOrderItem['weight'];
        $aOrder['tostr'] = $tostr;
        $aOrder['itemnum'] = $itemnum;
        $aOrder['cost_item'] = $aOrderItem['cost_item'];
        $aOrder['total_amount'] = $aOrderItem['total_amount'];
        $aOrder['cost_tax'] = $aOrderItem['cost_tax'];
        $aOrder['tax_company'] = $aOrderItem['tax_company'];
        $aOrder['final_amount'] = $aOrderItem['total_amount'];
        $aOrder['payed'] = $aOrderItem['payed'];
        $aOrder['last_change_time'] = $aOrderItem['last_change_time'];
        $aOrder['currency'] = $aOrderItem['currency'];
        $aOrder['cur_rate'] = $aOrderItem['cur_rate'];
        $aOrder['score_u'] = ($aOrderItem['score_u']==NULL)?0.000:$aOrderItem['score_u'];
        $aOrder['score_g'] = ($aOrderItem['score_g']==NULL)?0.000:$aOrderItem['score_g'];
        $aOrder['score_e'] = ($aOrderItem['score_e']==NULL)?0.000:$aOrderItem['score_e'];
        $aOrder['advance'] = ($aOrderItem['advance']==NULL)?0.000:$aOrderItem['advance'];
        $aOrder['discount'] = ($aOrderItem['discount']==NULL)?0.000:(0 - $aOrderItem['discount']);
        //$aOrder['status'] = $aOrderItem['status'];
        $aOrderItem['dt_id'] = $this->db->selectrow('select dt_id from sdb_dly_type where dt_name = "'.$aOrderItem['shipping']['shipping_name'].'"');
        $aOrder['shipping_id'] = $aOrderItem['dt_id']['dt_id'];
        $aOrder['shipping'] = $aOrderItem['shipping']['shipping_name'];
        $aOrder['cost_freight'] = $aOrderItem['shipping']['cost_freight'];
        $aOrder['is_protect'] = $aOrderItem['shipping']['is_protect'];
        $aOrder['cost_protect'] = $aOrderItem['shipping']['cost_protect'];
        $refund_list = $this->db->select('select refund_id from sdb_refunds where order_id = '.$aOrder['order_id']);
        if(($aOrderItem['payed'] > $aOrderItem['total_amount'])&&count($refund_list)>0){
            $aOrder['pay_status'] = 4;//部分退款
        }elseif(($aOrderItem['total_amount'] == $aOrderItem['payed'])||($aOrderItem['payed'] > $aOrderItem['total_amount'])){
            $aOrder['pay_status'] = 1;//已支付
        }elseif($aOrderItem['total_amount'] > $aOrderItem['payed']){
                        if(intval($aOrderItem['payed'])==0){
                           $aOrder['pay_status'] = 0;//未支付
                        }else{
               $aOrder['pay_status'] = 3;//部分支付
                        }
        }
        $aOrder['ship_status'] = $this->change_ship_status($aOrder['order_id']);
        //$aOrder['cost_payment'] = $aOrderItem['payment_type']['cost_payment'];
        $aOrder['member_id'] = $aOrderItem['member_id'];
        $aOrder['ship_name'] = $aOrderItem['consignee']['ship_name'];
        $aOrder['ship_area'] = $aOrderItem['ship_area'];
        $aOrder['ship_addr'] = $aOrderItem['consignee']['ship_addr'];
        $aOrder['ship_zip'] = $aOrderItem['consignee']['ship_zip'];
        $aOrder['ship_tel'] = $aOrderItem['consignee']['ship_tel'];
        $aOrder['ship_email'] = $aOrderItem['consignee']['ship_email'];
        $aOrder['ship_time'] = $aOrderItem['consignee']['ship_time'];
        $aOrder['ship_mobile'] = $aOrderItem['consignee']['ship_mobile'];

        return $aOrder;
    }

     /**
     * 订单编辑处理不同的商品类型
     * @param array $order_item 商品信息
     * @param array $type 商品类型
     * @return array
     */
     function edit_order_type($order_item,$type,$local_bn,$adj_local_bn=array(),$order_id){
          if($type =='goods'){
             if($order_item['del_status']=='normal'&&!in_array($order_item['bn'],$local_bn)){//新增
                      //$order_id = $aOrder['order_id'];
                      $products = $this->db->selectrow('select freez,store,goods_id,product_id from sdb_products where bn ="'.$order_item['bn'].'"');

                      $goods = $this->db->selectrow('select store,type_id from sdb_goods where goods_id ="'.$products['goods_id'].'"');
                      $new_order_item['order_id'] = $order_id;
                      $new_order_item['product_id'] = $products['product_id'];
                      $new_order_item['type_id'] = $goods['type_id'];
                      $new_order_item['bn'] = $order_item['bn'];
                      $new_order_item['name'] = $order_item['name'];
                      $new_order_item['dly_status'] = 'storage';
                      $new_order_item['nums'] = $order_item['nums'];
                      $new_order_item['sendnum'] = $order_item['sendnum'];
                      $new_order_item['price'] = $order_item['price'];
                      $new_order_item['amount'] = $order_item['amount'];
                      $new_order_item['is_type'] = $order_item['is_type'];
                      $new_order_item['point'] = $order_item['point'];
                          $aRs = $this->db->query("SELECT * FROM sdb_order_items WHERE 0=1");
                          $sSql = $this->db->getInsertSql($aRs,$new_order_item);
                          $this->db->exec($sSql);
                          $item_id = $this->db->lastinsertid();
                          if($item_id){
                            $nums = $this->db->selectrow('select nums from sdb_order_items where item_id = '.$item_id.'');
                          }
                          $get_bn = $this->db->selectrow('select bn from sdb_products where bn ="'.$order_item['bn'].'"');
                          if($get_bn['bn']){
                             $dif_pro_sql = 'update sdb_products set freez = '.($products['freez']+$nums['nums']).',store = '.($products['store']-$nums['nums']).' where bn = "'.$order_item['bn'].'"';
                             $this->db->exec($dif_pro_sql);
                             $dif_goods_sql = 'update sdb_goods set store = '.($goods['store']-$nums['nums']).' where goods_id = '.$products['goods_id'].'';
                             $this->db->exec($dif_goods_sql);
                          }
                }else{
                   if($order_item['del_status']=='cancel'){//删除
                      $item_id = $this->db->selectrow('select item_id from sdb_order_items where bn ="'.$order_item['bn'].'" and order_id ='.$order_id);

                      $get_bn = $this->db->selectrow('select bn from sdb_products where bn ="'.$order_item['bn'].'"');
                      if($item_id['item_id']){
                          $nums = $this->db->selectrow('select nums from sdb_order_items where item_id = '.$item_id['item_id'].'');
                          if($get_bn['goods_id']){
                            $products = $this->db->selectrow('select freez,store,goods_id from sdb_products where bn ="'.$order_item['bn'].'"');

                            $goods = $this->db->selectrow('select store from sdb_goods where goods_id = '.$products['goods_id'].'');
                          }

                          $sSql = 'delete from sdb_order_items where item_id = '.$item_id['item_id'].' ';
                          $this->db->exec($sSql);

                          if($get_bn['goods_id']){
                             $dif_pro_sql = 'update sdb_products set freez = '.($products['freez']-$nums['nums']).',store = '.($products['store']+$nums['nums']).' where bn = "'.$get_diff_result[$k].'"';
                              $this->db->exec($dif_pro_sql);

                             $dif_goods_sql = 'update sdb_goods set store = '.($goods['store']+$nums['nums']).' where goods_id = '.$products['goods_id'].'';
                             $this->db->exec($dif_goods_sql);
                          }

                      }
                    }else{//更新
                         $item_id = $this->db->selectrow('SELECT item_id FROM sdb_order_items WHERE order_id='.$order_id.' and bn ="'.$order_item['bn'].'"');
                       
                         $nums = $this->db->selectrow('select nums from sdb_order_items where item_id = '.$item_id['item_id']);
                       
                         $products = $this->db->selectrow('select freez,store,goods_id from sdb_products where bn ="'.$order_item['bn'].'"');
                     if($products['goods_id']){
                         $goods = $this->db->selectrow('select store from sdb_goods where goods_id = '.$products['goods_id']);

                        if($nums['nums']>$order_item['nums']){

                           $diff_nums = $nums['nums'] - $order_item['nums'];
                           $pro_freez = $products['freez']-$diff_nums;
                           $pro_store = $products['store']+$diff_nums;
                           $goods_store = $goods['store']+$diff_nums;

                        }elseif($nums['nums']<$order_item['nums']){
                             
                           $diff_nums = $order_item['nums'] - $nums['nums'];
                           $pro_freez = $products['freez']+$diff_nums;    
                           $pro_store = $products['store']-$diff_nums;
                           $goods_store = $goods['store']-$diff_nums;
                        }else{

                           $pro_freez = $products['freez'];
                           $pro_store = $products['store'];
                           $goods_store = $goods['store'];
                        }
                       
                         $dif_pro_sql = 'update sdb_products set freez = '.$pro_freez.',store = '.$pro_store.' where bn = "'.$order_item['bn'].'"';
                          $this->db->exec($dif_pro_sql);

                         $dif_goods_sql = 'update sdb_goods set store = '.$goods_store.' where goods_id =  '.$products['goods_id'];
                          $this->db->exec($dif_goods_sql);
                      }
                          $update_item['nums'] = $order_item['nums'];
                          $update_item['amount'] = $order_item['amount'];
                          $update_item['price'] = $order_item['price'];
                          $rs_sql = 'update sdb_order_items set nums = '.$order_item['nums'].',amount = '.$order_item['amount'].', price = '.$order_item['price'].'  where item_id='.$item_id['item_id'];
                          $this->db->exec($rs_sql);
                    }
          
                }
          }

          if($type == 'gift'){
                   if($order_item['del_status']=='cancel'){//删除
                      $gift_item = $this->db->selectrow('select gi.* from sdb_gift_items gi left join sdb_gift g on gi.gift_id = g.gift_id where g.gift_bn ="'.$order_item['bn'].'" and gi.order_id ='.$order_id);

                      $get_bn = $this->db->selectrow('select bn from sdb_products where bn ="'.$order_item['bn'].'"');

                      $nums = $this->db->selectrow('select nums from sdb_gift_items where order_id = '.$order_id);

                      if($get_bn['goods_id']){
                        $products = $this->db->selectrow('select freez,store,goods_id from sdb_products where bn ="'.$order_item['bn'].'"');

                        $goods = $this->db->selectrow('select store from sdb_goods where goods_id = '.$products['goods_id'].'');
                      }

                      $sSql = 'delete from sdb_gift_items where order_id = '.$order_id;
                      $this->db->exec($sSql);

                      if($get_bn['goods_id']){
                         $dif_pro_sql = 'update sdb_products set freez = '.($products['freez']-$nums['nums']).',store = '.($products['store']+$nums['nums']).' where bn = "'.$get_diff_result[$k].'"';
                          $this->db->exec($dif_pro_sql);

                         $dif_goods_sql = 'update sdb_goods set store = '.($goods['store']+$nums['nums']).' where goods_id = '.$products['goods_id'].'';
                         $this->db->exec($dif_goods_sql);
                      }


                    }else{//更新
                      $gift_item = $this->db->selectrow('select gi.* from sdb_gift_items gi left join sdb_gift g on gi.gift_id = g.gift_id where g.gift_bn ="'.$order_item['bn'].'" and gi.order_id ='.$order_id);
                       
                      $nums = $this->db->selectrow('select nums from sdb_gift_items where order_id = '.$order_id);
                      
                      $products = $this->db->selectrow('select freez,store,goods_id from sdb_products where bn ="'.$order_item['bn'].'"');

                     if($products['goods_id']){
                         $goods = $this->db->selectrow('select store from sdb_goods where goods_id = '.$products['goods_id']);

                        if($nums['nums']>$order_item['nums']){

                           $diff_nums = $nums['nums'] - $order_item['nums'];
                           $pro_freez = $products['freez']-$diff_nums;
                           $pro_store = $products['store']+$diff_nums;
                           $goods_store = $goods['store']+$diff_nums;

                        }elseif($nums['nums']<$order_item['nums']){
                             
                           $diff_nums = $order_item['nums'] - $nums['nums'];
                           $pro_freez = $products['freez']+$diff_nums;    
                           $pro_store = $products['store']-$diff_nums;
                           $goods_store = $goods['store']-$diff_nums;
                        }else{
                           $pro_freez = $products['freez'];
                           $pro_store = $products['store'];
                           $goods_store = $goods['store'];
                        }
                       
                         $dif_pro_sql = 'update sdb_products set freez = '.$pro_freez.',store = '.$pro_store.' where bn = "'.$order_item['bn'].'"';
                          $this->db->exec($dif_pro_sql);

                         $dif_goods_sql = 'update sdb_goods set store = '.$goods_store.' where goods_id =  '.$products['goods_id'];
                          $this->db->exec($dif_goods_sql);
                      }
                          $update_item['nums'] = $order_item['nums'];
                          $update_item['amount'] = $order_item['amount'];
                          $update_item['price'] = $order_item['price'];
                          $rs_sql = 'update sdb_gift_items set nums = '.$order_item['nums'].',amount = '.$order_item['amount'].' where order_id='.$order_id;
                          $this->db->exec($rs_sql);
                    }
          }
          if($type=='pkg'){
             if($order_item['del_status']=='normal'&&!in_array($order_item['bn'],$local_bn)){//新增
                      $order_id = $order_id;
                      $products = $this->db->selectrow('select freez,store,goods_id,product_id from sdb_products where bn ="'.$order_item['bn'].'"');

                      $package = $this->db->select('select pp.product_id,g.* from sdb_package_product pp left join sdb_goods g on pp.goods_id = g.goods_id where pp.product_id = '.$products['product_id']);

                      $find_pkg_goods = $this->db->selectrow('select goods_id from sdb_package_product where product_id = '.$products['product_id']);

                      $package_fetch = $this->db->select('select * from sdb_package_product where goods_id = '.$find_pkg_goods['goods_id']);

                      $pkg_adjinfo = '';
                      $pkg_adjname = '';
                     foreach($package_fetch as $pk=>$pv){
                        $pkg_adjinfo .= $pv['product_id'].'_0_'.$order_item['nums'].'|';
                        $product_name = $this->db->selectrow('select name from sdb_products where product_id = '.$pv['product_id']);
                        $pkg_adjname .= '+'.$product_name['name'].'('.$order_item['nums'].')';
                     }
                      $addon = array('adjinfo'=>$pkg_adjinfo,'adjname'=>$pkg_adjname);

                     foreach($package as $pkk=>$pkv){

                      $new_order_item['order_id'] = $order_id;
                      $new_order_item['product_id'] = $pkv['goods_id'];
                      $new_order_item['dly_status'] = 'storage';
                      $new_order_item['bn'] = $pkv['bn'];
                      $new_order_item['addon'] = serialize($addon);
                      $new_order_item['amount'] = $pkv['price']*$pkv['nums'];
                      $new_order_item['price'] = $pkv['price'];
                      $new_order_item['name'] = $pkv['name'];
                      $new_order_item['sendnum'] = $order_item['sendnum'];
                      $new_order_item['is_type'] = 'pkg';
                      $new_order_item['point'] = $order_item['point'];
                      $_query = $this->db->selectrow('select product_id from sdb_order_items where product_id = '.$pkv['goods_id'].' and order_id = '.$order_id);
                     //if($pkv['goods_id']!=$_query['product_id']){
                     if($_query==NULL){
                          $aRs = $this->db->query("SELECT * FROM sdb_order_items WHERE 0=1");
                          $sSql = $this->db->getInsertSql($aRs,$new_order_item);
                          $this->db->exec($sSql);
                          $item_id = $this->db->lastinsertid();

                          $nums = $this->db->selectrow('select nums from sdb_order_items where item_id = '.$item_id.'');
                          $get_bn = $this->db->selectrow('select bn from sdb_products where bn ="'.$order_item['bn'].'"');
                          if($get_bn['bn']){
                             $dif_pro_sql = 'update sdb_products set freez = '.($products['freez']+$nums['nums']).',store = '.($products['store']-$nums['nums']).' where bn = "'.$order_item['bn'].'"';
                             $this->db->exec($dif_pro_sql);
                             $dif_goods_sql = 'update sdb_goods set store = '.($goods['store']-$nums['nums']).' where goods_id = '.$products['goods_id'].'';
                             $this->db->exec($dif_goods_sql);
                          }
                      }
                   }
                }else{
                   if($order_item['del_status']=='cancel'){
                        $item_addon = $this->db->selectrow('SELECT addon,item_id FROM sdb_order_items WHERE order_id='.$order_id.' and is_type ="pkg"');
                       if($item_addon['addon']){
                           $addon = unserialize($item_addon['addon']);
                           if($addon['adjinfo']){
                               $viop = explode('|',$addon['adjinfo']);
                               foreach($viop as $vv=>$vt){
                                   if($vt){
                                       $rpcid =explode("_",$vt);
                                       if($rpcid[2]){
                                           $pkg_bn = $this->db->selectrow('select * from sdb_products where product_id = '.$rpcid[0]);
                                           if($pkg_bn['good_id']){
                                               $nums = $this->db->selectrow('select nums from sdb_order_items where item_id = '.$item_addon['item_id'].'');
                                               $goods = $this->db->selectrow('select store from sdb_goods where goods_id = '.$pkg_bn['goods_id'].'');
                                               $dif_pkg_pro_sql = 'update sdb_products set freez = '.($pkg_bn['freez']-$nums['nums']).',store = '.($pkg_bn['store']+$nums['nums']).' where bn = "'.$rpcid[0].'"';
                                               $this->db->exec($dif_pkg_pro_sql);

                                                $dif_pkg_goods_sql = 'update sdb_goods set store = '.($goods['store']+$nums['nums']).' where goods_id = '.$pkg_bn['goods_id'].'';
                                                $this->db->exec($dif_pkg_goods_sql);
                                           }
                                       }
                                   }
                               }
                           }
                           $this->db->exec('delete from sdb_order_items where order_id='.$order_id.' and item_id = '.$item_addon['item_id']);
                       }
                   }else{//更新

                        $item_addon = $this->db->selectrow('SELECT nums,addon,item_id,product_id FROM sdb_order_items WHERE order_id='.$order_id.' and is_type ="pkg"');
                        $product_item = $this->db->selectrow('SELECT product_id FROM sdb_products WHERE bn = "'.$item_addon['bn'].'"');

                       if($item_addon['addon']){
                           $addon = unserialize($item_addon['addon']);
                           if($addon['adjinfo']){
                               $viop = explode('|',$addon['adjinfo']);
                               $rpcid_implode = array();
                               foreach($viop as $vv=>$vt){
                                   if($vt){
                                       $rpcid =explode("_",$vt);
                                       if($rpcid[2]){
                                           if($rpcid[0] == $product_item['product_id']){
                                                $rpcid[2] = $order_item['nums'];
                                                $pkg_nums = $item_addon['nums'] - ((($item_addon['nums']*$rpcid[1]) - $order_item['nums'])/$rpcid[1]);
                                                $pro_id = $item_addon['product_id'];
                                           }
                                       }
                                       $rpcid_implode[] = implode('_',$rpcid);
                                       $rpcid = implode('|',$rpcid_implode);
                                   }
                               }
                               $addon['adjinfo'] = $rpcid;
                           }
                           $item_addon['addon'] = serialize($addon);
                           if($pkg_nums!=''){
                             $where = 'nums = "'.$pkg_nums.'",';
                           }
                           $this->db->exec("update sdb_order_items set ".$where." addon = '".$item_addon['addon']."' where product_id = ".$item_addon['product_id']." and order_id = ".$order_id." and is_type = 'pkg'");
                       }

                        /* $item_id = $this->db->selectrow('SELECT item_id FROM sdb_order_items WHERE order_id='.$order_id.' and bn ="'.$order_item['oid'].'"');
                       
                         $nums = $this->db->selectrow('select nums from sdb_order_items where item_id = '.$item_id['item_id']);
                       
                         $products = $this->db->selectrow('select freez,store,goods_id from sdb_products where bn ="'.$order_item['oid'].'"');
                     if($products['goods_id']){
                         $goods = $this->db->selectrow('select store from sdb_goods where goods_id = '.$products['goods_id']);

                        if($nums['nums']>$order_item['nums']){

                           $diff_nums = $nums['nums'] - $order_item['nums'];
                           $pro_freez = $products['freez']-$diff_nums;
                           $pro_store = $products['store']+$diff_nums;
                           $goods_store = $goods['store']+$diff_nums;

                        }elseif($nums['nums']<$order_item['nums']){
                             
                           $diff_nums = $order_item['nums'] - $nums['nums'];
                           $pro_freez = $products['freez']+$diff_nums;    
                           $pro_store = $products['store']-$diff_nums;
                           $goods_store = $goods['store']-$diff_nums;
                        }else{

                           $pro_freez = $products['freez'];
                           $pro_store = $products['store'];
                           $goods_store = $goods['store'];
                        }
                       
                         $dif_pro_sql = 'update sdb_products set freez = '.$pro_freez.',store = '.$pro_store.' where bn = "'.$order_item['bn'].'"';
                          $this->db->exec($dif_pro_sql);

                         $dif_goods_sql = 'update sdb_goods set store = '.$goods_store.' where goods_id =  '.$products['goods_id'];
                          $this->db->exec($dif_goods_sql);
                      }
                          $update_item['nums'] = $order_item['nums'];
                          $update_item['amount'] = $order_item['amount'];
                          $update_item['price'] = $order_item['price'];
                          //todo
                          $update_item['addon'] = serialize($addon);
                          $rs_sql = 'update sdb_order_items set addon = '.$update_item['addon'].',nums = '.$order_item['nums'].',amount = '.$order_item['amount'].', price = '.$order_item['price'].'  where item_id='.$item_id['item_id'];
                          $this->db->exec($rs_sql);*/
                    }//end
                }
          }
          if($type=='adjunct'){
              if($order_item['del_status']=='cancel'){
                   foreach($adj_local_bn as $adj_key=>$adj_val){
                       $adj_addon = $this->db->selectrow('SELECT addon FROM sdb_order_items WHERE order_id='.$order_id.' and bn = "'.$adj_val.'"');
                       $adj_addon_v = unserialize($adj_addon['addon']);
                       if($adj_addon_v['adjinfo']){
                               $viop = explode('|',$adj_addon_v['adjinfo']);
                               foreach($viop as $vv=>$vt){
                                   if($vt){
                                       $rpcid =explode("_",$vt);
                                       if($rpcid[2]){
                                           $pkg_bn = $this->db->selectrow('select * from sdb_products where product_id = '.$rpcid[0]);
                                         if($rpcid[0] == $order_item['bn']){
                                           if($pkg_bn['good_id']){
                                               //
                                               $nums = $this->db->selectrow('select nums from sdb_order_items where item_id = '.$item_addon['item_id'].'');
                                               $goods = $this->db->selectrow('select store from sdb_goods where goods_id = '.$pkg_bn['goods_id'].'');
                                               $dif_pkg_pro_sql = 'update sdb_products set freez = '.($pkg_bn['freez']-$nums['nums']).',store = '.($pkg_bn['store']+$nums['nums']).' where bn = "'.$rpcid[0].'"';
                                               $this->db->exec($dif_pkg_pro_sql);

                                                $dif_pkg_goods_sql = 'update sdb_goods set store = '.($goods['store']+$nums['nums']).' where goods_id = '.$pkg_bn['goods_id'].'';
                                                $this->db->exec($dif_pkg_goods_sql);
                                           }
                                         }
                                       }
                                   }
                               }
                       }
                   }
              }
          }
     }
     /*修改订单状态*/
     function change_ship_status($order_id){
          $orders = $this->db->selectrow('select order_id,ship_status from sdb_orders where order_id = '.$order_id);

          if($orders['order_id']){
            $num['nums'] = 0;
            $sendnum['sendnum'] = 0;
            $orders_items = $this->db->select('select order_id,nums,sendnum from sdb_order_items where order_id = '.$order_id);
            $gift_items = $this->db->select('select sendnum,nums from sdb_gift_items where order_id = '.$order_id);

            foreach($orders_items as $k => $v){
               $num['nums'] += $v['nums'];
               $sendnum['sendnum'] += $v['sendnum'];
            }

            foreach($gift_items as $k => $v){
               $num['nums'] += $v['nums'];
               $sendnum['sendnum'] += $v['sendnum'];
            }
            if($sendnum['sendnum'] == 0){
                $return_data['ship_status'] = 0;
            }elseif($num['nums'] > $sendnum['sendnum']){
                $return_data['ship_status'] = 2;
            }elseif($sendnum['sendnum'] == $num['nums']){
                $return_data['ship_status'] = 1;
            }else{
                $return_data['ship_status'] = $orders['ship_status'];
            }
             return $return_data['ship_status'];
          }

     }
     /**
     * 校验订单中商品是否有效
     * @param array $order_item
     * @return array
     */
     function check_goods_vaild($order_item,$order_id){

        //原始订单商品信息
        $local_order_pro = array();
        $local_gift = $this->db->select('select g.gift_bn from sdb_gift_items gi left join sdb_gift g on gi.gift_id = g.gift_id where gi.order_id = '.$order_id);
        foreach($local_gift as $gk =>$gv){
           $local_order_pro['gift'][] = $gv['gift_bn'];//赠品
        }
        $local_order = $this->db->select('select * from sdb_order_items where order_id = '.$order_id);
        foreach($local_order as $ok =>$ov){
           if($ov['is_type'] == 'goods'){
               $local_order_pro['goods'][] = $ov['bn'];//普通商品
               $addon = unserialize($ov['addon']);
               if($addon['adjinfo']){
                   $viop = explode('|',$addon['adjinfo']);
                   foreach($viop as $vv=>$vt){
                       if($vt){
                           $rpcid =explode("_",$vt);
                           if($rpcid[2]){
                               $adjunct_bn = $this->db->selectrow('select * from sdb_products where product_id = '.$rpcid[0]);
                               $local_order_pro['adjunct'][] = $adjunct_bn['bn'];//配件
                           }
                       }
                   }
               }

           }
           if($ov['is_type'] == 'pkg'){
               $addon = unserialize($ov['addon']);
               if($addon['adjinfo']){
                   $viop = explode('|',$addon['adjinfo']);
                   foreach($viop as $vv=>$vt){
                       if($vt){
                           $rpcid =explode("_",$vt);
                           if($rpcid[2]){
                               $adjuct_bn = $this->db->selectrow('select * from sdb_products where product_id = '.$rpcid[0]);
                               $local_order_pro['pkg'][] =$adjuct_bn['bn'];//捆绑商品
                           }
                       }
                   }
               }
           }
        }
        $remote_order_pro = array();
        //远程订单商品信息
        foreach($order_item as $order_key){
            switch($order_key['is_type']){
                case 'goods':
                    $remote_order_pro['goods'][] = $order_key['bn'];
                    break;
                case 'gift':
                    $remote_order_pro['gift'][] = $order_key['bn'];
                    break;
                case 'adjunct':
                    $remote_order_pro['adjunct'][] = $order_key['bn'];
                    break;
                case 'pkg':
                    $remote_order_pro['pkg'][] = $order_key['bn'];
                    break;
            }
        }
        $result = array();
        $sdf = array_merge_recursive($remote_order_pro,$local_order_pro);

        foreach($sdf as $remote_key =>$remote_value){
            $remote_value = array_unique($remote_value);
            switch($remote_key){
                case 'goods':
                   $result[] = $this->check_diff($remote_value,'goods');
                    break;
                case 'gift':
                   $result[] = $this->check_diff($remote_value,'gift');
                    break;
                case 'adjunct':
                    $result[] = $this->check_diff($remote_value,'adjunct');
                    break;
                case 'pkg':
                    $result[] = $this->check_diff($remote_value,'pkg');
                    break;
            }
        }


        return $result;
        
     }

     /**
     * 订单编辑---本地订单商品和远程订单商品比较
     * @param string $remote_value bn
     * @param string $type 商品类型
     * @return 
     */
     function check_diff($remote_value,$type){

            foreach($remote_value as $rk=>$rv){
              switch($type){
                case 'gift':
                    $gift = $this->db->selectrow('select * from sdb_gift where gift_bn = "'.$rv.'"');
                    if(!$gift){
                        $wrong_bn[] = '商品类型为:'.$type.',货号为:'.$rv;
                    }
                    break;
                case 'goods':
                case 'adjunct':
                case 'pkg':
                    $pkg = $this->db->selectrow('select * from sdb_products where bn = "'.$rv.'"');
                    if(!$pkg){
                        $wrong_bn[] = '商品类型为:'.$type.',货号为:'.$rv;
                    }
                    break;
              }

           }

               return $wrong_bn;
     }

     /**
     * 更新售后申请
     * @param array $aftersale_info 售后申请信息
     * @return array
     */
    function update_ome_aftersale($aftersale_info){
      $this->check_task_exists($data);
      if($this->system->getConf('site.is_open_return_product')){
        $this->check_aftersale_id($aftersale_info['aftersale_id']);
        $status = array(6,7,8,9);
        if(!in_array($aftersale_info['status'],$status)){
              $data['status'] = $aftersale_info['status'];
        }else{
              unset($aftersale_info['status']);
        }
        $ome_status = array(
                    0 => '默认',
                    1 => '退货',
                    2 => '换货',
                    3 => '拒绝',
        );
        $aftersale_info['comment'] = json_decode($aftersale_info['comment'],1);
        $comment = array();
        foreach($aftersale_info['comment'] as $k=>$v){
            $comment[$k]['time'] = time();
            $comment[$k]['content'] = '需'.$ome_status[$v['status']].'商品名:'.$v['name'].',货号:'.$v['bn'].'.需要支付的金额:'.$v['need_money'].',折旧(其他费用)为:'.$v['other_money'].'.<br>';
        }

        $data['comment'] = serialize($comment);

        $return_product = $this->db->selectrow('select return_id from sdb_connect_ome_order_return_product where refer_id = '.$aftersale_info['aftersale_id']);
        $return_product['return_id'] = $return_product['return_id']?$return_product['return_id']:$aftersale_info['aftersale_id'];
        $uAftersale=$this->db->query('select * from sdb_return_product where return_id = '.$return_product['return_id']);
        $sSQL=$this->db->GetUpdateSQL($uAftersale,$data);

        if($sSQL==''){
           $sSQL = 'update sdb_return_product set comment = "'.$data['comment'].'" where return_id ='.$return_product['return_id'];
        }
        if(!$this->db->exec($sSQL)){
            $this->api_response('fail','data fail',$result,'售后申请更新失败');
        }else{
            $this->api_response('true','data true',$result,'售后申请更新成功');
        }
      }else{
            $this->api_response('fail','data fail','','售后申请未开启');
      }
    }
    
    function check_aftersale_id($aftersale_id){
        $aftersale = $this->db->selectrow('select a.* from sdb_return_product a left join sdb_connect_ome_order_return_product b on a.return_id = b.return_id where a.return_id='.$aftersale_id.' or b.refer_id = '.$aftersale_id);
        if($aftersale){
            return true;
        }else{
            $this->api_response('fail','data fail',$result,'售后申请不存在');
        }
    }


     /**
     * 添加售后申请
     * @param array $aftersale_info 售后申请信息
     * @return array
     */
    function add_ome_aftersale($aftersale_info){

      $this->check_task_exists($data);
      if($this->system->getConf('site.is_open_return_product')){
        $aftersale = $this->db->selectrow('select order_id from sdb_orders where order_id = '.$aftersale_info['order_id']);
        if(!$aftersale){
            $this->api_response('true','fail fail',$result,'售后申请不存在');
        }
        $data['order_id'] = $aftersale_info['order_id'];
        $data['title'] = $aftersale_info['title'];
        $data['content'] = $aftersale_info['content'];
        $data['image_file'] = $aftersale_info['image_file'];
        $data['add_time'] = $aftersale_info['add_time'];
        $data['status'] = $aftersale_info['status'];
        $data['comment'] = $aftersale_info['comment'];
        $data['product_data'] = serialize(json_decode($aftersale_info['product_data'],1));
        $get_member_id = $this->db->selectrow('select member_id from sdb_members where uname = "'.$aftersale_info['member_uname'].'"');
        if($get_member_id['member_id']){
            $data['member_id'] = $get_member_id['member_id'];
        }else{
            $this->api_response('fail','data fail',null,'该会员不存在');
        }

        $aRs = $this->db->query('select * from sdb_return_product WHERE  0=1');
        $sSQL=$this->db->getInsertSql($aRs,$data);
        if(!$this->db->exec($sSQL)){
            $this->api_response('fail','data fail',null,'售后申请更新失败');
        }else{
            $return_product = array();
            $return_product['return_id'] = $this->db->lastInsertId();
            $return_product['refer_id'] = $aftersale_info['aftersale_id'];
            $aRs1 = $this->db->query('select * from sdb_connect_ome_order_return_product WHERE  0=1');
            $return_sSQL=$this->db->getInsertSql($aRs1,$return_product);
            $this->db->exec($return_sSQL);
            $this->api_response('true','data true',null,'售后申请更新成功');
        }
      }else{
            $this->api_response('fail','data fail','','售后申请未开启');
      }
    }//watermark
     /**
     * 取大B默认币别
     *
     * @return array
     */
    function getDefault(){
        if($cur = $this->db->selectrow('select * from sdb_currency where def_cur=1')){
            return $cur;
        }else{
            return $this->db->selectrow('select * FROM sdb_currency');
        }
    }
    
    /**
     * 计算订单总价不包括支付费用
     * @param array $order 
     *
     * @return decimal
     */
    function amountExceptPay($order){
        return $order['cost_protect']+$order['cost_freight']+$order['cost_tax']+$order['cost_item']-$order['pmt_amount']-$order['discount'];
    }
    
    /**
     * 订单支付
     * @param array $order 
     * @param int $money 
     * @return 订单支付
     */
    function payed($order,$money){
       $pay_money = $order['payed'] + $money;//已支付金额        
        if($pay_money >= $order['total_amount']){//全额付款
            $pay_status = $order['pay_status'] == 2 ? 2 : 1;
        }else{//部分付款
            $pay_status = 3;
        }
        
        $a_update_order['pay_status'] = $pay_status;
        $a_update_order['payed'] = $pay_money;   
        $a_update_order['total_amount'] = $order['total_amount'];    
      
        //实际货币所需付款计算
        $obj_payments = $this->load_api_instance('search_payments_by_order','1.0');
        $a_update_order['final_amount'] = $order['total_amount'] * $order['cur_rate'];
        $a_update_order['final_amount'] = $obj_payments->formatNumber($a_update_order['final_amount']);
                 
        $this->update_order($order['order_id'],$a_update_order);
        $this->addLog($order['order_id'],'订单'.$order['order_id'].'付款'.$money,null, null , '付款');
    }
    
    /**
     * 改变订单支付方式
     * @param int $order_id 
     * @param int $payment 
     * @return 改变订单支付方式
     */
    function changeOrderPayment($order_id,$payment){
        $a_update_order['payment'] = $payment;             
        $this->update_order($order_id,$a_update_order);
    }
    
    /**
     * 过滤订单输出数据
     * @param array $act 
     * @param array $aOrder 
     * @return 检查订单状态
     */
    function filter_order_output($aOrder){  
        if(isset($aOrder['items']['member_id']))unset($aOrder['items']['member_id']);
        if(isset($aOrder['order_source']))unset($aOrder['order_source']);
        
        return $aOrder;
    }
    
    /**
     * 因为订单修改导致需要补款
     * @param array $act 
     * @param array $aOrder 
     * @return 检查订单状态
     */
    function fill_section($aOrder){
        $aUpdate['pay_status'] = 3;
       
        return $aUpdate;
    }
    
    /**
     * 检查订单状态
     * @param array $act 
     * @param array $aOrder 
     * @return 检查订单状态
     */
    function checkOrderStatus($act,$aOrder){    
       $error_msg = '';
        switch($act){
            case 'pay':
                if($aOrder['status'] != 'active' && $aOrder['status'] != 'pending'){
                   $error_msg = $this->get_error('not_active');
                }else if( $aOrder['pay_status'] == '1'){
                   $error_msg = $this->get_error('already_pay');
                }else if($aOrder['pay_status'] == '2'){
                   $error_msg = $this->get_error('go_process');
                }else if($aOrder['pay_status'] == '4'){
                   $error_msg = $this->get_error('already_part_refund');
                }else if($aOrder['pay_status'] == '5'){
                   $error_msg = $this->get_error('already_full_refund');
                }
            break;
            case 'refund':
                if($aOrder['status'] != 'active'&& $aOrder['status'] != 'pending' ){
                    $error_msg = $this->get_error('not_active');
                }else if($aOrder['pay_status'] == '0'){
                    $error_msg = $this->get_error('not_pay');
                }else if($aOrder['pay_status'] == '5'){
                    $error_msg = $this->get_error('already_full_refund');
                }            
            break;
            case 'stop_shipping'://不能对死单进行操作 订单必须已支付 订单必须未配送
                if($aOrder['status'] == 'dead'){
                   $error_msg = $this->get_error('is_dead');
                }else if($aOrder['pay_status'] =='1'){
                         if($aOrder['ship_status'] !='0'){
                            $error_msg = $this->get_error('must_not_shipping');
                         }     
                }else{
                   $error_msg = $this->get_error('no_full_pay');
                }
            break;
            case 'cancel_stop_shipping'://不能对死单进行操作 订单必须是暂停发货
                 if($aOrder['status'] == 'dead'){
                   $error_msg = $this->get_error('is_dead');
                 }else if($aOrder['status'] != 'pending'){
                   $error_msg = $this->get_error('must_not_pending');
                 }
            break;
            case 'change_order'://不能对死单进行操作 订单必须未配送
                
                 if($aOrder['status'] == 'dead'){
                    $error_msg = $this->get_error('is_dead');
                 }else if($aOrder['ship_status'] !='0'){
                    $error_msg = $this->get_error('must_not_shipping');
                 }
            break;
         //else if($aOrder['pay_status']==1 ||$aOrder['pay_status']==2 ||$aOrder['pay_status']==3 || $aOrder['pay_status']!=4){
         //       $error_msg = $this->get_error('cancel_order_fail');
         //    }
            case 'cancel':
                 if($aOrder['status'] != 'active'){
                    //$error_msg = $this->get_error('not_active');
                 }else if($aOrder['pay_status']==1 ||$aOrder['pay_status']==2 ||$aOrder['pay_status']==3 ||$aOrder['pay_status']==4 || $aOrder['confirm']=='Y'){
                    $error_msg = $this->get_error('cancel_order_fail');
                 }else if($aOrder['ship_status'] >0){
                    $error_msg = $this->get_error('must_not_pay');
                 }
            break;
            case 'archive':
                 if($aOrder['status'] != 'active'){
                    $error_msg = $this->get_error('not_active');
                 }         
            break;
        }
       
        if(!empty($error_msg)){
            $this->api_response('true','data fail',$result,$error_msg);
        }else{
            return true;
        }        
    }
    
    function get_error($key){
        return $this->error[$key];
    }
    
     /**
     * 创建订单记录
     *
     * @param array $data 
     *
     * @return 创建订单记录
     */
    function create_order($data,& $insert_order){  
        $this->update_order_version($data['order_id']);
        $this->update_order_item_version($data['order_id']);
        $this->update_delivery_version($data['order_id']);
        $this->update_payments_version($data['order_id']);
        $this->update_refunds_version($data['order_id']);
        
        $curr_time = time();
        $data['acttime'] = $curr_time;
        $data['createtime'] = $curr_time;
        $data['last_change_time'] = $curr_time;
        $data['effect_time'] += $curr_time;
        $data['version_id'] = 0;
        $aRs = $this->db->query("SELECT * FROM sdb_orders WHERE 0=1");
        $sSql = $this->db->getInsertSql($aRs,$data);
        $this->db->exec($sSql);
        
        $insert_order = $data;
    }
    
     /**
     * 创建订单商品
     *
     * @param array $data 
     *
     * @return 创建订单商品
     */
    function create_order_item($order_id,$arr_data,& $insert_order_item){
        $obj_product = $this->load_api_instance('search_product_by_bn','1.0');
        
        foreach($arr_data as $k=>$data){
             $data['version_id'] = 0;
             $data['order_id'] = $order_id;
             $aRs = $this->db->query("SELECT * FROM sdb_order_items WHERE 0=1");
             $sSql = $this->db->getInsertSql($aRs,$data);
             $obj_product->update_product_store($data['bn'],$data['nums']);
             $this->db->exec($sSql);
             $arr_data[$k] = $data;
        }
        $insert_order_item = $arr_data;
    }
    
    /**
     * 在线支付时，设置支付费用
     *
     * @param int $order_id
     * @param array $aPay支付方式
     * @param int $total_amount
     */
    function set_order_payment($order_id,$cost_payment){    
        $obj_payments = $this->load_api_instance('search_payments_by_order','2.0');
        $data['cost_payment'] = $obj_payments->formatNumber($cost_payment);
        $this->update_order($order_id,$data);
    }
    
    /**
     * 更新原订单版本
     *
     * @param int $order_id
     * 
     * @return 更新原订单版本
     */
    function update_order_version($order_id){
        $this->db->exec('update sdb_orders set version_id=version_id+1 where order_id='.$order_id.' order by version_id desc');
    }
    
    /**
     * 更新原订单发货单版本
     *
     * @param int $order_id
     * 
     * @return 更新原订单发货单版本
     */
    function update_delivery_version($order_id){
        $this->db->exec('update sdb_delivery set version_id=version_id+1 where order_id='.$order_id.' order by version_id desc');
    }
    
    /**
     * 更新原订单退款单版本
     *
     * @param int $order_id
     * 
     * @return 更新原订单退款单版本
     */
    function update_refunds_version($order_id){
        $this->db->exec('update sdb_refunds set version_id=version_id+1 where order_id='.$order_id.' order by version_id desc');
    }
    
    /**
     * 更新原订单支付单版本
     *
     * @param int $order_id
     * 
     * @return 更新原订单支付单版本
     */
    function update_payments_version($order_id){
        $this->db->exec('update sdb_payments set version_id=version_id+1 where order_id='.$order_id.' order by version_id desc');
    }
    
    /**
     * 更新原订单商品版本
     *
     * @param int $order_id
     * 
     * @return 更新原订单版本
     */
    function update_order_item_version($order_id){    
        $this->db->exec('update sdb_order_items set version_id=version_id+1 where order_id='.$order_id.' order by version_id desc');
    }
    
    /**
     * 更新订单表记录
     *
     * @param int $order_id
     * @param array $data
     * @return 更新订单表记录
     */
    function update_order($order_id,$data){ 
      
        $rs = $this->db->exec('SELECT * FROM sdb_orders WHERE order_id='.$order_id);
        $sql = $this->db->getUpdateSQL($rs,$data);
        $this->db->exec($sql);
    }
    
    /**
     * 更新订单商品表记录
     *
     * @param int $item_id
     * @param array $data
     * @return 更新订单表记录
     */
    function update_order_item($order_id,$data){
      foreach ($data as $k => $v){
         if($order_id){
            $rs = $this->db->exec('SELECT * FROM sdb_order_items WHERE order_id='.$order_id);
            $sql = $this->db->getUpdateSQL($rs,$data);
            $this->db->exec($sql);
         }else{
            $rs = $this->db->exec('SELECT * FROM sdb_order_items WHERE 0=1');
            $sql = $this->db->getInsertSQL($rs,$data);
            $this->db->exec($sql);
         }
      }
    }
    
    /**
     * 验证订单是否有效
     *
     * @param int $order_id 
     * @param array $order 
     * @param string $colums 
     *
     * @return 验证订单是否存在
     */
    function verify_order_valid($order_id,& $order,$colums='*'){
        $_order = $this->db->selectrow('select '.$colums.' from sdb_orders where order_id='.$order_id.'');
      
        if(!$_order){
           $this->api_response('true','data true',$result,'订单不存在');
        }
         
        $order = $_order;
    }
    
    /**
     * 验证此订单是否属于经销商
     *
     * @param int $dealer_id 
     * @param array $order 
     *
     * @return 验证此订单是否属于经销商
     */
    function verify_is_dealerorder($dealer_id,$order){
        if($dealer_id != $order['dealer_id']){
            $this->api_response('fail','data fail',$result,'此订单不属于经销商');
        }
    }
    
    /**
     * 验证订单是否已经存在
     *
     * @param int $order_id 
     * @param array $order 
     * @param string $colums 
     *
     * @return 验证订单是否已经存在
     */
    function verify_order_exist($order_id){
        $_order = $this->db->selectrow('select order_id from sdb_orders where order_id='.$order_id);
        if($_order){
           $this->api_response('fail','data fail',$result,'订单已经存在');
        }
        
       $order = $_order;
    }
    
    /**
     * 验证订单是否是死单
     *
     * @param array $order 
     *
     * @return 验证订单是否是死单
     */
    function verify_order_dead($order){           
        if($order == 'dead'){
           $this->api_response('fail','data fail',$result,'此订单是死单,无法操作');
        }  
    }
    
    /**
     * 验证订单商品是否有效
     *
     * @param int $order_id 
     * @param array $order_item_list 
     * @param string $colums 
     *
     * @return 验证订单商品是否有效
     */
    function verify_order_item_valid($order_id,& $order_item_list,$colums='*'){
      
        $_order_item_list = $this->db->select('select '.$colums.' from sdb_order_items where order_id='.$order_id);
        if(!$_order_item_list){
           $this->api_response('fail','data error',$result,'订单没有对应的商品项');
        }
        
        $order_item_list = $_order_item_list;
    }
    
    /**
     * 验证支付金额是否大于订单总金额
     *
     * @param int $order 
     * @param int $payed 
     *
     * @return 验证支付金额是否大于订单总金额
     */
    function verify_payed_valid($order,$payed){
        $total_payed = $order['payed'] + $payed;//支付总金额
        if($total_payed > $order['total_amount']){
           $this->api_response('fail','data fail',$result,'支付金额已经超过订单总金额');
        }
       
    }
    
    /**
     * 增加订单日志
     *
     * @param int $order_id 
     * @param string $message 
     * @param int $op_id 
     * @param string $op_name 
     * @param string $behavior 
     * @param string $result 
     *
     * @return boolean
     */
    function addLog($order_id,$message,$op_id=null, $op_name=null , $behavior = '', $result = 'success'){
        if($message){
            $op_name='';
            $rs = $this->db->query('select * from sdb_order_log where 0=1');
            $sql = $this->db->getInsertSQL($rs,array(
                'order_id'=>$order_id,
                'op_id'=>$op_id,
                'op_name'=>$op_name,
                'behavior'=>$behavior,
                'result'=>$result,
                'log_text'=>addslashes($message),
                'acttime'=>time(),
                'last_change_time'=>time()
                ));
            return $this->db->exec($sql);
        }else{
            return false;
        }
    }


  
}