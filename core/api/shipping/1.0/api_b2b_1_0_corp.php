<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_corp extends shop_api_object {
 
    function getColumns(){
        $columns=array(
         'corp_id'=>array('type'=>'int'),
         'type'=>array('type'=>'string'),
         'name'=>array('type'=>'string'),
         'disabled'=>array('type'=>'string'),
         'ordernum'=>array('type'=>'int'),
         'website'=>array('type'=>'string')
        );
        return $columns;
    }
    
    /**
     * 获取供应商物流公司信息
     *
     * @param array $data 
     *
     * @return 供应商物流公司信息
     */
    function search_dly_corp($data){
        $data['disabled'] = 'false'; 
        $data['orderby'] = 'corp_id'; 
        $where =$this->_filter($data);
        $result['data_info'] = $this->db->select('select '.implode(',',$data['columns']).' from sdb_dly_corp'.$where);
     
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