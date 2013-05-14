<?php
class entity_goods extends entity{

    function &export_sdf_array($id){

        $trading = &$this->system->loadModel('trading/goods');
        $goods_info = $trading->getGoods($id,$levelid=0);
        $j_goods_arr = $this->_getGoodsInfo($goods_info,$this->_map_schema(),$goods_info['type_id']);

        return $j_goods_arr ;

    }

    function import_sdf_array(&$sdf_array,$type){
        $array = array();
        foreach ($sdf_array[$type] as $k=>$v){
            switch($k){
                case 'type':
                    $attributes['goods_type'] = $v;
                    $array = array_merge($array,$attributes);
                break;
                //case 'goods_id':
                    //$goods_id['goods_id'] = $v;
                    //$array = array_merge($array,$goods_id);
                //break;
                case 'orderinfo':
                    $mininfo['minfo'] = $v;
                    $array = array_merge($array,$mininfo);
                break;
                
                case 'bn':
                    $bn[$k] = $v;
                    $array = array_merge($array,$bn);
                break;
                
                case 'description':
                    $intro['intro'] =$v;
                    $array = array_merge($array,$intro);
                break;
                
                case 'meta':
                    if(count($v)>0){
                        foreach($v as $meta_k=>$meta_v){
                            $tmp_m[$meta_v['key']] = $meta_v['value'];
                        }
                    }
                    $array = array_merge($array,$tmp_m);
                break;
                
                case 'title':
                    $name['name'] = $v;
                    $array = array_merge($array,$name);
                break;
                
                case 'createtime':
                    $uptime['uptime'] = $v;
                    $array = array_merge($array,$uptime);
                break;
                case 'status':
                    $t_status[$k] = $v;
                    $array = array_merge($array,$t_status);
                break;
                
                case 'last_modified':
                    $last_modify['last_modify'] = $v;
                    $array = array_merge($array,$last_modify);
                break;
                
                case 'brief':
                    $brief[$k] = $v;
                    $array = array_merge($array,$brief);
                break;
                
                case 'props':
                    foreach($v as $props_k=>$props_v){
                        $tmp_props[$props_k] = $props_v['id'];
                    }
                    $array = array_merge($array,$tmp_props);
                
                case 'adjunct':
                    $adj_items['product_id'] = array();
                    $adj['adjunct'] = array();
                    $adj_filt = array();
                    foreach($v as $adjunct_k=>$adjunct_v){
                        $tmp_adjunct = array();
                        $items['items'] = array();
                        $tmp_adj['name'] = $adjunct_v['title'];
                        $tmp_adj['min_num'] = $adjunct_v['min'];
                        $tmp_adj['max_num'] = $adjunct_v['max'];
                        $tmp_adjunct = array_merge($tmp_adjunct,$tmp_adj);
                        if(array_key_exists('adj_include',$adjunct_v)){
                            if(count($adjunct_v['adj_include'])>0){
                                foreach($adjunct_v['adj_include'] as $a_incl){
                                    $a_pid = $a_incl['id'];
                                    array_push($adj_items['product_id'],$a_pid);
                                }
                                $items['items'] = array_merge($items['items'],$adj_items);
                                $tmp_adjunct = array_merge($tmp_adjunct,$items);
                            }
                        }elseif(array_key_exists('adj_filter',$adjunct_v)){
                            if(count($adjunct_v['adj_filter']>0)){
                                foreach($adjunct_v['adj_filter'] as $a_fil){
                                    $a_fv = $a_fil['value'];
                                    array_push($adj_filt,$a_fv);
                                }
                                $str['items'] = implode("&",$adj_filt);
                                $tmp_adjunct = array_merge($tmp_adjunct,$str);
                            }
                        }
                        array_push($adj['adjunct'],$tmp_adjunct);
                    }
                    $array = array_merge($array,$adj);
                
                break;
                
                case 'category':
                    //foreach($v as $cat_k=>$cat_v){
                    $tmp_cat['cat_id'] = $v['id'];
                    //}
                    
                    $array = array_merge($array,$tmp_cat);
                break;
                
                case 'brand':
                    foreach($v as $brand_k=>$brand_v){
                        $tmp_brand['brand_id'] = $brand_v['id'];
                        $tmp_brand['brand'] = $brand_v['value'];
                    }
                    $array = array_merge($array,$tmp_brand);
                break;
                
                case 'thumbnail':
                    unset($v['width']);
                    unset($v['height']);
                    $thumbnail_pic['thumbnail_pic'] = $v['value'];
                    $array = array_merge($array,$thumbnail_pic);
                break;

                case 'spec':
                    $spec_desc['spec_desc'] = array();
                    foreach($v as $spec_k=>$spec_v){
                        unset($spec_v['title']);
                        $tmp_spec_desc[$spec_v['key']] = array();
                        foreach($spec_v['option'] as $spec_value_k=>$spec_value_v){
                            $tmp_spec_value['spec_value'] = $spec_value_v['value'];
                            array_push($tmp_spec_desc[$spec_v['key']],$tmp_spec_value);
                        }
                    }
                    $spec_desc['spec_desc'] = $tmp_spec_desc;
                    
                    $array = array_merge($array,$spec_desc);
                break;
                case 'image':
                    foreach($v as $media_k=>$media_v){
                        //unset($media_v['default']);
                        $tmp_media[$media_v['type']] = $media_v['value'];
                        $tmp_media['height'] = $media_v['height'];
                        $tmp_media['width'] = $media_v['width'];
                    }
                    $array = array_merge($array,$tmp_media);
                break;
                
                case 'product':
                $products['products'] = array();
                
                if(count($v)>0){
                    foreach($v as $product_v){
                        $tmp_products['products'] = array();
                        foreach($product_v as $pr_k=>$pr_v){
                            if($pr_k=='id'){
                                //$tmp_pr['product_id'] = $pr_v;
                                
                            }elseif($pr_k=='meta'){
                                foreach($pr_v as $prmeta_k=>$prmeta_v){
                                    $tmp_pr[$prmeta_v['key']] = $prmeta_v['value'];
                                }
                            }elseif($pr_k=='last_modified'){
                                $tmp_pr['last_modify'] = $pr_v;
                            }elseif($pr_k=='store'){
                                if(count($pr_v)>0){
                                    foreach($pr_v as $pr_store_v){
                                        $tmp_pr['store_place'] = $pr_store_v['place'];
                                        $tmp_pr['freez'] = $pr_store_v['freez'];
                                        $tmp_pr['store'] = $pr_store_v['value'];
                                    }
                                }
                            }elseif($pr_k=='price'){
                                $tmp_price['mprice'] = array();
                                foreach($pr_v as $pr_price_k=>$pr_price_v){
                                
                                    if(array_key_exists('member_group_id',$pr_price_v)){
                                        $t[$pr_price_v['member_group_id']] = $pr_price_v['value'];
                                    }else{
                                        $tmp_pr[$pr_price_v['title']] = $pr_price_v['value'];
                                        $tmp_pr[$pr_price_v['disabled']] = $pr_price_v['disabled'];
                                    }
                                }
                                
                                $tmp_price['mprice'] = $t;
                                $tmp_products['products'] = array_merge($tmp_products['products'],$tmp_price);
                                
                                
                            }elseif($pr_k=='spec_def'){
                                $tmppspecid['spec_value_id'] = array();
                                $tmppspec['spec'] = array();
                                $tmp_pprops['props'] = array();
                                foreach($pr_v['spec_value'] as $pspec_k=>$pspec_v){
                                    array_push($tmppspecid['spec_value_id'],$pspec_v['key']);
                                    array_push($tmppspec['spec'],$pspec_v['value']);
                                }
                                for($i=0;$i<count($tmppspecid['spec_value_id']);$i++){
                                    $ltmppspecid['spec_value_id'][$i+1] = $tmppspecid['spec_value_id'][$i];
                                    $ltmppspec['spec'][$i+1] = $tmppspec['spec'][$i];
                                }
                                $tmp_pprops['props'] = array_merge($tmp_pprops['props'],$ltmppspec);
                                $tmp_pprops['props'] = array_merge($tmp_pprops['props'],$ltmppspecid);
                                
                            }elseif($pr_k=='bn'){
                                $tmp_pr['bn'] = $pr_v;
                            }
                        }
                        $tmp_products['products'] = array_merge($tmp_products['products'],$tmp_pr);
                        $tmp_products['products'] = array_merge($tmp_products['products'],$tmp_price);
                        $tmp_products['products'] = array_merge($tmp_products['products'],$tmp_pprops);
                        array_push($products['products'],$tmp_products['products']);
                    }
                }
                
                $array = array_merge($array,$products);
                break;
            }    
        }
        
        $productsdata = $array['products'];
        $adjunct = $array['adjunct'];
        $images['small'] = $array['small_pic'];
        $images['big'] = $array['big_pic'];
        $images['src_size_width'] = $array['width'];
        $images['src_size_height'] = $array['height'];
        $tmp_minfo['minfo'] = $array['minfo'];
        
        unset($array['products']);
        unset($array['adjunct']);
        unset($array['products']['mprice']);
        unset($array['products']['product_id']);
        unset($array['width']);
        unset($array['height']);
        unset($array['minfo']);
        
        
        
        $array['spec_desc'] = serialize($array['spec_desc']);
        $aRs = $this->db->query("SELECT * FROM sdb_goods WHERE 0");
        
        
        $sSql = $this->db->getInsertSql($aRs,$array);
        $this->db->exec($sSql);
        
        $new_goods_id_row = $this->db->selectrow('SELECT MAX(goods_id) FROM sdb_goods');
        $aRs_link = $this->db->query("SELECT * FROM sdb_goods_rate WHERE 0");
        $aRs_pro = $this->db->query("SELECT * FROM sdb_products WHERE 0");
        $aRs_adj = $this->db->query("SELECT * FROM sdb_goods_memo WHERE 0");
        $aRs_imgs = $this->db->query("SELECT * FROM sdb_gimages WHERE 0");
        $new_goods_id = $new_goods_id_row['MAX(goods_id)'];
        $ng['goods_id'] = $new_goods_id;
        
                   
        foreach($sdf_array[$type] as $kkey=>$vvalue){
            if($kkey=='link'){
                 foreach($vvalue as $link_k=>$link_v){
                    $tmp_goods_rate['goods_1'] = $new_goods_id;
                    $tmp_goods_rate['goods_2'] = $link_v['url'];
                    $tmp_goods_rate['manual'] = $link_v['type'];
                    $tmp_goods_rate['rate'] = $link_v['value'];
                    $sSql_link = $this->db->getInsertSql($aRs_link,$tmp_goods_rate);
                    $this->db->exec($sSql_link);
                }
            }
        }
        
        
        $images['goods_id']=$new_goods_id;
        
        $sSql_imgs = $this->db->getInsertSql($aRs_imgs,$images);
        $this->db->exec($sSql_imgs);
        foreach($productsdata as $prodk=>$prodv){
            $r = array_merge($prodv,$ng);
            $sSql_pro = $this->db->getInsertSql($aRs_pro,$r);
            $this->db->exec($sSql_pro);
        }
        
        $adjunct = serialize($adjunct);
        
        $p_value['p_value'] = $adjunct;
        $adjdata = array();
        $adjtype['p_key'] = 'adjunct';
        $adjdata = array_merge($adjdata,$ng);
        $adjdata = array_merge($adjdata,$adjtype);
        $adjdata = array_merge($adjdata,$p_value);
        $sSql_adj = $this->db->getInsertSql($aRs_adj,$adjdata);
        $this->db->exec($sSql_adj);
        return $new_goods_id;
    }

    function _map_schema(){

        $schema = array(
            'attributes'=>array('goods_type'),
            'meta'=>array('type_id','mktprice','cost','price','marketable','weight','unit','store_place','score_setting','downtime','disabled','notify_num','rank','rank_count','comments_count','view_w_count','view_count','buy_count','buy_w_count','count_stat'),
            'title'=>array('name'),
            'brand'=>array('brand_id','brand'),
            'category'=>array('cat_id'),
            'createtime'=>array('uptime'),
            'props'=>array(
                '1'=>'p_1',
                '2'=>'p_2',
                '3'=>'p_3',
                '4'=>'p_4',
                '5'=>'p_5',
                '6'=>'p_6',
                '7'=>'p_7',
                '8'=>'p_8',
                '9'=>'p_9',
                '10'=>'p_10',
                '11'=>'p_11',
                '12'=>'p_12',
                '13'=>'p_13',
                '14'=>'p_14',
                '15'=>'p_15',
                '16'=>'p_16',
                '17'=>'p_17',
                '18'=>'p_18',
                '19'=>'p_19',
                '20'=>'p_20',
                'p_21','p_22','p_23','p_24','p_25','p_26','p_27','p_28'),

            'thumbnail'=>array('thumbnail_pic'),

            'media'=>array('big_pic','small_pic'),
           
            'brief'=>array('brief'),

            'last_modified'=>array('last_modify'),

            'description'=>array('intro'),

            'product'=>array(
                'id'=>array('product_id'),
                'bn'=>array('bn'),
                'meta'=>array('barcode','weight','unit','cost','uptime','market_place','pdt_desc'),
                'price'=>array('price','mprice','sale_price'),
                'last_modified'=>array('last_modify'),
                'store'=>array('store','store_place','freez'),
                'spec_value'=>array('props'),
            ),
        );
        return $schema;
    }

    function _getGoodsInfo($arr,$map_arr,$type_id){
        $array['goods'] = array();
        $attributes = $this->_getGoodsAttributes($arr,$map_arr);
        $productnum = $this->_getProductNum($arr);
        $basecurrency = $this->_getBaseCurrency();
        $bn = $this->_getBn($arr);
        $goodsId = $this->_getGoodsId($arr);
        $title = $this->_getTitle($arr,$map_arr);
        $createtime = $this->_getCreatetime($arr,$map_arr);
        $link = $this->_getLink($arr);
        //$status = $this->_getStatus($arr);
        $meta = $this->_getMeta($arr,$map_arr);
        $last_modified = $this->_getLastmodified($arr,$map_arr);
        $brief =  $this->_getBrief($arr,$map_arr);
        $props = $this->_getProps($arr,$map_arr,$type_id);
        $description = $this->_getDescription($arr,$map_arr);
        $adjunct = $this->_getAdjunct($arr,$map_arr);
        $category = $this->_getCategory($arr,$map_arr);
        $brand = $this->_getBrand($arr,$map_arr);
        $thumbnail = $this->_getThumbnail($arr,$map_arr);
        $spec = $this->_getSpec($arr,$map_arr);
        $media = $this->_getMedia($arr,$map_arr);
        $product = $this->_getProduct($arr,$map_arr);
        $url = $this->_getUrl($arr);
        $orderinfo = $this->_getOrderInfo($arr);

        $array['goods'] = array_merge($array['goods'],$attributes);
        $array['goods'] = array_merge($array['goods'],$productnum);
        $array['goods'] = array_merge($array['goods'],$basecurrency);
        $array['goods'] = array_merge($array['goods'],$goodsId);
        $array['goods'] = array_merge($array['goods'],$title);
        $array['goods'] = array_merge($array['goods'],$bn);
        $array['goods'] = array_merge($array['goods'],$createtime);
        $array['goods'] = array_merge($array['goods'],$link);
        //$array['goods'] = array_merge($array['goods'],$status);
        $array['goods'] = array_merge($array['goods'],$meta);
        $array['goods'] = array_merge($array['goods'],$last_modified);
        $array['goods'] = array_merge($array['goods'],$brief);
        $array['goods'] = array_merge($array['goods'],$props);
        $array['goods'] = array_merge($array['goods'],$description);
        $array['goods'] = array_merge($array['goods'],$adjunct);
        $array['goods'] = array_merge($array['goods'],$category);
        $array['goods'] = array_merge($array['goods'],$brand);
        $array['goods'] = array_merge($array['goods'],$thumbnail);
        $array['goods'] = array_merge($array['goods'],$spec);
        $array['goods'] = array_merge($array['goods'],$media);
        $array['goods'] = array_merge($array['goods'],$product);
        $array['goods'] = array_merge($array['goods'],$url);
        $array['goods'] = array_merge($array['goods'],$orderinfo);

        return $array;

    }

    function _getTitle($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['title'])){
                $k = 'title';
                $title[$k] = $v;
            }
        }
        return $title;
    }
    function _getUrl($arr){
        $url['url'] = 'http://127.0.0.1/485/src/shopadmin/index.php?ctl=goods/product&act=edit&p[0]='.$arr['goods_id'];
        return $url;
    }
    function _getBaseCurrency(){
        $basecur['base_currency'] = 'CYN';
        
        return $basecur;
    }
    function _getBn($arr){
        foreach($arr as $k=>$v){
            if($k=='bn'){
                $bn[$k] = $v;
            }
        }
        return $bn;
    }
    function _getOrderInfo($arr){
        $order_info = $this->db->selectrow('SELECT minfo FROM sdb_goods_type WHERE type_id =\''.$arr['type_id'].'\'');
        $o_info['orderinfo'] = $order_info['minfo'];
        return $o_info;
    }
    function _getProductNum($arr){
        $productsnum['products'] = 6;
        return $productsnum;
    }
    
    function _getStatus($arr){
        foreach($arr as $k=>$v){
            if($k=='status'){
                $status[$k]=$v;
            }
        }
        return $status;
    }

    function _getGoodsAttributes($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['attributes'])){
                $k = 'type';
                $attributes[$k] = $v;
            }
        }
        return $attributes;
    }
    
    function _getLink($arr){
        $linklist = $this->db->select('SELECT * FROM sdb_goods_rate WHERE goods_1=\''.$arr['goods_id'].'\'');
        $link['link'] = array();
        foreach($linklist as $k=>$v){
            $tmp = array('url'=>$v['goods_2'],'type'=>$v['manual'],'value'=>$v['rate']);
            array_push($link['link'],$tmp);
        }
        return $link;
    }

    function _getGoodsId($arr){
        foreach($arr as $k=>$v){
            if($k=='goods_id'){
                $goods_id[$k] = $v;
            }
        }
        return $goods_id;
    }

    function _getBrand($arr,$map_arr){
        $brand_arr['brand'] = array();
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['brand'])){

                $tmp = array('id'=>$arr['brand_id'],'value'=>$arr['brand']);
                array_push($brand_arr['brand'],$tmp);
            }

        }
        return $brand_arr;
    }

    function _getCategory($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['category'])){
                $value = $this->db->selectrow('SELECT cat_name FROM sdb_goods_cat WHERE cat_id =\''.$v.'\'');
                $tmp['category'] = array('id'=>$v,'value'=>$value['cat_name']);
            }
        }
        return $tmp;
    }

    function _getCreatetime($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['createtime'])){
                $tmp['createtime'] = $v;
            }
        }
        return $tmp;
    }
    function _getAdjunct($arr,$map_arr){

        $adj_include['adj_include'] = array();
        $adj_filter['adj_filter'] = array();
        $adjunct['adjunct'] = array();
        $tamp_adjunct = array();

        foreach($arr as $k=>$v){
            if($k=='adjunct'&& is_array($v)){
                foreach($v as $kk=>$vv){
                    $tmp['title'] = $vv['name'];
                    $tmp['min'] = $vv['min_num'];
                    $tmp['max'] = $vv['max_num'];
                    $tamp_adjunct = array_merge($tamp_adjunct,$tmp);
                    if(is_array($vv['items'])){
                        foreach($vv['items']['product_id'] as $pk=>$pid){
                            $pname = $this->db->selectrow('SELECT name FROM sdb_products WHERE product_id=\''.$pid.'\'');
                            $adj_tmp = array('id'=>$pid,'value'=>$pname['name']);
                            array_push($adj_include['adj_include'],$adj_tmp);
                        }
                        $tamp_adjunct = array_merge($tamp_adjunct,$adj_include);
                    }else{
                        $conditionList = explode('&',$vv['items']);
                        foreach($conditionList as $ck=>$cv){
                            $adj_filter_tmp = array('value'=>$cv);
                            array_push($adj_filter['adj_filter'],$adj_filter_tmp);
                        }
                        $tamp_adjunct = array_merge($tamp_adjunct,$adj_filter);
                    }
                    array_push($adjunct['adjunct'],$tamp_adjunct);
                    $tamp_adjunct = array();
                }
            }
        }
        return $adjunct;
    }
    function _getMedia($arr,$map_arr){
        $media_arr['image'] = array();
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['media'])){
                if($k=='small_pic'){
                    $height_row = $this->db->selectrow('SELECT src_size_height FROM sdb_gimages WHERE goods_id=\''.$arr['goods_id'].'\' AND small=\''.$v.'\'');
                    $width_row = $this->db->selectrow('SELECT src_size_width FROM sdb_gimages WHERE goods_id=\''.$arr['goods_id'].'\' AND small=\''.$v.'\'');
                    $small = $this->db->selectrow('SELECT small FROM sdb_gimages WHERE goods_id=\''.$arr['goods_id'].'\'');
                    $smallatt = explode("|",$small['small']);
                    $tmp = array('type'=>$k,'value'=>$v,'height'=>$height_row['src_size_height'],'width'=>$width_row['src_size_width'],'default'=>1,'storage'=>$smallatt[2],'option'=>'id='.$smallatt[1]);
                    array_push($media_arr['image'],$tmp);
                }
                if($k=='big_pic'){
                    $height_row = $this->db->selectrow('SELECT src_size_height FROM sdb_gimages WHERE goods_id=\''.$arr['goods_id'].'\' AND big=\''.$v.'\'');
                    $width_row = $this->db->selectrow('SELECT src_size_width FROM sdb_gimages WHERE goods_id=\''.$arr['goods_id'].'\' AND big=\''.$v.'\'');
                    $big = $this->db->selectrow('SELECT big FROM sdb_gimages WHERE goods_id=\''.$arr['goods_id'].'\'');
                    $bigatt = explode("|",$big['big']);
                    $tmp = array('type'=>$k,'value'=>$v,'height'=>$height_row['src_size_height'],'width'=>$width_row['src_size_width'],'storage'=>$bigatt[2],'option'=> 'id='.$smallatt[1],'default'=>0);
                    array_push($media_arr['image'],$tmp);
                }
                
            }
        }
        
        return $media_arr;
    }

    function _getThumbnail($arr,$map_arr){
        $th['thumbnail'] = array();
        foreach($arr as $k=>$v){
            $thumbnail['value']=$arr['thumbnail_pic'];
            $th['thumbnail'] = array_merge($th['thumbnail'],$thumbnail);
        }
        if(!is_null($th['thumbnail'])){
            $height_row = $this->db->selectrow('SELECT src_size_height FROM sdb_gimages WHERE goods_id=\''.$arr['goods_id'].'\'AND thumbnail =\''.$thumbnail['value'].'\'');
            $width_row = $this->db->selectrow('SELECT src_size_width FROM sdb_gimages WHERE goods_id=\''.$arr['goods_id'].'\'AND thumbnail =\''.$thumbnail['value'].'\'');
            $thumbnail['width'] = 100;
            $thumbnail['height'] = 100;
            $th['thumbnail'] = array_merge($th['thumbnail'],$thumbnail);
        }
        return $th;
    }

    function _getSpec($arr,$map_arr){
        $spec_arr['spec'] = array();
        $option['option'] = array();
        foreach($arr as $k=>$v){
            if($k=='spec_desc'){
                foreach($v as $kk=>$vv){
                    foreach($vv as $key=>$value){
                        $temp = array('value'=>$value['spec_value']);
                        array_push($option['option'],$temp);
                    }
                    $spec_row = $this->db->selectrow('SELECT spec_name FROM sdb_specification WHERE spec_id=\''.$kk.'\'');
                    $tmp['spec'] = array('title'=>$spec_row['spec_name'],'key'=>$kk);
                    $tmp['spec'] = array_merge($tmp['spec'],$option);
                    $option['option'] = array();
                    array_push($spec_arr['spec'], $tmp['spec']);
                }
            }
        }
        return $spec_arr;
    }

    function _getLastmodified($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['last_modified'])){
                $k = 'last_modified';
                $last_modified[$k] = $v;
            }
        }
        return $last_modified;
    }

    function _getDescription($arr,$map_arr){

        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['description'])){
                $k = 'description';
                $description[$k] = $v;
            }
        }
        return $description;
    }

    function _getMeta($arr,$map_arr){
        $meta_arr['meta'] = array();
        if(is_array($arr)){
            foreach($arr as $k=>$v){
                if(in_array($k,$map_arr['meta'])){
                    $tmp = array('key'=>$k,'value'=>$v);
                    array_push($meta_arr['meta'],$tmp);
                }
            }
        }
        return $meta_arr;
    }


    function _getProps($arr,$map_arr,$type_id){
        $array['props'] = array();
        $props = $this->db->selectrow('SELECT props FROM sdb_goods_type WHERE type_id =\''.$type_id.'\'');
        $p = unserialize($props['props']);
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['props'])){
                if(!is_null($v)){
                    $tmp[$k] = array('id'=>$v,'value'=>$p[substr($k,2)]['options'][$v]);
                }
            }
        }
        $array['props'] = array_merge($array['props'],$tmp);
        return $array;
    }

    function _getBrief($arr,$map_arr){
        foreach($arr as $k=>$v){
            if(in_array($k,$map_arr['brief'])){
                $brief[$k]=$v;
            }
        }
        return $brief;
    }

    function _getProduct($arr,$map_arr){
        $product['meta']=array();
        $product['price'] = array();
        $product['store'] = array();
        $product['spec_def'] = array();
        $p['spec_value'] = array();
        $tamp['product'] = array();
        foreach($arr as $key=>$value){
            if($key=='products'){
                if(is_array($value)){
                    //print_r($value);
                    foreach($value as $k=>$v){

                        foreach($v as $kk=>$vv){

                            if(in_array($kk,$map_arr['product']['id'])){
                                $product['id']=$vv;
                            }

                            if(in_array($kk,$map_arr['product']['bn'])){
                                $product['bn']=$vv;
                            }
                            if(in_array($kk,$map_arr['product']['store'])){
                                $product['store'] = array('place'=>$v['store_place'],'freez'=>$v['freez'],'value'=>$v['store']);
                                //array_push($product['store'],$tmp['store']);
                            }

                            if(in_array($kk, $map_arr['product']['meta'])){

                                $tmp = array('key'=>$kk,'value'=>$vv);
                                array_push($product['meta'],$tmp);
                            }

                            if(in_array($kk,$map_arr['product']['price'])){

                                if(is_array($vv)){
                                    foreach($vv as $member_id=>$val){
                                        $tmp['price']=array('title'=>$kk,'member_group_id'=>$member_id,'value'=>$val,'disabled'=>$v['disabled']);
                                        array_push($product['price'],$tmp['price']);
                                    }

                                }else{
                                    $tmp['price']=array('title'=>$kk,'value'=>$vv,'disabled'=>$v['disabled']);
                                    array_push($product['price'],$tmp['price']);
                                }
                            }
                            if(in_array($kk,$map_arr['product']['last_modified'])){

                                $product['last_modified']=$vv;
                            }
                            if(in_array($kk,$map_arr['product']['spec_value'])){
                                foreach($vv['spec'] as $spec_k=>$spec_v){
                                    $temp_spec_val = array('key'=>$spec_k,'value'=>$spec_v);
                                    array_push($p['spec_value'],$temp_spec_val);
                                }
                                for($i=0;$i<count($vv['spec_value_id']);$i++){
                                    $p['spec_value'][$i]['key'] = $vv['spec_value_id'][$i+1];
                                }
                                $product['spec_def'] = array_merge($product['spec_def'],$p);
                            }

                        }

                        array_push($tamp['product'],$product);
                        $product['meta']=array();
                        $product['price'] = array();
                        $product['store'] = array();
                        $p['spec_value'] = array();
                    }
                }

            }
        }
        return $tamp;
    }
}