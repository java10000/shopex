<?php
/**
 * API 商品模块部份
 * @package
 * @version 1.0: 
 * @copyright 2003-2009 ShopEx
 * @author dreamdream
 * @license Commercial
 */
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_1_0_refund extends shop_api_object {
    var $select_limited=100;
    
    /**
    * 商品部份开放的字段，包括字段类型
    * @author DreamDream
    * @return 开放的字段相关信息
    */
    function getColumns(){
        $columns=array(
            'refund_id'=>array('type'=>'int'),
            'order_id'=>array('type'=>'int'),
            'member_id'=>array('type'=>'int'),
            'account'=>array('type'=>'string'),
            'bank'=>array('type'=>'string'),
            'pay_account'=>array('type'=>'string'),
            'currency'=>array('type'=>'string'),
            'money'=>array('type'=>'decimal'),
            'pay_type'=>array('type'=>'string'),
            'payment'=>array('type'=>'string'),
            'paymethod'=>array('type'=>'string'),
            'ip'=>array('type'=>'string'),
            't_ready'=>array('type'=>'int'),
            't_sent'=>array('type'=>'int'),
            't_received'=>array('type'=>'int'),
            'status'=>array('type'=>'string'),
            'memo'=>array('type'=>'string'),
            'title'=>array('type'=>'string'),
            'send_op_id'=>array('type'=>'int'),
            'disabled'=>array('type'=>'string')
        );
        return $columns;
    }
    /**
     * 生成退款单号
     *
     * @param 
     *
     * @return int $number
     */
    function gen_id(){
        $i = rand(0,9999);
        do{
            if(9999==$i){
                $i=0;
            }
            $i++;
            $refund_id = time().str_pad($i,4,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('select refund_id from sdb_refunds where refund_id =\''.$refund_id.'\'');
        }while($row);
        return $refund_id;
    }

    //检测task_id
    function check_task_exists($data){
        $task= array();
        $task_id=$this->db->selectrow('select task_id from sdb_connect_ome_connect_pool where task_id = "'.$data['task'].'"');
        if($task_id['task_id']){
           unset($data);
        }else{
            $task['date_time'] = time();
            $task['task_id'] = $data['task'];
            $aRs = $this->db->query('SELECT * FROM sdb_connect_ome_connect_pool WHERE 0=1');
            $sSql = $this->db->getInsertSql($aRs,$task);
            $this->db->exec($sSql);
        }
        
    }

    //生成退款单
    function insert_ome_refunds($data){
        $this->check_task_exists($data);
        $oOrder = &$this->system->loadModel('trading/order');
        $oOrder_cfg = &$this->system->loadModel('trading/payment');
        $order = $oOrder->load($data['order_id']);
        if(!$order ){
             $this->api_response('fail','data fail',null,'生成退款单失败:订单号不存在');
            exit; 
        }
      
        if($data['payment']){
            
            $filter['pay_type']=$data['payment'];
            $getpayment_type=$oOrder_cfg->db->selectrow('select * from sdb_payment_cfg where pay_type = "'.$filter['pay_type'].'"');
            
        }else{
            $filter['id']=$order['payment'];
             $getpayment_type=$oOrder_cfg->db->selectrow('select * from sdb_payment_cfg where id = "'.$filter['id'].'"');

        }
        $aData=array(
            'refund_id'=>$this->gen_id(),
            'order_id'=>$data['order_id'],
            'member_id'=>$order['member_id'],
            'account'=>$data['account'],
            'bank'=>$data['bank'],
            'pay_account'=>$data['pay_account'],
            'currency'=>$order['currency'],
            'money'=>$data['money'],
            'pay_type'=>$data['pay_type'],
            'payment'=>$getpayment_type['id'],
            'paymethod'=>$getpayment_type['custom_name'],
            'ip'=>$data['ip'],
            't_ready'=>time(),
            't_sent'=>time(),
            't_received'=>time(),
            'status'=>'sent',
            'memo'=>'ome同步退款单',
            'title'=>'title',
            'send_op_id'=>$data['send_op_id'],
            'disabled'=>'false',
            'trade_no'=>$data['trade_no'],//交易流水单号
        );

       $local_new_version_order = $this->verify_order_valid($data['order_id'],'*');

      $this->checkOrderStatus($local_new_version_order);

      /*if(is_string($aData['member_id'])&&$aData['member_id']!=''){
            $get_member_id = $this->db->selectrow('select member_id from sdb_members where uname = "'.$aData['member_id'].'"');
            
         if($get_member_id['member_id']){
              $aData['member_id'] = $get_member_id['member_id'];
         }else{
               $this->api_response('fail','data error',null,'用户名不存在');
         }
      }else{
         $aData['member_id'] = NULL;
      }
      */
      $payment_money = $this->db->select('select money from sdb_payments where order_id = "'.$aData['order_id'].'"  and status="succ"');
      $num_money = '';
     foreach($payment_money as $k => $v){
               $payment_num_money += $v['money'];
      }

      $refund_money = $this->db->select('select money from sdb_refunds where order_id = "'.$aData['order_id'].'" and status="sent"');
      
     foreach($refund_money as $k => $v){
               $num['money'] += $v['money'];
      }

     $payed = $aData['money'] + $num['money'];

     if($payment_num_money==$aData['money']){
           $data_new['pay_status'] = 5;
           $aData['status'] = 'sent';
           $payed_amount = 0;
     }else if($payment_num_money - $payed == 0) {
           $data_new['pay_status'] = 5;
           $aData['status'] = 'sent';
           $payed_amount = 0;
     }elseif($payment_num_money <$payed){
          $this->api_response('fail','data fail',null,'退款金额大于订单支付金额');
          exit;

     }else{
         $payed_amount = $payment_num_money -  $aData['money'];
         if($payed_amount<0){
               $payed_amount=0;
          }
         if($payed ==0){
           $data_new['pay_status'] =1;
           $aData['status'] = 'sent';
         }elseif($payed_amount==$local_new_version_order['total_amount']){
            $data_new['pay_status'] =1;
            $aData['status'] = 'sent';
         }elseif(($payment_num_money >$payed)&&($payed>0)){
           $data_new['pay_status'] = 4;
           $aData['status'] = 'sent';
         }

           
     }

     if($aData['member_id']!=NULL&&$data['pay_type']=='deposit'){
        $get_advance = $this->system->loadModel('member/advance');
        $advance = $get_advance->get($aData['member_id']);
        $advance_sum['advance'] = $advance+$aData['money'];
        $rs = $this->db->exec('SELECT * FROM sdb_members WHERE member_id='.$aData['member_id']);
        $_sql = $this->db->getUpdateSQL($rs,$advance_sum);
        $this->db->exec($_sql);

        $message .= '预存款退款：#O{'.$aData['order_id'].'}#';

        $get_advance->log($aData['member_id'],$aData['money'],$message,$aData['refund_id'],$aData['order_id'],$aData['paymethod'],$aData['paymethod'],$advance_sum['advance']);
     }

      $refund_id = $this->db->selectrow('select * from sdb_refunds where refund_id = '.$aData['refund_id']);
      if($refund_id){
            $this->api_response('true','data fail',null);
            exit;
      }
        $rs = $this->db->query('select * from sdb_refunds where 0=1');
        $sql = $this->db->getInsertSQL($rs,$aData);

        if(!$this->db->exec($sql)){
            $this->api_response('fail','sql exec error',$sql);
            exit;
        }else{

         $this->db->exec('update sdb_orders set pay_status ="'.$data_new['pay_status'].'" , payed ='.$payed_amount.' where order_id ='.$aData['order_id']);
         $this->addLog($aData['order_id'],'订单退款'.$aData['money'],$aData['send_op_id'],'退款');
         $mdl_ome = &$this->system->loadModel('plugins/connect_ome/connect_ome');
         $mdl_ome->refund_list($aData['refund_id'],$data['refund_id']);
            $this->api_response('true','data true',$result,'退款单新建成功');
        } 

    }
    

   function verify_order_valid($order_id,$colums='*'){
       $order_exist = $this->db->selectrow('select '.$colums.' from sdb_orders where order_id ='.$order_id);
       if(!$order_exist['order_id']){
          $this->api_response('fail','data fail',null,'订单不存在');
       }
      return $order_exist;
   }

   function checkOrderStatus($data){

       if($data['pay_status']==0 || $data['pay_status']==5 ||$data['status']!='active'){
         $this->api_response('fail','data fail',$result,'该订单已退款');
         exit;
      }

   }

   function addLog($order_id,$message,$op_name,$behavior){

        $rs = $this->db->query('select * from sdb_order_log where 0=1');
        $sql = $this->db->getInsertSQL($rs,array(
            'order_id'=>$order_id,
            'op_id'=>NULL,
            'op_name'=>$op_name,
            'behavior'=>$behavior,
            'result'=>'success',
            'log_text'=>addslashes($message),
            'acttime'=>time()
        ));
        return $this->db->exec($sql);
   }

}