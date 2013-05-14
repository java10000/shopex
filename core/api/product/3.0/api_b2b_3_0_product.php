<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_3_0_product extends shop_api_object {
    var $app_error=array(
            'sync_goods_no_exist'=>array('no'=>'b_product_001','debug'=>'','level'=>'error','desc'=>'同步订单商品数据不存在','info'=>''),
            'goods_not_exists'=>array('no'=>'b_product_002','debug'=>'','level'=>'error','desc'=>'订单商品不存在','info'=>''),
            'product_not_exists'=>array('no'=>'b_product_004','debug'=>'','level'=>'error','desc'=>'订单货品不存在','info'=>''),
            'goods_can_not_publish'=>array('no'=>'b_product_005','debug'=>'','level'=>'error','desc'=>'订单商品未发布不能下单','info'=>''),
            'goods_price_is_not_equal_to_the_suppliers_price'=>array('no'=>'b_product_005','debug'=>'','level'=>'error','desc'=>'供应商价格或者库存变动，请重新询价后下单','info'=>''),
            'product_no_store'=>array('no'=>'b_product_006','debug'=>'','level'=>'error','desc'=>'订单货品无库存','info'=>''),
            'product_no_available_store'=>array('no'=>'b_product_007','debug'=>'','level'=>'error','desc'=>'订单货品没有可下单库存','info'=>''),
            'pline_error'=>array('no'=>'b_product_008','debug'=>'','level'=>'error','desc'=>'查询了无权限的产品线或者本身无产品线权限')
    );

    /**
     * 根据产品线或者货品货号获取相应的货品信息
     * 下游的license必需
     *
     * @author hjx
     * @param array $data
     *
     * @return array(
     *              'bn'=>货品编号,
     *              'name'=>货品名,
     *              'brand'=>品牌名,
     *              'cat'=>分类名,
     *              'thumbnail_pic'=>默认缩略图,
     *              'pdt'=>array(
     *                        product_id=>array(
     *                              'bn'=>货品编号,
     *                              'name'=>货品名称,
     *                              'weight'=>货品重量,
     *                              'default'=>'true|false',
     *                              'props'=>array(
     *                                  spec_id=>array(
     *                                      'spec_name'=>规格名,
     *                                      'spec_type'=>规格类型,
     *                                      'spec_value'=>array(
     *                                          spec_value_id=>array(
     *                                              'spec_value_name'=>规格值名,
     *                                              'spec_image'=>规格值图片,
     *                                              'spec_goods_images'=>array(
     *                                                  image_id=>规格关联图片,
     *                                                  ................
     *                                              )
     *                                          ),
     *                                          ..............
     *                                      )
     *                                  ),
     *                                  ...........
     *                              )
     *                          ),
     *                        .........
     *                     )
     *               'link'=>链接地址
     *          )
     */
    function filt_goods($data){
       $dealer_id = $data['dealer_id'];
       $pline_id = json_decode($data['pline_id'],true);
       $bn = $data['bn'];

       $member = array();
       $pline_info = array();
       $pline = array();

       $obj_member = $this->load_api_instance('verify_member_valid','2.0');
       $obj_member->verify_member_valid($dealer_id,$member);//根据经销商ID验证会员记录有效性

       //检查传入的pline_id是否是该分销商有权限的
       switch ($member['dealer_purview']){
           case 3:
               $member_pline = $this->db->select("SELECT pline_id FROM sdb_pline_to_dealer WHERE member_id=".intval($member['member_id']));
               foreach($member_pline as $v){
                   $m_pline[] = $v['pline_id'];
               }
               if($pline_id){
                   foreach($pline_id as $v){
                       if(!in_array($v,$m_pline)){  //传入的产品线中出现了没有权限的产品线
                           $this->add_application_error('pline_error');
                       }
                   }
               }else{
                   $pline_id = $m_pline;
               }
               break;
           case 2:
               if(empty($pline_id)){    //给默认产品线，id为1
                   $pline_id = array(1);
               }
               break;
           case 1:
           default :
               $this->add_application_error('pline_error');
       }

       $obj_pline = $this->load_api_instance('search_product_line','2.0');
       //获取产品线的信息，算出有权限的子类cat_id
       foreach($pline_id as $v){
           $child_cat_id = NULL;
           $tmp_data = $this->db->selectrow("SELECT cat_id,brand_id FROM sdb_product_line WHERE pline_id=".$v);
           $cat_id = $tmp_data['cat_id'];
           $brand_id = $tmp_data['brand_id'];
           if($cat_id != "-1"){
               $child_cat_id = $obj_pline->_getSubCatId($cat_id);
               array_unshift($child_cat_id,$cat_id);
           }else{
               $child_cat_id = "-1";
           }
           $pline[] = array('cat_id'=>$child_cat_id,'brand_id'=>$brand_id);
       }

       $count = 0;
       //获取有权限的产品的goods_id
       if(!empty($bn)){
           $goods_id = array();
           $sql = "SELECT distinct(g.goods_id),g.cat_id,g.brand_id FROM sdb_goods AS g, sdb_products AS p ";
           $where = array("g.goods_id=p.goods_id","p.bn LIKE '".$bn."%'","g.disabled='false'");
           $sql .= $this->_filter($where,$data);
           $goods_info = $this->db->select($sql);
           if($goods_info){
               foreach($goods_info as $g){
                  if(in_array($g['goods_id'],$goods_id)){
                    continue;
                  }
                  $tmp_flag = false;
                  foreach($pline as $v){
                      if($v['cat_id'] == "-1" && $v['brand_id'] == "-1"){
                          $tmp_flag = true;
                      }else if($v['cat_id'] == "-1"){
                          if($g['brand_id'] == $v['brand_id']){
                              $tmp_flag = true;
                          }
                      }else if($v['brand_id'] == "-1"){
                          if(in_array($g['cat_id'],$v['cat_id'])){
                              $tmp_flag = true;
                          }
                      }else{
                          if($g['brand_id'] == $v['brand_id'] && in_array($g['cat_id'],$v['cat_id'])){
                              $tmp_flag = true;
                          }
                      }
                      if($tmp_flag){
                          $goods_id[] = $g['goods_id'];
                          break;
                      }
                   }
                   $count ++;
               }
           }else{
               $goods_id = array();
               $count = 0;
           }
       }else{
           $sql = "SELECT goods_id FROM sdb_goods ";
           if(!empty($pline)){
               foreach($pline as $v){
                   $cat_where = $v['cat_id']=="-1"?"":("cat_id IN (".implode(",",$v['cat_id']).")");
                   $brand_where = ($v['brand_id']=="-1"?"":(" brand_id=".$v['brand_id']));
                   if(!empty($cat_where) && !empty($brand_where)){
                       $where[] = "(" . $cat_where . " AND " . $brand_where . ")";
                   }else{
                       $t_w = $cat_where . $brand_where;
                       $where[] = empty($t_w)?"1":$t_w;
                   }
               }
               $where = array("(".implode(" OR ",$where).")");
           }else{
               $where = array(1);
           }
           $where[] = "disabled='false'";

           $sql .= $this->_filter($where,$data);

           $goods_info = $this->db->select($sql);
           if($goods_info){
               foreach($goods_info as $v){
                   $goods_id[] = $v['goods_id'];
               }
           }else{
               $goods_id = array();
           }

           $sql = "SELECT count(*) AS counts FROM sdb_goods ";
           if(count($where)>1){
                $sql .= " WHERE " . implode(" AND ",$where);
           }else{
                $sql .= " WHERE " . $where[0];
           }
           $sql .= " AND disabled='false'";
           $t_count = $this->db->selectrow($sql);
           $count = $t_count['counts'];
       }

       //组织合法的goods_id的返回信息
       if(!empty($goods_id)){
           $return = array();
           foreach($goods_id as $k=>$v){
               $goods_info = $this->db->selectrow("SELECT g.bn,g.name,g.brand,c.cat_name,g.thumbnail_pic,g.spec,g.spec_desc,g.pdt_desc FROM sdb_goods AS g,sdb_goods_cat AS c WHERE g.goods_id=".$v." AND g.cat_id=c.cat_id");
               $return[$k]['bn'] = $goods_info['bn'];
               $return[$k]['name'] = $goods_info['name'];
               $return[$k]['brand'] = $goods_info['brand'];
               $return[$k]['cat_name'] = $goods_info['cat_name'];
               $return[$k]['link'] = $this->system->base_url()."index.php?ctl=product&p[0]=".$v;

               $thumbnail_pic = explode("|",$goods_info['thumbnail_pic']);
               $return[$k]['thumbnail_pic'] = empty($goods_info['thumbnail_pic'])?"":(count($thumbnail_pic)==3?($this->system->base_url().$thumbnail_pic[0]):$thumbnail_pic[0]);

               $spec = unserialize($goods_info['spec']);
               $spec_desc = unserialize($goods_info['spec_desc']);
               $pdt_desc = unserialize($goods_info['pdt_desc']);

               $return[$k]['pdt'] = array();

               if(!empty($pdt_desc)){
                   $product_info = $this->db->select("SELECT product_id,bn,name,props,weight FROM sdb_products WHERE goods_id=".$v);
                   foreach($product_info as $product){
                       $tmp_product = array();
                       $tmp_product['bn'] = $product['bn'];
                       $tmp_product['name'] = $product['name'];
                       $tmp_product['default'] = $product['bn']==$bn?'true':'false';
                       $tmp_product['weight'] = $product['weight'];

                       $props = unserialize($product['props']);

                       foreach($props['spec'] as $spec_id=>$spec){
                           $spec_info = $this->db->selectrow("SELECT spec_name,spec_type FROM sdb_specification WHERE spec_id=".$spec_id);
                           $spec_name = $spec_info['spec_name'];
                           $spec_type = $spec_info['spec_type'];
                           $tmp_product['props'][$spec_id]['spec_name'] = $spec_name;
                           $tmp_product['props'][$spec_id]['spec_type'] = $spec_type;

                           $spec_value_id = $props['spec_value_id'][$spec_id];
                           $spec_private_value_id = $props['spec_private_value_id'][$spec_id];
                           $spec_value_info = $this->db->selectrow("SELECT spec_value,spec_image FROM sdb_spec_values WHERE spec_value_id=".$spec_value_id);
                           $spec_image = explode("|",$spec_value_info['spec_image']);
                           $spec_value_name = $spec_desc[$spec_id][$spec_private_value_id]['spec_value'];

                           $tmp_product['props'][$spec_id]['spec_value'] = array($spec_value_id=>array(
                               'spec_value_name' => $spec_value_name,
                               'spec_image' => empty($spec_value_info['spec_image'])?"":(count($spec_image)==3?($this->system->base_url().$spec_image[0]):$spec_image[0])
                           ));

                           $tmp_product['props'][$spec_id]['spec_value'][$spec_value_id]['spec_goods_images'] = array();
                           if(!empty($spec_desc[$spec_id][$spec_private_value_id]['spec_goods_images'])){
                               $spec_goods_image_ids = explode(",",$spec_desc[$spec_id][$spec_private_value_id]['spec_goods_images']);
                               foreach($spec_goods_image_ids as $spec_goods_image_id){
                                   $gimage_info = $this->db->selectrow("SELECT thumbnail FROM sdb_gimages WHERE gimage_id=".$spec_goods_image_id);
                                   $thumbnail = explode("|",$gimage_info['thumbnail']);
                                   $tmp_product['props'][$spec_id]['spec_value'][$spec_value_id]['spec_goods_images'][$spec_goods_image_id] = empty($gimage_info['thumbnail'])?"":(count($thumbnail)==3?($this->system->base_url().$thumbnail[0]):$thumbnail[0]);
                               }
                           }
                       }

                       $return[$k]['pdt'][$product['product_id']] = $tmp_product;
                   }
               }
               $return[$k]['count'] = $count;
           }
       }else{
           $return = "";
       }

       $result['data_info'] = $return;

       $this->api_response('true',false,$result);
    }
    
    /**
     * dearler_id,bn,name,specvalue
     *
     */
    function match_goods($data){
        safeVar($data);
        $s_dealer_id = $data['dealer_id'];
        $s_bn = $data['bn'];
        $s_name = $data['name'];
        $s_specvalue = $data['specvalue'];
        $s_status = $data['status'];
        
        $member = array();
        $result['alert_num'] = $this->system->getConf('system.product.alert.num');
        $obj_member = $this->load_api_instance('verify_member_valid','2.0');
        $obj_member->verify_member_valid($s_dealer_id,$member);//根据经销商ID验证会员记录有效性
        $obj_payments = $this->load_api_instance('search_payments_by_order','2.0');
        
        $count_goods = 0;
        $goods_id = array();
        if(!$s_status){
            if(!empty($s_bn)){
                $where = array();
                $where[] = 'g.goods_id = p.goods_id';
                $where[] = 'p.bn LIKE \''.$s_bn.'%\'';
                $where[] = 'g.marketable="true"';
                $where[] = 'g.disabled="false"';
                $filter = $this->_filter($where,$data);
                $goods_list_bn = $this->db->select('SELECT DISTINCT g.goods_id,g.cat_id,g.brand_id FROM sdb_goods AS g, sdb_products AS p'.$filter);
            }else{
                $goods_list_bn = array();
            }
            
            if($goods_list_bn){//搜索货号是否有搜索结果
                if(count($goods_list_bn)>10){//超过10个搜索结果，按商品名称搜索
                    $where = array();
                    $where[] = 'g.goods_id = p.goods_id';
                    $where[] = 'p.bn LIKE \''.$s_bn.'%\'';
                    $where[] = 'p.name LIKE \'%'.$s_name.'%\'';
                    $where[] = 'g.marketable="true"';
                    $where[] = 'g.disabled="false"';
                    $filter = $this->_filter($where,$data);
                    $goods_list_name = $this->db->select('SELECT DISTINCT g.goods_id,g.cat_id,g.brand_id FROM sdb_goods AS g, sdb_products AS p'.$filter);
                    if($goods_list_name){//货品名称搜索有结果
                        $tmp_goods_list = $this->db->select('SELECT DISTINCT g.goods_id FROM sdb_goods AS g, sdb_products AS p where g.goods_id = p.goods_id and g.marketable="true" and g.disabled="false" and p.bn LIKE \''.$s_bn.'%\' and p.name LIKE \'%'.$s_name.'%\'');
                        $count_goods = count($tmp_goods_list);
                        $goods_list = $goods_list_name;
                    }else{//
                        $tmp_goods_list = $this->db->select('SELECT DISTINCT g.goods_id FROM sdb_goods AS g, sdb_products AS p where g.goods_id = p.goods_id and g.marketable="true" and g.disabled="false" and p.bn LIKE \''.$s_bn.'%\'');
                        $count_goods = count($tmp_goods_list);
                        $goods_list = $goods_list_bn;
                    }
                }else{
                    $tmp_goods_list = $this->db->select('SELECT DISTINCT g.goods_id FROM sdb_goods AS g, sdb_products AS p where g.goods_id = p.goods_id and g.marketable="true" and g.disabled="false" and p.bn LIKE \''.$s_bn.'%\'');
                    $count_goods = count($tmp_goods_list);
                    $goods_list = $goods_list_bn;
                }
                
            }else{//货号搜不到，搜商品名称
                if(!empty($s_name)){
                    $where = array();
                    $where[] = 'g.goods_id = p.goods_id';
                    $where[] = 'p.name LIKE \'%'.$s_name.'%\'';
                    $where[] = 'g.marketable="true"';
                    $where[] = 'g.disabled="false"';
                    $filter = $this->_filter($where,$data);
                    $goods_list_name = $this->db->select('SELECT DISTINCT g.goods_id,g.cat_id,g.brand_id FROM sdb_goods AS g, sdb_products AS p'.$filter);
                }else{
                    $goods_list_name = array();
                }
                
                $tmp_goods_list = $this->db->select('SELECT DISTINCT g.goods_id FROM sdb_goods AS g, sdb_products AS p where g.goods_id = p.goods_id and g.marketable="true" and g.disabled="false" and p.name LIKE \'%'.$s_name.'%\'');
                $count_goods = count($tmp_goods_list);
                        
                if($goods_list_name){//有结果
                    $goods_list = $goods_list_name;
                }else{//无结果
                    $goods_list = array();
                }        
            }            
        }else{
            if(!empty($s_bn)){
                $goods_list = $this->db->select('SELECT DISTINCT g.goods_id,g.cat_id,g.brand_id FROM sdb_goods AS g, sdb_products AS p where g.goods_id = p.goods_id and g.marketable="true" and g.disabled="false" and p.bn=\''.$s_bn.'\'');
            }else if(!empty($s_name)){
                $goods_list = $this->db->select('SELECT DISTINCT g.goods_id,g.cat_id,g.brand_id FROM sdb_goods AS g, sdb_products AS p where g.goods_id = p.goods_id and g.marketable="true" and g.disabled="false" and p.name=\''.$s_name.'\'');
            }else{
                $goods_list = false;
            }
            if($goods_list){
                $count_goods = 1;
            }
        }
        
        if(!empty($goods_list)){
            //检查会员的代销权限
            $goods_list = $this->_checkDealerPurview($member,$goods_list);//检查会员的代销权限
            foreach($goods_list as $k => $goods_info){//删除没有代销权限的商品
                if($goods_info['is_dealer']==true){
                    $goods_id[]=$goods_info['goods_id'];
                }
            }
            if(count($goods_id) == 1){
                $is_compare = true;
            }else{
                $is_compare = false;
            }
            
            $return = array();
            foreach($goods_id as $k=>$v){
                $goods_info = $this->db->selectrow("SELECT g.bn,g.name,g.brand,c.cat_name,g.thumbnail_pic,g.spec,g.spec_desc,g.pdt_desc FROM sdb_goods AS g,sdb_goods_cat AS c WHERE g.goods_id=".$v." AND g.cat_id=c.cat_id");
                $return[$k]['bn'] = $goods_info['bn'];
                $return[$k]['name'] = $goods_info['name'];
                $return[$k]['brand'] = $goods_info['brand'];
                $return[$k]['cat_name'] = $goods_info['cat_name'];
                $return[$k]['link'] = $this->system->base_url()."index.php?ctl=product&p[0]=".$v;

                $thumbnail_pic = explode("|",$goods_info['thumbnail_pic']);
                $return[$k]['thumbnail_pic'] = empty($goods_info['thumbnail_pic'])?"":(count($thumbnail_pic)==3?($this->system->base_url().$thumbnail_pic[0]):$thumbnail_pic[0]);

                $spec = unserialize($goods_info['spec']);
                $spec_desc = unserialize($goods_info['spec_desc']);
                $pdt_desc = unserialize($goods_info['pdt_desc']);
 
                $return[$k]['pdt'] = array();

                //if(!empty($pdt_desc)){
                   $product_info = $this->db->select("SELECT product_id,bn,name,props,weight,store,freez,price FROM sdb_products WHERE goods_id=".$v);
                   foreach($product_info as $product){
                       $tmp_product = array();
                       $tmp_product['bn'] = $product['bn'];
                       $tmp_product['name'] = $product['name'];
                       $tmp_product['weight'] = $product['weight'];
                       $tmp_product['store'] = $product['store'];
                       $tmp_product['freez'] = $product['freez'];
                       $tmp_product['member_price'] = $obj_payments->changer($this->get_product_lv_price($member['member_lv_id'],$product['product_id'],$product['price']));
                       $tmp_product['default'] = 'false';
                       
                       $props = unserialize($product['props']);

                       if(!empty($pdt_desc)){
                           if($is_compare){
                               if($s_bn == $product['bn']){
                                   $tmp_product['default'] = 'true';
                               }else{
                                   foreach($props['spec'] as $spec_id=>$spec){
                                       if($spec == $s_specvalue){
                                            $tmp_product['default'] = 'true';
                                            break;
                                       }    
                                   }
                               }
                           }
                       }else{
                              $tmp_product['default'] = 'true';
                       }
                       
                       foreach($props['spec'] as $spec_id=>$spec){
                           $spec_info = $this->db->selectrow("SELECT spec_name,spec_type FROM sdb_specification WHERE spec_id=".$spec_id);
                           $spec_name = $spec_info['spec_name'];
                           $spec_type = $spec_info['spec_type'];
                           $tmp_product['props'][$spec_id]['spec_name'] = $spec_name;
                           $tmp_product['props'][$spec_id]['spec_type'] = $spec_type;

                           $spec_value_id = $props['spec_value_id'][$spec_id];
                           $spec_private_value_id = $props['spec_private_value_id'][$spec_id];
                           $spec_value_info = $this->db->selectrow("SELECT spec_value,spec_image FROM sdb_spec_values WHERE spec_value_id=".$spec_value_id);
                           $spec_image = explode("|",$spec_value_info['spec_image']);
                           $spec_value_name = $spec_desc[$spec_id][$spec_private_value_id]['spec_value'];

                           $tmp_product['props'][$spec_id]['spec_value'] = array($spec_value_id=>array(
                               'spec_value_name' => $spec_value_name,
                               'spec_image' => empty($spec_value_info['spec_image'])?"":(count($spec_image)==3?($this->system->base_url().$spec_image[0]):$spec_image[0])
                           ));

                           $tmp_product['props'][$spec_id]['spec_value'][$spec_value_id]['spec_goods_images'] = array();
                           if(!empty($spec_desc[$spec_id][$spec_private_value_id]['spec_goods_images'])){
                               $spec_goods_image_ids = explode(",",$spec_desc[$spec_id][$spec_private_value_id]['spec_goods_images']);
                               foreach($spec_goods_image_ids as $spec_goods_image_id){
                                   $gimage_info = $this->db->selectrow("SELECT thumbnail FROM sdb_gimages WHERE gimage_id=".$spec_goods_image_id);
                                   $thumbnail = explode("|",$gimage_info['thumbnail']);
                                   $tmp_product['props'][$spec_id]['spec_value'][$spec_value_id]['spec_goods_images'][$spec_goods_image_id] = empty($gimage_info['thumbnail'])?"":(count($thumbnail)==3?($this->system->base_url().$thumbnail[0]):$thumbnail[0]);
                               }
                           }
                       }

                       $return[$k]['pdt'][$product['product_id']] = $tmp_product;
                   }
               //}
               $return[$k]['count'] = $count_goods;
            }
        }else{
           $return = "";
        }
   
        $result['data_info'] = $return;
        $this->api_response('true',false,$result);
        
    }
    
     /**
     * 得到货品等级价格
     *
     * @param int $level_id
     * @param int $product_id
     * @param int $product_price
     *
     * @return 得到货品等级价格
     */
    function get_product_lv_price($level_id,$product_id,$product_price){
        $product_lv_price = $this->db->selectrow('select price from sdb_goods_lv_price where product_id="'.$product_id.'" and level_id='.$level_id);
        if($product_lv_price){
            return $product_lv_price['price'];
        }else{
            $obj_member = $this->load_api_instance('verify_member_valid','2.0');
            $obj_member->verify_member_lv_valid($level_id,$member_lv);

            return $member_lv['dis_count'] * $product_price;
        }
    }
    
/**
     * 检查会员的代销权限
     *
     * @param array $member 
     * @param array $arr_goods 
     *
     * @return 检查会员的代销权限
     */
    function _checkDealerPurview($member='',$arr_goods){
        if(!is_array($arr_goods) || count($arr_goods)<=0)return false;
        $obj_product_line = $this->load_api_instance('search_product_line','2.0');
        
        $dealer_purview = $member['dealer_purview'];
        $arr_dealer_purview = array(0,1,2,3);
        if(!empty($member) && in_array($dealer_purview,$arr_dealer_purview)){
           $member_id = $member['member_id'];
           $member_lv_id =  $member['member_lv_id'];
           switch($dealer_purview){
                 case 0://不在1,2,3范围里，会员没有设置代销权限
                     $this->add_application_error('member has not set sell permission');           
                 break;
                 case 1://无分销权限 
                     $this->add_application_error('member has not sell permission');            
                 break;
                 case 2://所有商品 
                     foreach($arr_goods as $k => $goods){
                         $arr_goods[$k]['is_dealer'] = true;
                     }
                 break;
                 case 3://指定产品线
                     $objPline = &$this->system->loadModel('trading/pline');
                     $objMemberPline = &$this->system->loadModel('member/memberpline');
                     $objGoods = &$this->system->loadModel('trading/goods');
                     
                     $member_line_list = $objMemberPline->getPlineListByMember($member_id);
                     if($member_line_list){
                         $is_dealer_cat = array();
                         foreach($member_line_list as $pline){
                             $pline_info = $obj_product_line->getInfo($pline['pline_id']);
                             
                             //如果此产品线分类不是任意，就获取它的分类ID以及子分类ID作比较
                             if($pline_info['cat_id'] != -1 && !in_array($pline_info['cat_id'],$is_dealer_cat)){
                                $arr_sub_cat_id = $obj_product_line->_getSubCatId($pline_info['cat_id']);
                                if(!empty($arr_sub_cat_id)){
                                   $arr_sub_cat_id[] = $pline_info['cat_id'];
                                   $is_dealer_cat = array_merge($is_dealer_cat,$arr_sub_cat_id);
                                }else{
                                   $is_dealer_cat[] = $pline_info['cat_id'];
                                }
                             }
                             
                             foreach($arr_goods as $k=>$goods){
                                 if(isset($goods['is_dealer']) && $goods['is_dealer']){continue;}//商品已经是可代销,就不作处理
                                 //保证商品数组里存在分类ID与品牌ID
                                
                                 if(!isset($goods['cat_id']) || (!isset($goods['brand_id']) && !is_null($goods['brand_id'])) ){
                                     $goods = $objGoods->getFieldById($goods['goods_id']);
                                 }
                                
                                 if($pline_info['cat_id'] == -1 && $pline_info['brand_id'] == -1){
                                   //当产品线配置为分类是任意并且品牌也是任意,可代销
                                    $arr_goods[$k]['is_dealer'] = true;
                                 }else if($pline_info['cat_id'] == -1 && $pline_info['brand_id'] == $goods['brand_id']){
                                   //当产品线配置为分类是任意,品牌与商品的品牌ID也能对上,可代销(如果商品品牌ID为NULL,不纳入可代销范围)
                                    $arr_goods[$k]['is_dealer'] = true;
                                 }else if($pline_info['brand_id'] == -1 && in_array($goods['cat_id'],$is_dealer_cat)){
                                   //当产品线配置为品牌是任意,分类与商品的分类ID也能对上,可代销(如果商品分类ID为NULL,不纳入可代销范围)
                                    $arr_goods[$k]['is_dealer'] = true;
                                 }else if(in_array($goods['cat_id'],$is_dealer_cat) && $pline_info['brand_id'] == $goods['brand_id']){ 
                                   //当产品线配置为分类与商品的分类ID能对上,品牌与商品的品牌ID也能对上,可代销
                                    $arr_goods[$k]['is_dealer'] = true;
                                 }else{
                                   //否则就是不可代销
                                    $arr_goods[$k]['is_dealer'] = false;
                                 }                  
                             }                                     
                         }                           
                     }
                     break;
                 default:
                     break;
           }           
        }
        
        return $arr_goods;
    }

}