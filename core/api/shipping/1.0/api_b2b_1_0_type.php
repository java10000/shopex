<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_type extends shop_api_object {
 
    function getColumns(){
        $columns=array(
         'dt_id'=>array('type'=>'int'),
         'dt_name'=>array('type'=>'string'),
         'dt_config'=>array('type'=>'string'),
         'dt_expressions'=>array('type'=>'string'),
         'detail'=>array('type'=>'string'),
         'price'=>array('type'=>'string'),
         'type'=>array('type'=>'int'),
         'gateway'=>array('type'=>'string'),
         'protect'=>array('type'=>'int'),
         'protect_rate'=>array('type'=>'float'),
         'ordernum'=>array('type'=>'string'),
          'has_cod'=>array('type'=>'int'),
         'minprice'=>array('type'=>'float'),
         'disabled'=>array('type'=>'string'),
         'corp_id'=>array('type'=>'int'),
         'dt_status'=>array('type'=>'int')
        );
        return $columns;
    }
    
    /**
     * 获取供应商配送信息
     *
     * @param array $data 
     *
     * @return 供应商配送地区信息
     */
    function search_dly_type($data){
        $data['disabled'] = 'false'; 
        $data['orderby'] = 'dt_id'; 
        $where =$this->_filter($data);
        $result['data_info'] = $this->db->select('select '.implode(',',$data['columns']).' from sdb_dly_type'.$where);
     
        $this->api_response('true',false,$result);
    }
    
    function get_dly_type_list(){
        $result['data_info'] = $this->db->select('select * from sdb_dly_type');
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
 
 
}