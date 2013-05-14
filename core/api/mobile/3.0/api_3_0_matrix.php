<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
/**
 * API matrix_order模块部份
 * @package
 * @version 1.0: 
 * @copyright 2003-2011 ShopEx
 * @author lushengchao
 * @license Commercial
 */
class api_3_0_matrix extends shop_api_object {
    /**
     *添加商品回调（存储num_iid）
     *@author chenxu
     *@date 2011-8-8
     *@params  $data 订单结构体
     */
    function store_num_iid($data){
        $sql="replace into sdb_goods_outer_id set goods_id =".$data['goods_id']." , outer_key ='mobile',  num_iid =".$data['num_iid'];
        $this->db->query($sql);
        $this->api_response('true',false);
    }
}
