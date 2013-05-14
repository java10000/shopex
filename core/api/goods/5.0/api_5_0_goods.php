<?php
class api_5_0_goods extends shop_api_object {

    function search_goods_list($data){
        $where = $this->_filter($data);

        $count =$this->db->select("SELECT COUNT(1) as c FROM sdb_goods $where");
        $result['count'] = $count[0]['c'];
        
        $datas = $this->db->select("SELECT goods_id FROM sdb_goods $where");
        $matrixMdl = &$this->system->loadModel('system/matrix');
        foreach( $datas as $i=>$v ) {
            $result['data_info'][] = $matrixMdl->format_matrix_goods($v['goods_id']);
        }
        
        $this->api_response('true',false,$result);
    }
    
    /**
    * 商品模块的过滤赛选器
    * @param 赛选条件
    * @author DreamDream
    * @return 过滤过的筛选条件
    */
    function _filter($filter){
        $where = array();
        ($stime = +$data['last_modify_st_time']) || ($stime = NOW-3650*24*3600);
        ($etime = +$data['last_modify_en_time']) || ($etime = NOW+1);
        $where[] = "last_modify BETWEEN $stime AND $etime";
        
        if( isset($filter['goods_id']) && $goods_id=json_decode($filter['goods_id']) ){
           $where[] = "goods_id IN(".implode(",",$goods_id).")";
        }
        
        if ( !isset($filter['goods_type']) ) {
            $where[] = 'goods_type = "normal"';
        } elseif ( '_ALL_' != $filter['goods_type'] ) {
            $where[] = 'goods_type = '.$this->db->quote($filter['goods_type']);
        }
        
        if ( !isset($filter['disabled']) ) {
            $where[] = 'disabled = "false"';
        } elseif ( '_ALL_' != $filter['disabled'] ) {
            $where[] = 'disabled = '.$this->db->quote($filter['disabled']);
        }
        
        if ( isset($filter['marketable']) ){
            $where[] = 'marketable = '.$this->db->quote($filter['marketable']);
        }
        
        if(isset($filter['cat_id'])) {
            $where[]='cat_id ='.$filter['cat_id'];
        }
        
        return parent::_filter($where,$filter);
    }
    
    /**
    * 查找商品详细信息(一次一条记录)
    * @param 查找商品的详细条件
    * @author DreamDream
    * @return 查找到的商品信息
    */
    function search_goods_detail($data){
        $result = $this->system->loadModel('system/matrix')->format_matrix_goods(+$data['goods_id']);

        $this->api_response('true',false,$result);

    }
}
