<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_pim_3_1_goods extends shop_api_object {
    /**
     * 添加商品类型
     *
     * @param array $data 
     *
     * @return id
     */
    function add_category($data){
        /*$data['datas']=array(
           'name'=>'商品类型名称',
           'alias'='商品别名',
           'brands'=>array('品牌id',.......),
           'is_physical'='是否为实体商品',
           'setting' = array(
            'use_brand' => '是否与品牌关联',
            'use_props' => '是否启用商品扩展属性',
            'use_params' => '是否启用商品参数表',
            'use_minfo' => '是否启用购物必填信息',
           ),
           'props'=>array(
               'name'=>array('扩展属性名',.......),
               'alias'=>('扩展属性别名',.......),
               'type'=>array('扩展属性前台表现类型',.......),
               'options'=>array('扩展属性可选值',.......),
               'show'=>array('是否显示',.......),
               'ordernum'=>array('扩展属性排序',.......)
           ),
           'spec'=>array(
               'id'=>array('规格id',.......),
               'type'=>array('规格类型',.......),
           ),
           'params'=>array(
               'group'=>array('参数组名称',.......),
               'name'=>array(array('参数名',.......),....),
               'alias'=>array(array('扩展属性别名',.......),....),
           ),
           'minfo'=>array(
               'label'=>array('用户购买必填项',.......),
               'type'=>array('输入类型',.......),
               'options'=>array('可选值',.......)
           ),
        );*/
        
        $data=json_decode($data['datas'],true);
        $data['schema_id']='custom';
        for($i=0;$i<count($data['spec']['id']);$i++){
           $data['spec_id'][]=$data['spec']['id'][$i];
           $data['spec_type'][]=$data['spec']['type'][$i];
        }
        if (empty($data['name'])){
            $this->api_response('fail','ture',$data,'类型名称不能为空');
        }
        
        if(count($data['props']['name']) > 30){
            $this->api_response('fail','ture',$data,'扩展属性不能超过30个');
        }
        
        $sql="select * from sdb_goods_type where name='".$data['name'].($data['type_id']?' AND type_id != '.$data['type_id'] : '');
        if($this->db->selectrow($sql)){
            $this->api_response('fail','ture',$data,'本类型名已存在。');
        }
        
        $objGtype = $this->system->loadModel('goods/gtype');
        $id=$objGtype->toSave($data);
        if($id){
            $this->api_response('true','',$id);
        }else{
            $this->api_response('fail','ture',$data,'sql错误');
        }
    }
    
    /**
     * 删除商品类型
     *
     * @param array $data 
     *
     * @return bool
     */
    function delete_category($data){
        /*$data['datas']=array(
           'type_id'=>array('商品类型id',.......),
           'del_type'=>'删除类型（0物理删除1逻辑删除）',
        );*/

        $data=json_decode($data['datas'],true);
        $objType = $this->system->loadModel('goods/gtype');
        $result='';
        foreach($data['type_id'] as $type_id){
            if(!$objType->checkDelete($type_id,$result)){
                if($result == 1){
                    $this->api_response('fail','ture',$data,'通用商品类型为系统默认类型，不能删除');
                }
                if($result == 2){
                    $this->api_response('fail','ture',$data,'类型下存在与之关联的商品，无法删除');
                }
            }
        }

        if($data['del_type']){
            $sql="UPDATE sdb_goods_type SET disabled = 'true' WHERE type_id IN (".implode(',',$data['type_id']).") ";
        }else{
            $sql="DELETE FROM sdb_goods_type WHERE type_id IN (".implode(',',$data['type_id']).") ";
        }
        $this->db->exec($sql);
        $this->api_response('true');
    }
    
    /**
     * 编辑商品类型
     *
     * @param array $data 
     *
     * @return bool
     */
    function update_category($data){
        /*$data['datas']=array(
           'type_id'=>'商品类型id',
           'name'=>'商品类型名称',
           'alias'='商品别名',
           'brands'=>array('品牌id',.......),
           'is_physical'='是否为实体商品',
           'setting' = array(
            'use_brand' => '是否与品牌关联',
            'use_props' => '是否启用商品扩展属性',
            'use_params' => '是否启用商品参数表',
            'use_minfo' => '是否启用购物必填信息',
           ),
           'props'=>array(
               'name'=>array('扩展属性名',.......),
               'alias'=>('扩展属性别名',.......),
               'type'=>array('扩展属性前台表现类型',.......),
               'options'=>array('扩展属性可选值',.......),
               'show'=>array('是否显示',.......),
               'ordernum'=>array('扩展属性排序',.......)
           ),
           'spec'=>array(
               'id'=>array('规格id',.......),
               'type'=>array('规格类型',.......),
           ),
           'params'=>array(
               'group'=>array('参数组名称',.......),
               'name'=>array(array('参数名',.......),....),
               'alias'=>array(array('扩展属性别名',.......),....),
           ),
           'minfo'=>array(
               'label'=>array('用户购买必填项',.......),
               'type'=>array('输入类型',.......),
               'options'=>array('可选值',.......)
           ),
        );*/
        $data=json_decode($data['datas'],true);
        $data['schema_id']='custom';
        for($i=0;$i<count($data['spec']['id']);$i++){
           $data['spec_id'][]=$data['spec']['id'][$i];
           $data['spec_type'][]=$data['spec']['type'][$i];
        }
        if (empty($data['name'])){
            $this->api_response('fail','ture',$data,'类型名称不能为空');
        }
        if(count($data['props']['name']) > 30){
            $this->api_response('fail','ture',$data,'扩展属性不能超过30个');
        }
        $sql="select * from sdb_goods_type where name='".$data['name'].($data['type_id']?' AND type_id != '.$data['type_id'] : '');
        if($this->db->selectrow($sql)){
            $this->api_response('fail','ture',$data,'本类型名已存在。');
        }
        $objGtype = $this->system->loadModel('goods/gtype');
        $id=$objGtype->toSave($data);
        if($id){
            $this->api_response('true');
        }else{
            $this->api_response('fail','ture',$data,'sql错误');
        }
    }
    
    /**
     * 获取商品类型列表
     *
     * @param array $data 
     *
     * @return 
     */
    function get_category(){
        $sql="SELECT type_id,name FROM sdb_goods_type WHERE disabled = 'false'";
        $arr = $this->db->select($sql);
        foreach($arr as $val){
            $val['cid']=$val['type_id'];
            unset($val['type_id']);
            /*$props=unserialize($val['props']);
            $i=0;
            foreach($props as $k => $v){
                $i++;
                $value[$i]['pid']=$k;
                $value[$i]['prop_name']=$v['name'];
                $value[$i]['value']=$v['options'];
                $value[$i]['sort_order']=$v['ordernum'];
                $value[$i]['Props_type']=$v['type'];
            }
            $val['props']=$value;
            
            $val['minfo']=unserialize($val['minfo']);
            $minfo=$val['minfo'];
            unset($val['minfo']);
            $i=0;
            foreach($minfo as $k=>$v){
                $i++;
                $val['minfo'][$i]['name']=$v['label'];
                $val['minfo'][$i]['type']=$v['type'];
                $val['minfo'][$i]['value']=$v['options'];
            }
            $val['params']=unserialize($val['params']);
            $params=$val['params'];
            unset($val['params']);
            $i=0;
            foreach($params as $k=>$v){
                $i++;
                $val['params'][$i]['group']=$k;
                $k=0;
                foreach($v as $name=>$alias){
                    $k++;
                    $val['params'][$i]['name'][$k]=$name;
                    $val['params'][$i]['alias'][$k]=$alias;
                }
                
                
            }
            $sql="SELECT b.brand_id,b.brand_name FROM sdb_type_brand as t INNER JOIN sdb_brand as b USING(brand_id) WHERE t.type_id ={$val['cid']}";
            $brand = $this->db->select($sql);
            foreach($brand as $k=>$v){
                $val['brand'][$k]['id']=$v['brand_id'];
                $val['brand'][$k]['name']=$v['brand_name'];
            }
            $sql="SELECT s.spec_id,s.spec_name FROM sdb_goods_type_spec AS t INNER JOIN sdb_specification AS s USING(spec_id) WHERE t.type_id ={$val['cid']}";
            $spec = $this->db->select($sql);
            foreach($spec as $k=>$v){
                $val['spec'][$k]['id']=$v['spec_id'];
                $val['spec'][$k]['name']=$v['spec_name'];
            }*/
            $return_data[]=$val;
        }
        $this->api_response('true','',$return_data);
    }
    
    /**
     * 获取商品类型（单个）
     *
     * @param array $data 
     *
     * @return 
     */
    function get_category_single($data){
        /*$data['type_id']=9;*/
        $sql="SELECT type_id,name,is_physical,props,setting,minfo,params FROM sdb_goods_type WHERE disabled = 'false' and type_id=".intval($data['type_id']);
        $val = $this->db->selectrow($sql);

            $val['cid']=$val['type_id'];
            unset($val['type_id']);
            $props=unserialize($val['props']);
            $i=0;
            foreach($props as $k => $v){
                $value[$i]['pid']=$k;
                $value[$i]['prop_name']=$v['name'];
                $value[$i]['value']=$v['options'];
                $value[$i]['sort_order']=$v['ordernum'];
                $value[$i]['type']=$v['type'];
                $i++;
            }
            $val['props']=$value;
            
            $val['minfo']=unserialize($val['minfo']);
            $minfo=$val['minfo'];
            unset($val['minfo']);
            $i=0;
            foreach($minfo as $k=>$v){
                $val['minfo'][$i]['name']=$v['label'];
                $val['minfo'][$i]['type']=$v['type'];
                $val['minfo'][$i]['value']=$v['options'];
                $i++;
            }
            $val['params']=unserialize($val['params']);
            $params=$val['params'];
            unset($val['params']);
            $i=0;
            foreach($params as $k=>$v){
                $val['params'][$i]['group']=$k;
                $k=0;
                foreach($v as $name=>$alias){
                    $val['params'][$i]['name'][$k]=$name;
                    $val['params'][$i]['alias'][$k]=$alias;
                    $k++;
                }
                $i++;
            }
            $sql="SELECT b.brand_id,b.brand_name FROM sdb_type_brand as t INNER JOIN sdb_brand as b USING(brand_id) WHERE t.type_id ={$val['cid']}";
            $brand = $this->db->select($sql);
            foreach($brand as $k=>$v){
                $val['brand'][$k]['id']=$v['brand_id'];
                $val['brand'][$k]['name']=$v['brand_name'];
            }
            $sql="SELECT s.spec_id,s.spec_name FROM sdb_goods_type_spec AS t INNER JOIN sdb_specification AS s USING(spec_id) WHERE t.type_id ={$val['cid']}";
            $spec = $this->db->select($sql);
            foreach($spec as $k=>$v){
                $val['spec'][$k]['id']=$v['spec_id'];
                $val['spec'][$k]['name']=$v['spec_name'];
            }
        unset($val['setting']);
        unset($val['is_physical']);
        $this->api_response('true','',$val);
    }
    
    /**
     * 添加商品分类
     *
     * @param array $data 
     *
     * @return id
     */
    function add_classification($data){
        /*
            $data['datas']['cat_name']=123;   //名称
            $data['datas']['p_order']=4;      //排序
            $data['datas']['keywords']=2;     //页面关键词
            $data['datas']['parent_id']=56;   //父ID
            $data['datas']['type_id']=2;      //通用商品类型
            $data['datas']['desc']=54345;     //分类说明
            $data['datas']['description']=4;  //页面描述
        */
        $data=json_decode($data['datas'],true);
        $data['last_modify'] = time();
        $data['version_id'] = $this->system->getConf('system.curr_sync_version_id');
        $data['parent_id'] = intval($data['parent_id']);
        $data['addon']['meta']['keywords'] = htmlspecialchars($data['keywords']);
        $data['addon']['meta']['description'] = htmlspecialchars($data['description']);
        $parent_id = $data['parent_id'];
        $path=array();
        while($parent_id){
            if($data['cat_id'] && $data['cat_id'] == $parent_id){
                $this->api_response('fail','ture',$data,'保存失败：上级类别不能选择当前分类或其子分类');
            }
            array_unshift($path, $parent_id);
            $row = $this->db->selectrow('SELECT parent_id, cat_path, p_order FROM sdb_goods_cat WHERE cat_id='.intval($parent_id));
            $parent_id = $row['parent_id'];
        }
        $data['cat_path'] = implode(',',$path).',';
        $rs = $this->db->exec('SELECT * FROM sdb_goods_cat WHERE 0=1');
        $sql = $this->db->getInsertSQL($rs,$data);
        if(!$sql || $this->db->exec($sql)){
            $id=$this->db->lastInsertId();
            $objCat = $this->system->loadModel('goods/productCat');
            $objCat->updateChildCount($data['parent_id']);
            $objCat->cat2json();
            $this->api_response('true','',$id);
        }else{
            $this->api_response('fail','ture',$data,'sql错误');
        }
        
    }
    
    /**
     * 删除商品分类
     *
     * @param array $data id
     *
     * @return bool
     */
    function delete_classification($data){
        //$data['cat_id']=67;  //分类id
        $aCats = $this->db->select('SELECT * FROM sdb_goods_cat WHERE parent_id = '.intval($data['cat_id']));
        if(count($aCats) > 0){
            $this->api_response('fail','ture',$data,'删除失败：本分类下面还有子分类');
        }
        $aGoods = $this->db->select('SELECT goods_id FROM sdb_goods WHERE cat_id = '.intval($data['cat_id']));
        if(count($aGoods) > 0){
            $this->api_response('fail','ture',$data,'删除失败：本分类下面还有商品');
        }
        $row = $this->db->selectrow('SELECT parent_id FROM sdb_goods_cat WHERE cat_id='.intval($data['cat_id']));
        $parent_id = $row['parent_id'];

        $this->db->exec('DELETE FROM sdb_goods_cat WHERE cat_id='.intval($data['cat_id']));
        $objCat = $this->system->loadModel('goods/productCat');
        $objCat -> updateChildCount($parent_id);
        $objCat -> cat2json();
        $this->api_response('true');
    }
    
    /**
     * 更新商品分类
     *
     * @param array $data
     *
     * @return bool
     */
    function update_classification($data){
        /*
            $data['datas']['cat_id']='24';      //分类id、
            $data['datas']['cat_name']='aaa';   //名称、
            $data['datas']['p_order']=4;      //排序、
            $data['datas']['keywords']=2;     //页面关键词
            $data['datas']['parent_id']=56;   //父ID
            $data['datas']['type_id']=2;      //通用商品类型
            $data['datas']['desc']=54345;     //分类说明
            $data['datas']['description']=4;  //页面描述
        */
        $data=json_decode($data['datas'],true);
        $data['last_modify'] = time();
        $data['version_id'] = $this->system->getConf('system.curr_sync_version_id');

        $data['parent_id'] = intval($data['parent_id']);
        $data['addon']['meta']['keywords'] = htmlspecialchars($data['keywords']);
        $data['addon']['meta']['description'] = htmlspecialchars($data['description']);
        $parent_id = $data['parent_id'];
        $path=array();
        while($parent_id){
            if($data['cat_id'] && $data['cat_id'] == $parent_id){
               $this->api_response('fail','ture',$data,'保存失败：上级类别不能选择当前分类或其子分类');
            }
            array_unshift($path, $parent_id);
            $row = $this->db->selectrow('SELECT parent_id, cat_path, p_order FROM sdb_goods_cat WHERE cat_id='.intval($parent_id));
            $parent_id = $row['parent_id'];
        }
        $data['cat_path'] = implode(',',$path).',';

        $sDefine=$this->db->selectrow('SELECT parent_id FROM sdb_goods_cat WHERE cat_id='.intval($data['cat_id']));
        $rs = $this->db->exec('SELECT * FROM sdb_goods_cat WHERE cat_id='.intval($data['cat_id']));
        $sql = $this->db->getUpdateSQL($rs,$data);
        if(!$sql || $this->db->exec($sql)){
            $objCat = $this->system->loadModel('goods/productCat');
            if($sDefine['parent_id']!=$data['parent_id']){
                $objCat->updatePath($data['cat_id'],$data['cat_path']);
                $objCat->updateChildCount($sDefine['parent_id']);
                $objCat->updateChildCount($data['parent_id']);
            }
            $objCat -> cat2json();
            $this->api_response('true');
        }else{
            $this->api_response('fail','ture',$data,'sql错误');
        }
    }
    
    /**
     * 获取商品分类列表
     *
     * @param array $data 
     *
     * @return 
     */
    
    function get_classification(){

        $objCat = $this->system->loadModel('goods/productCat');
        $file = MEDIA_DIR.'/goods_cat.data';
        $contents = file_get_contents($file);
        $arr=json_decode($contents,true);
        foreach($arr as $val){
            $value['cid']=$val['cat_id'];
            $value['name']=$val['cat_name'];
            $value['parent_cid']=$val['pid'];
            $return_data[]=$value;
        }
        $this->api_response('true','',$return_data);

    }
    
/**
     * 获取商品分类（单个）
     *
     * @param array $data 
     *
     * @return 
     */
    
    function get_classification_single($data){
        /*$data['cat_id']=4;*/
        $objCat = $this->system->loadModel('goods/productCat');
        $file = MEDIA_DIR.'/goods_cat.data';
        $contents = file_get_contents($file);
        $arr=json_decode($contents,true);
        foreach($arr as $val){
            $value['cid']=$val['cat_id'];
            $value['name']=$val['cat_name'];
            $value['parent_cid']=$val['pid'];
            $return_data[]=$value;
        }
        $return_data=$return_data[$data['cat_id']];
        $this->api_response('true','',$return_data);

    }
    
    /**
     * 添加品牌
     *
     * @param array $data
     *
     * @return id
     */
    function add_brand($data){
        /*
         $data['datas']=array(
             'brand_name'=>'品牌名称',
             'ordernum'=>'排序',
             'brand_url'=>'url',
             'brand_logo'=>'图片地址',
             'gtype'=>array('商品类型关联',......),
             'brand_desc'=>'详细说明'
         );
         */
        $data=json_decode($data['datas'],true);
        $data['brand_name'] = trim($data['brand_name']);
        $data['last_modify'] = time();
        $data['version_id'] = $this->system->getConf('system.curr_sync_version_id');

        $aRs = $this->db->query("SELECT * FROM sdb_brand WHERE 0=1");
        $sSql = $this->db->getInsertSql($aRs,$data);
        $this->db->exec($sSql);
        $data['brand_id'] = $this->db->lastInsertId();

       /* if($data['gtype']){
            foreach($data['gtype'] as $typeId){
                $str.='('.$typeId.','.$data['brand_id'].','.$data['last_modify'].','.$data['version_id'].'),';
            }
            $str=substr($str,0,-1);
            $sql="INSERT INTO sdb_brand (type_id,brand_id,last_modify,version_id) value ".$str;
            $this->db->query($sql);
        }*/
        
        $brand = $this->system->loadModel('goods/brand');
        $brand->brand2json();
        $this->api_response('true','',$data['brand_id']);
    }
    
    /**
     * 删除品牌
     *
     * @param array $data
     *
     * @return bool
     */
    function delete_brand($data){
        /*$data['datas']=array(
           'brand_id'=>array('品牌类型id',......),
           'del_type'=>'删除品牌（0物理删除1逻辑删除）',
        );*/
        $data=json_decode($data['datas'],true);
        $brand = $this->system->loadModel('goods/brand');
        $resC='';
        foreach ($data['brand_id'] as $brand_id){
            if(!$brand -> toSelectBrandCount($brand_id, $resC)){
                if($resC == 1){ 
                     $this->api_response('fail','ture',$data,'品牌下有商品，无法删除。');
                }
                if($resC == 2){
                    $this->api_response('fail','ture',$data,'品牌关联产品线，无法删除。');
                }
            }
        }

        if($data['del_type']){
            $sql="UPDATE sdb_brand SET disabled = 'true' WHERE brand_id IN (".implode(',',$data['brand_id']).") ";
        }else{
            $sql="DELETE FROM sdb_brand WHERE brand_id IN (".implode(',',$data['brand_id']).") ";
        }
        $this->db->exec($sql);
        $this->api_response('true');
    }
    
    /**
     * 获取品牌列表
     *
     * @param array $data
     *
     * @return 
     */
    function get_brand(){
    /*$data=array(
        '0'=>array(
             'brand_id'=>'品牌id',
             'brand_name'=>'品牌名称',
             'brand_logo'=>'图片地址',
             'brand_url'=>'品牌网址',
             'brand_desc'=>'详细说明'
         ),
        '1'=>array(
             'brand_id'=>'品牌id',
             'brand_name'=>'品牌名称',
             'brand_logo'=>'图片地址',
             'brand_url'=>'品牌网址',
             'brand_desc'=>'详细说明'
         ),
         .........
       );*/
        $sql="SELECT brand_id,brand_name,brand_logo,brand_url,brand_desc FROM sdb_brand WHERE disabled = 'false'";
        $return_data = $this->db->select($sql);
        $this->api_response('true','',$return_data);
    }
    
    /**
     * 获取品牌（单个）
     *
     * @param array $data
     *
     * @return 
     */
    function get_brand_single($data){
        /*$data['brand_id']=2;*/
        $sql="SELECT brand_id,brand_name,brand_logo,brand_url,brand_desc FROM sdb_brand WHERE disabled = 'false' and brand_id =".intval($data['brand_id']);
        $return_data = $this->db->selectrow($sql);
        $this->api_response('true','',$return_data);
    }
    
    /**
     * 添加规格
     *
     * @param array $data
     *
     * @return id
     */
    function add_spec($data){
        /*
           $data['datas']=array(
               'spec_name'=>'规格名',
               'spec_memo'=>'规格备注',
               'spec_show_type'=>'显示方式',
               'spec_type'=>'显示类型',
               'spec_value'=>array('规格值名称',......),
               'spec_image'=>array('规格值图片',......)
           );
        */
        $data=json_decode($data['datas'],true);
        $data['last_modify'] = time();
       // $data['version_id'] = $this->system->getConf('system.curr_sync_version_id');
        $data['spec_name'] = trim($data['spec_name']);
        $data['spec_memo'] = trim($data['spec_memo']);
        if($data['spec_name']==""){
            $this->api_response('fail','ture',$data,'规格名称不能为空');
        }
        foreach($data['spec_value'] as $k => $v){
            if($v==""){
                $this->api_response('fail','ture',$data,'规格值名称不能为空');
            }
        }

        $aRs = $this->db->exec("SELECT * FROM sdb_specification WHERE 0=1");
        $sSql = $this->db->getInsertSql($aRs,$data);
        if($sSql && !$this->db->exec($sSql)){
            $this->api_response('fail','ture',$data,'保存规格数据库出错');
        }else{
            $data['spec_id'] = $this->db->lastInsertId();
        }
        $objSpec = $this->system->loadModel('goods/specification');
        $objSpec->saveValue($data['spec_id'],$data['spec_value'],$data['val'],$data['spec_image']);
        $this->api_response('true','',$data['spec_id']);
    }
    
    /**
     * 删除规格
     *
     * @param array $data
     *
     * @return bool
     * 
     */
    function delete_spec($data){
        /*
          $data=array(
              'spec_id'=>array('规格ID',......),
          );
         */

        $data['spec_id']=json_decode($data['spec_id'],true);

        $objSpec = $this->system->loadModel('goods/specification');
        if(!$objSpec->toSelectForType($data['spec_id'])){
            $this->api_response('fail','ture',$data,'该规格已被现有商品类型使用,不能删除,请先取消类型绑定');
        };
        if(!$objSpec->toSelectForGoods($data['spec_id'])){
            $this->api_response('fail','ture',$data,'该规格已被现有商品使用,不能删除,请先取消相关商品规格');
        };
        $objSpec->removeSpecValue($data['spec_id']);
        $this->api_response('true');
    }
    
    /**
     * 修改规格
     *
     * @param array $data
     *
     * @return bool
     */
    function update_spec($data){
        /*
           $data['datas']=array(
               'spec_id'=>'规格id',
               'spec_name'=>'规格名',
               'spec_memo'=>'规格备注',
               'spec_show_type'=>'显示方式',
               'spec_type'=>'显示类型',
               'spec_value'=>array('规格值名称',......),
               'spec_image'=>array('规格值图片',......),
               'val'=>array('规格值id',......),
           );
        */
        $data=json_decode($data['datas'],true);
        $data['last_modify'] = time();
        //$data['version_id'] = $this->system->getConf('system.curr_sync_version_id');
        $data['spec_name'] = trim($data['spec_name']);
        $data['spec_memo'] = trim($data['spec_memo']);
        if($data['spec_name']==""){
            $this->api_response('fail','ture',$data,'规格名称不能为空');
        }
        foreach($data['spec_value'] as $k => $v){
            if($v==""){
                $this->api_response('fail','ture',$data,'规格值名称不能为空');
            }
        }
        $objSpec = $this->system->loadModel('goods/specification');
        $tdata = $objSpec->getFieldById($data['spec_id'],array('spec_type'));
        //判断是否做过类型切换
        if($tdata['spec_type'] !=$data['spec_type']){
            $this->db->exec("DELETE FROM sdb_spec_values WHERE spec_id =".intval($data['spec_id']));
        }
        $aRs = $this->db->exec("SELECT * FROM sdb_specification WHERE spec_id=".intval($data['spec_id']));
        $sSql = $this->db->getUpdateSql($aRs,$data);
        if($sSql && !$this->db->exec($sSql)){
            $this->api_response('fail','ture',$data,'保存规格数据库出错');
        }
        
        $objSpec->saveValue($data['spec_id'],$data['spec_value'],$data['val'],$data['spec_image']);
        $this->api_response('true');
    }
    
    /**
     * 获取规格列表
     *
     * @param array $data
     *
     * @return
     */
    function get_spec(){
        $sql="SELECT spec_id,spec_name FROM sdb_specification WHERE disabled = 'false'";
        $return_data = $this->db->select($sql);
        $this->api_response('true','',$return_data);
    }
    
    /**
     * 获取规格（单个）
     *
     * @param array $data
     *
     * @return
     */
    function get_spec_single($data){
        /*$data['spec_id']=2;*/
        $sql="SELECT spec_name FROM sdb_specification WHERE disabled = 'false' and spec_id=".intval($data['spec_id']);
        $spec = $this->db->selectrow($sql);
        $return_data['spec_id']=$data['spec_id'];
        $return_data['spec_name']=$spec['spec_name'];
        $sql="SELECT spec_value_id,spec_value,spec_image FROM sdb_spec_values WHERE spec_id ={$data['spec_id']}";
        $spec_value = $this->db->select($sql);
        
        foreach($spec_value as $v){
            $v['spec_value_name']=$v['spec_value'];
            if($v['spec_image']){
                $image=explode('|',$v['spec_image']);
                $v['spec_value_image']=$this->system->base_url().$image[0];
            }
            unset($v['spec_value']);
            unset($v['spec_image']);
            $return_data['spec_value'][]=$v;
        }
        $this->api_response('true','',$return_data);
    }
    
    
    
    /**
     * 添加规格值
     *
     * @param array $data
     *
     * @return id
     */
    function add_spec_value($data){
        $this->update_spec($data);
    }
    
    /**
     * 删除规格值
     *
     * @param array $data
     *
     * @return id
     */
    function delete_spec_value($data){
        $this->update_spec($data);
    }
    
    /**
     * 编辑规格值
     *
     * @param array $data
     *
     * @return bool
     */
    function update_spec_value($data){
        $this->update_spec($data);
    }
    
    /**
     * 获取规格值列表
     *
     * @param array $data
     *
     * @return 
     */
    function get_spec_value($data){
        /*$data['spec_id']=1;*/
        $sql="SELECT spec_value_id,spec_value,spec_image FROM sdb_spec_values WHERE spec_id ={$data['spec_id']}";
        $spec_value = $this->db->select($sql);
        foreach($spec_value as $v){
            $v['spec_value_name']=$v['spec_value'];
            if($v['spec_image']){
                $image=explode('|',$v['spec_image']);
                $v['spec_value_image']=$this->system->base_url().$image[0];
            }
            unset($v['spec_value']);
            unset($v['spec_image']);
            $return_data[]=$v;
        }
        $this->api_response('true','',$return_data);
    }
    /**
     * 获取规格值列表(单个)
     *
     * @param array $data
     *
     * @return 
     */
    function get_spec_value_single($data){
        /*$data['spec_value_id']=2;*/
        $sql="SELECT spec_value_id,spec_value,spec_image FROM sdb_spec_values WHERE spec_value_id = ".intval($data['spec_value_id']);
        $spec_value = $this->db->selectrow($sql);
        $return_data['spec_value_id']=$data['spec_value_id'];
        $return_data['spec_value_name']=$spec_value['spec_value'];
        if($spec_value['spec_image']){
            $image=explode('|',$spec_value['spec_image']);
            $return_data['spec_value_image']=$this->system->base_url().$image[0];
        }
        $this->api_response('true','',$return_data);
    }
    
    /**
     * 添加属性
     *
     * @param array $data
     *
     * @return bool
     */
    function add_props($data){
        /*$data['datas']=array(
           'type_id'=>'商品类型ID',
           'props'=>array(
               'name'=>array('扩展属性名',.......),
               'alias'=>('扩展属性别名',.......),
               'type'=>array('扩展属性前台表现类型',.......),
               'options'=>array('扩展属性可选值',.......),
               'show'=>array('是否显示',.......),
               'ordernum'=>array('扩展属性排序',.......)
            )
          );
        */
        $data=json_decode($data['datas'],true);
        if(count($data['props']['name']) > 30){
            $this->api_response('fail','ture',$data,'扩展属性不能超过30个');
        }
        $sql="select * from sdb_goods_type where type_id=".$data['type_id'];
        if(!$this->db->selectrow($sql)){
            $this->api_response('fail','ture',$data,'本类型不存在。');
        }
        
        if(is_array($data['props']['name'])){
            $inputLoop = 21;
            $selectLoop = 1;
            $props = array();

            foreach($data['props']['name'] as $k=>$v){
                if($data['props']['name'][$k]){
                    if($data['props']['type'][$k] <= 1){

                        //陈绪2010/12/17
                        $props[$inputLoop] = array('name'=>trim($v),'alias'=>$data['props']['alias'][$k],'type'=>'input','search'=>($data['props']['type'][$k]?'disabled':'input'),'show'=>intval($data['props']['show'][$k]),'ordernum'=>$data['props']['ordernum'][$k],'p_col'=>'p_'.$inputLoop);
                        $inputLoop++;
                    }else{
                        $t_options = preg_split("/[,]+/",trim($data['props']['options'][$k]));
                        $reg = '/{(.*)}/U';
                        foreach($t_options as $k1=>$v1){
                            if(!empty($v1)){
                                preg_match_all($reg,$v1,$o);
                                $t_options[$k1] = $o[1][0]?str_replace($o[0][0],'',$t_options[$k1]):$t_options[$k1];
                                $t_optionAlias[$k1] = $o[1][0]?$o[1][0]:'';
                            }else{
                                unset($t_options[$k1]);
                                unset($t_optionAlias[$k1]);
                            }
                        }
                        switch($data['props']['type'][$k]){
                            case 2:
                            $search = 'nav';
                            break;
                            case 3:
                            $search = 'select';
                            break;
                            case 4:
                            $search = 'disabled';
                            break;
                        }
                        $props[$selectLoop] = array('name'=>trim($v),'alias'=>$data['props']['alias'][$k],'type'=>'select','search'=>$search,'options'=>$t_options,'optionAlias'=>$t_optionAlias,'show'=>intval($data['props']['show'][$k]),'ordernum'=>$data['props']['ordernum'][$k],'p_col'=>'p_'.$selectLoop);
                        $selectLoop++;
                    }
                }
            }
            $data['props'] = &$props;
        }
        
        $rs = $this->db->query('SELECT * FROM sdb_goods_type where type_id='.intval($data['type_id']));
        $sql = $this->db->getUpdateSQL($rs,$data);
        if(!$sql || $this->db->exec($sql)){
             $this->api_response('true','',$id);
        }else{
            $this->api_response('fail','ture',$data,'sql错误');
        }
    }
    
    /**
     * 删除属性
     *
     * @param array $data
     *
     * @return bool
     */
    function delete_props($data){
        $this->add_props($data);
    }
    
    /**
     * 更新属性
     *
     * @param array $data
     *
     * @return bool
     */
    function update_props($data){
        $this->add_props($data);
    }
    
    /**
     * 增加属性值
     *
     * @param array $data
     *
     * @return bool
     */
    function add_props_value($data){
        $this->add_props($data);
    }
    
    /**
     * 删除属性值
     *
     * @param array $data
     *
     * @return bool
     */
    function delete_props_value($data){
        $this->add_props($data);
    }
    
    /**
     * 更新属性值
     *
     * @param array $data
     *
     * @return bool
     */
    function update_props_value($data){
        $this->add_props($data);
    }

     //新增商品
    function add_product($data){
        //error_log(print_r($data,true),3,HOME_DIR.'/logs/lscadd.log');
        $data['goods']=json_decode($data['goods'],true);
        //$data['keywords']=json_decode($data['keywords'],true);
        $goods=$data['goods'];
        $data['keywords']=$goods['keywords'];
        if(!isset($goods['bn'])){
            $bnsql = "select bn from sdb_goods where goods_id=".$goods['goods_id'];
            $bnrow = $this->db->selectrow($bnsql);
            $goods['bn']=$bnrow['bn'];
        }
        $props=$this->matrix_propsToB2c_props($goods['props']);
        $input_pids = explode(',',$goods['input_pids']);
        $input_str = json_decode($goods['input_str'],true);
        foreach($input_pids as $k=>$v){
            $props[$v] = $input_str[$k];
        }
        foreach($props as $k=>$v){
                $goods['p_'.$k] = $v;
        }
        //处理规格属性

        if(isset($goods['sku_properties'])){
            $sku_props = explode(',',$goods['sku_properties']);
            $goods['sku_properties'] = implode(';',$sku_props);
            if(isset($goods['sku_properties'])){
                $stores=explode(',',$data['sku_quantities']);
                $sku_bns=explode(',',$data['sku_bns']);
                $price=explode(',',$data['sku_prices']);
                $cost=explode(',',$data['sku_costs']);
                $weight=explode(',',$data['sku_weights']);
                if(is_array($goods['sku_properties'])){//add_sku流程
                       $sku_properties=$goods['sku_properties'];
                }else{//add_product流程
                    $sku_properties = $this->matrix_specToB2c_spec($goods['sku_properties']);
                }
            }else{
                $sql = "select spec_desc from sdb_goods where goods_id=".$goods['goods_id'];
                $row = $this->db->selectrow($sql);
                $spec_desc = unserialize($row['spec_desc']);
                foreach($spec_desc as $k=>$v){
                    $i=0;
                    foreach($v as $k2=>$v2){
                        $properties[$k][$i]=$v2['spec_value_id'];
                        $i++;
                    }
                }
                $sql2 = "select store,price,weight,bn,cost from sdb_products where goods_id=".$goods['goods_id'];
                $rs = $this->db->select($sql2);
                foreach($rs as $pk=>$pv){
                    $stores.=$pv['store'].',';
                    $sku_bns.=$pv['bn'].',';
                    $price.=$pv['price'].',';
                    $cost.=$pv['cost'].',';
                    $weight.=$pv['weight'].',';
                }
                $stores=explode(',',trim($stores,','));
                $sku_bns=explode(',',trim($sku_bns,','));
                $price=explode(',',trim($price,','));
                $cost=explode(',',trim($cost,','));
                $weight=explode(',',trim($weight,','));
                $sku_properties=$properties;
             }

             foreach($sku_properties as $k=>$v){
                foreach($v as $k2=>$v2){
                    $spec_value = $this->getSpecvalueByids($v2);
                    $spec_info = $this->getSpecInfo($k);
                    $pSpecId = $v2;
                    $spec_desc[$k][$pSpecId]['spec_value'] = $spec_value;
                    $spec_desc[$k][$pSpecId]['spec_type'] = $spec_info['spec_type'];
                    $spec_desc[$k][$pSpecId]['spec_value_id'] = $v2;
                    $spec_desc[$k][$pSpecId]['spec_image'] = '';
                    $spec_desc[$k][$pSpecId]['spec_goods_images'] = '';
        
                    $data['vars'][$k] = $spec_info['spec_name'];
                    $data['bn'][$k2] = $sku_bns[$k2];
                    $data['val'][$k][$k2] = $spec_value;
                    $data['pSpecId'][$k][$k2] = $pSpecId;
                    $data['specVId'][$k][$k2] = $v2;
                    $data['store'][$k2] = $stores[$k2];
                    $data['price'][$k2] = $price[$k2];
                    $data['cost'][$k2] = $cost[$k2];
                    $data['weight'][$k2] = $weight[$k2];
                }
            }
        }
        $goods['spec_desc']=$spec_desc;
//       $goods['spec_desc'] = urldecode( $goods['spec_desc'] );
//        $goods['spec_desc'] = addslashes_array($goods['spec_desc']);
//        $goods['params'] = stripslashes_array($goods['params']);
        //$objGoodsStatus = $this->system->loadModel('trading/goodsstatus');
        if($goods['goods_id']){
            //$objGoodsStatus->checkStart($goods['goods_id'],array('goods_marketable','product_store','gimage_update','goods_update','product_update','goods_lv_price_update'));
            $is_new_product = false;
        }else{
            $is_new_product = true;
        }
        
        $udfimg = $goods['udfimg'];
//        unset($goods['udfimg']);
//        $goods['adjunct'] = $data['adjunct'];
        
        //单批方案---------------------
//        if($data['ws_policy']){
//            if(is_array($data['num'])&&!empty($data['num'])){
//                $aParams = array();
//
//                foreach($data['num'] as $k => $v){
//                    $nDiscount = ($data['distype']==1)?$data['discount'][$k]/100:$data['discount'][$k];
//                    $aParams[] = array('num' => intval($v),
//                                                        'distype' => $data['distype'],
//                                                        'discount' => $nDiscount);
//                }
//                $_nums = array_item($aParams, 'num');
//                array_multisort($_nums, SORT_ASC, $aParams);
//                if($data['limit_quantity']){
//                    $limit_quantity = array('num'=>intval($data['limit_quantity']),'distype'=>1,'discount'=>1);
//                }else{
//                    $limit_quantity = array('num'=>1,'distype'=>1,'discount'=>1);
//                }
//                array_unshift($aParams,$limit_quantity);
//                $goods['wss_params'] = $aParams;
//            }else{
//                if($data['limit_quantity'] && $data['limit_quantity'] > 1){
//                    $goods['wss_params'][] = array('num'=>intval($data['limit_quantity']),'distype'=>1,'discount'=>1);
//                }else{
//                    $goods['wss_params'] = '';
//                }
//            }
//
//            $goods['ws_policy'] = $data['ws_policy'];
//        }else{
//            $goods['ws_policy'] = '01';  //非单可混
//        }
            if(isset($goods['sku_properties'])&&strlen($goods['sku_properties'])==0){
                unset($spec_desc);
            }
            if(count($spec_desc)>0){    //开启规格 多货品
            foreach($data['vars'] as $vark=>$varv){
                $goods['spec'][$vark] = $varv;
            }
            $goods['spec'] = serialize($goods['spec']);
            $sameProFlag = array();
            foreach($data['price'] as $k => $price){    //设置销售多货品销售价等价格
                $goods['price'] = $goods['price']?min($price,$goods['price']):$price;    //取最小商品价格
                $goods['cost'] = $goods['cost']?min($data['cost'][$k],$goods['cost']):$data['cost'][$k];
                $goods['weight'] = $goods['weight']?min($data['weight'][$k],$goods['weight']):$data['weight'][$k];

                $products[$k]['price'] = $price;
                $products[$k]['bn'] = $data['bn'][$k];
                $products[$k]['store'] = (trim($data['store'][$k]) === '' ? '' : intval($data['store'][$k]));
                $products[$k]['alert'] = $data['alert'][$k];
                $products[$k]['cost'] = $data['cost'][$k];
                $products[$k]['weight'] = $data['weight'][$k];
                $products[$k]['goodsspace'] = $data['goodsspace'][$k];//新增货位
                $newSpecI = 0;
                $proSpecFlag = '';
                foreach($data['vars'] as $i=>$v){

                    $products[$k]['props']['spec'][$i] = trim($data['val'][$i][$k]);        //array('规格(颜色)序号'=>'规格值(红色)')
                    $products[$k]['props']['spec_private_value_id'][$i] = trim($data['pSpecId'][$i][$k]);
                    $products[$k]['props']['spec_value_id'][$i] = trim($data['specVId'][$i][$k]);
                    if( !$products[$k]['props']['spec'][$i] ){
                        $this->api_response('fail','请为所有货品定义规格值 ');
                    }
                    $proSpecFlag .= $products[$k]['props']['spec_private_value_id'][$i].'_';
                }
                if( in_array( $proSpecFlag, $sameProFlag ) ){
                    $this->api_response('fail','不能添加相同规格货品 ');
                }
                $sameProFlag[$k] = $proSpecFlag;
                reset($proSpecFlag);

                reset($data['vars'],$data['pSpecId']);
                $products[$k]['pdt_desc'] = implode('、', $products[$k]['props']['spec']);    //物品描述
                $products[$k]['pdt_desc'] = addslashes_array($products[$k]['pdt_desc']);
                foreach($data['idata'] as $i=>$v){
                    $products[$k]['props']['idata'][$i] = $v[$k];
                }

                //设置会员价格
                if(is_array($data['mprice']))
                    foreach($data['mprice'] as $levelid => $rows){
                        $products[$k]['mprice'][$levelid] = floatval($rows[$k]);
                    }
            }
            unset( $sameProFlag );
            $goods['products'] = &$products;
        }else{
            $goods['props']['idata'] = $data['idata'];
        }
        

        
        $objGoods = $this->system->loadModel('trading/goods');
        foreach($products as $k => $p){
            if(empty($p['bn'])) continue;
            if($objGoods->checkProductBn($p['bn'], $goods['goods_id'])){
                $this->api_response('fail','货号重复，请检查 ');
            }
            $aBn[] = $p['bn'];
        }
        if(!empty($goods['bn'])){
            if($objGoods->checkProductBn($goods['bn'], $goods['goods_id'])){
                $this->api_response('fail','货号重复，请检查 ');
            }
        }
        if(count($aBn) > count(array_unique($aBn))){
            $this->api_response('fail','货号重复，请检查 ');
        }
        if(!$goods['type_id']){
            $objCat = $this->system->loadModel('goods/productCat');
            $aCat = $objCat->getFieldById($goods['cat_id'], array('type_id'));
            $goods['type_id'] = $aCat['type_id'];
        }
        /*判断图片有无更新*/
//        if ($goods['goods_id']){
//            $goods['imgUPdate'] = $objGoods->check_ImgUpdate($goods['goods_id'],$data['goods']['image_file']);
//        }
        if((!isset($goods['name']))&&isset($goods['goods_id'])){
            $getnamesql = "select name,cat_id,type_id,brand_id from sdb_goods where goods_id=".$goods['goods_id'];
            $row = $this->db->selectrow($getnamesql);
            $goods['name']=$row['name'];
            $goods['cat_id']=$row['cat_id'];
            $goods['type_id']=$row['type_id'];
            $goods['brand_id']=$row['brand_id'];
        }
        
        if(!($gid = $objGoods->save($goods))){
            $this->api_response('fail','保存失败，请重试 ');
        }
        if(isset($data['keywords'])){
            $keywords = array();
            foreach( $objGoods->getKeywords($gid) as $keywordvalue )
                $keywords[] = $keywordvalue['keyword'];
            $keyword = implode('|', $keywords);
    
            if($keyword != $data['keywords']['keyword']){
                $objGoods->deleteKeywords($gid);
                if( $data['keywords']['keyword'] )
                    $objGoods->addKeywords($gid, explode('|',$data['keywords']['keyword']) );
            }
        }
        //处理商品图片
        //$gimage= &$this->system->loadModel('goods/gimage');
        //$gimage->saveImage($goods['goods_id'], $goods['db_thumbnail_pic'], $data['image_default'], $image_file, $udfimg, $_FILES);
        if(isset($goods['image_url'])){
            $img_result=$this->saveImage($goods);
        }
        //相关商品
//        foreach($data['linkid'] as $k => $id){
//            if($goods['goods_id']==$id){
//                $this->api_response('fail','不能相关自身商品,请检查 ');
//            }
//            $aLink[] = array('goods_1' => $goods['goods_id'], 'goods_2' => $id, 'manual' => $data['linktype'][$id], 'rate' => 100);
//        }
//        $objProduct = $this->system->loadModel('goods/products');
//        $objProduct->toInsertLink($goods['goods_id'], $aLink);
        
        //处理TAG
//        $objTag = $this->system->loadModel('system/tag');
//        $objTag->removeObjTag($goods['goods_id']);
//        foreach(space_split(stripslashes($data['tags'])) as $tagName){
//            $tagName = trim($tagName);
//            if($tagName){
//                if(!($tagid = $objTag->getTagByName('goods', $tagName))){
//                    $tagid = $objTag->newTag($tagName, 'goods');
//                }
//                $objTag->addTag($tagid, $gid);
//            }
//        }

        //by shiy 商品op_status sync_status version_id
//        if(!$is_new_product){
//            $objGoodsStatus->checkEnd();
//        }else{
//            $objGoodsStatus->jumpCheck($gid,'new_goods');
//        }
        $returndata = $this->getReturnData($goods['goods_id']);
        $this->api_response('true',false,$returndata);
        
    }
    
    //删除商品
    function delete_product($data){
        //$data['goods_id'][0]='83';
        //if(!$data['goods_id']){
            // 原方法按搜索条件进行处理时会把所有的商品ID取出来, 现在用model的父类方法来处理 2009-12-15 15:15 wubin
        //    $post = $this->model->getPrimaryKeyByFilter($data);
        //    $aId = $post['goods_id'];
       // }else{
            if(is_array($data['goods_id'])){
                $aId = $data['goods_id'];
            }else{
                $aId[0]=$data['goods_id'];;
            }
       // }
       //商品状态删除
        $objGoodsStatus = $this->system->loadModel('trading/goodsstatus');
        $objGoodsStatus->jumpCheck($aId,'goods_delete');
        if($data['disable']){
            $this->db->exec('UPDATE sdb_goods SET disabled ="true"  WHERE goods_id IN('.implode(',',$aId).')');
            $objProduct = $this->system->loadModel('goods/products');   
            $objProduct->setDisabled($aId, 'true');
        }else{
            $objGoods = $this->system->loadModel('trading/goods');
            foreach($aId as $id){
                   $objGoods->toRemove($id);
            }
        }
        $this->api_response('true',false,'');
    }
    
    //编辑商品
    function update_product($data){
        $data['goods']=json_decode($data['goods'],true);
//$data['keywords']=json_decode($data['keywords'],true);
        $goods=$data['goods'];
        $data['keywords']=$goods['keywords'];
        if(!isset($goods['bn'])){
            $bnsql = "select bn from sdb_goods where goods_id=".$goods['goods_id'];
            $bnrow = $this->db->selectrow($bnsql);
            $goods['bn']=$bnrow['bn'];
        }
        $props=$this->matrix_propsToB2c_props($goods['props']);
        $input_pids = explode(',',$goods['input_pids']);
        $input_str = json_decode($goods['input_str'],true);
        foreach($input_pids as $k=>$v){
            $props[$v] = $input_str[$k];
        }
        foreach($props as $k=>$v){
                $goods['p_'.$k] = $v;
        }
        //处理规格属性
        //if(isset($goods['sku_properties'])){
            if(isset($data['sku_bns'])){
                $sku_props = explode(',',$goods['sku_properties']);
                $goods['sku_properties'] = implode(';',$sku_props);
                $stores=explode(',',$data['sku_quantities']);
                $sku_bns=explode(',',$data['sku_bns']);
                $price=explode(',',$data['sku_prices']);
                $cost=explode(',',$data['sku_costs']);
                $weight=explode(',',$data['sku_weights']);
                if(is_array($goods['sku_properties'])){//add_sku流程
                       $sku_properties=$goods['sku_properties'];
                }else{//add_product流程
                    $sku_properties = $this->matrix_specToB2c_spec($goods['sku_properties']);
                }
            }else{
                unset($goods['sku_properties']);
                $sql = "select spec_desc from sdb_goods where goods_id=".$goods['goods_id'];
                $row = $this->db->selectrow($sql);
                $spec_desc = unserialize($row['spec_desc']);
                error_log(print_r($sql,true),3,HOME_DIR.'/logs/bbbbbbbbb.log');
                foreach($spec_desc as $k=>$v){
                    $i=0;
                    foreach($v as $k2=>$v2){
                        $properties[$k][$i]=$v2['spec_value_id'];
                        $i++;
                    }
                }
                $sql2 = "select store,price,weight,bn,cost from sdb_products where goods_id=".$goods['goods_id'];
                $rs = $this->db->select($sql2);
                foreach($rs as $pk=>$pv){
                    $stores.=$pv['store'].',';
                    $sku_bns.=$pv['bn'].',';
                    $price.=$pv['price'].',';
                    $cost.=$pv['cost'].',';
                    $weight.=$pv['weight'].',';
                }
                $stores=explode(',',trim($stores,','));
                $sku_bns=explode(',',trim($sku_bns,','));
                $price=explode(',',trim($price,','));
                $cost=explode(',',trim($cost,','));
                $weight=explode(',',trim($weight,','));
                $sku_properties=$properties;
             }

             foreach($sku_properties as $k=>$v){
                foreach($v as $k2=>$v2){
                    $spec_value = $this->getSpecvalueByids($v2);
                    $spec_info = $this->getSpecInfo($k);
                    $pSpecId = $v2;
                    $spec_desc[$k][$pSpecId]['spec_value'] = $spec_value;
                    $spec_desc[$k][$pSpecId]['spec_type'] = $spec_info['spec_type'];
                    $spec_desc[$k][$pSpecId]['spec_value_id'] = $v2;
                    $spec_desc[$k][$pSpecId]['spec_image'] = '';
                    $spec_desc[$k][$pSpecId]['spec_goods_images'] = '';
        
                    $data['vars'][$k] = $spec_info['spec_name'];
                    $data['bn'][$k2] = $sku_bns[$k2];
                    $data['val'][$k][$k2] = $spec_value;
                    $data['pSpecId'][$k][$k2] = $pSpecId;
                    $data['specVId'][$k][$k2] = $v2;
                    $data['store'][$k2] = $stores[$k2];
                    $data['price'][$k2] = $price[$k2];
                    $data['cost'][$k2] = $cost[$k2];
                    $data['weight'][$k2] = $weight[$k2];
                }
            }
        //}
        
        $goods['spec_desc']=$spec_desc;
        
//       $goods['spec_desc'] = urldecode( $goods['spec_desc'] );
//        $goods['spec_desc'] = addslashes_array($goods['spec_desc']);
//        $goods['params'] = stripslashes_array($goods['params']);
        $objGoodsStatus = $this->system->loadModel('trading/goodsstatus');
        if($goods['goods_id']){
            $objGoodsStatus->checkStart($goods['goods_id'],array('goods_marketable','product_store','gimage_update','goods_update','product_update','goods_lv_price_update'));
            $is_new_product = false;
        }else{
            $is_new_product = true;
        }
        
        $udfimg = $goods['udfimg'];
//        unset($goods['udfimg']);
//        $goods['adjunct'] = $data['adjunct'];
        
        //单批方案---------------------
//        if($data['ws_policy']){
//            if(is_array($data['num'])&&!empty($data['num'])){
//                $aParams = array();
//
//                foreach($data['num'] as $k => $v){
//                    $nDiscount = ($data['distype']==1)?$data['discount'][$k]/100:$data['discount'][$k];
//                    $aParams[] = array('num' => intval($v),
//                                                        'distype' => $data['distype'],
//                                                        'discount' => $nDiscount);
//                }
//                $_nums = array_item($aParams, 'num');
//                array_multisort($_nums, SORT_ASC, $aParams);
//                if($data['limit_quantity']){
//                    $limit_quantity = array('num'=>intval($data['limit_quantity']),'distype'=>1,'discount'=>1);
//                }else{
//                    $limit_quantity = array('num'=>1,'distype'=>1,'discount'=>1);
//                }
//                array_unshift($aParams,$limit_quantity);
//                $goods['wss_params'] = $aParams;
//            }else{
//                if($data['limit_quantity'] && $data['limit_quantity'] > 1){
//                    $goods['wss_params'][] = array('num'=>intval($data['limit_quantity']),'distype'=>1,'discount'=>1);
//                }else{
//                    $goods['wss_params'] = '';
//                }
//            }
//
//            $goods['ws_policy'] = $data['ws_policy'];
//        }else{
//            $goods['ws_policy'] = '01';  //非单可混
//        }
            if(isset($goods['sku_properties'])&&strlen($goods['sku_properties'])==0){
                unset($spec_desc);
            }
            if(count($spec_desc)>0){    //开启规格 多货品
            foreach($data['vars'] as $vark=>$varv){
                $goods['spec'][$vark] = $varv;
            }
            $goods['spec'] = serialize($goods['spec']);
            $sameProFlag = array();
            foreach($data['price'] as $k => $price){    //设置销售多货品销售价等价格
                $goods['price'] = $goods['price']?min($price,$goods['price']):$price;    //取最小商品价格
                $goods['cost'] = $goods['cost']?min($data['cost'][$k],$goods['cost']):$data['cost'][$k];
                $goods['weight'] = $goods['weight']?min($data['weight'][$k],$goods['weight']):$data['weight'][$k];

                $products[$k]['price'] = $price;
                $products[$k]['bn'] = $data['bn'][$k];
                $products[$k]['store'] = (trim($data['store'][$k]) === '' ? '' : intval($data['store'][$k]));
                $products[$k]['alert'] = $data['alert'][$k];
                $products[$k]['cost'] = $data['cost'][$k];
                $products[$k]['weight'] = $data['weight'][$k];
                $products[$k]['goodsspace'] = $data['goodsspace'][$k];//新增货位
                $newSpecI = 0;
                $proSpecFlag = '';
                foreach($data['vars'] as $i=>$v){

                    $products[$k]['props']['spec'][$i] = trim($data['val'][$i][$k]);        //array('规格(颜色)序号'=>'规格值(红色)')
                    $products[$k]['props']['spec_private_value_id'][$i] = trim($data['pSpecId'][$i][$k]);
                    $products[$k]['props']['spec_value_id'][$i] = trim($data['specVId'][$i][$k]);
                    if( !$products[$k]['props']['spec'][$i] ){
                        $this->api_response('fail','请为所有货品定义规格值 ');
                    }
                    $proSpecFlag .= $products[$k]['props']['spec_private_value_id'][$i].'_';
                }
                if( in_array( $proSpecFlag, $sameProFlag ) ){
                    $this->api_response('fail','不能添加相同规格货品 ');
                }
                $sameProFlag[$k] = $proSpecFlag;
                reset($proSpecFlag);

                reset($data['vars'],$data['pSpecId']);
                $products[$k]['pdt_desc'] = implode('、', $products[$k]['props']['spec']);    //物品描述
                $products[$k]['pdt_desc'] = addslashes_array($products[$k]['pdt_desc']);
                foreach($data['idata'] as $i=>$v){
                    $products[$k]['props']['idata'][$i] = $v[$k];
                }

                //设置会员价格
                if(is_array($data['mprice']))
                    foreach($data['mprice'] as $levelid => $rows){
                        $products[$k]['mprice'][$levelid] = floatval($rows[$k]);
                    }
            }
            unset( $sameProFlag );
            $goods['products'] = &$products;
        }else{
            $goods['props']['idata'] = $data['idata'];
        }
        

        
        $objGoods = $this->system->loadModel('trading/goods');
        foreach($products as $k => $p){
            if(empty($p['bn'])) continue;
            if($objGoods->checkProductBn($p['bn'], $goods['goods_id'])){
                $this->api_response('fail','货号重复，请检查 ');
            }
            $aBn[] = $p['bn'];
        }
        if(!empty($goods['bn'])){
            if($objGoods->checkProductBn($goods['bn'], $goods['goods_id'])){
                $this->api_response('fail','货号重复，请检查 ');
            }
        }
        if(count($aBn) > count(array_unique($aBn))){
            $this->api_response('fail','货号重复，请检查 ');
        }
        if(!$goods['type_id']){
            $objCat = $this->system->loadModel('goods/productCat');
            $aCat = $objCat->getFieldById($goods['cat_id'], array('type_id'));
            $goods['type_id'] = $aCat['type_id'];
        }
        /*判断图片有无更新*/
//        if ($goods['goods_id']){
//            $goods['imgUPdate'] = $objGoods->check_ImgUpdate($goods['goods_id'],$data['goods']['image_file']);
//        }
        if((!isset($goods['name']))&&isset($goods['goods_id'])){
            $getnamesql = "select name,cat_id,type_id,brand_id from sdb_goods where goods_id=".$goods['goods_id'];
            $row = $this->db->selectrow($getnamesql);
            $goods['name']=$row['name'];
            $goods['cat_id']=$row['cat_id'];
            $goods['type_id']=$row['type_id'];
            $goods['brand_id']=$row['brand_id'];
        }
        
        if(!($gid = $objGoods->save($goods))){
            $this->api_response('fail','保存失败，请重试 ');
        }
        if(isset($data['keywords'])){
            $keywords = array();
            foreach( $objGoods->getKeywords($gid) as $keywordvalue )
                $keywords[] = $keywordvalue['keyword'];
            $keyword = implode('|', $keywords);
    
            if($keyword != $data['keywords']['keyword']){
                $objGoods->deleteKeywords($gid);
                if( $data['keywords']['keyword'] )
                    $objGoods->addKeywords($gid, explode('|',$data['keywords']['keyword']) );
            }
        }
        //处理商品图片
        //$gimage= &$this->system->loadModel('goods/gimage');
        //$gimage->saveImage($goods['goods_id'], $goods['db_thumbnail_pic'], $data['image_default'], $image_file, $udfimg, $_FILES);
        if(isset($goods['image_url'])){
            $img_result=$this->saveImage($goods);
        }
        //相关商品
//        foreach($data['linkid'] as $k => $id){
//            if($goods['goods_id']==$id){
//                $this->api_response('fail','不能相关自身商品,请检查 ');
//            }
//            $aLink[] = array('goods_1' => $goods['goods_id'], 'goods_2' => $id, 'manual' => $data['linktype'][$id], 'rate' => 100);
//        }
//        $objProduct = $this->system->loadModel('goods/products');
//        $objProduct->toInsertLink($goods['goods_id'], $aLink);
        
        //处理TAG
//        $objTag = $this->system->loadModel('system/tag');
//        $objTag->removeObjTag($goods['goods_id']);
//        foreach(space_split(stripslashes($data['tags'])) as $tagName){
//            $tagName = trim($tagName);
//            if($tagName){
//                if(!($tagid = $objTag->getTagByName('goods', $tagName))){
//                    $tagid = $objTag->newTag($tagName, 'goods');
//                }
//                $objTag->addTag($tagid, $gid);
//            }
//        }

        //by shiy 商品op_status sync_status version_id
//        if(!$is_new_product){
//            $objGoodsStatus->checkEnd();
//        }else{
//            $objGoodsStatus->jumpCheck($gid,'new_goods');
//        }
        $returndata = $this->getReturnData($goods['goods_id']);
        $this->api_response('true',false,$returndata);
        
    }
    
    //获取商品列表
    function get_products_list($data){
        //$data=array('cat_id'=>'外套','brand_id'=>'');
        $sql='select * from sdb_goods';
        $where=$this->_filter($data);
        $sql = $sql.$where;
        if(!$result = $this->db->select($sql)){
            $this->api_response('fail','没有相关商品');
        }else{
            $returndata=array();
            $returndata['total_results']=count($result);
            foreach($result as $gk=>$gv){
                //扩展属性
                for($i=1;$i<=28;$i++){
                    $pk[$i]='p_'.$i;
                }
                $props='';
                foreach($gv as $k=>$v){
                    
                    if(in_array($k,$pk)){
                        if(strlen($v)!=0){
                            foreach($pk as $k2=>$v2){
                                if($v2==$k){
                                    $props .= $k2.':'.$v.';';
                                }
                            }
                        }
                    }
                }
                $props = trim($props,';');
                
                //sku列表
                $skusql = "select * from sdb_products where goods_id=".$gv['goods_id'];
                $rs = $this->db->select($skusql);
                $skuinfo = array();
                foreach($rs as $k=>$v){
                    //properties信息
                    $skuprops='';
                    $properties = unserialize($v['props']);
                    foreach($properties['spec_value_id'] as $k2=>$v2){
                        $skuprops .=$k2.':'.$v2.';';
                    }
                    $skuprops = trim($skuprops,';');
                    $skuinfo[$k]['sku_id'] = $v['product_id'];
                    $skuinfo[$k]['iid'] = $v['goods_id'];
                    $skuinfo[$k]['bn'] = $v['bn'];
                    $skuinfo[$k]['properties'] = $skuprops;
                    $skuinfo[$k]['quantity'] = $v['store'];
                    $skuinfo[$k]['weight'] = $v['weight'];
                    $skuinfo[$k]['price'] = $v['price'];
                    $skuinfo[$k]['modified'] = $v['last_modify'];
                }
                $returndata['goods'][$gk]['iid'] = $gv['goods_id'];
                $returndata['goods'][$gk]['title'] = $gv['name'];
                $returndata['goods'][$gk]['bn'] = $gv['bn'];
                $returndata['goods'][$gk]['shop_cids'] = $gv['cat_id'];
                $returndata['goods'][$gk]['brand_id'] = $gv['brand_id'];
                $returndata['goods'][$gk]['props'] = $props;  
                $returndata['goods'][$gk]['description'] = $gv['intro'];
                $returndata['goods'][$gk]['default_img_url'] = $gv['big_pic'];
                $returndata['goods'][$gk]['num'] = $gv['store'];
                $returndata['goods'][$gk]['weight'] = $gv['weight'];
                $returndata['goods'][$gk]['price'] = $gv['price'];
                $returndata['goods'][$gk]['market_price'] = $gv['mktprice'];
                $returndata['goods'][$gk]['cost_price'] = $gv['cost'];
                $returndata['goods'][$gk]['unit'] = $gv['unit'];
                $returndata['goods'][$gk]['modified'] = $gv['last_modify'];
                $returndata['goods'][$gk]['list_time'] = $gv['uptime'];
                $returndata['goods'][$gk]['delist_time'] = $gv['downtime'];
                $returndata['goods'][$gk]['created'] = $gv['create_time'];
                $returndata['goods'][$gk]['skus'] = $skuinfo;
            }
            
            $this->api_response('true',false,$returndata);
        }
    }
    
    function _filter($filter){
//        if($filter['name']){
//            $where[]=" name ='".$filter['name']."'";
//        }
//        if($filter['bn']){
//            $where[]=" bn ='".$filter['bn']."'";
//        }
        if($filter['cat_id']){
            $where[]=" cat_id ='".$filter['cat_id']."'";
        }
        if($filter['brand_id']){
            $where[]=" brand_id ='".$filter['brand_id']."'";
        }
        $where[]=' 1';
        return parent::_filter($where,$filter);
    }
    
    //获取商品信息
    function get_product_detail_info($data){
        $sql = "select * from sdb_goods where ";
        if(isset($data['goods_id'])){
            $where = " goods_id=".$data['goods_id'];
        }
        if(isset($data['bn'])){
            $where = " bn='".$data['bn']."'";
        }
        $sql .=$where;
        
        if(!$result = $this->db->selectrow($sql)){
            $this->api_response('fail','没有相关商品');
        }else{
            //扩展属性
            for($i=1;$i<=28;$i++){
                $pk[$i]='p_'.$i;
            }
            foreach($result as $k=>$v){
                if(in_array($k,$pk)){
                    if(strlen($v)!=0){
                        foreach($pk as $k2=>$v2){
                            if($v2==$k){
                                $props .= $k2.':'.$v.';';
                            }
                        }
                    }
                }
            }
            $props = trim($props,';');
            
            //sku列表
            $skusql = "select * from sdb_products where goods_id=".$result['goods_id'];
            $rs = $this->db->select($skusql);
            $skuinfo = array();
            foreach($rs as $k=>$v){
                //properties信息
                $skuprops='';
                $properties = unserialize($v['props']);
                foreach($properties['spec_value_id'] as $k2=>$v2){
                    $skuprops .=$k2.':'.$v2.';';
                }
                $skuprops = trim($skuprops,';');
                $skuinfo[$k]['sku_id'] = $v['product_id'];
                $skuinfo[$k]['iid'] = $v['goods_id'];
                $skuinfo[$k]['bn'] = $v['bn'];
                $skuinfo[$k]['properties'] = $skuprops;
                $skuinfo[$k]['quantity'] = $v['store'];
                $skuinfo[$k]['weight'] = $v['weight'];
                $skuinfo[$k]['price'] = $v['price'];
                $skuinfo[$k]['modified'] = $v['last_modify'];
            }

            $returndata = array();
            $returndata['goods']['iid'] = $result['goods_id'];
            $returndata['goods']['title'] = $result['name'];
            $returndata['goods']['bn'] = $result['bn'];
            $returndata['goods']['shop_cids'] = $result['cat_id'];
            $returndata['goods']['brand_id'] = $result['brand_id'];
            $returndata['goods']['props'] = $props;      
            $returndata['goods']['description'] = $result['intro'];
            $returndata['goods']['default_img_url'] = $result['big_pic'];
            $returndata['goods']['num'] = $result['store'];
            $returndata['goods']['weight'] = $result['weight'];
            $returndata['goods']['price'] = $result['price'];
            $returndata['goods']['market_price'] = $result['mktprice'];
            $returndata['goods']['cost_price'] = $result['cost'];
            $returndata['goods']['unit'] = $result['unit'];
            $returndata['goods']['modified'] = $result['last_modify'];
            $returndata['goods']['list_time'] = $result['uptime'];
            $returndata['goods']['delist_time'] = $result['downtime'];
            $returndata['goods']['created'] = $result['create_time'];
            $returndata['goods']['skus'] = $skuinfo;

            $this->api_response('true',false,$returndata);
        }
    }
    
    //添加货品
    function add_sku($data){
        $properties = $this->matrix_specToB2c_spec($data['properties']);
        foreach($properties as $k=>$v){
            foreach($v as $k2=>$v2){
                $spec_value = $this->getSpecvalueByids($v2);
                $props['spec'][$k]=$spec_value;
                $props['spec_private_value_id'][$k]=$v2;
                $props['spec_value_id'][$k]=$v2;
            }
        }
        $spec = implode('、',$props['spec']);
        $spec_desc = $this->addSpec_desc($data['goods_id'],$props);
        $props=serialize($props);
        $propsql="select count(*) as num from sdb_products where goods_id=".$data['goods_id']." and props='".$props."'";
        $num = $this->db->selectrow($propsql);
        if($num['num']>0){
            $this->api_response('fail','无法添加相同规格商品');
        }
        $goodssql = "select name,pdt_desc,bn from sdb_goods where goods_id=".$data['goods_id'];
        $goods = $this->db->selectrow($goodssql);
        $productnum = "select count(*) as pnum from sdb_products where goods_id=".$data['goods_id'];
        $pnum = $this->db->selectrow($productnum);
        $propsnumsql = "select count(*) as propsnum from sdb_products where goods_id=".$data['goods_id']." and props='a:1:{s:5:\"idata\";N;}'";
        $propsnum = $this->db->selectrow($propsnumsql);
        if(!isset($data['bn'])){
            if($pnum['pnum']==1&&$propsnum['propsnum']==1){
                $data['bn']=$goods['bn'].'-1';
            }elseif($pnum['pnum']>=1){
                $data['bn']=$goods['bn'].'-'.($pnum['pnum']+1);
            }
        }
        if($pnum['pnum']==1&&$propsnum['propsnum']==1){
            $deletesql = "delete from sdb_products where goods_id=".$data['goods_id'];
            $this->db->exec($deletesql);
        }
        
        $aData=array(
            'goods_id'=>$data['goods_id'],
            'bn'=>$data['bn'],
            'props'=>$props,
            'store'=>$data['store'],
            'weight'=>$data['weight'],
            'price'=>$data['price'],
            'name'=>$goods['name'],
            'pdt_desc'=>$spec
        );
        $rs = $this->db->query("select * from sdb_products where 0=1");
        $sql = $this->db->getInsertSQL($rs,$aData);
        if(!$this->db->exec($sql)){
            $this->api_response('fail','sql exec error :'.$sql,$sql);
        }
        $product_id=$this->db->lastInsertId();
        $pdt_desc = unserialize($goods['pdt_desc']);
        $pdt_desc[$product_id]=$spec;
        $pdt_desc = serialize($pdt_desc);
        $updategoodssql = "update sdb_goods set pdt_desc='".$pdt_desc."' where goods_id=".$data['goods_id'];
        if(!$this->db->exec($updategoodssql)){
            $this->api_response('fail','sql exec error :'.$updategoodssql,$updategoodssql);
        }
        $returndata['goods_id']=$data['goods_id'];
        $returndata['product_id']=$product_id;
        $this->api_response('true',false,$returndata);
    }
    
    //删除货品
    function delete_sku($data){       
        $sql = "delete from sdb_products where product_id=".$data['product_id'];
        if(!$this->db->exec($sql)){
            $this->api_response('fail','sql exec error :'.$sql,$sql);
        }
        $checksql = "select count(*) as num from sdb_products where goods_id=".$data['goods_id'];
        $row = $this->db->selectrow($checksql);
        if($row['num']<1){
            $goodssql = "select name,bn,price,weight,unit from sdb_goods where goods_id=".$data['goods_id'];
            $goods = $this->db->selectrow($goodssql);
            $aData=array(
                'goods_id'=>$data['goods_id'],
                'bn'=>$goods['bn'],
                'price'=>$goods['price'],
                'cost'=>$goods['cost'],
                'name'=>$goods['name'],
                'weight'=>$goods['weight'],
                'unit'=>$goods['unit'],
                'store'=>$goods['store'],
                'props'=>'a:1:{s:5:"idata";N;}'
            );
            $rs = $this->db->query("select * from sdb_products where 0=1");
            $insertsql = $this->db->getInsertSQL($rs,$aData);
            if(!$this->db->exec($insertsql)){
              $this->api_response('fail','sql exec error :'.$insertsql,$insertsql);
            }
            $delspcsql = "update sdb_goods set spec='' where goods_id=".$data['goods_id'];
            if(!$this->db->exec($delspcsql)){
              $this->api_response('fail','sql exec error :'.$delspcsql,$delspcsql);
            }
        }
        $returndata['goods_id']=$data['goods_id'];
        $returndata['product_id']=$data['product_id'];
        $this->api_response('true',false,$returndata);
    }
    
    //更新货品
    function update_sku($data){
        $properties = $this->matrix_specToB2c_spec($data['properties']);
        foreach($properties as $k=>$v){
            foreach($v as $k2=>$v2){
                $spec_value = $this->getSpecvalueByids($v2);
                $props['spec'][$k]=$spec_value;
                $props['spec_private_value_id'][$k]=$v2;
                $props['spec_value_id'][$k]=$v2;
            }
        }
        $prop=serialize($props);
        $sql = "update sdb_products set ";
        if(isset($data['bn'])){
            $sql.="bn='".$data['bn']."',";
        }
        if(isset($data['store'])){
            $sql.="store='".$data['store']."',";
        }
        if(isset($data['weight'])){
            $sql.="weight='".$data['weight']."',";
        }
        if(isset($data['price'])){
            $sql.="price='".$data['price']."',";
        }
        $sql.="props='".$prop."' where product_id=".$data['product_id'];
        if(!$this->db->exec($sql)){
            $this->api_response('fail','sql exec error :'.$sql,$sql);
        }
        $returndata['goods_id']=$data['goods_id'];
        $returndata['product_id']=$data['product_id'];
        $this->api_response('true',false,$returndata);
    }
    
    //获取货品列表
    function get_sku_list($data){
        //$data=array('name'=>'','bn'=>'','cat'=>'气质单鞋','brand'=>'Nike');
        $goods_c_id=$this->getGoodsidByCatname($data['cat']);
        foreach($goods_c_id as $k=>$val){
            $goods_c_id[$k]=$val['goods_id'];
        }
        $data['goods_c_id']=implode(',',$goods_c_id);
        $goods_b_id=$this->getGoodsidByBrandname($data['brand']);
        foreach($goods_b_id as $k=>$val){
            $goods_b_id[$k]=$val['goods_id'];
        }
        $data['goods_b_id']=implode(',',$goods_b_id);
        $sql='select * from sdb_products';
        $where=$this->_skufilter($data);
        $sql = $sql.$where;
        if(!$returndata = $this->db->select($sql)){
            $this->api_response('fail','没有相关货品');
        }else{
            $this->api_response('true',false,$returndata);
        }
    }
    
    function _skufilter($filter){
        if($filter['name']){
            $where[]=" name ='".$filter['name']."'";
        }
        if($filter['bn']){
            $where[]=" bn ='".$filter['bn']."'";
        }
        if($filter['goods_c_id']){
            $where[]=" goods_id IN (".$filter['goods_c_id'].")";
        }
        if($filter['goods_b_id']){
            $where[]=" goods_id IN (".$filter['goods_b_id'].")";
        }
        $where[]=' 1';
        return parent::_filter($where,$filter);
    }
    
    function getcatid($catname){
        $sql="select cat_id from sdb_goods_cat where cat_name='".$catname."'";
        $row=$this->db->selectrow($sql);
        return $row['cat_id'];
    }
    
    function getbrandid($brandname){
        $sql="select brand_id from sdb_brand where brand_name='".$brandname."'";
        $row=$this->db->selectrow($sql);
        return $row['brand_id'];
    }
    
    function getGoodsidByCatname($catname){
        $sql="select goods_id from sdb_goods where cat_id=(select cat_id from sdb_goods_cat where cat_name='".$catname."')";
        $rows=$this->db->select($sql);
        return $rows;
    }
    
    function getGoodsidByBrandname($brandname){
        $sql="select goods_id from sdb_goods where brand_id=(select brand_id from sdb_brand where brand_name='".$brandname."')";
        $rows=$this->db->select($sql);
        return $rows;
    }

    //获取sku信息
    function get_sku_detail($data){
        $sql = "select * from sdb_products where product_id =".$data['product_id'];
        if(!$result = $this->db->selectrow($sql)){
            $this->api_response('fail','没有相关货品');
        }else{
            $returndata=array();
            $skuprops='';
            $properties = unserialize($result['props']);
            foreach($properties['spec_value_id'] as $k=>$v){
                $skuprops .=$k.':'.$v.';';
            }
            $skuprops = trim($skuprops,';');
            $returndata['product']['sku_id'] = $result['product_id'];
            $returndata['product']['iid'] = $result['goods_id'];
            $returndata['product']['bn'] = $result['bn'];
            $returndata['product']['properties'] = $skuprops;
            $returndata['product']['quantity'] = $result['store'];
            $returndata['product']['weight'] = $result['weight'];
            $returndata['product']['price'] = $result['price'];
            $returndata['product']['modified'] = $result['last_modify'];
            $this->api_response('true',false,$returndata);
        }
    }
    
    function saveImage($data){
        $goods_id=$data['goods_id'];
        $db_thumbnail_pic=$data['image_url'];
        $db_small_pic=$data['image_url'];
        $db_big_pic=$data['image_url'];
        $this->db->exec("delete from sdb_gimages where goods_id=".$goods_id);
        $db_thumbnail_pic_arr=explode('|',$db_thumbnail_pic);
        $db_small_pic_arr=explode('|',$db_small_pic);
        $db_big_pic_arr=explode('|',$db_big_pic);
        if($data['image_url']!=''){
            foreach($db_thumbnail_pic_arr as $k=>$v){
                $rs = $this->db->exec("select * from sdb_gimages where 0=1");
                $data['goods_id'] = $goods_id;
                $data['thumbnail'] = $db_thumbnail_pic_arr[$k];
                $data['small'] = $db_small_pic_arr[$k];
                $data['big'] = $db_big_pic_arr[$k];
                $data['source'] = 'N';
                $data['src_size_width'] = '0';
                $data['src_size_height'] = '0';
                $sql = $this->db->getInsertSql($rs,$data);
                if(!$this->db->exec($sql)){
                    $this->api_response('fail','图片保存失败，请重试 ');
                }
            }
        }
        $sSql = "select min(gimage_id) as image_default from sdb_gimages where goods_id=".$goods_id;    
        $row  = $this->db->selectrow($sSql);
        $good_sql = "update sdb_goods set image_default='".$row['image_default']."',thumbnail_pic='".$db_thumbnail_pic_arr[0]."',small_pic='".$db_small_pic_arr[0]."',big_pic='".$db_big_pic_arr[0]."' where goods_id=".$goods_id;
        if(!$this->db->exec($good_sql)){
            $this->api_response('fail','图片保存失败，请重试 ');
        }
    }
    
    //更新图片
    function addImage($data){
        $goods_id=$data['goods_id'];
        $db_thumbnail_pic=$data['image_url'];
        $db_small_pic=$data['image_url'];
        $db_big_pic=$data['image_url'];
        $this->db->exec("delete from sdb_gimages where goods_id=".$goods_id);
        $db_thumbnail_pic_arr=explode('|',$db_thumbnail_pic);
        $db_small_pic_arr=explode('|',$db_small_pic);
        $db_big_pic_arr=explode('|',$db_big_pic);
        if($data['image_url']!=''){
            foreach($db_thumbnail_pic_arr as $k=>$v){
                $rs = $this->db->exec("select * from sdb_gimages where 0=1");
                $data['goods_id'] = $goods_id;
                $data['thumbnail'] = $db_thumbnail_pic_arr[$k];
                $data['small'] = $db_small_pic_arr[$k];
                $data['big'] = $db_big_pic_arr[$k];
                $data['source'] = 'N';
                $data['src_size_width'] = '0';
                $data['src_size_height'] = '0';
                $sql = $this->db->getInsertSql($rs,$data);
                if(!$this->db->exec($sql)){
                    $this->api_response('fail','图片保存失败，请重试 ');
                }
            }
        }
        $sSql = "select min(gimage_id) as image_default from sdb_gimages where goods_id=".$goods_id;    
        $row  = $this->db->selectrow($sSql);
        $good_sql = "update sdb_goods set image_default='".$row['image_default']."',thumbnail_pic='".$db_thumbnail_pic_arr[0]."',small_pic='".$db_small_pic_arr[0]."',big_pic='".$db_big_pic_arr[0]."' where goods_id=".$goods_id;
        if(!$this->db->exec($good_sql)){
            $this->api_response('fail','图片保存失败，请重试 ');
        }
        $this->api_response('true');
    }
    
    //商品详情更新
    function update_goods_intro($data){
        $sql="update sdb_goods set intro='".$data['intro']."' where goods_id=".$data['goods_id'];
        if(!$this->db->exec($sql)){
            $this->api_response('fail','商品详情更新失败，请重试 ');
        }
        $this->api_response('true');
    }
    
    //获取新增商品返回结构
    function getReturnData($goods_id){
        $goods_sql = "select bn from sdb_goods where goods_id=".$goods_id;
        $goodrow = $this->db->selectrow($goods_sql);
        $products_sql = "select product_id,bn from sdb_products where goods_id=".$goods_id;
        $productrow = $this->db->select($products_sql);
        foreach($productrow as $k=>$v){
            $productrow['product_id'][$k]=$v['product_id'];
            $productrow['bn'][$k]=$v['bn'];
        }
        $productids = implode(";",$productrow['product_id']);
        $productbns = implode(";",$productrow['bn']);
        $returndata['goods_id']=$goods_id;
        $returndata['bn']=$goodrow['bn'];
        $returndata['product_ids']=$productids;
        $returndata['product_bns']=$productbns;
        return $returndata;
    }
    
    //扩展属性
    function matrix_propsToB2c_props($props){
        $b = explode(';',$props);
        foreach($b as $k=>$v){
            $c = explode(':',$v);
            $d[$c[0]]=$c[1];
        }
        return $d;
    }

    //规格
    function matrix_specToB2c_spec($sku_properties){
        $b = explode(';',$sku_properties);
        foreach($b as $k=>$v){
            $c = explode(':',$v);
            $d[$c[0]].=$c[1].',';
        }
        foreach($d as $key=>$val){
            $val = trim($val,',');
            $e=explode(',',$val);
            $f[$key]=array();
            foreach($e as $k2=>$v2){
                array_push($f[$key],$v2);
            }
        }
        return $f;
    }
    
    //通过id获取规格值
    function getSpecvalueByids($spec_value_id){
        $sql = "select spec_value from sdb_spec_values where spec_value_id=".$spec_value_id;
        $row  = $this->db->selectrow($sql);
        return $row['spec_value'];
    }
    
    //获取规格信息
    function getSpecInfo($spec_id){
        $sql = "select spec_type,spec_name from sdb_specification where spec_id=".$spec_id;
        $row = $this->db->selectrow($sql);
        return $row;
    }
    
    //添加sku是增加spec_desc
    function addSpec_desc($goods_id,$props){
        $sql = "select spec_desc from sdb_goods where goods_id=".$goods_id;
        $row = $this->db->selectrow($sql);
        if(empty($row)){
            $this->api_response('fail','该商品ID不存在');
        }
        $spec_desc = unserialize($row['spec_desc']);
        foreach($spec_desc as $k=>$v){
            if(!($props['spec_value_id'][$k]==$v[$props['spec_value_id'][$k]]['spec_value_id'])){
                $spec_value = $this->getSpecvalueByids($props['spec_value_id'][$k]);
                $spec_info = $this->getSpecInfo($k);
                $spec_desc[$k][$props['spec_value_id'][$k]]['spec_value'] = $spec_value;
                $spec_desc[$k][$props['spec_value_id'][$k]]['spec_value_id'] = $props['spec_value_id'][$k];
                $spec_desc[$k][$props['spec_value_id'][$k]]['spec_type'] = $spec_info['spec_type'];
                $spec_desc[$k][$props['spec_value_id'][$k]]['spec_image'] = '';
                $spec_desc[$k][$props['spec_value_id'][$k]]['spec_goods_images'] = '';
            }
        }
        $spec_desc = serialize($spec_desc);
        $upsql = "update sdb_goods set spec_desc='".$spec_desc."' where goods_id=".$goods_id;
        if(!$this->db->exec($upsql)){
            $this->api_response('fail','sql exec error :'.$sql,$sql);
        }
    }
    
    //验证cat_id是否存在
    function checkCatid($cat_id){
        $sql = "select count(*) as num from sdb_goods_cat where cat_id=".$cat_id;
        $row = $this->db->selectrow($sql);
        if($row['num']==0){
            $this->api_response('fail','没有相应商品分类');
        }
    }
}

