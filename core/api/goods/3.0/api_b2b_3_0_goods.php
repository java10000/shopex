<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_3_0_goods extends shop_api_object {   
      /**
     * 获取商品同步详细信息
     *
     * @param array $data 必须包含goods_id
     *
     * @return 商品同步详细信息
     */
    //var $goods_include_column = array('cat_id','type_id','goods_type','brand_id','brand','brief','intro','mktprice','bn','name','unit','params','price','weight','image_default');
     var $app_error=array(
            'no_product_information'=>array('no'=>'b_goods_001','debug'=>'','level'=>'error','desc'=>'没有商品信息','info'=>'')  
    );
        
    function search_sync_goods_detail($data){
        if(!($goods_info=$this->db->selectrow('select * from sdb_goods where goods_id='.intval($data['goods_id'])))){
            $this->add_application_error('no_product_information');
        }
        
        unset($goods_info['cost']);
        unset($goods_info['score_setting']);
        
        $cat_info = $this->db->selectrow('select cat_path from sdb_goods_cat where cat_id='.$goods_info['cat_id']);
        $goods_info['cat_path'] = $cat_info['cat_path'];
        
        $goods_id = $goods_info['goods_id'];
        /* BEGIN 对应商品是否有淘宝CSV数据，有则添加到$goods_info中 by yangbing */
        if($row = $this->db->selectrow('SELECT c.csv_platform FROM sdb_csv c LEFT JOIN sdb_goods g ON g.bn = c.bn WHERE g.goods_id ='.$goods_id)){
            $goods_info['taobao_csv'] = $row['csv_platform'];
        }
        /* END */
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
                      // $data_info['update_goods_info'] = $goods_info;
                       $data_info['update_goods_product_info'] = $this->_get_products_by_gid($goods_id,$goods_info);
                       break;
                   case 6://商品新增   所有商品信息
                       $goods['goodsinfo'] = $goods_info;
                       $goods['products'] = $this->_get_products_by_gid($goods_id,$goods_info);
                       $goods['gimages'] = $this->_get_gimage_by_gid($goods_id);
                       $goods['glvprice'] = $this->_get_goods_lv_price($goods_id);
                       $data_info['add_goods'] = $goods;
                       break;
                   case 8://商品会员价格
                       $data_info['update_goods_lv_price'] = $this->_get_goods_lv_price($goods_id);
                       break;
               }
        }
        
        if(!empty($sync_status)){  
            $this->db->exec('update sdb_goods set sync_status="0" where goods_id='.$goods_id);//状态清空      
        }
        
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
    }
    
    function _get_goods_lv_price($goods_id){
        $sqlString = 'SELECT * FROM sdb_goods_lv_price WHERE goods_id ='.$goods_id;
        $lv_price_list = $this->db->select($sqlString);
        $tmp_lv_price_list = array();
        $products_list = $this->_get_products_by_gid($goods_id,'','product_id,price');
        $sqlString = 'SELECT * FROM sdb_member_lv WHERE disabled="false"';
        $member_lv_list = $this->db->select($sqlString);
        
        if($lv_price_list){
            foreach($lv_price_list as $k=>$lv_price){//会员价格
                unset($lv_price['goods_id']);
                
                if(!isset($tmp_lv_price_list[$lv_price['product_id']])){
                    $tmp_lv_price_list[$lv_price['product_id']] = array();
                }
                
                $new_lv_price = $lv_price;
                unset($new_lv_price['product_id']);
                $new_lv_price['is_dis_count'] = 'false';
                $tmp_lv_price_list[$lv_price['product_id']][] = $new_lv_price;
            }
            
            $lv_price_list = array();
            $item = array();
            
            foreach($products_list as $k=>$product){
                $lv_prices = array();
                $product_id = $product['product_id'];
                $price = $product['price'];
                
                if(isset($tmp_lv_price_list[$product_id])){
                    $lv_prices = $tmp_lv_price_list[$product_id];
                    
                    if($member_lv_list && (count($member_lv_list) != count($lv_prices))){//补全商品会员价格
                        $product_has_lv_price = array();
                        foreach($lv_prices as $lv_price){
                            $product_has_lv_price[$lv_price['level_id']] = $lv_price;
                        }
                    
                        foreach($member_lv_list as $member_lv){
                            if(!isset($product_has_lv_price[$member_lv['member_lv_id']])){
                                $lv_prices[] = array('level_id'=>$member_lv['member_lv_id'],
                                                     'price'=>number_format($member_lv['dis_count'] * $price,3,'.',''),
                                                     'is_dis_count'=>'true'
                                );
                            }
                        }
                    }
                }
               
                $item['product_id'] = $product_id;
                $item['price'] = $price;
                $item['data'] = $lv_prices;
                $lv_price_list[] = $item;
            }
        }else{
            foreach($products_list as $k=>$product){
                $item['product_id'] = $product['product_id'];
                $item['price'] = $product['price'];
                $item['data'] = array();
                $lv_price_list[] = $item;
            }
        }
        
        return  $lv_price_list;
    }
    
     /**
     * 根据商品ID获取货品库存，ID,货号列表
     *
     * @param array $data 必须包含goods_id
     *
     * @return 商品ID获取货品库存，ID,货号列
     */
     function _get_products_by_gid($goods_id,$goods_info='',$columns='*'){
         $products_list=$this->db->select('select '.$columns.' from sdb_products where goods_id='.$goods_id);
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
                        $products['goods_images'] = $arr_goods_images[$products['product_id']];
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
    
    /**
     * 平台重抓商品数据时对sdb_goods表的sync_status进行重置为6
     * 
     * @param array $data
     * @return 重置成功与否
     */
    function reset_goods_sync_status($data){
        $rs = $this->db->query('SELECT * FROM sdb_goods');
        $sql = $this->db->GetUpdateSQL($rs,array('sync_status'=>6));
        if($sql && $this->db->exec($sql)){
            $this->api_response('true');
        }else{
            $this->api_response('fail','db error');
        }
    }
}