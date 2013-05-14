<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_payment extends shop_api_object {
    var $max_number=100;
    var $arr_pay_plugins;
    var $app_error=array(
            'valid payment'=>array('no'=>'b_payment_001','debug'=>'','level'=>'warning','info'=>'支付单无效','desc'=>'','debug'=>''),
            'fail to create payment'=>array('no'=>'b_payment_002','debug'=>'','level'=>'warning','info'=>'支付单生成失败','desc'=>'','debug'=>''),
    );

    function getColumns(){
        $columns=array(
            'payment_id'=>array('type'=>'int'),
            'order_id'=>array('type'=>'string'),
            'member_id'=>array('type'=>'string'),
            'account'=>array('type'=>'string'),
            'bank'=>array('type'=>'decimal'),
            'pay_account'=>array('type'=>'string'),
            'currency'=>array('type'=>'int'),  
            'money'=>array('type'=>'string'),
            'paycost'=>array('type'=>'int'),
            'cur_money'=>array('type'=>'int'),
            'pay_type'=>array('type'=>'string'),
            'payment'=>array('type'=>'string'),
            'paymethod'=>array('type'=>'string'),
            'op_id'=>array('type'=>'decimal'),
            'ip'=>array('type'=>'string'),
            't_begin'=>array('type'=>'int'),  
            't_end'=>array('type'=>'string'),
            'status'=>array('type'=>'int'),
            'memo'=>array('type'=>'int'),
            'disabled'=>array('type'=>'string'),
            'trade_no'=>array('type'=>'int')
        );
        return $columns;
    }
    
    /**
     * 获取对帐单
     *
     * @param array $data 
     *
     * @return 获取对帐单
     */
    function search_payments_by_order($data){
        //$result = $this->db->selectrow('select count(*) as all_counts from sdb_payments');
       // $result['last_modify_st_time'] = $data['last_modify_st_time'];
       // $result['last_modify_en_time'] = $data['last_modify_en_time'];
       // $where =$this->_filter($data);
     
        $data_info=$this->db->select('select '.implode(',',$data['columns']).' from sdb_payments where order_id='.$data['order_id']);
        $result['counts'] = count($data_info);
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
    }
    
   /**
     * 获父支付单
     * @param array $data 
     * @return 获父支付单
     */
    function get_payment_info($data){
        $data_info=$this->db->selectrow('select '.implode(',',$data['columns']).' from sdb_payments where payment_id='.$data['payment_id']);
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
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

    function insert_ome_payments($data){
        $this->check_task_exists($data);
        if(floatval($data['money']) <= 0){
             $this->api_response('fail','data fail',null,'支付失败：支付金额非法');
            exit;
        }
        
        $oOrder = &$this->system->loadModel('trading/order');
        $oOrder_cfg = &$this->system->loadModel('trading/paymentcfg');
        
        $order = $oOrder->load($data['order_id']);
        if(!$order ){
             $this->api_response('fail','data fail',null,'支付失败：订单号不存在');
            exit; 
        }

        if($order['status'] != 'active'){
            $this->api_response('fail','data fail',null,'支付失败：订单状态锁定，不能支付！');
            exit;
           
        }
     

        if($data['payment']){
                preg_match('/(\d+)$/',$data['payment'],$match);
                $data['payment'] = str_replace($match[0],'',$data['payment']);

            $filter['pay_type']=$data['payment'];
            if(!$getpayment_type=$oOrder_cfg->getlist('*',$filter,0,1)){
              $this->api_response('fail','data fail',null,'支付失败：该支付方式已被禁用，请选择其他支付方式');
            exit;
            }
            $filter['pay_type']=$data['payment'];
            $getpayment_type=$this->db->selectrow('select * from sdb_payment_cfg where pay_type = "'.$filter['pay_type'].'"');

        }else{
            $filter['id']=$order['payment'];
            $getpayment_type=$this->db->selectrow('select * from sdb_payment_cfg where id = "'.$filter['id'].'"');
         
        }
       

        $aData=array(
            'payment_id'=>$this->gen_id(),
            'order_id'=>$data['order_id'],
            'member_id'=>$order['member_id'],
            'account'=>$data['account'],
            'bank'=>$data['bank'],
            'pay_account'=>$data['pay_account'],
            'currency'=>$order['currency'],
            'money'=>$data['money'],
            'paycost'=>$data['paycost'],
            'cur_money'=>$data['money']*$order['cur_rate'],
            'pay_type'=>$data['pay_type'],
            'payment'=>$getpayment_type['id'],
            'paymethod'=>$getpayment_type['custom_name'],
            'ip'=>$data['ip'],
            't_begin'=>time(),
            't_end'=>time(),
            'status'=>'succ',
            'memo'=>'ome同步支付单',
            'disabled'=>'false',
            'trade_no'=>$data['trade_no'],//交易流水单号
            'parent_payment_id'=>$data['parent_payment_id']
        );
         if($data['pay_type']=='deposit'){
              $oAdvance = &$this->system->loadModel("member/advance");
            $status_member = $oAdvance->checkAccount($aData['member_id'], $aData['money'], $message,$rows);
            if(!$status_member){
                if($status_member === 0){
                      $this->api_response('fail','data error',null,'支付失败：'.$message);
                }else{
                    $this->api_response('fail','data error',null,'支付失败：'.$message);
                    
                }
            }

        }


      $order = $this->verify_order_valid($data['order_id'],'*');//验证订单号存在

      $this->checkOrderStatus('pay',$order);//验证订单状态

      //if($data['money'] == $order['payed']){
       //  $this->api_response('true','data true',null,'yun');
      //}
      $payment_money = $this->db->select('select money from sdb_payments where order_id = "'.$aData['order_id'].'" and status="succ"' );
      $num_money = '';
     foreach($payment_money as $k => $v){
               $num_money += $v['money'];
      }
       $refund_money = $this->db->select('select money from sdb_refunds where order_id = "'.$aData['order_id'].'" and status="sent"');
      
     foreach($refund_money as $k => $v){
               $refund_num['money'] += $v['money'];
      }
      $payed = $aData['money'] + $num_money-$refund_num['money'];
      $payed = number_format($payed,$this->system->getConf('site.decimal_digit'),".","");
      $mdl_order = &$this->system->loadModel('trading/order');

        if($data['money'] == $order['final_amount']){
                $data_new['pay_status'] = 1;
                //$aData['status'] = 1;
                $data_new['payed_money'] = $order['final_amount'];
                $mdl_order->toCoupon($aData);  //给优惠券
                $mdl_order->toPoint($aData);  //给积分
                $mdl_order->toExperience($aData);

        }else if($order['final_amount'] == $payed){
                $data_new['pay_status'] = 1;
                //$aData['status'] = 1;
                $data_new['payed_money'] = $order['final_amount'];
                $mdl_order->toCoupon($aData);  //给优惠券
                $mdl_order->toPoint($aData);  //给积分
                $mdl_order->toExperience($aData);

        }else if($order['final_amount'] < $payed){
              $this->api_response('fail','data fail',null,'支付金额大于订单总金额');
          exit;
        }else{
                $data_new['pay_status'] = 3;
                //$aData['status'] = 1;
                $data_new['payed_money'] = $payed;
        }
       
      
        $rs = $this->db->query('select * from sdb_payments where 0=1');
      
        $sql = $this->db->getInsertSQL($rs,$aData);
        if(!$this->db->exec($sql)){   
            $this->api_response('fail','data error',null,'新建支付单失败');
        }else{
            if($data['pay_type']=='deposit'){
               $message='预存款支付：订单号{'.$aData['order_id'].'}';
                if(!$oAdvance->deduct($aData['member_id'],$aData['money'],$message,$errMsg,$aData['payment_id'],$aData['order_id'],'预支付款',$data['memo'])){
                     $this->db->exec('delete  from sdb_payments  payment_id="'.$aData['payment_id'].'"');
                    $this->api_response('fail','data error',null,'支付失败：更新预存款失败');
                    
                }else{
         $this->db->exec('update sdb_orders set pay_status ="'.$data_new['pay_status'].'" , payed='.$data_new['payed_money'].' where order_id ='.$data['order_id']);

         $this->addLog($aData['order_id'],'订单'.$aData['order_id'].'付款'.$data_new['payed_money'],'付款');
          $mdl_ome = &$this->system->loadModel('plugins/connect_ome/connect_ome');
         $mdl_ome->payment_list($aData['payment_id']);
         $this->api_response('true','data true',$result,'支付单创建成功等待同步');
                }
            }else{
                $this->db->exec('update sdb_orders set pay_status ="'.$data_new['pay_status'].'" , payed='.$data_new['payed_money'].' where order_id ='.$data['order_id']);

         $this->addLog($aData['order_id'],'订单'.$aData['order_id'].'付款'.$data_new['payed_money'],'付款');
          $mdl_ome = &$this->system->loadModel('plugins/connect_ome/connect_ome');
         $mdl_ome->payment_list($aData['payment_id']);
         $this->api_response('true','data true',$result,'支付单创建成功等待同步');

            }

        } 
            
        
    }

   function verify_order_valid($order_id,$colums='*'){
       $order_exist = $this->db->selectrow('select '.$colums.' from sdb_orders where order_id ='.$order_id);
       if(!$order_exist['order_id']){
          $this->api_response('fail','data error',null,'订单不存在');
       }
    
      return $order_exist;
   }

   
   function checkOrderStatus($type,$aOrder){
       
        switch($type){
             case 'pay':
                 if($aOrder['status'] != 'active' || $aOrder['pay_status'] ==1){
                   $error_msg = '订单已付款';
                }
          break;
      }
         if(!empty($error_msg)){
            $this->api_response('fail','data error',$result,$error_msg);
        }else{
            return true;
        } 

   }

    /**
     * 供应商订单用在线支付
     *
     * @param array $data 
     *
     * @return 供应商订单用在线支付
     */
    function online_pay_center($data){
        $order_id = $data['order_id'];
        $pay_id = $data['pay_id'];//支付方式ID
        $currency = $data['currency'];//支付币别
        
        $obj_order = $this->load_api_instance('set_dead_order','1.0');
        $obj_order->verify_order_valid($order_id,$order,'*');//验证订单有效性 
        $dealer_id = $order['dealer_id'];//todo dealer_id
        $obj_order->checkOrderStatus('pay',$order);//检查订单状态是否能够支付
        $obj_order->verify_order_item_valid($order_id,$local_order_item_list);//验证订单订单商品的有效性
        
        $obj_member = $this->load_api_instance('verify_member_valid','1.0');    
        $obj_member->verify_member_valid($dealer_id,$member);//根据经销商ID验证会员记录有效性
        
        if($pay_id != -1){
            $obj_payment_cfg = $this->load_api_instance('search_payment_cfg_list','1.0');    
            $obj_payment_cfg->verify_paymentcfg_not_advance($pay_id,$local_payment_cfg);//验证支付方式不应该是预存款支付
            $this->local_payment_cfg = $local_payment_cfg;
            $this->type = $local_payment_cfg['pay_type'];
        }else{//货到付款
            $this->type = 'offline';//与线下支付处理一致
        }
        
        if($this->type == 'offline'){//线下支付
            $act_url = 'index.php'.$this->system->mkUrl('passport','payCenterOffline');
            $html ="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
                \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
                <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">
                <head>
</header><body><div>Redirecting...</div>";
            $html .= '<form id="payment" action="'.$act_url.'" method="post">';
            $html.='
                </form>
                <script language="javascript">
                document.getElementById(\'payment\').submit();
                </script>
                </html>';
                
            echo $html;
            exit;
        }
         
        $last_cost_payment = empty($order['cost_payment']) ? 0 : $order['cost_payment'];//最后次订单支付费用
        $money = $order['total_amount'] - $order['payed'];//取支付金额
        $cost_payment = $local_payment_cfg['fee'] * $money;//当前支付金额支付费
        $money = $money + $cost_payment;//需要支付的金额
        //$order['total_amount'] = $order['total_amount'] + $cost_payment;//最新订单总价入库
  
        //$obj_order->verify_payed_valid($order,$money);//检查支付金额是否大于订单总金额    
        
        $order['payment'] = $pay_id;//指定订单支付方式
        
        $order_payment = array('order_id'=>$data['order_id'],
                               'money'=>$money,
                               'paycost'=>$cost_payment
        );
        $order_payment =  array_merge($order_payment,$order);
        $payment_id = $this->create_payment($pay_id,$order_payment,'online');//生成支付单
        
        //$this->verify_payment_valid($payment_id,$payment);
        
        //通知平台支付单
        $objPlatform = &$this->system->loadModel('system/platform');
        if($objPlatform->tell_platform('payments',array('pay_id'=>$payment_id)) === false){
            $this->deletePayment($payment_id);
            $this->api_response('fail','data fail',$result,$objPlatform->getErrorInfo());
        }
        
        $obj_order->changeOrderPayment($order_id,$pay_id);//改变支付方式
        //$obj_order->update_order($order['order_id'],array('total_amount'=>$total_amount));
        //$order['total_amount'] = $total_amount; 
        $this->dopay($order,$member,$payment_id,$money,$currency);//选择支付方式进行支付   
    }
    
    /**
     * 选择支付方式
     *
     *
     *
     * @return 选择支付方式
     */
    function dopay($order,$member,$payment_id,$money,$currency){
        $payObj = $this->loadMethod($this->type);
          if ($payObj->head_charset)
                header("Content-Type: text/html;charset=".$payObj->head_charset);

            $html ="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
                \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
                <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">
                <head>
</header><body><div>Redirecting...</div>";
//            $this->money += $this->paycost;（money中 已经包含paycost）
            $payObj->_payment = $this->payment;
            $toSubmit = $payObj->toSubmit($this->getPaymentInfo($order,$member,$payment_id,$money,$currency));
            if('utf8' != strtolower($payObj->charset)){    
                $charset = &$this->system->loadModel('utility/charset');
                foreach($toSubmit as $k=>$v){
                    if(!is_numeric($v)){
                        $toSubmit[$k] = $charset->utf2local($v,'zh');
                    }
                }
            }

            $html .= '<form id="payment" action="'.$payObj->submitUrl.'" method="'.$payObj->method.'">';
            foreach($toSubmit as $k=>$v){
                if ($k<>"ikey"){
                    $html.='<input name="'.urldecode($k).'" type="hidden" value="'.htmlspecialchars($v).'" />';
                    if ($v){
                        $buffer.=urldecode($k)."=".$v."&";
                    }
                }
            }
            if (strtoupper($this->type)=="TENPAYTRAD"){
                $buffer=substr($buffer,0,strlen($buffer)-1);
                $md5_sign=strtoupper(md5($buffer."&key=".$toSubmit['ikey']));

                $url=$payObj->submitUrl."?".$buffer."&sign=".$md5_sign;
                echo "<script language='javascript'>";
                echo "window.location.href='".$url."';";
                echo "</script>";
            }
            $html.='
            </form>
            <script language="javascript">
            document.getElementById(\'payment\').submit();
            </script>
            </html>';
            
            echo $html;
    }
    
    
      /**
     * 生成付款单
     *
     * @param array $data 
     *
     * @return 生成付款单
     */
    function create_payment($pay_id,$data,$pay_type = 'deposit'){
        $order_id = $data['order_id'];
        $money = $data['money'];
        
        $this->payment_id = $this->gen_id();
        $this->order_id = $order_id;
        $this->member_id = $data['member_id'];
        $this->bank = $pay_type;
        $this->currency = !empty($data['currency']) ? $data['currency'] : 'CNY';
        $this->money = $money;
        $this->pay_type = $pay_type;
        $this->payment = $data['payment'];
        $this->t_begin = time();
        $this->t_end = time();
        $this->status = $pay_type == 'deposit' ? 'succ' : 'ready';
        
        if($pay_type == 'deposit'){
            $this->cur_money = $this->money;
        }else{
           if($this->currency != 'CNY'){
             //实际费用计算
              $currency = $this->getcur($this->currency);
              $cur_rate = ($currency['cur_rate']>0 ? $currency['cur_rate']:1);
              $this->cur_money = $this->money * $cur_rate;
           }else{
              $this->cur_money = $this->money;
           }
        }
       
        if($payCfg = $this->db->selectrow('SELECT pay_type,fee,custom_name FROM sdb_payment_cfg WHERE id='.intval($pay_id))){
            $this->paycost = $this->money * $payCfg['fee'] / (1+$payCfg['fee']);
            $this->paycost = $this->formatNumber($this->paycost);
            $this->paymethod = addslashes($payCfg['custom_name']);//by sy 转义支付方式引号
        }
        $aRs = $this->db->query('SELECT * FROM sdb_payments WHERE 0=1');
        $sSql = $this->db->GetInsertSQL($aRs,$this);
        if($this->db->exec($sSql)){
            return $this->payment_id;
        }else{
           $this->api_response('fail','data fail',$result,'支付单生成失败');
        }
    }

    function loadMethod($payPlugin){
        if(!isset($this->arr_pay_plugins[$payPlugin])){
            require_once(PLUGIN_DIR.'/payment/pay.'.$payPlugin.'.php');
    
            $className = 'pay_'.$payPlugin;
            $method = new $className($this->system);
            $this->arr_pay_plugins[$payPlugin] = $method;
        }else{
            $method = $this->arr_pay_plugins[$payPlugin];
        }
        
        return $method;
    }

    function addLog($order_id,$message,$behavior){

        $rs = $this->db->query('select * from sdb_order_log where 0=1');
        $sql = $this->db->getInsertSQL($rs,array(
            'order_id'=>$order_id,
            'op_id'=>NULL,
            'op_name'=>NULL,
            'behavior'=>$behavior,
            'result'=>'success',
            'log_text'=>addslashes($message),
            'acttime'=>time()
        ));

        return $this->db->exec($sql);
   }

    function getPaymentInfo($order,$member,$payment_id,$money,$currency){
        $payment['M_OrderId'] = $payment_id;        //    订单的id---支付流水号
        $payment['M_OrderNO'] = $order['order_id'];        //    订单号
        $payment['M_Amount'] = $money;        //    本次支付金额        小数点后保留两位，如10或12.34
        $payment['M_Def_Amount'] = $money;        //    本次支付本位币金额        小数点后保留两位，如10或12.34
        $payment['M_Currency'] = $currency;    //    支付币种
        $payment['M_Remark'] = $order['memo'];        //    订单备注
        $payment['M_Time'] = $order['createtime'];        //    订单生成时间
        $payment['M_Language'] = 'zh_CN';    //    语言选择        表示商家使用的页面语言
        $payment['R_Name'] = $order['ship_name'];        //    收货人姓名    订单支付成功后货品收货人的姓名
        $payment['R_Address'] = $order['ship_addr'];        //    收货人住址    订单支付成功后货品收货人的住址
        $payment['R_Postcode'] = $order['ship_zip'];    //    收货人邮政编码    订单支付成功后货品收货人的住址所在地的邮政编码
        $payment['R_Telephone'] = $order['ship_tel'];    //    收货人联系电话    订单支付成功后货品收货人的联系电话
        $payment['R_Mobile'] = $order['ship_mobile'];        //    收货人移动电话    订单支付成功后货品收货人的移动电话
        $payment['R_Email'] = $order['ship_email'];        //    收货人电子邮件地址    订单支付成功后货品收货人的邮件地址
        $payment['P_Name'] = $member['name'];        //    付款人姓名    支付时消费者的姓名
        $payment['P_Address'] = $member['addr'];        //    付款人住址    进行订单支付的消费者的住址
        $payment['P_PostCode'] = $member['zip'];    //    付款人邮政编码        进行订单支付的消费者住址的邮政编码
        $payment['P_Telephone'] = $member['tel'];    //    付款人联系电话     进行订单支付的消费者的联系电话
        $payment['P_Mobile'] = $member['mobile'];        //    付款人移动电话     进行订单支付的消费者的移动电话
        $payment['P_Email'] = $member['email'];        //    付款人电子邮件地址     进行订单支付的消费者的电子邮件地址
        $payment['K_key'] = $this->system->getConf('certificate.token');    //商店Key
        $configinfo = $this->local_payment_cfg;
        $pma=$this->getPaymentFileName($configinfo['config'],$configinfo['pay_type']);
        if (is_array($pma)){
            foreach($pma as $key => $val){
                $payment[$key]=$val;
            }
        }
        return $payment;
    }
  
    function getPaymentFileName($config,$ptype){//获取支付所需文件，如密钥文件、公钥文件
        if(!empty($config)){//添加
            $pmt=$this->loadMethod($ptype);
            $field=$pmt->getfields();
            $config=unserialize($config);
            if (is_array($config)){
                foreach($field as $k => $v){
                    if (strtoupper($v['type'])=="FILE"||$k=="keyPass")//判断支付网关是否有文件或者是私钥保护密码
                        $payment[$k] = $config[$k];
                }
            }
        }
        return $payment;
    }
    
    /**
     * 取回格式化的数据，供运算使用
     *
     * @param int $number
     *
     * @return string $number
     */
    function formatNumber($number){
        $this->_money_format=array(
            'decimals'=>$this->system->getConf('system.money.operation.decimals'),
            'dec_point'=>$this->system->getConf('system.money.dec_point')
        );
        
        return number_format(trim($number),
            $this->_money_format['decimals'],
            $this->_money_format['dec_point'],'');
    }
    
    /**
     * 生成支付单号
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
            $payment_id = time().str_pad($i,4,'0',STR_PAD_LEFT);
            $row = $this->db->selectrow('select payment_id from sdb_payments where payment_id =\''.$payment_id.'\'');
        }while($row);
        return $payment_id;
    }
    
    function getcur($id, $getDef=false){
        $aCur = $this->db->selectrow('select * FROM sdb_currency where cur_code="'.$id.'"');
        if($aCur['cur_code'] || !$getDef){
            return $this->_in_cur = $aCur;
        }else{
            return $this->_in_cur = $this->getDefault();
        }
    }
    
    function getDefault(){
        if($cur = $this->db->selectrow('select * from sdb_currency where def_cur=1')){
            return $cur;
        }else{    //if have no default currency, read the first currency as default value
            return $this->db->selectrow('select * FROM sdb_currency');
        }
    }
    
    function verify_payment_valid($paymentId,& $payment){
        $aTemp = $this->db->selectrow('SELECT * FROM sdb_payments WHERE payment_id=\''.$paymentId.'\'');
        if(!$aTemp['payment_id']){
           $this->api_response('fail','data fail',$result,'支付单无效');
        }
       
        $payment = $aTemp;
    }
    
    function deletePayment($sId=null){
        if($sId){
            $sSql = 'DELETE FROM sdb_payments WHERE payment_id in ('.$sId.')';
            return (!$sSql || $this->db->exec($sSql));
        }
        return false;
    }
      //初始化支付方式
      //使用原来的接口
    function start_ome_payment(){
                //error_log('here',3,'c:/start_ome_payment.txt');
      $oPayment = $this->system->loadModel('trading/payment');
            $plugin = $oPayment->db->select('SELECT * FROM sdb_payment_cfg WHERE disabled =  \'false\'');
            if($plugin){
                                foreach($plugin as $val){
                                   if($val['pay_type']){
                                        if($val['pay_type']=='deposit'){
                                                $data['payout_type']='deposit';
                                        }else if($val['pay_type']=='offline'){
                                                $data['payout_type']='offline';
                                        }else{
                                                $data['payout_type']='online';
                                        }                  
                                                $data['payment_name']=$val['custom_name'];
                                                $data['payment_id']=$val['pay_type'].$val['id'];
                                                $data_list[]=$data;
                                   }
                                }
            }else{
                $data['payout_type']=null;
                $data['payment_name']=null;
                $data['payment_id']=null;
                $data_list[0]=$data;
            }
                //error_log(var_export($data_list,1),3,'c:/data_list.txt');

           $this->api_response('true','data true',null,$data_list);
    }


 
}