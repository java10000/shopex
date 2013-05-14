<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_1_goods extends shop_api_object {
    var $app_error=array(
            'no product information'=>array('no'=>'b_search_sync_goods_detail_001','debug'=>'','level'=>'error','info'=>'没有商品信息','desc'=>'','debug'=>'')
     );
    function getColumns(){
        $columns=array(
          'goods_id'=>array('type'=>'int')
        );
        return $columns;
    }
    
    /**
     * 获取所有需要同步的商品ID
     *
     *
     *
     * @return 需要同步的商品ID
     */
    function search_sync_goods_id($data){
        $data['disabled'] = 'false'; 
        $data['orderby'] = 'goods_id'; 
        $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_goods where '.implode(' and ',$where));
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where =$this->_filter($data);
        $data_info=$this->db->select('select goods_id from sdb_goods '. $where);
        $result['counts'] = count($data_info);
        $result['data_info'] = $data_info;        
        $this->api_response('true',false,$result);
    }
    
    function before_filter($filter){
        $where = array(1);
        if(isset($filter['last_modify_st_time'])){
            $where[]='last_modify >='.intval($filter['last_modify_st_time']);
        }
        if(isset($filter['last_modify_en_time'])){
            $where[]='last_modify <'.intval($filter['last_modify_en_time']);
        }
        if(isset($filter['disabled'])){
            $where[]='disabled="'.$filter['disabled'].'"';
        }
        
        return $where;
    }
     
    function _filter($filter){
        $where = $this->before_filter($filter);
        
        return parent::_filter($where,$filter);
    }
    
    /**
     * 获取删除的商品ID
     *
     * 
     * 
     * @return 删除的商品ID
     */
    function search_del_goods_id($data){
        $data['orderby'] = 'goods_id'; 
        $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_supplier_goods_delete where '.implode(' and ',$where));
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where =$this->_filter($data);
        $data_info=$this->db->select('select goods_id from sdb_supplier_goods_delete'.$where);
        $result['counts'] = count($data_info);
        $result['data_info'] = $data_info;        
        $this->api_response('true',false,$result);
        
    }
    
    /**
     * 设置商品删除同步成功
     *
     * @param array $data 
     *
     * @return 设置删除同步成功
     */
    function set_goods_del_succ($data){  
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where =$this->before_filter($data);
        $this->db->exec('delete from sdb_supplier_goods_delete where last_modify>='.$data['last_modify_st_time'].' and last_modify<'.$data['last_modify_en_time']);
        $this->api_response('true',false,$result);
    }
    
    /**
     * 获取商品同步详细信息
     *
     * @param array $data 必须包含goods_id
     *
     * @return 商品同步详细信息
     */
    function search_sync_goods_detail($data){
        if(!($goods_info=$this->db->selectrow('select * from sdb_goods where goods_id='.intval($data['goods_id'])))){
            $this->api_response('fail','data fail',$data,'没有商品信息');
        }
        
        unset($goods_info['cost']);
        unset($goods_info['score_setting']);
        
        $cat_info = $this->db->selectrow('select cat_path from sdb_goods_cat where cat_id='.$goods_info['cat_id']);
        $goods_info['cat_path'] = $cat_info['cat_path'];
        
        $goods_id = $goods_info['goods_id'];
        $sync_status = $goods_info['sync_status'];
        $result['goods_id'] = $goods_id;
        $result['sync_status'] = $sync_status;
        
        if(!empty($sync_status)){
           $arr_sync_status = explode('_',$sync_status);       
        }else{
           $arr_sync_status = array(6);//同步状态为空，默认为新增商品
        }  
        
        foreach($arr_sync_status as $status){
               switch($status){
                   case 1://商品上下架
                       $data_info['update_marketable'] = $goods_info;
                       break;
                   case 2://货品库存
                       $data_info['update_products_store'] = $this->_get_products_by_gid($goods_id);
                       break;
                   case 3://商品图片变化
                       $data_info['update_img'] = $this->_get_gimage_by_gid($goods_id);
                       break;
                   case 4://商品信息变化
                       $data_info['update_goods_info'] = $goods_info;
                       break;
                   case 5://商品的货品信息变化 货品列表
                       //$data_info['update_goods_info'] = $goods_info;
                       $data_info['update_goods_product_info'] =  $this->_get_products_by_gid($goods_id,$goods_info);
                       break;
                   case 6://商品新增   所有商品信息
                       $goods['goodsinfo'] = $goods_info;
                       $goods['products'] = $this->_get_products_by_gid($goods_id,$goods_info);
                       $goods['gimages'] = $this->_get_gimage_by_gid($goods_id);
                       $data_info['add_goods'] = $goods;
                       break;
               }
        }
        
        if(!empty($sync_status)){  
            $this->db->exec('update sdb_goods set sync_status="0" where goods_id='.$goods_id);//状态清空      
        }
       
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
    }
    
    /**
     * 根据商品ID获取货品库存，ID,货号列表
     *
     * @param array $data 必须包含goods_id
     *
     * @return 商品ID获取货品库存，ID,货号列
     */
    function _get_products_by_gid($goods_id,$goods_info='',$columns='*'){
         $products_list=$this->db->select('select * from sdb_products where goods_id='.$goods_id);
         foreach($products_list as $k=>$product){
              unset($product['cost']);
              $products_list[$k] = $product;
         }
         
         if(!empty($goods_info)){
             if(isset($goods_info['spec_desc']) && !empty($goods_info['spec_desc'])){
                 $arr_spec_desc = unserialize($goods_info['spec_desc']);
                 foreach($products_list as $k=>$products){
                      $product_id = $products['product_id'];
                      $arr_spec_private_value = unserialize($products['props']);
                      $tmp_arr_goods_images = array();
                      foreach($arr_spec_private_value['spec_private_value_id'] as $spec_id=>$private_value_id){
                          if(isset($arr_spec_desc[$spec_id][$private_value_id])){
                              $spec_desc = $arr_spec_desc[$spec_id][$private_value_id];
                              if(!empty($spec_desc['spec_goods_images'])){
                                  $tmp_arr_goods_images[$spec_desc['spec_value_id']] = $spec_desc['spec_goods_images'];
                              }
                          }else{
                              continue;
                          }
                      }
                                   
                      $arr_goods_images[$product_id] = $tmp_arr_goods_images;
                   }
                }else{
                   $arr_goods_images = array();
                }       
                        
                foreach($products_list as $k=>$products){
                    if(isset($arr_goods_images[$products['product_id']])){
                        $products['goods_images'] = serialize($arr_goods_images[$products['product_id']]);
                    }else{
                        $products['goods_images'] = '';
                    }
                              
                    $products_list[$k] = $products;
                }
            }
                          
         return $products_list;
    }
    
    /**
     * 根据商品ID获取gimage列表
     *
     * @param array $data 必须包含goods_id
     *
     * @return 商品ID获取gimage列表
     */
    function _get_gimage_by_gid($goods_id){
         $data_info=$this->db->select('select gimage_id,goods_id,goods_id,source,orderby,src_size_width,src_size_height,small,big,thumbnail,up_time as last_modify from sdb_gimages where goods_id='.$goods_id);
       
         return $data_info;
    }

}