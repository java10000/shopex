<?php
/**
 * @author rocky zhang
 * @version 1.0
 * @copyright ShopEx
 * 商品规格相关接口
 */ 
class api_5_0_spec extends shop_api_object {
    /**
     * get specification metas
     */
    function get_spec_meta($data){
        $spec_id = +$data['spec_id'];
        $result = array();
        if ( !$spec_id ) {
            $sql = 'SELECT * FROM sdb_specification';
        } else {
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
        if ( !$spec_id ) {
            $sql = "SELECT * FROM sdb_spec_values ORDER BY spec_id ASC, p_order ASC";
        } else {
            $sql = "SELECT * FROM sdb_spec_values WHERE spec_id = '$spec_id' ORDER BY p_order ASC";
        }
        $spec_values = $this->db->select($sql);
        $this->api_response('true','',$spec_values);
    }
}
