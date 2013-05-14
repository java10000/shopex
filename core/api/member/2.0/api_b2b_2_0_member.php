<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_2_0_member extends shop_api_object {
    var $max_number=100;
    var $app_error=array(
            'dealer_member_not_exists'=>array('no'=>'b_member_001','debug'=>'','level'=>'error','desc'=>'经销商所对应的会员记录无效','info'=>''),
            'dealer_member_lv_not_exists'=>array('no'=>'b_member_005','debug'=>'','level'=>'error','desc'=>'经销商所对应的会员级别不存在','info'=>'')
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
           $this->add_application_error('dealer_member_not_exists');  
        }else{
            $member = $_member;
        }
    }
    
     /**
     * 验证经销商会员等级是否存在
     *
     * @param member_lv_id $dealer_id 
     * @param array $member 
     * @param string $colums 
     *
     * @return 验证经销商记录是否存在
     */
    function verify_member_lv_valid($member_lv_id,& $member_lv,$colums='*'){
        if(empty($member_lv_id)){
            $member_lv = array('dis_count'=>1);
            return true;
        }
        
        $_member_lv = $this->db->selectrow('select '.$colums.' from sdb_member_lv where member_lv_id='.$member_lv_id);      
        if(!$_member_lv){
           $this->add_application_error('dealer_member_lv_not_exists');  
        }else{
            $member_lv = $_member_lv;
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
    
    function getMemberPoint($userId) {
        $sSql = 'SELECT point FROM sdb_members WHERE member_id='.intval($userId);
        $aUserPoint = $this->db->selectrow($sSql);        
        return intval($aUserPoint['point']);
    }
    
    function chgPoint($userId, $nPoint, $sReason, $relatedId=null){
            $aUserPoint['point'] = $this->getMemberPoint($userId);
            $aUserPoint['point'] += $nPoint;

            $rRs = $this->db->query('select * from sdb_members where member_id='.$userId);
            $sSql = $this->db->GetUpdateSQL($rRs, $aUserPoint);
            if($sSql)$this->db->exec($sSql);
            $aPointHistory = array(
                                'member_id' => $userId,
                                'point' => $nPoint,
                                'reason' => $sReason,
                                'related_id' => $relatedId);
            $this->addHistory($aPointHistory);
    }
    
    function addHistory($aData) {
        $aHistoryReason = $this->getHistoryReason();
        $aData['time'] = time();
        $aData['type'] = $aHistoryReason[$aData['reason']]['type'];
        $aData['describe'] = $aHistoryReason[$aData['reason']]['describe'];
        $aData['type'] = $aHistoryReason[$aData['reason']]['type'];
        $rRs = $this->db->query('SELECT * FROM sdb_point_history WHERE 0=1');
        $sSql = $this->db->GetInsertSQL($rRs, $aData);
        $this->db->query($sSql);
    }
    
     function getHistoryReason() {

        $aHistoryReason = array(
                            'order_pay_use' => array(
                                                    'describe' => __('订单消费积分'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_pay_get' => array(
                                                    'describe' => __('订单获得积分.'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_refund_use' => array(
                                                    'describe' => __('退还订单消费积分'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_refund_get' => array(
                                                    'describe' => __('扣掉订单所得积分'),
                                                    'type' => 2,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'order_cancel_refund_consume_gift' => array(
                                                    'describe' => __('Score deduction for gifts refunded for order cancelling.'),
                                                    'type' => 1,
                                                    'related_id' => 'sdb_mall_orders',
                                                ),
                            'exchange_coupon' => array(
                                                    'describe' => __('兑换优惠券'),
                                                    'type' => 3,
                                                    'related_id' => '',
                                                ),
                            'operator_adjust' => array(
                                                    'describe' => __('管理员改变积分.'),
                                                    'type' => 3,
                                                    'related_id' => '',
                                                ),
                            'consume_gift' => array(
                                                    'describe' => __('积分换赠品.'),
                                                    'type' => 3,
                                                    'related_id' => 'sdb_mall_orders',
                                                )
            );
                            
        return $aHistoryReason;
    }
}