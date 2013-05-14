<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_2_0_payment extends shop_api_object {
    var $max_number=100;
    var $arr_pay_plugins;
    var $app_error=array(
            'not_valid_payment'=>array('no'=>'b_payment_001','debug'=>'','level'=>'error','desc'=>'支付单无效','info'=>''),
            'fail_to_create_payment'=>array('no'=>'b_payment_002','debug'=>'','level'=>'error','desc'=>'支付单生成失败','info'=>'')
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
        
        $obj_order = $this->load_api_instance('set_dead_order','2.0');
        $obj_order->verify_order_valid($order_id,$order,'*');//验证订单有效性 
        $dealer_id = $order['dealer_id'];//todo dealer_id
        $obj_order->checkOrderStatus('pay',$order);//检查订单状态是否能够支付
        $obj_order->verify_order_item_valid($order_id,$local_order_item_list);//验证订单订单商品的有效性

        $obj_member = $this->load_api_instance('verify_member_valid','2.0');    
        $obj_member->verify_member_valid($dealer_id,$member);//根据经销商ID验证会员记录有效性
        
        $oOrder = $this->system->loadModel('trading/order');
        
        // 有预占库存时间点的设置 2010-03-24 11:30 wubin
        if(method_exists($oOrder,'getFreezeStorageStatus')) {
            // 如果没有冻结过库存 2010-03-01 17:30 wubin
            if(!$obj_order->is_freeze_store($order_id)) {
                // 验证是否够库存
                if($aTemp = $oOrder->isNotEnoughStore($order_id)) {
                    $this->api_response('fail','data fail',$result,'订单货品('.$aResult['product']['name'].')没有可下单库存');
                }        
                // 冻结库存
                $oOrder->freezeStorage($order_id);
            
                $obj_order->update_order_freeze($order_id);
            }
        } // 原处理在下订单时就已占库存
        
        
        if($pay_id != -1){
            $obj_payment_cfg = $this->load_api_instance('search_payment_cfg_list','2.0');    
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
        //$money = $obj_order->getPayMoney($order_id);
        $money = $order['total_amount'] - $order['payed'];//取支付金额
        $cost_payment = $local_payment_cfg['fee'] * $money;//当前支付金额支付费
        $cost_payment = $this->formatNumber($cost_payment);
        $order['total_amount'] = $order['total_amount'] + $cost_payment;//最新订单总价入库
        $order['total_amount'] = $this->getOrderDecimal($order['total_amount']);    
        $money = $order['total_amount'] - $order['payed'];//需要支付的金额
        //$order['total_amount'] = $order['total_amount'] + $cost_payment;//最新订单总价入库
          $obj_order->is_payed($money);//检查支付金额是否还需要被支付 
        $obj_order->verify_payed_valid($order,$money);//检查支付金额是否大于订单总金额    
        
        $order['payment'] = $pay_id;//指定订单支付方式
        
        $order_payment = array('order_id'=>$data['order_id'],
                               'money'=>$money,
                               'paycost'=>$cost_payment,
                               'member_id'=>$order['member_id'],
                               'currency'=>$order['currency'],
                               'payment'=>$order['payment']
                               
        );
        $payment_id = $this->create_payment($pay_id,$order_payment,'online');//生成支付单
        
        //$this->verify_payment_valid($payment_id,$payment);
        
        //通知平台支付单
        $objPlatform = &$this->system->loadModel('system/platform');
        if($objPlatform->tell_platform('payments',array('pay_id'=>$payment_id)) === false){
            $this->deletePayment($payment_id);
            $this->api_response('fail','data fail',$result,$objPlatform->getErrorInfo());
        }
        
         //设置订单支付费用,加上上次的支付费用
        //$curr_cost_payment = $last_cost_payment + $cost_payment;
        //$obj_order->set_order_payment($order_id,$curr_cost_payment);
        
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
        
        $this->paycost = $data['paycost'];
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
        $this->memo = '远程支付成功！';
        
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
            //$this->paycost = $this->money * $payCfg['fee'] / (1+$payCfg['fee']);
           // $this->paycost = $this->formatNumber($this->paycost);
            $this->paymethod = addslashes($payCfg['custom_name']);//by sy 转义支付方式引号
        }
        $aRs = $this->db->query('SELECT * FROM sdb_payments WHERE 0=1');
        $sSql = $this->db->GetInsertSQL($aRs,$this);
        if($this->db->exec($sSql)){
            return $this->payment_id;
        }else{
            $this->add_application_error('fail_to_create_payment');
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
        $decimals = $this->system->getConf('system.money.operation.decimals');
        $carryset = $this->system->getConf('system.money.operation.carryset');
        if($decimals < 3){
            $mul = 1;
            $mul = pow(10, $decimals);
            switch($carryset){
                case 0:
                $number = number_format($number, $decimals, '.', '');
                break;
                case 1:
                $number = ceil($number*$mul) / $mul;
                break;
                case 2:
                $number = floor($number*$mul) / $mul;
                break;
            }
        }
        return $number;
    }
    
    /**
     * 订单金额 订单金额取整方式 订单金额取整位数
     *
     * @param int $number
     *
     * @return string $number
     */
    function getOrderDecimal($number){
        $decimal_digit = $this->system->getConf('site.decimal_digit');
        $decimal_type = $this->system->getConf('site.decimal_type');
        if($decimal_digit < 3){
            $mul = 1;
            $mul = pow(10, $decimal_digit);
            switch($decimal_type){
                case 0:
                $number = number_format($number, $decimal_digit, '.', '');
                break;
                case 1:
                $number = ceil($number*$mul) / $mul;
                break;
                case 2:
                $number = floor($number*$mul) / $mul;
                break;
            }
        }
        return $number;
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
            $this->add_application_error('not_valid_payment');
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
    
    function changer($money){
         $_money_format = array(
            'decimals' => $this->system->getConf('system.money.operation.decimals'),
            'carryset' => $this->system->getConf('system.money.operation.carryset'),
            'dec_point' => $this->system->getConf('system.money.dec_point'),
            'thousands_sep' => $this->system->getConf('system.money.thousands_sep'),
            'decimal_digit' => $this->system->getConf('site.decimal_digit'),
            'decimal_type' => $this->system->getConf('site.decimal_type'),
            'trigger_tax' => $this->system->getConf('site.trigger_tax'),
            'tax_ratio' => $this->system->getConf('site.tax_ratio')
          );
          
          if($_money_format['carryset']){
            $mul = 1;
            $mul = pow(10, $_money_format['decimals']);
            switch($_money_format['carryset']){
                case 0:
                    $money = number_format(trim($money), $_money_format['decimals'], '.', '');
                break;
                case 1:
                    $money = ceil(trim($money)*$mul) / $mul;
                break;
                case 2:
                    $money = floor(trim($money)*$mul) / $mul;
                break;
            }
        }
        return  number_format($money,
                $_money_format['decimals'],
                $_money_format['dec_point'],
                $_money_format['thousands_sep']);
        
    }
}