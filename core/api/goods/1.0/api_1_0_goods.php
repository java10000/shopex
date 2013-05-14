<?php
/**
 * API 商品模块部份
 * @package
 * @version 1.0:
 * @copyright 2003-2009 ShopEx
 * @author dreamdream
 * @license Commercial
 */
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_1_0_goods extends shop_api_object {
    var $select_limited=100;
    var $app_error=array(
        'can not find the goods'=>array('no'=>'c_goods_001','debug'=>'','level'=>'warning','info'=>'找不到相应的商品','desc'=>'','debug'=>'')
    );
    /**
    * 商品部份开放的字段，包括字段类型
    * @author DreamDream
    * @return 开放的字段相关信息
    */
    function getColumns(){
        $columns=array(
            'goods_id'=>array('type'=>'int'),
            'cat_id'=>array('type'=>'int'),
            'type_id'=>array('type'=>'int'),
            'goods_type'=>array('type'=>'int'),
            'brand_name'=>array('type'=>'string','name'=>'brand_id'),
            'image_default'=>array('type'=>'string'),
            'image'=>array('type'=>'string','join'=>true),
            'udfimg'=>array('type'=>'string'),
            'brief'=>array('type'=>'string'),
            'mktprice'=>array('type'=>'string'),
            'price'=>array('type'=>'decimal'),
            'bn'=>array('type'=>'string'),
            'name'=>array('type'=>'string'),
            'marketable'=>array('type'=>'string'),
            'pdt_desc'=>array('type'=>'string'),
            'spec_desc'=>array('type'=>'string'),
            'props_name'=>array('type'=>'string','name'=>'params'),
            'category_name'=>array('type'=>'string','join'=>true),
            'goods_link'=>array('type'=>'string','join'=>true),
            'last_modify'=>array('type'=>'int'),
            'products_info'=>array('type'=>'string','join'=>true),
            'intro'=>array('type'=>'string')
        );
        return $columns;
    }

    /**
    * 设置库存
    * @param 设置库存的数据值包括Goods_id
    * @author DreamDream
    * @return 设置库存是否成功
    */
    function set_goods_bn($data){
        safeVar($data);
        if(!($rs=$this->db->exec('select bn from sdb_goods where goods_id='.intval($data['goods_id'])))){
            $this->api_response('fail','data fail',$data,'can not find the goods');
        }
        unset($data['goods_id']);
        $aData=$this->varify_date_whole($data);
        $sql=$this->db->getUpdateSQL($rs,$aData);
        if(!$this->db->exec($sql)){
            $this->api_response('fail','db error',$data);
        }
        return $this->api_response('true');
    }
    /**
    * 查找商品详细信息(一次一条记录)
    * @param 查找商品的详细条件
    * @author DreamDream
    * @return 查找到的商品信息
    */
    function search_goods_detail($data){
        if(!($result['data_info']=$this->db->selectrow('select * from sdb_goods where goods_id='.intval($data['goods_id'])))){
            $this->api_response('fail','data fail',$data,'can not find the goods');
        }
        $props_ar = array();
        foreach($result['data_info'] as $kk =>$vv){
            if(is_numeric(substr($kk,-1))&&$vv){
                $props_ar[] = substr($kk,strpos($kk,"_")+1).":".$vv;
            }
        }
        if($props_ar){
            $result['data_info']['props'] = implode(",",$props_ar);
        }
        $image_default_id = $result['data_info']['image_default'];
        if($result['data_info']['brand_name']){
            $brand=$this->db->selectrow('select brand_name from sdb_brand where brand_id='.intval($result['data_info']['brand_name']));
            $result['data_info']['brand_name']=$brand['brand_name'];
        }

        if($result['data_info']['intro']){
           $result['data_info']['intro']=str_replace('\"','\'',$result['data_info']['intro']);
           //$result['data_info']['intro']=htmlspecialchars($result['data_info']['intro'],ENT_QUOTES);
        }
        if($result['data_info']['pdt_desc']){
            $pdt_desc=unserialize($result['data_info']['pdt_desc']);
            unset($result['data_info']['pdt_desc']);
            foreach($pdt_desc as $key=>$value){
                $result['data_info']['pdt_desc'][]=array('product_id'=>$key?$key:'','product_desc'=>$value?$value:'');
            }
        }
        if($result['data_info']['spec_desc']){
            $props=unserialize($result['data_info']['spec_desc']);
            unset($result['data_info']['spec_desc']);
            foreach($props as $key=>$value){
                    foreach($value as $s_key=>$s_value){
                        $result['data_info']['spec_desc'][$key][$s_key]['prop_id']=$key?$key:'';
                        $result['data_info']['spec_desc'][$key][$s_key]['spec_value']=$s_value['spec_value']?$s_value['spec_value']:'';
                        $result['data_info']['spec_desc'][$key][$s_key]['spec_type']=$s_value['spec_type']?$s_value['spec_type']:'';
                        $result['data_info']['spec_desc'][$key][$s_key]['spec_value_id']=$s_value['spec_value_id']?$s_value['spec_value_id']:'';
                    }
            }

        }
        if($data['columns_join']['category_name']){
            $category_name=$this->db->selectrow('select cat_name as category_name from sdb_goods_cat where cat_id='.intval($result['data_info']['cat_id']));
            $result['data_info']['category_name']=$category_name['category_name']?$category_name['category_name']:'';

        }
        if($data['columns_join']['goods_link']){
            $result['data_info']['goods_link']=$this->system->realUrl('product','index',array($data['goods_id']),null,$this->system->base_url());
        }
        if(empty($data['columns_join']['goods_link'])){
            $data['columns_join']['goods_link']='';
        }

        if($result['data_info']['props_name']){
            $props_name=unserialize($result['data_info']['props_name']);
            $result['data_info']['props_name']=array();
            foreach($props_name as $key=>$value){
                if(is_array($value)){
                    $result['data_info']['props_name'][]=array('name'=>key($value),'value'=>current($value)?current($value):'');
                }
            }
        }
        if($result['data_info']['image_default']){
            if($image=$this->db->selectrow("select big from sdb_gimages where gimage_id='".$result['data_info']['image_default']."'")){
                $result['data_info']['image_default']=$image['big']?$image['big']:'';
            }
            if($image=explode('|',$result['data_info']['image_default'])){
                if(substr($image[0],0,4)=='http'){
                    $result['data_info']['image_default']=$image[0];
                }else{
                    $result['data_info']['image_default']=$this->system->base_url().$image[0];
                }
            }

        }
        if($data['columns_join']['image']){
            $image=$this->db->select('select gimage_id,small,big,thumbnail from sdb_gimages where goods_id='.$data['goods_id']);
            foreach($image as $key=>$value){
                $result['data_info']['images'][]=array('image_id'=>$value['gimage_id'],'small'=>$value['small']?$value['small']:'','big'=>$value['big']?$value['big']:'','thumbnail'=>$value['thumbnail']?$value['thumbnail']:'','is_default'=>($value['gimage_id']==$image_default_id)?'true':'false');
            }
        }
        if($data['columns_join']['products_info']){
            if($products=$this->db->select('select product_id,name,bn,price,store,marketable,last_modify from sdb_products where goods_id='.intval($result['data_info']['goods_id']))){
               foreach($products as $key=>$value){
                    $pro_descv="";
                    if($prdo_desc=$this->db->select("SELECT spec_id,spec_value_id FROM sdb_goods_spec_index WHERE product_id =".$value['product_id'])){
                        foreach($prdo_desc as $v=>$y){
                            $pro_descv .=$y['spec_id'].':'.$y['spec_value_id'].';';
                        }
                        $result['data_info']['products_info'][$key]['spec_info'] = substr($pro_descv,0,-1);
                    }
                    $result['data_info']['products_info'][$key]['marketable']=$value['marketable']?$value['marketable']:'';
                    $result['data_info']['products_info'][$key]['product_id']=$value['product_id']?$value['product_id']:'';
                    $result['data_info']['products_info'][$key]['bn']=$value['bn']?$value['bn']:'';
                    $result['data_info']['products_info'][$key]['price']=$value['price']?$value['price']:'';
                    $result['data_info']['products_info'][$key]['store']=$value['store']?$value['store']:'';
                    $result['data_info']['products_info'][$key]['last_modify']=$value['last_modify']?$value['last_modify']:'';
               }
               unset($product_info);
            }
        }
        $this->api_response('true',false,$result);

    }

    function get_spec_info($data){
        $result['data_info'] = $this->db->selectrow('SELECT spec_id,spec_name FROM sdb_specification WHERE spec_id ='.$data['spec_id']);
        if(!$result['data_info']){
             $this->api_response('fail','no data',$data);
        }
        $result['data_info']['spec_value_detail'] = $this->db->select('SELECT spec_value_id,spec_value,p_order FROM sdb_spec_values WHERE spec_id ='.$data['spec_id']);
        $this->api_response('true',false,$result);
    }
    function get_goods_brand(){
        $result['data_info'] = $this->db->select('SELECT * FROM sdb_brand ');
        $result['counts']=count($result['data_info']);
        $this->api_response('true',false,$result);
    }
    function get_goods_cat(){
        $result['data_info'] = $this->db->select('SELECT * FROM sdb_goods_cat ');
        $result['counts']=count($result['data_info']);
        $this->api_response('true',false,$result);
    }

    function add_goods_info($data){
        if(!$data['type_id']){
            $this->api_response('fail','type_id is empty',$data);
        }
        if(!$data['name']){
            $this->api_response('fail','name is empty',$data);
        }
        $aData = $data;
        $aData['uptime'] = time();
        $aData['last_modify'] = time();
        $cof_props = explode(";",$data['props']);
        foreach($cof_props as $pp_key=>$pp_val){
            $aData["p_".substr($pp_val,0,strpos($pp_val,':'))] = substr($pp_val,strpos($pp_val,':')+1);
        }
        $rs = $this->db->exec("SELECT * FROM sdb_goods WHERE 0=1");
        $sql  = $this->db->getInsertSQL($rs,$aData);

        if(!$this->db->exec($sql)){
            $this->api_response('fail','sql is empty',$data);
        }
        $goods_id = $this->db->lastinsertid();
        if($data['uptime']){
            $gtask['goods_id'] = $goods_id;
            $gtask['tasktime'] = $data['uptime'];
            $gtask['action'] = 'online';
            $rs = $this->db->exec("SELECT * FROM sdb_gtask WHERE 0=1");
            $sql  = $this->db->getInsertSQL($rs,$gtask);
            $this->db->exec($sql);
        }
        if($data['downtime']){
            $gtask['goods_id'] = $goods_id;
            $gtask['tasktime'] = $data['downtime'];
            $gtask['action'] = 'offline';
            $rs = $this->db->exec("SELECT * FROM sdb_gtask WHERE 0=1");
            $sql  = $this->db->getInsertSQL($rs,$gtask);
            $this->db->exec($sql);
        }
        foreach(json_decode($data['images'],true) as $i_k =>$i_v){
            $image = $i_v;
            $image['goods_id'] = $goods_id;
            $image['up_time'] = time();
            $rs = $this->db->exec("SELECT * FROM sdb_gimages WHERE 0=1");
            $sql  = $this->db->getInsertSQL($rs,$image);
            $this->db->exec($sql);
            if($i_v['is_default']=='true'){
                $update_g['image_default'] = $this->db->lastinsertid();
                $update_g['thumbnail_pic'] = $i_v['thumbnail'];
                $update_g['small_pic'] = $i_v['small'];
                $update_g['big_pic'] = $i_v['big'];
            }
        }
        $has_good = false;
        foreach(json_decode($data['products_info'],true) as $c_k=>$c_v){
            $in_spec = array();
            $has_good = true;
            $c_v['name']= $data['name'];
            $c_v['goods_id']= $goods_id;
            $c_v['uptime']= time();
            $c_v['last_modify']= time();
            if(!$c_v['spec_info'])$this->api_response('fail','has no spec',$result);
            $g_spec = explode(";",$c_v['spec_info']);
            $ss = time();
            foreach($g_spec as $g_k=>$g_v){
                $spec_value_id = substr($g_v,(strpos($g_v,':')+1));
                $spec_id = substr($g_v,0,strpos($g_v,':'));
                $temp = $this->db->selectrow("SELECT spec_value FROM sdb_spec_values WHERE spec_value_id =".$spec_value_id);
                $t_spec = $this->db->selectrow("SELECT spec_name,spec_type FROM sdb_specification WHERE spec_id =".$spec_id);
                $t_spec_name[$spec_id] = $t_spec['spec_name'];
                $c_v['pdt_desc'] .= $temp['spec_value'].'、';
                $te_spec['spec'][$spec_id] = $temp['spec_value'];
                $ss = intval(rand(123123123,910910910));

                if(!$spec_v1[$spec_value_id]){
                    $t_spec_name_c[$spec_id][$ss] = array('spec_value'=>$temp['spec_value'],'spec_type'=>$temp['spec_type'],'spec_value_id'=>$spec_value_id,'spec_image'=>'','spec_goods_images'=>'');
                    $spec_v1[$spec_value_id] =$ss;
                }
                $te_spec['spec_private_value_id'][$spec_id] = $spec_v1[$spec_value_id];
                $te_spec['spec_value_id'][$spec_id] = $spec_value_id;
                $in_spec[] = array('spec_id'=>$spec_id,'spec_value_id'=>$spec_value_id,'goods_id'=>$goods_id,'type_id'=>$data['type_id']);
            }

            $c_v['pdt_desc'] = substr($c_v['pdt_desc'],0,-3);
            $c_v['props'] = serialize($te_spec);
            $rs = $this->db->exec("SELECT * FROM sdb_products WHERE 0=1");
            $sql  = $this->db->getInsertSQL($rs,$c_v);
            $this->db->exec($sql);
            $product_id = $this->db->lastinsertid();
            $update_g['spec'] = serialize($t_spec_name);
            $nc_v[$product_id]=$c_v['pdt_desc'];
            foreach($in_spec as $pp_k=>$pp_v){
                $in_spec[$pp_k]['product_id'] = $product_id;
                $ncc_data = $this->db->exec("SELECT * FROM sdb_goods_spec_index WHERE 0=1");
                $sql  = $this->db->getInsertSQL($ncc_data,$in_spec[$pp_k]);
                $this->db->exec($sql);
            }
        }
        if($update_g){
            $update_g['spec_desc'] = serialize($t_spec_name_c);
            $update_g['pdt_desc'] = serialize($nc_v);
            $res = $this->db->exec("SELECT * FROM sdb_goods WHERE goods_id =".$goods_id);
            $sql = $this->db->getUpdateSQL($res,$update_g);
            $this->db->exec($sql);
        }
        if(!$has_good){
            $aData['goods_id'] = $goods_id;
            $rs = $this->db->exec("SELECT * FROM sdb_products WHERE 0=1");
            $sql  = $this->db->getInsertSQL($rs,$aData);
            $this->db->exec($sql);
        }
        $result['data_info'] = array('goods_id'=>$goods_id,'last_modified'=>time());
        $this->api_response('true',false,$result);
    }

    function goods_wltx_exp_list($data){
        $goods_id=@explode(',',$this->system->getConf('utility.wltx'));
        $result['data_info']=$this->db->select('select goods_id,last_modify from sdb_goods where goods_id in (\''.implode('\',\'',$goods_id).'\') limit 0,30');
        $result['counts']=count($result['data_info']);
        $this->api_response('true',false,$result);
    }
    /**
    * 查找商品删除列表
    * @param 查找商品列表条件
    * @author DreamDream
    * @return 查找到的商品列表结果集
    */
    function search_deleted_goods_list($data){
        if($data['last_modify_st_time']=='0'){
            $result=$this->db->selectrow('select count(*) as counts from sdb_goods where (( last_modify>='.intval($data['last_modify_st_time']).' and last_modify<'.intval($data['last_modify_en_time']).') or (last_modify is null)) and (disabled="true" or marketable="false")');
        }else{
            $result=$this->db->selectrow('select count(*) as counts from sdb_goods where last_modify>='.intval($data['last_modify_st_time']).' and last_modify<'.intval($data['last_modify_en_time']).' and (disabled="true" or marketable="false")');
        }
        $data['deleted']=true;
        $where=$this->_filter($data);
        $result['data_info']=$this->db->select('select '.implode(',',$data['columns']).' from sdb_goods '.$where);
        $this->api_response('true',false,$result);
    }
    function set_goods_marketable_succ($data){
        if(!$data['goods_id']){
             $this->api_response('fail','data fail',$data,'');
        }else{
            $result=$this->db->selectrow('select marketable from sdb_goods where goods_id='.$data['goods_id']);
            $this->api_response('true',false,$result);
        }
    }
    /**
    * 查找商品列表
    * @param 查找商品列表条件
    * @author DreamDream
    * @return 查找到的商品列表结果集
    */
    function search_goods_list_by_lastmodify($data){
        if($data['last_modify_st_time']=='0'){
            $result=$this->db->selectrow('select count(*) as counts from sdb_goods where (( last_modify>='.intval($data['last_modify_st_time']).' and last_modify<'.intval($data['last_modify_en_time']).') or (last_modify is null)) and disabled="false" and marketable="true"');

        }else{
            $result=$this->db->selectrow('select count(*) as counts from sdb_goods where last_modify>='.intval($data['last_modify_st_time']).' and last_modify<'.intval($data['last_modify_en_time']).' and disabled="false" and marketable="true"');
        }

        $where=$this->_filter($data);
        $result['data_info']=$this->db->select('select '.implode(',',$data['columns']).' from sdb_goods '.$where);
        $this->api_response('true',false,$result);
    }

    /**
    * 商品模块的过滤赛选器
    * @param 赛选条件
    * @author DreamDream
    * @return 过滤过的筛选条件
    */
    function _filter($filter){
        $where = array();
        if($filter['last_modify_st_time']=='0'){
                $where[]='((last_modify >='.intval($filter['last_modify_st_time']).' or last_modify <'.intval($filter['last_modify_en_time']).') or last_modify is null)';
        }else{
            if(isset($filter['last_modify_st_time'])){
                $where[]='last_modify >='.intval($filter['last_modify_st_time']);
            }
            if(isset($filter['last_modify_en_time'])){
                $where[]='last_modify <'.intval($filter['last_modify_en_time']);
            }
        }
        if(isset($filter['cat_id']))$where[]='cat_id ='.$filter['cat_id'];
        if(isset($filter['deleted'])){
            $where[]=' ( disabled="true" or marketable="false") ';
        }else{
            $where[]='disabled="false"';
            $where[]='marketable="true"';
            $where[]='goods_type!="bind"';
        }
        return parent::_filter($where,$filter);
    }

    /**
     * 添加销售日志
     * @param array $data
     * @return
     */
    function create_sell_log($data){
        $orderData = $this->db->selectrow('SELECT o.member_id, m.uname,o.ship_email FROM sdb_orders o LEFT JOIN sdb_members m ON o.member_id = m.member_id WHERE o.order_id = '.$data['order_id']);
        $orderItem = $this->db->select('SELECT p.price, p.goods_id, i.product_id, p.name,p.pdt_desc, i.nums FROM sdb_order_items i LEFT JOIN sdb_products p ON p.product_id = i.product_id WHERE i.order_id = '.$data['order_id']);
        foreach( $orderItem as $iKey => $iValue ){
            $sql = 'INSERT INTO sdb_sell_logs (member_id,name,price,goods_id,product_id,product_name,pdt_desc,number,createtime) VALUES ( "'.($orderData['member_id']?$orderData['member_id']:0).'", "'.($orderData['uname']?$orderData['uname']:$orderData['ship_email']).'", "'.$iValue['price'].'", "'.$iValue['goods_id'].'", "'.$iValue['product_id'].'", "'.$iValue['name'].'", "'.$iValue['pdt_desc'].'" , "'.$iValue['nums'].'", "'.time().'" )';
            if(!$this->db->exec($sql)){
                $this->api_response('fail','db error',$data);
            }
        }
        $this->api_response('true');
    }

    /**
     * 更新所有商品的库存
     * @param $data
     * @return unknown_type
     */
    function update_goods_store($data){
        $sql ="SELECT goods_id , SUM(store) AS sum_store FROM sdb_products GROUP BY goods_id";
        $goods_info = $this->db->select($sql);

        foreach ($goods_info as $key => $val){
            $update_str = "UPDATE sdb_goods SET store=".$val['sum_store']." WHERE goods_id=".$val['goods_id']." AND store!=".$val['sum_store'];
            if(!$this->db->exec($update_str)){
                $this->api_response('fail','db error',$data);
            }
        }
        $this->api_response('true');
    }



    function get_spec_list($data){
        $result['data_info'] = $this->db->select('SELECT spec_id,spec_name FROM sdb_specification');
        if(!$result['data_info']){
             $this->api_response('fail','no data',$data);
        }
        foreach($result['data_info'] as $key=>$value){
            $result['data_info'][$key]['spec_value_detail'] = $this->db->select('SELECT spec_value_id,spec_value,p_order FROM sdb_spec_values WHERE spec_id ='.$value['spec_id']);
        }
        $this->api_response('true',false,$result);
    }

    function update_goods_image($data){
        if(!$this->db->select("SELECT goods_id FROM sdb_goods WHERE goods_id=".$data['goods_id'])){
            $this->api_response('fail','no goods',$result);
        }

        $data['images'][] = array("image_id"=>$data['image_id'],
                                "is_default"=>$data['is_default'],
                                "big"=>$data['big'],
                                "small"=>$data['small'],
                                "thumbnail"=>$data['thumbnail']
                                );
        foreach($data['images'] as $i_k =>$i_v){
            $image = $i_v;
            $image['goods_id'] = $data['goods_id'];
            $image['up_time'] = time();
            if(!$i_v['image_id']){
                $rs = $this->db->exec("SELECT * FROM sdb_gimages WHERE 0=1");
                $sql  = $this->db->getInsertSQL($rs,$image);
                $insert= true;
            }else{
                $rs = $this->db->exec("SELECT * FROM sdb_gimages WHERE gimage_id=".$i_v['image_id']);
                $sql  = $this->db->getUpdateSQL($rs,$image);
                $image_id = $i_v['image_id'];
            }

            $this->db->exec($sql);
            if($insert)$image_id = $this->db->lastinsertid();
            if($i_v['is_default']=='true'){
                $update_g['goods_id'] = $data['goods_id'];
                $update_g['image_default'] = $i_v['image_id']?$i_v['image_id']:$this->db->lastinsertid();
                $update_g['thumbnail_pic'] = $i_v['thumbnail'];
                $update_g['small_pic'] = $i_v['small'];
                $update_g['big_pic'] = $i_v['big'];
                $ress = $this->db->exec("SELECT * FROM sdb_goods WHERE goods_id =".$data['goods_id']);
                $sql = $this->db->getUpdateSQL($ress,$update_g);
                if($sql)$this->db->exec($sql);
            }
        }
        $result['data_info'] = array('goods_id'=>$data['goods_id'],'image_id'=>$image_id,'modified'=>time());
        $this->api_response('true',false,$result);
    }

    function get_shop_goods_type(){
        $result['data_info'] = $this->db->select("SELECT type_id,name,props,setting FROM sdb_goods_type");
        foreach($result['data_info'] as $k=>$v){
            $result['data_info'][$k]['props'] = unserialize($v['props']);
        }
        $this->api_response('true',false,$result);
    }
    function get_goods_props($data){
        $result['data_info'] = $this->db->selectrow("SELECT type_id,name,props,setting FROM sdb_goods_type WHERE type_id=".$data['type_id']);
        $result['data_info']['props'] = unserialize($result['data_info']['props']);
        $this->api_response('true',false,$result);
    }

    function delete_goods_images($data){  // update by 20100611 guhaiguo
        if(substr($data['image_id'],-1)==',')$this->api_response('fail','image_id is error',$data);

        $image_id = explode(",",$data['image_id']);

        foreach ($image_id as $key=>$value){
           $this->db->exec("DELETE FROM sdb_gimages WHERE gimage_id =".intval($value));
           $returnid .= $value.",";
        }
        $returnid = substr($returnid,0,strlen($returnid)-1);
        $result['data_info']=array('image_id'=>$returnid,'modified'=>time());
        $this->api_response('true',false,$result);
    }


    function delete_goods_image($data){  // update by 20100611 guhaiguo
        if (intval($data['goods_id']) =='' || intval($data['image_id']) == '')
            $this->api_response('fail','goods_id or image_id is error',$data);
        $this->db->exec("DELETE FROM sdb_gimages WHERE goods_id =".intval($data['goods_id'])." and gimage_id =".intval($data['image_id']));
        $result['data_info']=array('goods_id'=>$data['goods_id'],'image_id'=>$data['image_id'],'modified'=>time());
        $this->api_response('true',false,$result);
    }

    function update_goods_info($data){
        $aData = $data;
        $aData['uptime'] = time();
        $aData['last_modify'] = time();
        $goods_id = $data['goods_id'];
        $rs = $this->db->exec("SELECT * FROM sdb_goods WHERE goods_id=".$goods_id);
        $sql  = $this->db->getUpdateSQL($rs,$aData);
        $this->db->exec($sql);
        if($data['uptime']){
            $gtask['goods_id'] = $goods_id;
            $gtask['tasktime'] = $data['uptime'];
            $gtask['action'] = 'online';
            $rs = $this->db->exec("SELECT * FROM sdb_gtask WHERE goods_id=".$goods_id." AND action='online'");
            $sql  = $this->db->getUpdateSQL($rs,$gtask);
            $this->db->exec($sql);
        }
        if($data['downtime']){
            $gtask['goods_id'] = $goods_id;
            $gtask['tasktime'] = $data['downtime'];
            $gtask['action'] = 'offline';
            $rs = $this->db->exec("SELECT * FROM sdb_gtask WHERE goods_id=".$goods_id." AND action='offline'");
            $sql  = $this->db->getUpdateSQL($rs,$gtask);
            $this->db->exec($sql);
        }
        foreach(json_decode($data['product_info'],true) as $c_k=>$c_v){
            $c_v['goods_id'] = $goods_id;
            $this->tobedit($c_v);
        }
        $result['data_info'] = array('goods_id'=>$goods_id,'last_modified'=>time());
        $this->api_response('true',false,$result);
    }



    function add_products_info($c_v){

        $goods_id = $c_v['goods_id'];
        $ccc_data = $this->db->selectrow("SELECT spec,pdt_desc,spec_desc FROM sdb_goods WHERE goods_id=".$c_v['goods_id']);
        if(!$ccc_data){
            $this->api_response('fail','has no products',$result);
        }
        if(substr($c_v['spec_info'],-1)==';')$this->api_response('fail','spec_info is error',$c_v);
        $t_spec_name = unserialize($ccc_data['spec']);

        $nc_v = unserialize($ccc_data['pdt_desc']);
        $t_spec_name_c = unserialize($ccc_data['spec_desc']);

        foreach($t_spec_name_c as $c_k=>$cv){
            foreach($cv as $t_k=>$t_v){
                $spec_v1[$t_v['spec_value_id']]=$t_k;
            }
        }
        if($data['name'])$c_v['name']= $data['name'];
        $c_v['goods_id']= $goods_id;
        $c_v['uptime']= time();
        $c_v['last_modify']= time();
        if(!$c_v['spec_info']){
            $this->api_response('fail','has no spec',$result);
        }
        $g_spec = explode(";",$c_v['spec_info']);

        foreach($g_spec as $g_k=>$g_v){
            $spec_value_id = substr($g_v,(strpos($g_v,':')+1));
            $spec_id = substr($g_v,0,strpos($g_v,':'));
            $temp = $this->db->selectrow("SELECT spec_value FROM sdb_spec_values WHERE spec_value_id =".$spec_value_id);
            $t_spec = $this->db->selectrow("SELECT spec_name,spec_type FROM sdb_specification WHERE spec_id =".$spec_id);
            $t_spec_name[$spec_id] = $t_spec['spec_name'];
            $c_v['pdt_desc'] .= $temp['spec_value'].'、';
            $te_spec['spec'][$spec_id] = $temp['spec_value'];
            $ss = intval(rand(123123123,910910910));

            if(!$spec_v1[$spec_value_id]){
                $t_spec_name_c[$spec_id][$ss] = array('spec_value'=>$temp['spec_value'],'spec_type'=>$temp['spec_type'],'spec_value_id'=>$spec_value_id,'spec_image'=>'','spec_goods_images'=>'');
                $spec_v1[$spec_value_id] =$ss;
            }
            $te_spec['spec_private_value_id'][$spec_id] = $spec_v1[$spec_value_id];
            $te_spec['spec_value_id'][$spec_id] = $spec_value_id;
            $in_spec[] = array('spec_id'=>$spec_id,'spec_value_id'=>$spec_value_id,'goods_id'=>$goods_id,'type_id'=>$data['type_id']);
        }


        $c_v['pdt_desc'] = substr($c_v['pdt_desc'],0,-3);
        $c_v['props'] = serialize($te_spec);
        $rs = $this->db->exec("SELECT * FROM sdb_products WHERE 1=0");
        $sql  = $this->db->getInsertSQL($rs,$c_v);
        $this->db->exec($sql);
        $product_id = $this->db->lastinsertid();
        $update_g['spec'] = serialize($t_spec_name);
        $nc_v[$product_id]=$c_v['pdt_desc'];
        foreach($in_spec as $pp_k=>$pp_v){
            $in_spec[$pp_k]['product_id'] = $product_id;
            $ncc_data = $this->db->exec("SELECT * FROM sdb_goods_spec_index WHERE 1=0");
            $sql  = $this->db->getInsertSQL($ncc_data,$in_spec[$pp_k]);
            $this->db->exec($sql);
        }
        if($update_g){
            $update_g['spec_desc'] = serialize($t_spec_name_c);
            $update_g['pdt_desc'] = serialize($nc_v);
            $res = $this->db->exec("SELECT * FROM sdb_goods WHERE goods_id =".$goods_id);
            $sql = $this->db->getUpdateSQL($res,$update_g);
            $this->db->exec($sql);
        }
        $result['data_info'] = array('goods_id'=>$goods_id,'products_id'=>$product_id,'last_modified'=>time());
        $this->api_response('true',false,$result);
    }


    function delete_products($data){
        if(!$this->db->selectrow("SELECT * FROM sdb_products WHERE product_id=".$data['products_id']." AND goods_id=".$data['goods_id'])){
            $this->api_response('fail','data fail',$result);
        }
        $this->db->exec("DELETE FROM sdb_products WHERE product_id=".$data['products_id']);

        $this->db->exec("DELETE FROM sdb_goods_spec_index WHERE product_id=".$data['products_id']);
        $this->convert_props($data);
        //$grd_data = $this->db->selectrow("SELECT spec,pdt_desc,spec_desc FROM sdb_goods WHERE goods_id=".$data['goods_id']);
        $result['data_info'] = array('products_id'=>$data['products_id'],'last_modified'=>time(),'goods_id'=>$data['goods_id']);
        $this->api_response('true',false,$result);
    }

    function convert_props($data){
        $prd_data = $this->db->select("SELECT props,product_id,pdt_desc FROM sdb_products WHERE goods_id=".$data['goods_id']);
        $i=0;
        foreach($prd_data as $key=>$value){
            $np = unserialize($value['props']);
            foreach($np['spec'] as $ke=>$va){
                if($i==0){
                    $tmp = $this->db->selectrow("SELECT spec_name,spec_type FROM sdb_specification WHERE spec_id =".$ke);
                    $spec[$ke] = $tmp['spec_name'];
                }
                if(!$goods[$np['spec_value_id'][$ke]]){
                    $spec_desc[$ke][$np['spec_private_value_id'][$ke]]=array('spec_value'=>$va,'spec_type'=>$tmp['spec_type'],'spec_value_id'=>$np['spec_value_id'][$ke],'spec_image'=>'','spec_goods_images'=>'');
                }
                $goods[$np['spec_value_id'][$ke]]= $np['spec_private_value_id'][$ke];
            }
            $pdt_desc[$value['product_id']] = $value['pdt_desc'];
            $i++;
        }
        $good_s['spec'] = serialize($spec);
        $good_s['spec_desc'] = serialize($spec_desc);
        $good_s['pdt_desc'] = serialize($pdt_desc);
        $res = $this->db->exec("SELECT * FROM sdb_goods WHERE goods_id =".$data['goods_id']);
        $sql = $this->db->getUpdateSQL($res,$good_s);
        if($sql){
            $this->db->exec($sql);
        }
    }

    function tobedit($data){
        $recs = $this->db->selectrow("SELECT spec_desc FROM sdb_goods WHERE goods_id=".$data['goods_id']);
        foreach(unserialize($recs['spec_desc']) as $t=>$p){
            foreach($p as $pb=>$pc){
                $ped[$pc['spec_value_id']]=$pb;
            }
        }
        $res = $this->db->exec("SELECT * FROM sdb_products WHERE product_id=".$data['products_id']);
        $ss = $data;
        if($data['spec_info']){
            $g_spec = explode(";",$data['spec_info']);
            foreach($g_spec as $key=>$value){
                $spec_value_id = substr($value,(strpos($value,':')+1));
                $spec_id = substr($value,0,strpos($value,':'));
                $temp = $this->db->selectrow("SELECT spec_value FROM sdb_spec_values WHERE spec_value_id =".$spec_value_id);
                $need['spec'][$spec_id] = $temp['spec_value'];
                $need['spec_value_id'][$spec_id] = $spec_value_id;
                $need['spec_private_value_id'][$spec_id] = $ped[$spec_value_id]?$ped[$spec_value_id]:intval(rand(123123123,910910910));
                $ptd_name[] = $temp['spec_value'];
            }
            $ss['pdt_desc'] = explode("、",$ptd_name);
            $ss['props'] = serialize($need);
        }

        $sql = $this->db->getUpdateSQL($res,$ss);
        if($sql){
            $this->db->exec($sql);
        }
        if($data['spec_info']){
            $this->convert_props($data);
        }
    }


    function update_products_info($data){
        $this->tobedit($data);
        $result['data_info'] = array('products_id'=>$data['products_id'],'last_modified'=>time(),'goods_id'=>$data['goods_id']);
        $this->api_response('true',false,$result);
    }


    function get_type_spec($data){
        $temp_g = $this->db->select("SELECT spec_id FROM sdb_goods_type_spec WHERE type_id=".$data['type_id']);
        if(!$temp_g){
             $this->api_response('fail','no data',$data);
        }
        foreach($temp_g as $ks=>$kv){
            $spec[] = $kv['spec_id'];
        }
        $result['data_info'] = $this->db->select('SELECT spec_id,spec_name FROM sdb_specification WHERE spec_id IN('.implode(",",$spec).')');
        foreach($result['data_info'] as $key=>$value){
            $result['data_info'][$key]['spec_value_detail'] = $this->db->select('SELECT spec_value_id,spec_value,p_order FROM sdb_spec_values WHERE spec_id ='.$value['spec_id']);
        }
        $this->api_response('true',false,$result);
    }

    function del_images_byid($data){
        $this->db->exec("DELETE FROM sdb_gimages WHERE goods_id =".$data['goods_id']);
        $params['thumbnail_pic']="";
        $params['small_pic']="";
        $params['big_pic']="";
        $params['image_default']="";
        $res = $this->db->exec("SELECT * FROM sdb_goods WHERE goods_id=".$data['goods_id']);
        $sql = $this->db->getUpdateSQL($res,$params);
        if($sql){
            $this->db->exec($sql);
        }
        $result['data_info'] = array('goods_id'=>$data['goods_id'],'last_modified'=>time());

        $this->api_response('true',false,$result);
    }




}