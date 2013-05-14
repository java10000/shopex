<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_2_0_spec extends shop_api_object {
   
    function getColumns(){
        $columns=array(
         'spec_id'=>array('type'=>'int'),
         'spec_name'=>array('type'=>'string'),
         'spec_show_type'=>array('type'=>'string'),
         'spec_type'=>array('type'=>'string'),
         'spec_memo'=>array('type'=>'string'),
         'p_order'=>array('type'=>'int'),
         'disabled'=>array('type'=>'string'),   
         'last_modify'=>array('type'=>'int')
        );
        return $columns;
    }
    
    /**
     * 获取供应商规格信息
     *
     * @param array $data 
     *
     * @return 供应商规格信息
     */
     function search_spec_list($data){
        $data['disabled'] = 'false'; 
        $data['orderby'] = 'spec_id'; 
        $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_specification where '.implode(' and ',$where));
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where =$this->_filter($data);
        $specification=$this->db->select('select '.implode(',',$data['columns']).' from sdb_specification'.$where);
        
        if($specification){
           $objStorager = &$this->system->loadModel('system/storager');
           //$obj_tools = $this->load_api_instance('get_http','1.0');
           
           foreach($specification as $k=>$spec_row){ 
              $spec_values_list = $this->db->select('select * from sdb_spec_values where spec_id='.$spec_row['spec_id']);
              
              if($spec_values_list){
                  foreach($spec_values_list as $j=>$spec_values){
                    if(!empty($spec_values['spec_image'])){
                        $spec_image = $objStorager->getUrl($spec_values['spec_image']);
                        //$arr_http = $obj_tools->get_http_var($spec_image);
                        //$return_img = $obj_tools->get_http($arr_http['host'],$arr_http['port'],$arr_http['path'],'',5);
                        
                        if(!empty($spec_image)){
                            $spec_values_list[$j]['spec_image'] = $spec_image;
                        }else{
                            $spec_values_list[$j]['spec_image'] = '';
                        }
                    }         
                  }
                  
                  $spec_row['spec_values'] =  $spec_values_list;
              }else{
                  $spec_row['spec_values'] =  array();
              }
                      
              $specification[$k] = $spec_row;       
           }
        }
    
        $result['counts'] = count($specification);
        $result['data_info'] = $specification;
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