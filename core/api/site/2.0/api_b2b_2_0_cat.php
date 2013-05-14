<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_2_0_cat extends shop_api_object {
   
    function getColumns(){
        $columns=array(
         'cat_id'=>array('type'=>'int'),
         'parent_id'=>array('type'=>'int'),
         'cat_path'=>array('type'=>'string'),
         'is_leaf'=>array('type'=>'string'),
         'type_id'=>array('type'=>'int'),
         'cat_name'=>array('type'=>'string'),
         'disabled'=>array('type'=>'string'),
         'p_order'=>array('type'=>'int'),
         'goods_count'=>array('type'=>'int'),
         'tabs'=>array('type'=>'string'),
         'finder'=>array('type'=>'string'),
         'addon'=>array('type'=>'string'),
         'child_count'=>array('type'=>'int'),
         'last_modify'=>array('type'=>'int')
        );
        return $columns;
    }
    
    /**
     * 获取供应商分类信息
     *
     * @param array $data 
     *
     * @return 供应商分类信息
     */
    function search_cat_list($data){
        $data['disabled'] = 'false'; 
        $data['orderby'] = 'cat_id'; 
        $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_goods_cat where '.implode(' and ',$where));
        $result['start_version_id'] = $data['start_version_id'];
        $result['last_version_id'] = $data['last_version_id'];
        $where =$this->_filter($data);
        $data_info = $this->db->select('select '.implode(',',$data['columns']).' from sdb_goods_cat'.$where);
        $result['counts'] = count($data_info);
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
    }
    
    function before_filter($filter){
        $where = array(1);
        if(isset($filter['start_version_id'])){
            $where[]='version_id >='.intval($filter['start_version_id']);
        }
        if(isset($filter['last_version_id'])){
            $where[]='version_id <'.intval($filter['last_version_id']);
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