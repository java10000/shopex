<?php
//发货,订单编辑
include_once CORE_DIR.'/api/shop_api_object.php';

class api_order extends shop_api_object {
    function __construct() {
        parent::__construct();
        $this->orderMdl = $this->system->loadModel('trading/order');
    }

    function update_aftersale($aftersale){
        if ( !$this->system->getConf('site.is_open_return_product') ) {
            $this->api_response('fail','data fail',null,'售后申请未开启');
        }
        if ( !$return_id = $aftersale['aftersale_id'] ) {
            $this->api_response('fail','data fail',null,'少主键');
        }
        
        // 检查订单
        $order_id = $aftersale['order_id'];
        $order = $this->db->selectrow('SELECT member_id FROM sdb_orders WHERE order_id='.$this->db->quote($order_id));
        if ( !$order ) {
            $this->api_response('fail','data fail',null,'订单不存在');
        }
        
        $status = +$aftersale['status'];
        if( !in_array($status, array(6,7,8,9)) ) {
            $data['status'] = $status;
        }
        
        $ome_status = array(
            0 => '默认',
            1 => '退货',
            2 => '换货',
            3 => '拒绝',
        );
        $comments_ = (array)json_decode($aftersale['comment'],1);
        foreach($comments_ as $k=>$v){
            $comments[] = array(
                'time'=>NOW,
                'content' => '需'.$ome_status[$status].'商品名:'.$v['name'].',货号:'.$v['bn'].'.需要支付的金额:'.$v['need_money'].',折旧(其他费用)为:'.$v['other_money'].'.<br>',
            );
        }

        $data['comment'] = serialize($comments);
        $data['return_id'] = $return_id;
        $data['order_id'] = $order_id;
        $aftersale['title'] && ($data['title'] = $aftersale['title']);
        $aftersale['content'] && ($data['content'] = $aftersale['content']);
        $aftersale['image_file'] && ($data['image_file'] = $aftersale['image_file']);
        $data['add_time'] = $aftersale['add_time'] ? $aftersale['add_time']:NOW;
        //$data['product_data'] = serialize(json_decode($aftersale['product_data'],1));
        $data['member_id'] = $order['member_id'];
        
        $rs = $this->db->exec('SELECT * FROM sdb_return_product WHERE return_id='.$this->db->quote($return_id).' LIMIT 1');
        if ( $aftersale_ = $this->db->getRows($rs,1) ) {
            $sql = $this->db->getUpdateSql($rs,$data,true);
        } else {
            $sql = $this->db->getInsertSql($rs,$data,true);
        }
        
        if ( !$sql || $this->db->exec($sql) ) {
            $this->api_response('true','售后申请成功');
        }
        
        $this->api_response('fail','售后申请失败');
    }

    function set_order_status($data) {
        $order_id = $data['order_id'];
        $order = $this->orderMdl->instance($order_id,'status,pay_status,ship_status');
        if ( !$order ) {
            $this->api_response('fail','data fail',null,'订单不存在');
        }
        if ( 'active' != $order['status'] ) {
            $this->api_response('fail','data fail',null,'订单不在active状态');
        }
        
        // 解冻库存
        $this->orderMdl->toUnfreez($order_id);
        $this->db->exec('UPDATE sdb_orders SET status="dead" WHERE order_id='.$this->db->quote($order_id));
        
        $this->api_response('true',null,'取消订单成功');
    }
    
    function update_store($data){
        $store_list = json_decode($data['store_str'],true);
        if( count($store_list) <= 0 ) {
            $this->api_response('true','没有库存数据');
        }
        
        // 更新 sdb_products
        foreach($store_list as $val) {
            $bn = $val['bn'];
            $store = (int)$val['store'];
            $product = $this->db->selectrow('SELECT 1 FROM sdb_products WHERE bn='.$this->db->quote($bn));
            if ( !$product ) { // 恶心的方式 检查货品有米有
                $gift = $this->db->selectrow('SELECT 1 FROM sdb_gift WHERE gift_bn='.$this->db->quote($bn));
                if ( !$gift ) {
                    $errorbn[] = $bn; continue;
                }
                $this->db->exec("UPDATE sdb_gift SET storage=$store,freez=0 WHERE gift_bn=".$this->db->quote($bn));
            } else {
                $bns[] = $this->db->quote($bn);
                $this->db->exec("UPDATE sdb_products SET store=$store,freez=0 WHERE bn=".$this->db->quote($bn));
            }
        }
        
        if( $errorbn ) {
            $this->api_response('fail','bn没有货物，部分货物更新失败:'.implode(',', $errorbn));
        }
        
        // 更新 sdb_goods
        if ( $bns ) {
            $bns = implode(',', $bns);
            $goods_store = $this->db->select("SELECT goods_id,SUM(store) store,bn FROM sdb_products WHERE bn IN($bns) GROUP BY goods_id");
            foreach( $goods_store as $v ) {
                $this->db->exec("UPDATE sdb_goods SET store={$v['store']} WHERE goods_id={$v['goods_id']}");
            }
        }
        
        $this->api_response('true','更新库存成功');
    }
}
