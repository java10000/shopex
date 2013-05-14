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
    }//G49B893E99B6BE

    //检测task_id
    function check_task_exists($data){
        $task_id=$this->db->selectrow('select task_id from sdb_connect_ome_connect_pool where task_id = "'.$data['task_id'].'"');
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
//task
    function insert_ome_delivery($data){
        $this->check_task_exists($data);
        $aData=array(
            'order_id'=>$data['order_id'],
            'member_id'=>$data['member_id'],
            'money'=>$data['money']?$data['money']:0.00,
            'type'=>$data['type'],
            'is_protect'=>$data['is_protect'],
            'delivery'=>$data['delivery'],
            'logi_id'=>$data['logi_id'],
            'logi_name'=>$data['logi_name'],
            'logi_no'=>$data['logi_no'],
            'ship_name'=>$data['ship_name'],
            'ship_state' => $data['ship_state'],
            'ship_city' => $data['ship_city'],
            'ship_district' => $data['ship_district'],
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
            'disabled'=>$data['disabled']?$data['disabled']:'false',
            //'replacement'=>$data['replacement'],
            //'return_id'=>$data['return_id'],
        );

        $local_new_version_order = $this->verify_order_valid($aData['order_id'],'*');//验证订单有效性
        $this->checkOrderStatus($aData['type'],$local_new_version_order);
        if(is_string($aData['member_id'])&&$aData['member_id']!=''){
            $get_member_id = $this->db->selectrow('select member_id from sdb_members where uname = "'.$aData['member_id'].'"');
            if($get_member_id['member_id']){
              $aData['member_id'] = $get_member_id['member_id'];
            }else{
               $this->api_response('fail','data error',null,'用户名不存在');
            }
        }else{
            $aData['member_id'] = NULL;
        }
        $aData['ship_area'] = $this->getRegion($aData);

        $objShipping = &$this->system->loadModel('trading/delivery');
        $aData['delivery_id'] = $data['delivery_id']?$data['delivery_id']:$objShipping->getNewNumber($data['type']);

        $sql = "SELECT delivery_id FROM sdb_delivery WHERE delivery_id='".$aData['delivery_id']."'";

        $row = $this->db->selectrow($sql);

        if($row){//更新发(退)货单

            if($data['delivery_item']){

                $delivery_item1=json_decode($data['delivery_item'],true);
                $wrong_bn = '';
               foreach($delivery_item1 as $key=>$value){
                    $get_bn=$this->db->selectrow("SELECT bn,product_id,store FROM sdb_products WHERE  bn = '".$value['product_bn']."'");
                    if(!$get_bn){
                        $gift_bn=$this->db->selectrow("SELECT gift_bn,gift_id FROM sdb_gift WHERE  gift_bn = '".$value['product_bn']."'");
                        $get_bn['product_id'] = $gift_bn['gift_id'];
                        $get_bn['product_bn'] = $gift_bn['gift_bn'];
                        $get_bn['item_type'] = 'gift';
                        if(!$gift_bn){
                           $wrong_bn .= $value['product_bn'].',';
                        }
                    }else{
                        $get_bn['product_id'] = $get_bn['product_id'];
                        $get_bn['product_bn'] = $get_bn['bn'];
                        $get_bn['item_type'] = 'goods';
                    }

                    if($wrong_bn !=''){
                        $this->api_response('fail','data fail',null,'以下货号:'.$wrong_bn.'不存在');
                    }
                 $adata=array(
                            'delivery_id'=>$data['delivery_id']?$data['delivery_id']:$value['delivery_id'],
                            'item_type'=>$get_bn['item_type'],
                            'product_id'=>$get_bn['product_id'],
                            'product_bn'=>$get_bn['product_bn'],
                            'product_name'=>$value['product_name'],
                            'number'=>$value['number'],
                        );
                $rs_delivery_item = $this->db->query('SELECT * FROM sdb_delivery_item WHERE delivery_id = '.$adata['delivery_id']);
                $adata = addslashes_array($adata);
                $_sql = $this->db->getUpdateSQL($rs_delivery_item, $adata);

                if($_sql&&!$this->db->exec($_sql)){
                    $this->api_response('fail','sql exec error',$_sql);
                }
                 if($data['type']=='delivery'){
                    $result_delivery = '发货单';
                    $return_data['ship_status'] = 0;
                    $bData['sendnum'] = 0;

                    $this->_add_order_log($data['order_id'],$data['delivery_id']);

                    //$this->db->exec('update sdb_orders set ship_status = "'.$return_data['ship_status'].'" where order_id ='.$data['order_id']);

                 }else{
   
                    if($dc_data['sendnum'] > $value['number']){
                        $bData['sendnum'] = $dc_data['sendnum']-$value['number'];
                    }else if($dc_data['sendnum'] == $value['number']){
                        $bData['sendnum'] = $dc_data['sendnum']-$value['number'];
                    }else{
                        $this->api_response('fail','data fail',null,'退货数大于未退货数');
                    }
                 }
             $this->db->exec("update sdb_order_items set sendnum = ".$bData['sendnum']." where product_id =".$get_bn['product_id']." and order_id = ".$data['order_id']);
             $this->_add_order_log($data['order_id'],$data['delivery_id']);

           } 
               
            if($data['type']=='return'){
               $sum = $this->db->select('SELECT sendnum,nums FROM sdb_order_items WHERE order_id ='.$data['order_id']);
                   $send = 0;
                   $nums = 0;
                   foreach($sum as $k =>$v){
                       $send += $v['sendnum'];
                       $nums += $v['nums'];
               }

              if($send == 0){
                       $return_data['ship_status'] = 4;
              }else{
                       $return_data['ship_status'] = 3;
              }

               $this->db->exec('update sdb_orders set ship_status = "'.$return_data['ship_status'].'" where order_id ='.$data['order_id']);
            }
         }
         $rs = $this->db->exec("SELECT * FROM sdb_delivery WHERE delivery_id='".$aData['delivery_id']."'");
         $aData = addslashes_array($aData);
         $sql = $this->db->getUpdateSQL($rs,$aData);
         if(!$this->db->exec($sql)){
             $this->api_response('fail','sql exec error',$sql);
         }
        }else{

                if($data['delivery_item']){

                  $delivery_item=json_decode($data['delivery_item'],true);
                  $wrong_bn = '';
                  foreach($delivery_item as $key=>$value){
                    $get_bn=$this->db->selectrow("SELECT bn,product_id,store FROM sdb_products WHERE  bn = '".$value['product_bn']."'");
                    if(!$get_bn){
                        $gift_bn=$this->db->selectrow("SELECT gift_bn,gift_id FROM sdb_gift WHERE  gift_bn = '".$value['product_bn']."'");
                        $get_bn['product_id'] = $gift_bn['gift_id'];
                        $get_bn['product_bn'] = $gift_bn['gift_bn'];
                        $get_bn['item_type'] = 'gift';
                        if(!$gift_bn){
                           $wrong_bn .= $value['product_bn'].',';
                        }
                    }else{
                        $get_bn['product_id'] = $get_bn['product_id'];
                        $get_bn['product_bn'] = $get_bn['bn'];
                        $get_bn['item_type'] = 'goods';
                    }

                    if($wrong_bn !=''){
                        $this->api_response('fail','data fail',null,'以下货号:'.$wrong_bn.'不存在');
                    }

                    $adata=array(
                          'delivery_id'=>$data['delivery_id']?$data['delivery_id']:$value['delivery_id'],
                          'item_type'=>$get_bn['item_type'],
                          'product_id'=>$get_bn['product_id'],
                          'product_bn'=>$get_bn['product_bn'],
                          'product_name'=>$value['product_name'],
                          'number'=>$value['number'],
                    );

                    $rs_delivery = $this->db->query('SELECT * FROM sdb_delivery_item WHERE 0=1');
                    $adata = addslashes_array($adata);
                    $_sql = $this->db->GetInsertSQL($rs_delivery, $adata);
                    if(!$this->db->exec($_sql)){
                          $this->api_response('fail','sql exec error',$_sql);
                    }
                        if($data['type']=='delivery'){
                            $result_delivery = '发货单';
                            $return_data['ship_status'] = 0;
                            $bData['sendnum'] = 0;

                           //$this->db->exec('update sdb_orders set ship_status = "'.$return_data['ship_status'].'" where order_id ='.$data['order_id']);
                           $this->_add_order_log($data['order_id'],$data['delivery_id']);

                        }else{

                          $dc_data=$this->db->selectrow("SELECT sendnum,nums FROM sdb_order_items WHERE product_id =".$get_bn['product_id']." and order_id = ".$data['order_id']);

                           if($dc_data['sendnum'] > $value['number']){
                              $bData['sendnum'] = $dc_data['sendnum']-$value['number'];
                           }else if($dc_data['sendnum'] == $value['number']){
                              $bData['sendnum'] = $dc_data['sendnum']-$value['number'];
            
                           }else{
                              $this->api_response('fail','data fail',null,'退货数大于未退货数');
                           }
                        }
                         $this->db->exec("update sdb_order_items set sendnum = ".$bData['sendnum']." where product_id =".$get_bn['product_id']." and order_id = ".$data['order_id']);

                    }

                     if($data['type']=='return'){
                        $sum = $this->db->select('SELECT sendnum,nums FROM sdb_order_items WHERE order_id ='.$data['order_id']);
                        $send = 0;
                        $nums = 0;
                        foreach($sum as $k =>$v){
                            $send += $v['sendnum'];
                            $nums += $v['nums'];
                        }

                        if($send == 0){
                           $return_data['ship_status'] = 4;
                        }else{
                           $return_data['ship_status'] = 3;
                        }

                        $this->db->exec('update sdb_orders set ship_status = "'.$return_data['ship_status'].'" where order_id ='.$data['order_id']);
                        $this->_add_order_log($data['order_id'],$data['delivery_id']);
                    }
                }                
               $rs = $this->db->query('select * from sdb_delivery where 0=1');
               $aData = addslashes_array($aData);
               $sql = $this->db->getInsertSQL($rs,$aData);
               if(!$this->db->exec($sql)){
                  $this->api_response('fail','sql exec error',$sql);
               }

            
        }
        $this->api_response('true','data true',null,'新建'.(($result_delivery)?$result_delivery:'退货单').'成功');
   
    }
                       
     
    /*获取地区*/
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
   /*清除货品预占库存*/
   function clear_product_store($product_id,$nums){

      $freez = $this->db->selectrow('select goods_id,freez from sdb_products where product_id ='.$product_id);
      $f_freez = $freez['freez']-$nums;
      if($f_freez <=0 ){
          $f_freez = 0;
      }

      $this->db->exec('update sdb_products set freez ='.$f_freez.' where product_id = '.$product_id);

      $store = $this->db->selectrow('select store from sdb_products where product_id ='.$product_id);
      $f_store = $store['store']-$nums;
      if($f_store <=0 ){
          $f_store = 0;
      }
      $this->db->exec('update sdb_products set store ='.$f_store.' where product_id = '.$product_id);
      if($freez['goods_id']){
          $goods = $this->db->selectrow('select store from sdb_goods where goods_id ='.$freez['goods_id']);
          $f_goods = $goods['store']-$nums;
          if($f_goods <=0 ){
              $f_goods = 0;
          }
          $this->db->exec('update sdb_goods set store ='.$f_goods.' where goods_id ='.$freez['goods_id']);
      }
   }

   /*清除赠品预占库存*/
   function clear_gift_store($gift_id,$nums){

      $gift = $this->db->selectrow('select freez from sdb_gift where gift_id ='.$gift_id);
      $this->db->exec('update sdb_gift set freez ='.($gift['freez']-$nums).' where gift_id ='.$gift_id);

      $gift = $this->db->selectrow('select storage from sdb_gift where gift_id ='.$gift_id);
      $this->db->exec('update sdb_gift set storage ='.($gift['storage']-$nums).' where gift_id ='.$gift_id);
   }

/*新建发货单的物流单号*/
     function update_ome_logi_inf($data){
      $this->check_task_exists($data);
      $aData = array(
            'delivery_id'=>$data['delivery_id'],//发货单号
            'logi_no'=>$data['logi_no'],//物流单号
            'logi_id'=>$data['logi_id'],
            'logi_name'=>$data['logi_name'],//物流名称
         );
       $delivery_id = $this->db->selectrow('select * from sdb_delivery where delivery_id = '.$aData['delivery_id']);
       if($delivery_id['delivery_id']){

            $this->db->exec('update sdb_delivery set logi_no = "'.$aData['logi_no'].'" ,logi_name = "'.$aData['logi_name'].'" ,logi_id = "'.$aData['logi_id'].'" where delivery_id ='.$data['delivery_id']);

            $this->_add_order_log($data['order_id'],$data['delivery_id']);

            $this->api_response('true','data true',$result,'物流单号新建成功');
       }else{
         $this->api_response('fail','data fail',$result,'物流单号不存在');
      }

    }

/*
更新发货单状态
*/

     function update_ome_delivery_status($data){
        $this->check_task_exists($data);
       if($data['ship_status']!=''){
        if($data['ship_status']=='cancel'){
            $this->check_delivery_exists($data['delivery_id'],$data['order_id']);
            //$delivery_data['status'] = $data['ship_status'];
            //$delivery_data['disabled'] = true;
            $uv_sSQL = 'delete from sdb_delivery_item where delivery_id='.$data['delivery_id'];
            $this->db->exec($uv_sSQL);

            $sSQL= 'delete from sdb_delivery where order_id='.$data['order_id'].' and delivery_id = '.$data['delivery_id'];

            if($this->db->exec($sSQL)){
                 $this->api_response('true','data true',null,'发货单撤销成功');
            }else{
                 $this->api_response('fail','data fail',null,'发货单撤销失败');
            }
        }else{

            $delivery = $this->db->selectrow('select delivery_id,type from sdb_delivery where order_id = '.$data['order_id'].' and delivery_id ='.$data['delivery_id']);

             if($delivery['delivery_id']){
                if($data['ship_status']=='succ'){
                  if($delivery['type'] == 'delivery'){
                       $orders_list = $this->db->select('select item_id,sendnum,nums,product_id,is_type,addon from sdb_order_items where order_id = '.$data['order_id']);

                       $delivery_item_list = $this->db->select('select number,product_id,product_bn,item_type from sdb_delivery_item where delivery_id = '.$data['delivery_id']);
                       $pkg_is_send = false;
                       $adj_is_send = false;
                       $pkg_send = false;
                        foreach($orders_list as $k => $v){            
                            foreach($delivery_item_list as $dk =>$dv){
                                if($dv['item_type']=='goods'&&$v['is_type'] == 'goods'){
                                /*发货商品处理----begin---*/
                                     $adj_addon = unserialize($v['addon']);
                                     
                                         if($v['addon'] ==''||$v['addon'] =='na'||$v['addon'] =='b:0;'){//处理普通商品 addon无为空时
                                             if($v['product_id'] == $dv['product_id']){
                                                     $sendnum_v = $dv['number'];
                                                     $adj_addon = array('proinfo'=>$v['product_id'].'_0_'.$v['nums'].'_'.$sendnum_v.'|');
                                             }
                                         }
                                         if($adj_addon){
                                             if($adj_addon['adjinfo']&&$adj_addon['adjinfo']!='na'||$v['addon'] =='b:0;'){//处理配件时
                                                $viop = explode('|',$adj_addon['adjinfo']);
                                                $rpcid_implode = array();
                                                foreach($viop as $vv=>$vt){
                                                  if($vt){
                                                    $rpcid =explode("_",$vt);
                                                    if($rpcid[2]){
                                                       if($rpcid[0] == $dv['product_id']){
                                                           if(($dv['number']!=$v['nums'])&&($dv['number']<$v['nums'])){
                                                               $rpcid[3] += $dv['number'];
                                                           }
                                                       }
                                                    }
                                                    $rpcid_implode[] = implode('_',$rpcid);
                                                    $rpcid = implode('|',$rpcid_implode);
                                                  }

                                               }
                                               $adj_addon['adjinfo'] = $rpcid;
                                               if($v['product_id'] == $dv['product_id']){
                                                     $sendnum_v = $dv['number'];
                                                     $adj_addon = array('proinfo'=>$v['product_id'].'_0_'.$v['nums'].'_'.$sendnum_v.'|');
                                               }
                                             }elseif($adj_addon['adjinfo']=='na'||$v['addon'] =='b:0;'){
                                               if($v['product_id'] == $dv['product_id']){
                                                     $sendnum_v = $dv['number'];
                                                     $adj_addon['proinfo'] = $v['product_id'].'_0_'.$v['nums'].'_'.$sendnum_v.'|';
                                               }
                                             }elseif($adj_addon['proinfo']){//有proinfo 处理普通商品时
                                                $provip = explode('|',$adj_addon['proinfo']);
                                                $product_implode = array();
                                                foreach($provip as $pv=>$pt){
                                                  if($pt){
                                                    $pro_cid =explode("_",$pt);
                                                    if($pro_cid[2]){
                                                       if($pro_cid[0] == $dv['product_id']){
                                                             $sendnum_v = $dv['number'];
                                                             $pro_cid[3] += $sendnum_v;

                                                       }
                                                    }
                                                    $product_implode[] = implode('_',$pro_cid);
                                                    $pro_cid = implode('|',$product_implode);
                                                  }
                                               }
                                               $adj_addon['proinfo'] = $pro_cid;
                                             }else{//无proinfo 处理普通商品时
                                                if($v['product_id'] == $dv['product_id']){
                                                       $sendnum_v = $dv['number'];
                                                       $adj_addon['proinfo'] = $v['product_id'].'_0_'.$v['nums'].'_'.$sendnum_v.'|';
                                                }
                                             }
                                         }
                                         $v['addon'] = serialize($adj_addon);
                                         $this->db->exec("update sdb_order_items set addon = '".$v['addon']."' where product_id = ".$v['product_id']." and order_id = ".$data['order_id']." and is_type = 'goods' and item_id = ".$v['item_id']);
                                /*发货商品处理----end---*/

                                /*统计发货商品数量----begin---*/
                                         $pro_addon = unserialize($v['addon']);
                                         if(($pro_addon['adjinfo']!='na'&&$pro_addon['adjinfo'])&&$pro_addon['proinfo']){//处理配件+普通商品情况
                                //配件
                                           $adj_viop = explode('|',$pro_addon['adjinfo']);
                                           foreach($adj_viop as $adj_vv=>$adj_vt){
                                             if($adj_vt){
                                               $adj_rpcid =explode("_",$adj_vt);
                                               if($adj_rpcid[2]){
                                                 if($adj_rpcid[2]==$adj_rpcid[3]){
                                                   $adj_send = 1;
                                                 }elseif($adj_rpcid[2]<$adj_rpcid[3]){
                                                    if((($v['nums']*$adj_rpcid[2])%$adj_rpcid[3])==0){
                                                      $adj_send = ($v['nums']*$adj_rpcid[2])/$adj_rpcid[3];
                                                    }else{
                                                      $adj_send = 0;
                                                    }
                                                 }elseif($adj_rpcid[2]>$adj_rpcid[3]){
                                                   $adj_send = 0;
                                                 }
                                               }
                                             }
                                           }

                                //普通商品
                                           $adj_pro_op = explode('|',$pro_addon['proinfo']);
                                           foreach($adj_pro_op as $adj_pro_vv=>$adj_pro_vt){
                                             if($adj_pro_vt){
                                               $rpcid =explode("_",$adj_pro_vt);
                                               if($rpcid[2]){
                                                   $pro_send = $rpcid[3];
                                               }
                                             }
                                           }
                                           if($pro_send!=$adj_send){
                                             $adj_is_send = true;
                                             $sendnum_adj = min($pro_send,$adj_send);
                                           }elseif($pro_send==$adj_send){
                                             $adj_is_send = true;
                                             $sendnum_adj = $adj_send;
                                           }
                                         }
                                         if($pro_addon['proinfo']){//处理普通商品情况
                                           $send_pro_op = explode('|',$pro_addon['proinfo']);
                                           foreach($send_pro_op as $pro_vv=>$pro_vt){
                                             if($pro_vt){
                                               $rpcid =explode("_",$pro_vt);
                                               if($rpcid[2]){
                                                   $sendnum_adj = $rpcid[3];
                                                   $adj_is_send = true;
                                               }
                                             }
                                           }
                                         }//统计下普通商品(配件)中的商品发货数量
                                         if($adj_is_send!==false){
                                           if($dv['product_id'] == $v['product_id']){
                                            $this->db->exec('update sdb_order_items set sendnum = "'.$sendnum_adj.'" where product_id = '.$dv['product_id'].' and order_id = '.$data['order_id'].' and is_type = "goods"');
                                           }
                                         }
                                /*统计发货商品数量----end---*/
                                }else{//处理捆绑商品---begin---

                                     if($v['is_type']=='pkg'){
                                         $pkg_addon = unserialize($v['addon']);

                                         if($pkg_addon['adjinfo']){
                                            $viop = explode('|',$pkg_addon['adjinfo']);
                                            $rpcid_implode = array();
                                            foreach($viop as $vv=>$vt){
                                               if($vt){
                                                  $rpcid =explode("_",$vt);
                                                  if($rpcid[2]){
                                                      if($rpcid[0] == $dv['product_id']){
                                                            $rpcid[3] += $dv['number'];
                                                      }
                                                  }
                                                  $rpcid_implode[] = implode('_',$rpcid);
                                                  $rpcid = implode('|',$rpcid_implode);
                                               }

                                            }
                                            $pkg_addon['adjinfo'] = $rpcid;

                                         }
                                         $v['addon'] = serialize($pkg_addon);
                                         //if($dv['product_id'] == $v['product_id']){
                                            $this->db->exec("update sdb_order_items set addon = '".$v['addon']."' where product_id = ".$v['product_id']." and order_id = ".$data['order_id']." and is_type = 'pkg' and item_id = ".$v['item_id']);
                                         //}
                                     }

                       $orders_num = $this->db->selectrow('select item_id,nums,is_type,addon from sdb_order_items where order_id = '.$data['order_id']." and is_type = 'pkg' and item_id = ".$v['item_id']);

                                 if($v['is_type']=='pkg'){
                                    $addon = unserialize($orders_num['addon']);
                                    if($addon['adjinfo']){
                                       $viop = explode('|',$addon['adjinfo']);
                                       $viop_count = count($viop);
                                       $pkg_count = 0;
                                       foreach($viop as $vv=>$vt){
                                          if($vt){
                                            $rpcid =explode("_",$vt);
                                            if($rpcid[2]){
                                              if(($rpcid[3]*$orders_num['nums']) == $dv['number']){
                                                  $sendnum_pkg = $orders_num['nums'];
                                                  //$pkg_count += 1;
                                                  $pkg_send = true;
                                                  $this->db->exec('update sdb_order_items set sendnum = '.$sendnum_pkg.' where order_id = '.$data['order_id'].' and is_type = "pkg" and item_id = '.$v['item_id']);
                                              }else{
                                                  if(($rpcid[3]*$orders_num['nums'])>$rpcid[3]){
                                                      $send_num = $rpcid[3]/$rpcid[2];
                                                      $pkg_send = true;
                                                       $this->db->exec('update sdb_order_items set sendnum = '.$send_num.' where order_id = '.$data['order_id'].' and is_type = "pkg" and item_id = '.$v['item_id']);
                                                  }
                                              }
                                            }
                                          }
                                        }
                                        if($pkg_count == $viop_count){
                                            $pkg_is_send = true;
                                        }else{
                                            $pkg_is_send = false;
                                        }
                                     }//统计下捆绑商品中的商品发货数量
                                     if(($pkg_is_send!==false)&&($pkg_send==false)){
                                         //if($dv['product_id'] == $v['product_id']){
                                            $this->db->exec('update sdb_order_items set sendnum = '.$sendnum_pkg.' where order_id = '.$data['order_id'].' and is_type = "pkg" and item_id = '.$v['item_id']);
                                         //}
                                     }
                                  }//处理捆绑商品---end---
                                 
                                  //处理赠品---begin---
                                  if($dv['item_type'] == 'gift'){
                                   $gift_items = $this->db->select('select g.gift_bn,gi.nums,gi.sendnum,gi.gift_id from sdb_gift_items gi left join sdb_gift g on gi.gift_id = g.gift_id where gi.order_id  = '.$data['order_id'].'');
                                     foreach($gift_items as $gk=>$gv){
                                       if($gv['gift_id'] == $dv['product_id']){
                                           $sendnum_gift = $dv['number'];
                                           $this->db->exec('update sdb_gift_items set sendnum = "'.$sendnum_gift.'" where gift_id = '.$gv['gift_id'].' and order_id = '.$data['order_id']);
                                       }
                                     }
                                   }
                                   //处理赠品---end---
                            }
                          }
                        }

                        $orders = $this->db->select('select sendnum,nums from sdb_order_items where order_id = '.$data['order_id']);
                        $gift = $this->db->select('select sendnum,nums from sdb_gift_items where order_id = '.$data['order_id']);

                        foreach($orders as $k => $v){
                            $num['nums'] += $v['nums'];
                            $sendnum['sendnum'] += $v['sendnum'];
                        }

                        foreach($gift as $k => $v){
                            $num['nums'] += $v['nums'];
                            $sendnum['sendnum'] += $v['sendnum'];
                        }

                        $get_product  = $this->db->select('select product_id,sendnum from sdb_order_items where order_id = '.$data['order_id']);
                        if($sendnum['sendnum']==0){
                           $return_data['ship_status'] = 0; 
                        }else{
                            if($num['nums'] > $sendnum['sendnum']){
                               $return_data['ship_status'] = 2; 

                             /*清除预占库存*/
                             if($data['ship_status']=='succ'){
                               foreach($get_product as $k => $v){
                                  $this->clear_product_store($v['product_id'],$v['sendnum']);//259 1
                               }
                             }
                            }else if($sendnum['sendnum'] == $num['nums']){
                              $return_data['ship_status'] = 1; 
                              /*清除预占库存*/
                              if($data['ship_status']=='succ'){
                                foreach($get_product as $k => $v){
                                   $this->clear_product_store($v['product_id'],$v['sendnum']);//259 1
                                }
                              }
                            }
                         }
            
                        $gift = $this->db->select('select * from sdb_gift_items where order_id = '.$data['order_id']);
                        foreach($gift as $k => $v){
                           $gift_num['nums'] += $v['nums'];
                           $gift_sendnum['sendnum'] += $v['sendnum'];
                        }

                        if($gift_num['nums'] == $gift_sendnum['sendnum']){
                            foreach($gift as $k => $v){
                               $this->clear_gift_store($v['gift_id'],$v['sendnum']);
                            }
                        }
                                                                

                        if($return_data['ship_status']!=''){
                            $this->db->exec('update sdb_orders set ship_status = "'.$return_data['ship_status'].'" where order_id ='.$data['order_id']);
                
                            $this->db->exec('update sdb_delivery set status = "'.$return_data['ship_status'].'" where order_id ='.$data['order_id'].' and delivery_id ='.$data['delivery_id']);
                
                            $log = $this->db->selectrow('select order_id,behavior from sdb_order_log where order_id = '.$data['order_id']);
                            $this->_add_order_log($data['order_id'],$delivery['delivery_id']);//
                        }
                        $this->api_response('true','data true',null,'发货单状态更新成功');
                    }else{
                        $this->db->exec('update sdb_delivery_item set number = 0 where delivery_id = '.$data['delivery_id']);

                        $dl_items = $this->db->select('select product_id,number from sdb_delivery_item where delivery_id = '.$data['delivery_id']);
                        $orders_items = $this->db->select('select product_id,nums,sendnum from sdb_order_items where order_id = '.$data['order_id']);

                        foreach($dl_items as $dk=>$dv){
                            foreach($orders_items as $ok=>$ov){
                                 if($dv['product_id'] == $ov['product_id']){
                                      //$ov['sendnum'] = $dv['number'];
                                      $this->db->exec('update sdb_order_items set sendnum = '.($ov['sendnum'] - $dv['number']).' where order_id ='.$data['order_id'].' and product_id ='.$ov['product_id']);
                                 }
                            }
                        }

                        $orders_res = $this->db->select('select sendnum,nums from sdb_order_items where order_id = '.$data['order_id']);
                        $number = 0;
                        $sendnum = 0;

                        foreach($orders_res as $k => $v){
                            $number += $v['nums'];
                            $sendnum += $v['sendnum'];
                        }

                        if($number == $sendnum){
                            $return['ship_status'] = 4;
                        }else{
                            $return['ship_status'] = 3;
                        }
                        $delivery_sql = 'update sdb_delivery set status = "failed" where order_id ='.$data['order_id'].' and delivery_id ='.$data['delivery_id'];
                        $this->db->exec($delivery_sql);
                        $order_sql = 'update sdb_orders set ship_status = '.$return['ship_status'].' where order_id ='.$data['order_id'];
                        if($this->db->exec($order_sql)){
                            $this->api_response('true','data true',null,'退货单状态更新成功');
                        }else{
                            $this->api_response('fail','data fail',null,'退货单状态更新失败');
                        }
                    }
               }
            }else{
                
               $this->api_response('fail','data fail',null,'发货单不存在');
            }
            if($data['ship_status']=='progress'){
                $sql = 'update sdb_delivery set status = "'.$data['ship_status'].'" where order_id ='.$data['order_id'].' and delivery_id ='.$data['delivery_id'];
                if($this->db->exec($sql)){
                  $this->api_response('true','data true',null,'发货单状态更新成功');
                }else{
                  $this->api_response('fail','data fail',null,'发货单状态更新失败');
                }
            }
        }
      }else{
         
           $this->api_response('true','data true',null,'');
      }
         
    }

/*
更新订单状态
*/

     function update_ome_order_status($data){
        $this->check_task_exists($data);
      $orders = $this->db->selectrow('select order_id,ship_status from sdb_orders where order_id = '.$data['order_id']);

      if($orders['order_id']){

        $orders_items = $this->db->select('select order_id,nums,sendnum from sdb_order_items where order_id = '.$data['order_id']);
        $gift_items = $this->db->select('select sendnum,nums from sdb_gift_items where order_id = '.$data['order_id']);

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
        }

        $sql = $this->db->exec('update sdb_orders set ship_status = "'.$return_data['ship_status'].'" where order_id ='.$data['order_id']);

        $oPro = &$this->system->loadModel('goods/products');
        $oPro->addSellLog($data['order_id']);

        $this->api_response('true','data true',null,'订单状态更新成功');

      }else{
          $this->api_response('fail','data fail',null,'订单不存在');
      }
         
    }

     function check_delivery_exists($delivery_id,$order_id){
         $orders = $this->db->selectrow('select order_id from sdb_orders where order_id = '.$order_id);
         if($orders){
            $delivery = $this->db->selectrow('select delivery_id from sdb_delivery where delivery_id = '.$delivery_id.' and  order_id = '.$order_id);
            if(!$delivery['delivery_id']){
               $this->api_response('fail','data fail',null,'订单不存在');
            }
         }else{
            $this->api_response('fail','data fail',null,'改订单不存在发货单');
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
    function verify_order_valid($order_id,$colums='*'){
    
        $_order = $this->db->selectrow('select '.$colums.' from sdb_orders where order_id='.$order_id.'');

        if(!$_order){
           $this->api_response('fail','data fail',$result,'订单不存在');
        }
        
        if(isset($_order['dealer_id'])&&empty($_order['dealer_id'])){
           $this->api_response('fail','data fail',$result,'此订单不是经销商订单');
        }
       return $_order;
    }
    
   function checkOrderStatus($type,$data){
     
      switch($type){
         case 'delivery':
            if($data['ship_status']==1||$data['ship_status']==3||$data['ship_status']==4||$data['status']!='active'){

               $this->api_response('fail','data fail',$result,'订单已发货');
            }
         break;
         case 'return':
                if($data['ship_status']==0||$data['ship_status']==4||$data['status']!='active'){

               $this->api_response('fail','data fail',$result,'订单已退货');
            }
         break;
      }
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
        if(!$delivery_id){
            $this->api_response('true','data true',$result,'发货单不存在');
      }
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
        //error_log(date("Y:m:d:H:i:s").$error_info."\n", 3, HOME_DIR."/bryant/errors.log");            
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
