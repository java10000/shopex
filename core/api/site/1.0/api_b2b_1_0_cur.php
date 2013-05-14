<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_cur extends shop_api_object {
    var $max_number=100;
    
    function getColumns(){
        $columns=array(
            'cur_name'=>array('type'=>'string'),
            'cur_code'=>array('type'=>'string'),
            'cur_sign'=>array('type'=>'string'),
            'cur_rate'=>array('type'=>'decimal'),
            'def_cur'=>array('type'=>'string'),
            'disabled'=>array('type'=>'string')
        );
        return $columns;
    }
    function get_currency_list(){
        $data_info = $this->db->select('select * from sdb_currency');
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
    }
    function search_cur_list($data){
        $data['orderby'] = 'cur_name'; 
        $where =$this->_filter(array(1),$data);
        $data_info = $this->db->select('select * from sdb_currency'.$where);
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
    }
}