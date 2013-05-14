<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_h_area extends shop_api_object {
 
    function getColumns(){
        $columns=array(
         'dha_id'=>array('type'=>'int'),
         'dt_id'=>array('type'=>'int'),
         'area_id'=>array('type'=>'int'),
         'price'=>array('type'=>'string'),
         'has_cod'=>array('type'=>'int'),
         'areaname_group'=>array('type'=>'string'),
         'areaid_group'=>array('type'=>'string'),
         'config'=>array('type'=>'string'),
         'expressions'=>array('type'=>'string'),
         'ordernum'=>array('type'=>'int')
        );
        return $columns;
    }
    
    function get_dly_h_area_list(){
        $result['data_info'] = $this->db->select('select * from sdb_dly_h_area');
        $this->api_response('true',false,$result);
    }
    /**
     * 获取供应商配送指定地区信息
     *
     * @param array $data 
     *
     * @return 供应商配送地区信息
     */
    function search_dly_h_area($data){
        $data['orderby'] = 'dha_id'; 
        $where =$this->_filter($data);
        $result['data_info'] = $this->db->select('select '.implode(',',$data['columns']).' from sdb_dly_h_area'.$where);
     
        $this->api_response('true',false,$result);
    }
 
    function before_filter($filter){
        $where = array(1);
    
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