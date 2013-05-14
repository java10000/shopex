<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_area extends shop_api_object {
 
    function getColumns(){
      /*  $columns=array(
         'area_id'=>array('type'=>'int'),
         'name'=>array('type'=>'int'),
         'disabled'=>array('type'=>'int'),
         'ordernum'=>array('type'=>'string')
        );
        return $columns;*/
    }
    
    /**
     * 获取供应商配送地区信息
     *
     * @param array $data 
     *
     * @return 供应商配送地区信息
     */
    function search_dly_area($data){
        $data['disabled'] = 'false'; 
        $data['orderby'] = 'area_id'; 
        $where =$this->_filter($data);
        $result['data_info'] = $this->db->select('select * from sdb_dly_area'.$where);
     
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