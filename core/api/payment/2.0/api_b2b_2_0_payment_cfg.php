<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_2_0_payment_cfg extends shop_api_object {
    var $max_number=100;
    var $app_error=array(
            'payment_is_not_predeposits'=>array('no'=>'b_payment_cfg_001','debug'=>'','level'=>'warning','desc'=>'订单支付方式不是预存款','info'=>'','debug'=>''),
            'payment_can_not_predeposits'=>array('no'=>'b_payment_cfg_002','debug'=>'','level'=>'warning','desc'=>'订单支付方式不能是预存款','info'=>'','debug'=>''),
            'valid_payment'=>array('no'=>'b_payment_cfg_003','debug'=>'','level'=>'warning','desc'=>'订单支付方式无效','info'=>'','debug'=>''),
    );

    function getColumns(){
        $columns=array(
            'id'=>array('type'=>'int'),
            'custom_name'=>array('type'=>'string'),
            'pay_type'=>array('type'=>'string'),
            'config'=>array('type'=>'string'),
            'fee'=>array('type'=>'decimal'),
            'des'=>array('type'=>'string'),
            'order_num'=>array('type'=>'int'),  
            'disabled'=>array('type'=>'string'),
            'orderlist'=>array('type'=>'int')
        );
        return $columns;
    }
    
    /**
     * 获取供应商支付方式配置信息
     *
     * @param array $data 
     *
     * @return 供应商支付方式配置信息
     */
    function search_payment_cfg_list($data){
        $data['disabled'] = 'false'; 
        $data['orderby'] = 'id';
        //bryant 拉所有信息
        $data['last_modify_st_time'] = 0;
        $data['last_modify_en_time'] = time();
        $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_payment_cfg where '.implode(' and ',$where));
        //$result['last_modify_st_time'] = $data['last_modify_st_time'];
        //$result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where =$this->_filter($data);
        $data_info = $this->db->select('select '.implode(',',$data['columns']).' from sdb_payment_cfg'.$where);
        $result['counts'] = count($data_info);
        
        /*foreach($data_info as $k=>$payment_cfg){
             $payment_cfg['config'] = unserialize($payment_cfg['config']);
             $data_info[$k] = $payment_cfg;
        }*/
        
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
    }
    
    /**
     * 验证支付方式是否有效
     *
     * @param array $data 
     *
     * @return 验证支付方式是否有效
     */
    function verify_payment_cfg_valid($payment_id,& $local_payment_cfg){
       $payment_cfg = $this->db->selectrow('select * from sdb_payment_cfg where id='.$payment_id);    
        if(!$payment_cfg){
           $this->add_application_error('valid_payment');  
        }
        $local_payment_cfg = $payment_cfg;
    }
    
    function verify_paymentcfg_advance_valid($payment_id,& $local_payment_cfg){
        $this->verify_payment_cfg_valid($payment_id,$tmp_payment_cfg);
        
        if($tmp_payment_cfg['pay_type'] != 'deposit'){
           $this->add_application_error('payment_is_not_predeposits');  
        }
        $local_payment_cfg = $tmp_payment_cfg;
    }
    
    function verify_paymentcfg_not_advance($payment_id,& $local_payment_cfg){
        $this->verify_payment_cfg_valid($payment_id,$tmp_payment_cfg);
        
        if($tmp_payment_cfg['pay_type'] == 'deposit'){
           $this->add_application_error('payment_can_not_predeposits');  
        }
        $local_payment_cfg = $tmp_payment_cfg;
    }
    
    function before_filter($filter){
        $where = array(1);
        if(isset($filter['last_modify_st_time'])){
            $where[]='last_modify >='.intval($filter['last_modify_st_time']);
        }
        if(isset($filter['last_modify_en_time'])){
            $where[]='last_modify <'.intval($filter['last_modify_en_time']);
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