<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_1_0_product extends shop_api_object {
    var $api_type="native_api";
    var $max_number=200;
    function getColumns(){
        $columns=array(
            'goods_id'=>array('type'=>'int'),
            'title'=>array('type'=>'string'),
            'bn'=>array('type'=>'int'),
            'price'=>array('type'=>'int'),
            'cost'=>array('type'=>'int'),
            'name'=>array('type'=>'string'),
            'weight'=>array('type'=>'int'),
            'unit'=>array('type'=>'string'),
            'store'=>array('type'=>'int'),
            'pdt_desc'=>array('type'=>'string'),
            'props'=>array('type'=>'string'),
            'last_modify'=>array('type'=>'int')
        );
        return $columns;
    }
    
    function get_product_detail($data){
        if($data['product_id']){
            $result['data_info']=$this->db->select('SELECT '.implode(',',$data['columns']).' FROM sdb_products WHERE product_id='.$data['product_id']);
            $this->api_response('true',false,$result);
        }else{
            $this->api_response('fail','data fail',$data);
        }
    }

    function set_products_bn($data){
        //safeVar($data);
        if(!($rs=$this->db->exec('select bn from sdb_products where products_id='.intval($data['products_id'])))){
            $this->api_response('fail','data fail',$data);
        }
        unset($data['products_id']);
        $aData=$this->varify_date_whole($data);
        $sql=$this->db->getUpdateSQL($rs,$aData);
        if(!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }
        return $this->api_response('true');
    }
    function set_product_freeze_store($data){
        if(!($rs=$this->db->query('update sdb_products set freez='.intval($data['freeze']).' where product_id='.intval($data['product_id'])))){
            $this->api_response('fail','data fail',$data);
        }
        return $this->api_response('true');
    }
    function search_products_list($data){
        
        if($data['last_modify_st_time']=='0'){
            $result=$this->db->selectrow('select count(*) as counts from sdb_products where (( last_modify>='.intval($data['last_modify_st_time']).' and last_modify<'.intval($data['last_modify_en_time']).') or (last_modify is null)) and disabled="false"');

        }else{
            $result=$this->db->selectrow('select count(*) as counts from sdb_products where last_modify>='.intval($data['last_modify_st_time']).' and last_modify<'.intval($data['last_modify_en_time']).' and disabled="false"');
        }


         $where=$this->_filter($data);
         $cols='product_id,goods_id,name,bn,price,cost,weight,unit,store,freez,pdt_desc,props,uptime,last_modify';
        
         $info=$this->db->select('select '.$cols.' from sdb_products '.$where);
        
         foreach($info as $key=>$value){
                $info[$key]['pdt_desc']=unserialize($info[$key]['pdt_desc']);
                $info[$key]['props']=unserialize($info[$key]['props']);
         }
         $result['data_info']=$info;
         $this->api_response('true',false,$result);
    }
    function set_product_store($data){
        $sql = 'update sdb_products set store='.intval($data['store']>0 ? $data['store'] : 0).' where bn="'.$data['product_id'].'" or product_id='.$data['product_id'];
        if(!($rs=$this->db->query($sql))){
            $this->api_response('fail','data fail',$data);
        }
        return $this->api_response('true');
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
    /*
   set_products_store
   array(
      store_str =>json数据[{store:库存数量;bn:商品编号}]
   );
   */
    function set_ome_products_store($data){

      $this->check_task_exists($data);
      $store_list = json_decode($data['store_str'],true);
        if(!is_array($store_list)||count($store_list)==0){
            $this->api_response('true','data true',null);
        }
        foreach($store_list as $key => $val){  

          $get_bn = $this->db->selectrow('select bn from sdb_products where bn ="'.$val['bn'].'"');

          if($get_bn){
             if($get_bn['bn']!=$val['bn']){
                 $error =true;
                 $response['error_response'][] = array('bn'=>$val['bn'],'store'=>$val['store']);
             }else{
                 $sql = "update sdb_products set store='".intval($val['store']>0 ? $val['store'] : 0)."' where bn='".($val['bn'])."'";
                     $this->db->exec($sql);
                 $bn .= '"'.$val['bn'].'",';
             }
          }else{
             $gift_bn = $this->db->selectrow('select gift_bn from sdb_gift where gift_bn ="'.$val['bn'].'"');
             if($gift_bn){
                                     if($gift_bn['gift_bn']!=$val['bn']){
                                              $error =true;
                     $response['error_response'][] = array('bn'=>$val['bn'],'store'=>$val['store']);
                                     }else{
                                              $gift_sql = "update sdb_gift set storage='".intval($val['store']>0 ? $val['store'] : 0)."' where gift_bn='".($val['bn'])."'";
                                                       $this->db->exec($gift_sql);
                                     }
                            }else{
                 $error =true;
                 $response['error_response'][] = array('bn'=>$val['bn'],'store'=>$val['store']);
                            }
          }

        }
//4e9785f59aec5
        $bn = substr($bn,0,strlen($bn)-1);
        $num['store'] = 0;
        $new_goods = array();
        if($bn&&$bn!=''){
          $get_goods=$this->db->select('select goods_id from sdb_products where bn IN ('.$bn.')');
          foreach($get_goods as $gk=>$gv){
                if($gv['goods_id']&&$gv['goods_id']!=''){
                    $new_goods[$gk] = $gv['goods_id'];
                }
          }
          for($i=0;$i<count($new_goods);$i++){

             $up_goods=$this->db->select('select store from sdb_products where goods_id = '.$new_goods[$i]);
             foreach($up_goods as $k=>$v){
                $num[$i]['store'] += $v['store'];
             }

            $this->db->exec('update sdb_goods set store = '.$num[$i]['store'].' where goods_id ='.$new_goods[$i]);
          }
        }


        if($error){
         $response['error_response'] = json_encode($response['error_response']);

         return $this->api_response('fail','db fail','部分商品库存更新失败',$response);

        }else{

           return $this->api_response('true','data true','','更新库存成功');
      }
    }

 
    function _filter($filter){
        $where = array();
        if(isset($filter['last_modify_st_time'])){
            $where[]='last_modify >'.intval($filter['last_modify_st_time']);
        }
        if(isset($filter['last_modify_en_time'])){
            $where[]='last_modify <'.intval($filter['last_modify_en_time']);
        }
        return parent::_filter($where,$filter);
    }

}