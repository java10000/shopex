<?php
/**
 * @author rocky zhang
 * @version 1.0
 * @copyright ShopEx
 * 商品规格相关接口
 */ 

include_once(CORE_DIR.'/api/shop_api_object.php');

class api_3_0_spec extends shop_api_object {
    function api_3_0_spec() {
        parent::shop_api_object();
    }
    
    /**
     * get specification metas
     */
    function get_spec_meta($data){
        $spec_id = $data['spec_id'];
        $result = array();
        if ( 0 == $spec_id ) {
            $sql = 'SELECT * FROM sdb_specification';
        } elseif ( ($spec_id = (int) $spec_id) > 0 ) {
            $sql = "SELECT * FROM sdb_specification WHERE spec_id='$spec_id'";
        }
        $specs = $this->db->select($sql);
        $this->api_response('true','',$specs);
    }
    
    /**
     * get specification values
     */
    function get_spec_value($data){
        $spec_id = (int)$data['spec_id'];
        if ( 0 === $spec_id ) {
            $sql = "SELECT * FROM sdb_spec_values ORDER BY spec_id ASC, p_order ASC";
        } elseif ( $spec_id > 0 ) {
            $sql = "SELECT * FROM sdb_spec_values WHERE spec_id = '$spec_id' ORDER BY p_order ASC";
        }
        $spec_values = $this->db->select($sql);
        $this->api_response('true','',$spec_values);
    }
}