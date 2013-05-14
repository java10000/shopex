<?php
class api_products{
    var $api_type="native_api";
    function api_products(){
        $this->system = &$GLOBALS['system'];
        $this->db = $this->system->database();

    }
    function taobao_query_and_errorlog($sql){
        if(!$this->db->exec($sql)){
            $handle=fopen(HOME_DIR.'/logs/'.date('Ymd').'.log','a+');
            fwrite($handle,$content."\r\n");
            fclose($handle);
        }
    }
    function taobao_cat_transfer($data=''){
        $cat_info=json_decode($data['data']);
        foreach(array($cat_info['rsp']['seller_cats']) as $name=>$value){
             $aDta=array(
                'cat_id'=>$value['cid'],//目录ID
                'cat_name'=>$value['name'],//目录名称
                'parent_id'=>$value['parent_cid'],//父ID
                'p_order'=>$value['sort_order']//排序
             );
             $rs =$this->db->exec('select * from sdb_goods_cat where 0=1');
             $sql = $this->db->getInsertSQL($rs,$aDta);
             $this->taobao_query_and_errorlog($sql);
        }
    }
    function api_products_goods_insert($data){
        $rs=$this->db->exec('select * from sdb_goods where 0=1');
        $aData=array(
            'type_id'=>$data['cid'],
            'bn'=>$data['taobao_outer_id'],
            'intro'=>$data['desc'],
            'price'=>$data['price'],
            'name'=>$data['title'],
            'store'=>$data['num'],
            'score'=>0,
            'uptime'=>$data['modified'],
            'last_modify'=>$data['modified'],
            'spec'=>$data['spec'],
            'spec_desc'=>$data['spec_desc']
        );
        $sql=$this->db->getInsertSQL($rs,$aData);
        return $this->taobao_query_and_errorlog($sql);
    }
    function import_taobao_database($data=''){
         if($data){
             $res_info=json_decode($data['data']);
             //$res_info=$data['data'];
             foreach((array)$res_info['rsp']['items'] as $k_info=>$v_info){
                    $v_info['taobao_outer_id']=strtoupper(uniqid('g'));
                    $v_info['desc'] = addslashes(str_replace('\n','',$v_info['desc']));
                    if (!$v_info['sku']){
                        $this->single_specifications($v_info);
                    }else{
                        $sepcial=$this->get_taobao_spec($v_info);
                        $v_info['spec'] = serialize($sepcial['spec_tmp']);
                        $v_info['spec_desc'] =serialize($sepcial['spec_desc_tmp']);
                        $this->api_products_goods_insert($v_info);
                        $goods_id =$this->db->lastInsertId(); //添加货品表--开始
                        if(($img_id=$this->insert_pic($v_info,$goods_id))){
                            $rs =$this->db->exec('select * from sdb_goods where goods_id='.$goods_id);
                            $aData=array(
                                'image_default'=>$img_id[0]
                            );
                            $sql=$this->db->getUpdateSQL($rs,$aData);
                            $this->taobao_query_and_errorlog($sql);
                        }

                    }
                    $goods_pdt_desc=$this->insert_more_specifications($v_info,$goods_id,$spec,$spec_desc);
                    $rs=$this->db->exec('select * from sdb_goods where goods_id='.$goods_id);
                    $aData=array(
                        'pdt_desc'=>serialize($goods_pdt_desc)
                    );
                    $sql=$this->db->getUpdateSQL($rs,$aData);
                    $this->taobao_query_and_errorlog($sql);
                    unset($goods_pdt_desc);
                    $this->inser_into_taobao_common($v_info,$goods_id);
               }
         }
    }
    function insert_pic(&$v_info,&$goods_id){
        if($v_info['item_img']){
            foreach($v_info['item_img'] as $img_key=>$img_value){
                $aData=array(
                    'goods_id'=>$goods_id,
                    'big'=>$img_value['url'],
                    'outer_id'=>$img_value['itemimg_id']
                );
                $rs=$this->db->exec('select * from sdb_gimages where 0=1');
                $sql=$this->db->getInsertSQL($rs,$aData);
                $this->taobao_query_and_errorlog($sql);
                $img_id[] =$this->db->lastInsertId();
            }
        }
        return $img_id;
    }
    function insert_more_specifications(&$v_info,&$goods_id,&$spec,&$spec_desc){
        if(!is_array($v_info)){
            return false;
        }

        foreach((array)$v_info['sku'] as $k_sku=>$v_sku){

                unset($spec_private_value_id);
                unset($props);
                unset($spec);
                unset($p_arr);
                $pdt_desc_tmp_arr = '';

                $p_arr = explode(";",$v_sku['properties']);

                while(list($p_key,$p_v)=each($p_arr)){
                         $p_v = explode(":",$p_v);
                         $mem_key2 = "getgoods_".$v_info['cid']."_".$p_v[0]."_".$p_v[1];
                         if(($mem_row2 = unserialize($this->system->mem_get($mem_key2)))){
                            $pdt_desc_tmp_arr .= $mem_row2['value_name']." ";
                         }
                         $spec[$p_v[0]] = $mem_row2['value_name'];
                         $spec_private_value_id[] = $p_v[1];
                }
                $pdt_desc = substr($pdt_desc_tmp_arr,0,-1);        //商品规格值描述
                $props = array(                                        //货品规格
                            "spec" =>$spec,
                            "idata" =>array(),
                            "spec_private_value_id" =>array_unique($spec_private_value_id),
                            "taobao_sku_id"=>$v_sku['sku_id'],
                );
                $rs=$this->db->exec('select * from sdb_products where 0=1');
                $aData=array(
                    'goods_id'=>$goods_id,
                    'bn'=>$v_sku['outer_id'],
                    'price'=>$v_sku['price'],
                    'cost'=>$v_sku['cost'],
                    'name'=>$v_info['title'],
                    'store'=>$v_sku['quantity'],
                    'pdt_desc'=>serialize($pdt_desc),
                    'props'=>serialize($props),
                    'uptime'=>$v_info['modified'],
                    'last_modify'=>$v_info['modified']
                );
                $sql=$this->db->getInsertSQL($rs,$aData);
                $this->taobao_query_and_errorlog($sql);
                $product_id = $this->db->lastInsertId();
                foreach((array)$sepcial['spec_desc_tmp'] as $k_index=>$v_index){
                    foreach ($v_index as $k2_index=>$v2_index){
                        $rs=$this->db->exec('select * from sdb_goods_spec_index where 0=1');
                        $aData=array(
                            'type_id'=>$v_info['cid'],
                            'spec_id'=>$k_index,
                            'spec_value_id'=>$k2_index,
                            'goods_id'=>$goods_id,
                            'product_id'=>$product_id
                         );
                        $sql=$this->db->getInsertSQL($rs,$aData);
                        $this->taobao_query_and_errorlog($sql);
                    }
                }

                $goods_pdt_desc[$product_id] = $pdt_desc;
        }
        return $goods_pdt_desc;
    }
    function single_specifications(&$v_info){
         $rs=$this->db->exec('select * from sdb_goods where 0=1');
         $aData=array(
            'type_id'=>$v_info['cid'],
            'intro'=>$v_info['desc'],
            'price'=>$v_info['price'],
            'bn'=>$v_info['taobao_outer_id'],
            'name'=>$v_info['title'],
            'store'=>$v_info['num'],
            'score'=>0,
            'uptime'=>$v_info['modified'],
            'last_modify'=>$v_info['modified']
         );
         $sql=$this->db->GetInsertSQL($rs,$aData);
         if($this->db->exec($sql)){
             $goods_id =$this->db->lastInsertId();
             $aData=array(
                'goods_id'=>$goods_id,
                'bn'=>$v_info['taobao_outer_id'],
                'price'=>$v_info['price'],
                'cost'=>$v_info['cost'],
                'name'=>$v_info['title'],
                'store'=>$v_info['num'],
                'uptime'=>$v_info['modified'],
                'last_modify'=>$v_info['modified']
             );
             $rs=$this->db->exec('select * from sdb_products where 0=1');
             $sql=$this->db->GetInsertSQL($rs,$aData);
             $this->taobao_query_and_errorlog($sql);
         }

    }

    function inser_into_taobao_common(&$v_info,&$goods_id){
        $p_value=array(
            "iid"=>$v_info['iid'],
            "title"=>$v_info['title'],
            "cid"=>$v_info['cid'],
            "store"=>$v_info['num'],
            "outer_id"=>$v_info['outer_id'],
            "product_id"=>$v_info['product_id'],
            "valid_thru"=>$v_info['valid_thru'],
            "price"=>$v_info['price'],
            "onsale_option"=>"now",
            "list_time"=>$v_info['list_time'],
            "has_invoice"=>$v_info['has_invoice'],
            "has_warranty"=>$v_info['has_warranty'],
            "stuff_status"=>$v_info['stuff_status'],
            "freight_payer"=>$v_info['freight_payer'],
            "state"=>$v_info['location.state'],
            "city"=>$v_info['location.city'],
            "auto_repost"=>$v_info['auto_repost'],
            "has_showcase"=>$v_info['has_showcase'],
            "seller_cids"=>$v_info['seller_cids'],
            "post_fee"=>$v_info['post_fee'],
            "express_fee"=>$v_info['express_fee'],
            "ems_fee"=>$v_info['ems_fee'],
            "modified"=>$v_info['modified'],
            "postage_id"=>$v_info['postage_id'],
        );
         $rs=$this->db->exec('select * from sdb_goods_memo where 0=1');
         $aData=array(
             'goods_id'=>$goods_id,
             'p_key'=>'taobao',
             'p_value'=>serialize($p_value)
         );
         $sql=$this->db->GetInsertSQL($rs,$aData);
         $this->taobao_query_and_errorlog($sql);

         $rs=$this->db->exec('select * from sdb_goods_outer_id where 0=1');
         $aData=array(
             'goods_id'=>$goods_id,
             'outer_id'=>$v_info['iid'],
             'outer_key'=>'taobao'
         );
         $sql=$this->db->GetInsertSQL($rs,$aData);
         $this->taobao_query_and_errorlog($sql);
         $props_arr = explode(";",$v_info['props']);
         foreach($props_arr as $k_props=>$v_props){
                $props_tmp =  explode(":",$v_props);
                $mem_key3 = "getgoods_nosales_".$v_info['cid']."_".$props_tmp[0];
                if (($mem_row3 = unserialize($this->system->mem_get($mem_key3)))){
                    if ($mem_row3['multi']==1){
                       $rs=$this->db->exec('select * from sdb_props_multi_select where 0=1');
                       $aData=array(
                           'goods_id'=>$goods_id,
                           'prop_key'=>$mem_row3['prop_key'],
                           'prop_value'=>$props_tmp[1],
                       );
                       $sql=$this->db->GetInsertSQL($rs,$aData);
                       $this->taobao_query_and_errorlog($sql);
                    }else{
                       $rs=$this->db->exec('select * from sdb_goods where goods_id='.$goods_id);
                       $aData=array(
                            "p_".$mem_row3['prop_key']=>$props_tmp[1]
                       );
                       $sql=$this->db->GetUpdateSQL($rs,$aData);
                       $this->taobao_query_and_errorlog($sql);
                    }
                }
                unset($props_tmp);
       }

      foreach($props_arr as $k_props=>$v_props){
             $props_tmp =  explode(":",$v_props);
             $mem_key3 = "getgoods_nosales_".$v_info['cid']."_".$props_tmp[0];
              if (($mem_row3 = unserialize($this->system->mem_get($mem_key3))))
              {
                  if ($mem_row3['multi']==1){
                     $rs=$this->db->exec('select * from sdb_props_multi_select where 0=1');
                     $aData=array(
                        'goods_id'=>$goods_id,
                        'prop_key'=>$mem_row3['prop_key'],
                        'prop_value'=>$props_tmp[1]

                     );
                     $sql=$this->db->GetInsertSQL($rs,$aData);
                     $this->taobao_query_and_errorlog($sql);

                  }else{
                     $rs=$this->db->exec('select * from sdb_goods where goods_id='.$goods_id);
                     $aData=array(
                        'p_'.$mem_row3['prop_key']=>$props_tmp[1]
                     );

                     $sql=$this->db->GetUpdateSQL($rs,$aData);
                     $this->taobao_query_and_errorlog($sql);
                  }

             }
       }
    }
    function taobao_upload_complete(){
        return $this->system->setConf('system.taobao_uploaded',true);
    }
    function get_taobao_spec(&$v_info){
        foreach((array)$v_info['sku'] as $k_sku=>$v_sku){
            $tp_result=explode(":",str_replace(';',':',$v_sku['properties']));
            for($k=0;$k<count($tp_result)/2;$k++){
                  unset($alias_name_tmp);
                  if(!in_array($tp_result[$k*2+1],$props_tmparr[$tp_result[$k*2]])){
                        $props_tmparr[$tp_result[$k*2]][]=$tp_result[$k*2+1];
                        $name=$tp_result[$k*2].':'.$tp_result[$k*2+1];
                        $mem_key1 = "getgoods_".$v_info['cid']."_".$tp_result[$k*2];

                        if(($mem_row1 =unserialize($this->system->mem_get($mem_key1)))){
                            $spec_tmp[$mem_row1['prop_id']] = $mem_row1['prop_name'];   //商品规格名称
                            ($mem_row1['is_color_prop']==1)?($spec_type ="image"):($spec_type = "text");
                            if($v_info['property_alias']){   //遍历属性别名值,去销售属性别名

                                if(preg_match('/'.$name.'+[\s\S]+[\;|\z]/',$v_info['property_alias'],$match)){
                                    $alias_name_tmp = substr(str_replace($name,'',$match[0]),1);
                                }
                            }

                            if (!$alias_name_tmp){

                                 $mem_key2 = "getgoods_".$v_info['cid']."_".$tp_result[$k*2]."_".$tp_result[$k*2+1];

                                 if (($mem_row2 = unserialize($this->system->mem_get($mem_key2)))){

                                        $alias_name_tmp = $mem_row2['value_name'];

                                 }
                            }
                        }

                        $tmp[$tp_result[$k*2+1]]=array(
                                   "spec_value_id"=>$tp_result[$k*2+1],
                                   "spec_value"=>$alias_name_tmp,
                                   "spec_type"=>$spec_type,
                                   "spec_image"=>'',
                                   "spec_goods_images"=>''
                        );

                        $spec_desc_tmp[$tp_result[$k*2]]=array(
                            'spec_id'=>$tp_result[$k*2],
                            'spec_name'=>$mem_row1['prop_name'],
                            'spec_type'=>$spec_type,
                            'spec_style'=>'flat',
                            'options'=>$tmp
                         );

                        //$spec_desc_tmp[$tp_result[$k*2]] =$desc_tmp_arr;  //私有规格描述
                 }
            }
         }

         return array('spec_tmp'=>$spec_tmp,'spec_desc_tmp'=>$spec_desc_tmp);
    }}
