<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_member extends shop_api_object {
    var $max_number=100;
    var $app_error=array(
            'dealer member not exists'=>array('no'=>'b_verify_member_valid_001','debug'=>'','level'=>'warning','info'=>'经销商所对应的会员记录无效','desc'=>'','debug'=>'')
    );
    function getColumns(){
        $columns=array(
         
        );
        return $columns;
    }
   
       /**
     * 验证经销商记录是否存在
     *
     * @param int $dealer_id 
     * @param array $member 
     * @param string $colums 
     *
     * @return 验证经销商记录是否存在
     */
    function verify_member_valid($dealer_id,& $member,$colums='*'){
        $_member = $this->db->selectrow('select '.$colums.' from sdb_members where certificate_id='.$dealer_id);      
        if(!$_member){
           $this->api_response('fail','data fail',$result,'经销商所对应的会员记录无效');
        }else{
            $member = $_member;
        }
    }
    


     
     /**
     * 获取经销商更改过的licence信息
     *
     * @param array $data 
     *
     * @return 经销商更改过的licence信息
     
    function search_licence_changed(){       
        $result['data_info'] = $this->db->selec('select member_id,curr_licence from sdb_members where curr_licence!=last_licence');
        $this->api_response('true',false,$result);
    }*/
 
}