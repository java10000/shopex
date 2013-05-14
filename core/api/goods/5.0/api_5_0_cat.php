<?php
/**
 * API 模块部份
 * @package
 * @version 3.0: 
 * @copyright 2003-2011 ShopEx
 * @author 
 * @license Commercial
 */

class api_5_0_cat extends shop_api_object {

    function get_goods_cat(){
        $datas = $this->db->select('SELECT cat_id FROM sdb_goods_cat');
        $matrixMdl = &$this->system->loadModel('system/matrix');
        foreach( $datas as $v ) {
            $result['data_info'][] = $matrixMdl->format_matrix_cat($v['cat_id']);
        }

        $result['counts'] = count($datas);

        $this->api_response('true',false,$result);
    }
}
