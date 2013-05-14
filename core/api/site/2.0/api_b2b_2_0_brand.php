<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_2_0_brand extends shop_api_object {
    
    function getColumns(){
        $columns=array(
         'brand_id'=>array('type'=>'int'),
         'brand_name'=>array('type'=>'string'),
         'brand_url'=>array('type'=>'string'),
         'brand_desc'=>array('type'=>'string'),
         'brand_logo'=>array('type'=>'string'),
         'brand_keywords'=>array('type'=>'string'),
         'disabled'=>array('type'=>'string'),
         'ordernum'=>array('type'=>'int'),
         'last_modify'=>array('type'=>'int')
        );
        return $columns;
    }
    
    /**
     * 获取供应商品牌信息
     *
     * @param array $data 
     *
     * @return 供应商品牌信息
     */
    function search_brand_list($data){
        $data['disabled'] = 'false'; 
        $data['orderby'] = 'brand_id'; 
        $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_brand where '.implode(' and ',$where));
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where =$this->_filter($data);
        $brand_list =  $this->db->select('select '.implode(',',$data['columns']).' from sdb_brand'.$where);
        $objStorager = &$this->system->loadModel('system/storager');
        //$obj_tools = $this->load_api_instance('get_http','1.0');
        
        foreach($brand_list as $k=>$brand){
            if(!empty($brand['brand_logo'])){         
                $brand_logo = $objStorager->getUrl($brand['brand_logo']);
                //$arr_http = $obj_tools->get_http_var($brand_logo);
                //$return_img = $obj_tools->get_http($arr_http['host'],$arr_http['port'],$arr_http['path'],'',5);
                
                if(!empty($brand_logo)){
                    $brand_list[$k]['brand_logo'] = $brand_logo;
                }else{
                    $brand_list[$k]['brand_logo'] = '';
                }        
            }        
        }
       
        $result['counts'] = count($brand_list);
        $result['data_info'] = $brand_list;
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