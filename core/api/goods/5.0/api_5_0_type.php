<?php

class api_5_0_type extends shop_api_object {
   
    function getColumns(){
        $columns=array(
         'type_id'=>array('type'=>'int'),
         'name'=>array('type'=>'string'),
         'alias'=>array('type'=>'string'),
         'is_physical'=>array('type'=>'string'),
         'schema_id'=>array('type'=>'string'),
         'props'=>array('type'=>'string'),
         'spec'=>array('type'=>'string'),
         'setting'=>array('type'=>'string'),
         'minfo'=>array('type'=>'string'),
         'params'=>array('type'=>'string'),
         'dly_func'=>array('type'=>'int'),
         'ret_func'=>array('type'=>'string'),
         'reship'=>array('type'=>'string'),
         'disabled'=>array('type'=>'string'),
         'is_def'=>array('type'=>'string'),
         'lastmodify'=>array('type'=>'int')     
        );
        return $columns;
    }
    
    /**
     * 获取供应商类型信息
     *
     * @param array $data 
     *
     * @return 供应商类型信息
     */
    function get_type($data){
        $data['disabled'] = isset($data['disabled']) ? $data['disabled'] : 'false'; 
        $data['orderby'] = 'type_id';
        $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_goods_type where '.implode(' and ',$where));
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where =$this->_filter($data);
        $data_info = $this->db->select('select * from sdb_goods_type'.$where);
        
        /*foreach($data_info as $k=>$goodstype){
             $goodstype['props'] = unserialize($goodstype['props']);
             $goodstype['setting'] = unserialize($goodstype['setting']);
             $goodstype['params'] = unserialize($goodstype['params']);
             $data_info[$k] = $goodstype;
        }*/
        
        $result['counts'] = count($data_info);
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
    }
    
    /**
     * 获取供应商类型品牌关联信息
     *
     * @param array $data 
     *
     * @return 供应商类型品牌关联信息
     */
    function search_goodstype_brand($data){
       // $data['orderby'] = 'type_id'; 
       // $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_type_brand as tb,sdb_brand as b where tb.brand_id = b.brand_id and b.disabled="false" and tb.version_id >='.intval($data['start_version_id']).' and tb.version_id <'.intval($data['last_version_id']).' order by tb.type_id,tb.brand_id');
        $result['start_version_id'] = $data['start_version_id'];
        $result['last_version_id'] = $data['last_version_id'];
       // $where =$this->_filter($data);
        $data_info = $this->db->select('select tb.type_id,tb.brand_id,tb.brand_order from sdb_type_brand as tb,sdb_brand as b where tb.brand_id = b.brand_id and b.disabled="false" and tb.version_id >='.intval($data['start_version_id']).' and tb.version_id <'.intval($data['last_version_id']).' order by tb.type_id,tb.brand_id');
        $result['counts'] = count($data_info);
        $result['data_info'] = $data_info;
     
        $this->api_response('true',false,$result);
    }
    
    /* 获取供应商类型规格关联信息
     *
     * @param array $data 
     *
     * @return 供应商类型规格关联信息
     */
    function search_goodstype_spec($data){
        $data['orderby'] = 'spec_id,type_id'; 
        $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_goods_type_spec where '.implode(' and ',$where));
        $result['start_version_id'] = $data['start_version_id'];
        $result['last_version_id'] = $data['last_version_id'];
        $where =$this->_filter($data);
        $data_info = $this->db->select('select spec_id,type_id,spec_style from sdb_goods_type_spec'.$where);
        $result['counts'] = count($data_info);
        $result['data_info'] = $data_info;
     
        $this->api_response('true',false,$result);
    }
    
       function before_filter($filter){
        $where = array(1);
        if(isset($filter['last_modify_st_time'])){
            $where[]='lastmodify >='.intval($filter['last_modify_st_time']);
        }
        if(isset($filter['last_modify_en_time'])){
            $where[]='lastmodify <'.intval($filter['last_modify_en_time']);
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
    
}