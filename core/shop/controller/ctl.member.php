<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_member extends shopPage{

    var $noCache = true;

    function ctl_member(&$system){
        parent::shopPage($system);
        $this->_verifyMember(true);
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->title=__('会员中心');
        $action = $this->system->request['action']['method'];
        $this->_tmpl = $action.'.html';
        $this->map = array(
            array('label'=>__('交易记录'),
                  'mid'=>0,
                  'items'=>array(
                      array('label'=>__('我的订单'),'link'=>'orders'),
                      array('label'=>__('我的积分'),'link'=>'pointHistory'),
                      array('label'=>__('积分兑换优惠券'),'link'=>'couponExchange'),
                      array('label'=>__('我的优惠券'),'link'=>'coupon')
                      )
                ),
            array('label'=>__('收藏夹'),
                  'mid'=>1,
                  'items'=>array(
                      array('label'=>__('商品收藏'),'link'=>'favorite'),
                      array('label'=>__('缺货登记'),'link'=>'notify'),
                      )
                ),
            array('label'=>__('商品留言'),
                  'mid'=>2,
                  'items'=>array(
                      array('label'=>__('评论与咨询'),'link'=>'comment'),
                      )
                ),

            array('label'=>__('个人设置'),
                  'mid'=>3,
                  'items'=>array(
                      array('label'=>__('个人信息'),'link'=>'setting'),
                      array('label'=>__('修改密码'),'link'=>'security'),
                      array('label'=>__('收货地址'),'link'=>'receiver'),
                      )
                ),
            array('label'=>__('预存款'),
                  'mid'=>4,
                  'items'=>array(
                      array('label'=>__('我的预存款'),'link'=>'balance'),
                      array('label'=>__('预存款充值'),'link'=>'deposit'),
                      )
                ),
            array('label'=>__("站内消息(").$this->member['unreadmsg'].")",
                  'mid'=>5,
                  'items'=>array(
                      array('label'=>__('发送消息'),'link'=>'send'),
                      array('label'=>__('收件箱'),'link'=>'inbox'),
                      array('label'=>__('草稿箱'),'link'=>'outbox'),
                      array('label'=>__('发件箱'),'link'=>'track'),
                      array('label'=>__('给管理员发消息'),'link'=>'message'),
                      //    array('label'=>'搜索短消息','link'=>'review'),
                      //    array('label'=>'导出短消息','link'=>'review'),
                      //    array('label'=>'忽略列表','link'=>'review'),
                      )
                ),
            array('label'=>__('售后服务'),
                  'mid'=>6,
                  'items'=>array(
                      array('label'=>__('申请售后服务'),'link'=>'return_policy')
                      )
                ),
            array('label'=>__('应用配置'),
                  'mid'=>7,
                  'items'=>array(
                      
                      )  
                ),

//              array('label'=>'商业合作',
//                'items'=>array(
//                      array('label'=>'合作方式','link'=>'partner'),
//                      array('label'=>'申请成为代理','link'=>'agent'),
//                      array('label'=>'文档与协议','link'=>'shared'),
//                      array('label'=>'佣金结算','link'=>'commission'),
//                  )
//                ),
            );
        if(!$this->system->getConf('site.is_open_return_product')){
            unset($this->map[6]);
        }

        $addons =$this->system->loadModel('system/addons');
        foreach($addons->getList('plugin_name,plugin_ident',array('plugin_type'=>'app')) as $r){
            $app_names[$r['plugin_ident']] = $r['plugin_name'];
            if($app_c = $addons->load($r['plugin_ident'],'app')){
                if(method_exists($app_c,'mem_center_menu')){

                    $app_c->mem_center_menu($this->map);
                }
            }
        }
        if(count($this->map[7]['items'])<=0)
            unset($this->map[7]);
        $this->_action = $action;
    }

    function partner(){
        $this->_output();
    }

    function pagination($current,$totalPage,$act){ //本控制器公共分页函数
        $this->pagedata['pager'] = array(
            'current'=>$current,
            'total'=>$totalPage,
            'link'=>$this->system->mkUrl('member',$act,array('orz')),
            'token'=>'orz'
            );
    }

    function setting(){
        $oCur = &$this->system->loadModel('system/cur');
        $oMem = &$this->system->loadModel('member/member');
        $oLang = &$this->system->loadModel('utility/language');
        $aInfo = $oMem->getMemberInfo($this->member['member_id']);
        $messenger = &$this->system->loadModel('system/messenger');
        $this->pagedata['messenger'] = $messenger->getList();
        foreach($this->pagedata['messenger'] as $key=>$item){
            if($item['dataname']){
                unset($this->pagedata['messenger'][$key]);
            }
        }
        $aInfo['custom'] = unserialize($aInfo['custom']);
        $this->pagedata['lang'] = $oLang->getLangs();
        $this->pagedata['currency']=$oCur->curAll();
        $this->pagedata['mem'] = $aInfo;
        $Memattr = &$this->system->loadModel("member/memberattr");
        $filter['attr_show'] = 'true';
        $attr = $Memattr->getList('*',$filter,0,-1,array('attr_order','asc'));
        $memberinfo = $oMem->getMemberByid($this->member['member_id']);
        $memberattrvalue = $oMem->getMemberAttrvalue($this->member['member_id']);
        $_attr_num = count($attr);
          for($i=0;$i<$_attr_num;$i++){
               if($attr[$i]['attr_type'] =='checkbox'||$attr[$i]['attr_type'] =='select'){
                    $attr[$i]['attr_option'] = unserialize($attr[$i]['attr_option']);
          }
               if($attr[$i]['attr_group'] == 'defalut'){
                    switch($attr[$i]['attr_type']){
                         case 'area':
                         $attr[$i]['value'] = $memberinfo[0]['area'];
                         $regionId=substr($memberinfo[0]['area'],strrpos($memberinfo[0]['area'],":")+1);
                         $dArea=&$this->system->loadModel('trading/deliveryarea');
                         $row=$dArea->getById($regionId);
                         if ($row)
                             $attr[$i]['rStatus']=true;
                         break;
                         case 'date':
                         $attr[$i]['value'] = strtotime($memberinfo[0]['b_year'].'-'.$memberinfo[0]['b_month'].'-'.$memberinfo[0]['b_day']);
                         break;
                         default:
                         $attr[$i]['value'] = $memberinfo[0][$attr[$i]['attr_type']];
                         break;
                    }
               }else{
                   $mem_attr = count($memberattrvalue);
                    for($j=0;$j<$mem_attr;$j++){
                         if($attr[$i]['attr_id'] == $memberattrvalue[$j]['attr_id']){
                              $attr[$i]['value'] = $memberattrvalue[$j]['value'];
                                   if($attr[$i]['attr_type'] =='checkbox'){
                                        $date = $oMem->getattrvalue($this->member['member_id'],$attr[$i]['attr_id']);
                                        $attr[$i]['value'] =  $date;
                                   }
                         }
                    }
              }
        }
          $this->pagedata['tree'] = $attr;
          $this->_output();
    }
    function agent(){
        $this->_output();
    }
    function agreement(){
        $this->_output();
    }
    function shared(){
        $this->_output();
    }
    function orders($nPage=1){
        $order = &$this->system->loadModel('trading/order');
        $aData = $order->fetchByMember($this->member['member_id'],$nPage-1);
        $this->pagedata['orders'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'orders');
        $this->_output();
    }

    function orderdetail($order_id){
        $objOrder = &$this->system->loadModel('trading/order');
        $aOrder = $objOrder->load($order_id);
        $this->_verifyMember($aOrder['member_id']);
        $logs = $objOrder->getLogs($order_id);

        $this->pagedata['orderlogs'] = $objOrder->alterOrderLog($logs);

        if(!$aOrder||$this->member['member_id']!=$aOrder['member_id']){
            $this->system->error(404);
            exit;
        }
        if($aOrder['member_id']){
            $member = &$this->system->loadModel('member/member');
            $aMember = $member->getFieldById($aOrder['member_id'], array('email'));
            $aOrder['receiver']['email'] = $aMember['email'];
        }
         if ($aOrder['pay_extend']){
            $payment=$this->system->loadModel('trading/payment');
            $aOrder['extendCon'] = $payment->getExtendCon($aOrder['pay_extend'],$aOrder['payment']);
        }
       
        $aOrder['discount'] = 0 - $aOrder['discount'];
        $this->pagedata['order'] = $aOrder;

        $gItems = $objOrder->getItemList($order_id);
        foreach($gItems as $key => $item){
            $gItems[$key]['addon'] = unserialize($item['addon']);
            if($item['minfo'] && unserialize($item['minfo'])){
                $gItems[$key]['minfo'] = unserialize($item['minfo']);
            }else{
                $gItems[$key]['minfo'] = array();
            }
        }
        $this->pagedata['order']['items'] = $gItems;
        $this->pagedata['order']['giftItems'] = $objOrder->getGiftItemList($order_id);
        //----查找物流公司相关信息
        /*
        $corp=&$this->system->loadModel('trading/delivery');
        $cinfo=$corp->getCorpInfoByShipId($this->pagedata['order']['shipping']['id']);
        $corp=array('name'=>$cinfo['name'],'website'=>$cinfo['website']);
        $this->pagedata['order']['corp']=$corp;*/
        //----

        $oMsg = &$this->system->loadModel('resources/message');
        $orderMsg = $oMsg->getOrderMessage($order_id);
        $this->pagedata['ordermsg'] = $orderMsg;
        $this->_output();
    }

    function orderpay($order_id, $selecttype=false){
        $objOrder = &$this->system->loadModel('trading/order');
        $order = $objOrder->load($order_id);
        $this->_verifyMember($order['member_id']);
        $order['cur_money'] = ($order['amount']['total'] - $order['amount']['payed']) * $order['cur_rate'];
        $this->pagedata['order'] = $order;
        if(!$this->pagedata['order']){
            $this->system->error(404);
            exit;
        }
        if($order['status'] != 'active'){
            $this->splash('failed', $this->system->mkUrl("member","orderdetail",array($order_id)), __('订单状态锁定，不能支付！'));
        }
        $gItems = $objOrder->getItemList($order_id);
        foreach($gItems as $key => $item){
            $gItems[$key]['addon'] = unserialize($item['addon']);
            if($item['minfo'] && unserialize($item['minfo'])){
                $gItems[$key]['minfo'] = unserialize($item['minfo']);
            }else{
                $gItems[$key]['minfo'] = array();
            }
        }
        $this->pagedata['order']['items'] = $gItems;
        $this->pagedata['order']['giftItems'] = $objOrder->getGiftItemList($order_id);

//        $shipping = &$this->system->loadModel('trading/delivery');
//        $this->pagedata['delivery'] = $shipping->checkDlTypePay($this->pagedata['order']['shipping']['id'], $this->pagedata['order']['shipping']['area']);

        if($selecttype){
            $selecttype = 1;
//            $shipping = &$this->system->loadModel('trading/delivery');
//            $this->pagedata['delivery'] = $shipping->checkDlTypePay($this->pagedata['order']['shipping']['id'], $this->pagedata['order']['shipping']['area']);
            $payment = &$this->system->loadModel('trading/payment');
            $payments = $payment->getByCur($this->pagedata['order']['currency']);
            foreach($payments as $key => $val){
                $payments[$key]['money'] = $objOrder->chgPayment($order_id,$val['id'],$order['amount']['total']-$order['amount']['payed'],1);
                $payments[$key]['config']=unserialize($val['config']);
            }
            $payment = $this->system->loadModel('trading/payment');
            $payment->showPayExtendCon($payments,$order['pay_extend']);
            $this->pagedata['payments'] = $payments;
        }else{
            $selecttype = 0;
        }
        $this->pagedata['order']['selecttype'] = $selecttype;
        $this->pagedata['order']['paytype'] = strtoupper($this->pagedata['order']['paytype']);
        $objCur = &$this->system->loadModel('system/cur');
        $aCur = $objCur->getDefault();
        $this->pagedata['order']['cur_def'] = $aCur['cur_code'];
        /**检查支付方式是否有二级内容,如快钱直连的银行****/
        $payment=$this->system->loadModel('trading/payment');
        $payment->OrdMemExtend($order,$extendInfo);
        if ($extendInfo)
            $this->pagedata['extendInfo']=$extendInfo;
        /*************************************************/
        $this->_output();
    }

    function deposit(){
        $oCur = &$this->system->loadModel('system/cur');
        $currency = $oCur->getcur($currency, true);
        $this->pagedata['currencys'] = $oCur->curAll();
        $this->pagedata['currency'] = $currency['cur_code'];

        $payment = &$this->system->loadModel('trading/payment');

        $this->pagedata['payments'] = $payment->getByCur($currency['cur_code'], 'online');//$payment->getMethods('online');
        $this->pagedata['member_id'] = $this->member['member_id'];
        foreach($this->pagedata['payments'] as $k=>$v){
            $this->pagedata['payments'][$k]['config'] = unserialize($v['config']);
        }

        $this->_output();
    }

    function return_policy(){
        if(!$this->system->getConf('site.is_open_return_product')){
            $this->system->error(404);
            exit;
        }
        $oPage = &$this->system->loadModel('content/page');
        $this->pagedata['comment'] = $oPage->get_tpl_content('return_policy');
        $this->_output();
    }
    function return_list($nPage=1) {
         if(!$this->system->getConf('site.is_open_return_product')){
            $this->system->error(404);
            exit;
        }
        $rProduct = &$this->system->loadModel('trading/return_product');
        $clos = "return_id,order_id,title,add_time,status";
        $filter = array();
        $filter["member_id"] = $this->member['member_id'];
        if( $_POST["title"] != "" ){
            $filter["title"] = $_POST["title"];
        }

        if( $_POST["status"] != "" ){
            $filter["status"] = $_POST["status"];
        }

        if( $_POST["order_id"] != "" ){
            $filter["order_id"] = $_POST["order_id"];
        }
        $count;
        $aData = $rProduct->getList($clos,$filter,($nPage-1)*20,20);
        $this->pagedata['return_list'] = $aData;
        $count = $rProduct->count($filter);

        $this->pagedata['pager'] = array(
            'current'=>$nPage,
            'total'=>ceil($count/20),
            'link'=>$this->system->mkUrl('member','return_list',array('rlt')),
            'token'=>'rlt'
            );

        $this->_output();
    }

    function return_details($return_id) {
         if(!$this->system->getConf('site.is_open_return_product')){
            $this->system->error(404);
            exit;
        }
        $obj = &$this->system->loadModel('trading/return_product');
        $this->pagedata['return_item'] =  $obj->load($return_id);
        $this->pagedata['return_id'] = $return_id;
        if( !($this->pagedata['return_item']) ){
            $this->system->error(404);
            exit;
        }
        $this->_output();
    }

    function return_order_list(){
        if(!$this->system->getConf('site.is_open_return_product')){
            $this->system->error(404);
            exit;
        }
        $order = &$this->system->loadModel('trading/order');
        $clos = "order_id,createtime,final_amount,currency";
        $filter = array();
        if( $_POST['order_id'] ){
            $filter['order_id'] = $_POST['order_id'];
        }
        $filter['member_id'] = $this->member['member_id'];
        $filter['pay_status'] = 1;
        $filter['ship_status'] = 1;
        $aData = $order->getList($clos,$filter,($nPage-1)*20,20);

        $count = $order->count($filter);
        $this->pagedata['orders'] = $aData;
        $this->pagedata['pager'] = array(
         'current'=>$nPage,
         'total'=>ceil($count/20),
         'link'=>$this->system->mkUrl('member','return_order_list',array('orz')),
         'token'=>'orz'
         );
        $this->_output();
    }

    function return_add($order_id,$page=1){
         if(!$this->system->getConf('site.is_open_return_product')){
            $this->system->error(404);
            exit;
        }
        $limit = 20;
        $this->_verifyMember(false);
        $objOrder = &$this->system->loadModel('trading/order');
        $this->pagedata['orderlogs'] = $objOrder->getLogs($order_id);
        $this->pagedata['order'] = $objOrder->load($order_id);
        if(!$this->pagedata['order']){
            $this->system->error(404);
            exit;
        }

        $gItems = $objOrder->getItemList($order_id);
        foreach($gItems as $key => $item){
            $gItems[$key]['addon'] = unserialize($item['addon']);
            if($item['minfo'] && unserialize($item['minfo'])){
                $gItems[$key]['minfo'] = unserialize($item['minfo']);
            }else{
                $gItems[$key]['minfo'] = array();
            }
        }
        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = array_slice($gItems,($page-1)*$limit,$limit);
        $count = count($gItems);
        $this->pagedata['pager'] = array(
             'current'=>$page,
             'total'=>ceil($count/$limit),
             'link'=>'javascript:jump_to_return_list(orz)',
             'token'=>'orz'
            );
        $this->pagedata['url'] = $this->system->mkUrl('member','return_order_items',array($order_id));
        $this->pagedata['order']['giftItems'] = $objOrder->getGiftItemList($order_id);
        $this->_output();
    }

    function return_order_items($order_id){
         if(!$this->system->getConf('site.is_open_return_product')){
            $this->system->error(404);
            exit;
        }
        $limit = 20;
        $page = $_POST["page"];
        $this->_verifyMember(false);
        $objOrder = &$this->system->loadModel('trading/order');
        $this->pagedata['orderlogs'] = $objOrder->getLogs($order_id);
        $this->pagedata['order'] = $objOrder->load($order_id);
        if(!$this->pagedata['order']){
            $this->system->error(404);
            exit;
        }

        $gItems = $objOrder->getItemList($order_id);
        foreach($gItems as $key => $item){
            $gItems[$key]['addon'] = unserialize($item['addon']);
            if($item['minfo'] && unserialize($item['minfo'])){
                $gItems[$key]['minfo'] = unserialize($item['minfo']);
            }else{
                $gItems[$key]['minfo'] = array();
            }
        }
        $this->pagedata['order']['items'] = array_slice($gItems,($page-1)*$limit,$limit);
        $count = count($gItems);
        $this->pagedata['pager'] = array(
         'current'=>$page,
         'total'=>ceil($count/$limit),
         'link'=>'javascript:jump_to_return_list(orz)',
         'token'=>'orz'
         );
        $this->pagedata['url'] = $this->system->mkUrl('member','return_order_items',array($order_id));
        $this->pagedata['order']['giftItems'] = $objOrder->getGiftItemList($order_id);
        $this->__tmpl = "member/return_list_item.html";
        $this->_output();
    }

    function return_save(){
        if(!$this->system->getConf('site.is_open_return_product')){
            $this->system->error(404);
            exit;
        }
        $upload_file = "";
        if( $_FILES['file']['size'] > 314572800 )
        {
            $com_url = $this->system->mkUrl('member','return_add',array($_POST['order_id']));
            $this->splash('failed',$com_url,__("上传文件不能超过300M"));
        }
        if( $_FILES['file']['name'] != "" ){
                    $type=array("jpg","gif","bmp","jpeg","rar","zip");
            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])),$type))
            {
                $text=implode(",",$type);
                $com_url = $this->system->mkUrl('member','return_add',array($_POST['order_id']));
                $this->splash('failed',$com_url,__("您只能上传以下类型文件: ").$text."<br>");
            }
            $file_type = strtolower($this->fileext($_FILES['file']['name']));
            $file_path = HOME_DIR."/upload/";
            $file_name = time().rand(0,15);
            $upload_file = $file_path.$file_name.".".$file_type;
            if(move_uploaded_file($_FILES['file']['tmp_name'],$upload_file)){
                $upload_file = realpath($upload_file);
            }
        }
        $product_data = array();
        $_POST = $this->rec_htmlspecialchars($_POST);
        foreach($_POST['product_bn'] as $key => $val){
            $item = array();
            $item['bn'] = $val;
            $item['name'] = $_POST['product_name'][$key];
            $item['num'] = intval($_POST['product_nums'][$key]);
            $product_data[] = $item;
        }
        $aData['order_id'] = $_POST['order_id'];
        $aData['title'] = $_POST['title'];
        $aData['add_time'] = time();
        $aData['image_file'] = $upload_file;
        $aData['member_id'] = $this->member['member_id'];
        $aData['product_data'] = $product_data;
        $aData['content'] = $_POST['content'];
        $aData['status'] = 1;
        $obj = &$this->system->loadModel('trading/return_product');
        if($obj->save($aData)){
            $this->redirect('member','return_list');
        }
    }

    function fileext($filename){
        return substr(strrchr($filename, '.'), 1);
    }

    function file_download($return_id){
        $rp = &$this->system->loadModel('trading/return_product');

        $info = $rp->load($return_id);
        $filename = $info['image_file'];

        $rp->file_download($filename);
    }

    function balance($nPage=1){
        $oMem = &$this->system->loadModel('member/advance');
        $aData = $oMem->getFrontAdvList($this->member['member_id'],$nPage-1);

        $this->pagedata['advance'] = $oMem->get($this->member['member_id']);
        $this->pagedata['advlogs'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'balance');
        $this->_output();
    }

    function pointHistory($nPage=1) {
        $userId = $this->member['member_id'];
        $oPointHistory = &$this->system->loadModel('trading/pointHistory');
        $oMemberPoint = &$this->system->loadModel('trading/memberPoint');

        $aData = $oPointHistory->getFrontPointHistoryList($userId,$nPage-1);
        $this->pagedata['historys'] = $aData['data'];
        $this->pagedata['total_c'] = $oPointHistory->getConsumePoint($userId);
        $this->pagedata['total_g'] = $oPointHistory->getGainedPoint($userId);
        $this->pagedata['total'] = $oMemberPoint->getMemberPoint($userId);

        $this->pagination($nPage,$aData['page'],'pointHistory');
        $this->_output();
    }

    function coupon($nPage=1) {
        $oCoupon = &$this->system->loadModel('trading/coupon');
        $aData = $oCoupon->getMemberCoupon($this->member['member_id'],$nPage-1);
        if ($aData['data']) {
            foreach ($aData['data'] as $k => $item) {
                if ($item['cpns_status']==1) {
                    if ($oCoupon->isLevelAllowUse($this->member['member_id'],$item['pmt_id'], $GLOBALS['runtime']['member_lv'])) {
                        $curTime = time();
                        if ($curTime>=$item['pmt_time_begin'] && $curTime<$item['pmt_time_end']) {
                            if ($item['memc_used_times']<$this->system->getConf('coupon.mc.use_times')) {
                                if ($item['memc_enabled']=='true') {
                                    $aData['data'][$k]['memc_status'] = __('可使用');
                                }else{
                                    $aData['data'][$k]['memc_status'] = __('本优惠券已作废');
                                }
                            }else{
                                $aData['data'][$k]['memc_status'] = __('本优惠券次数已用完');
                            }
                        }else{
                            $aData['data'][$k]['memc_status'] = __('还未开始或已过期');
                        }
                    }else{
                        $aData['data'][$k]['memc_status'] = __('本级别不准使用');
                    }
                }else{
                    $aData['data'][$k]['memc_status'] == __('此种优惠券已取消');
                }
            }
        }
        $this->pagedata['coupons'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'coupon');
        $this->_output();
    }

    function couponExchange($page=1) {
        $pageLimit = 20;
        $oExchangeCoupon = &$this->system->loadModel('trading/exchangeCoupon');
        $filter = array('ifvalid'=>1);
        if ($aExchange = $oExchangeCoupon->getList('*', $filter,($page-1)*$pageLimit, $pageLimit)) {
            $counter = $oExchangeCoupon->count($filter);
            $this->pagedata['couponList'] = $aExchange;
        }

        if (is_array($this->pagedata['couponList'])) {
            $coupon = &$this->system->loadModel('trading/coupon');
            foreach($this->pagedata['couponList'] as $key => $val){
                if ($coupon->isLevelAllowUse($this->member['member_id'],$val['pmt_id'],$GLOBALS['runtime']['member_lv'],$val['cpns_point'])){
                    $this->pagedata['couponList'][$key]['use_status'] = 1;
                }
                else{
                    $this->pagedata['couponList'][$key]['use_status'] = 0;
                }
            }
        }
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($counter/$pageLimit),
            'link'=>$this->system->mkUrl('member','couponExchange',array($tmp = time())),
            'token'=>$tmp);
        $this->_output();
    }

    function favorite($nPage=1){
        $oMem = &$this->system->loadModel('member/member');
        $aData = $oMem->getFavorite($this->member['member_id'],$nPage-1);
        $this->pagedata['favorite'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'favorite');
        $setting['buytarget'] = $this->system->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->_output();
    }

    //welcome
    function index() {
        $oMem = &$this->system->loadModel('member/member');
        $aInfo = $oMem->getMemberInfo($this->member['member_id']);
        $this->pagedata['mem'] = $aInfo;

        $wInfo = $oMem->getWelcomeInfo($this->member['member_id']);
        $this->pagedata['wel'] = $wInfo;

        $order = &$this->system->loadModel('trading/order');
        $aData = $order->fetchByMember($this->member['member_id']);
        $this->pagedata['orders'] = $aData['data'];

        $aData = $oMem->getFavorite($this->member['member_id']);
        $this->pagedata['favorite'] = $aData['data'];

        $this->_output();
    }

    function delFav($nGid,$delAll=false){
        $oMem = &$this->system->loadModel('member/member');
        if($delAll){
            $oMem->delAllFav($this->member['member_id']);
        }else{
            if($oMem->delFav($this->member['member_id'],$nGid)){
                $this->redirect('member','favorite');
            }else{
                echo __('删除失败！');
            }
        }
        $this->_output();
    }

    function ajaxAddFav($nGid){
        if(!$this->member['member_id']){
            echo '<script>alert('.__('未登陆').');</script>';
            exit;
        }

        if($nGid){
            $oMem = &$this->system->loadModel('member/member');
            $oMem->addFav($this->member['member_id'],$nGid);
        }

        $this->__tmpl = 'empty.html';
        $this->pagedata['goods_id'] = $nGid;

        $this->output();
    }

    function ajaxDelFav($nGid=null,$delAll=false){
        if(!$this->member['member_id']){
            echo '<script>alert('.__('未登陆').');</script>';
            exit;
        }
        if(!$delAll){
            if($nGid){
                $oMem = &$this->system->loadModel('member/member');
                $oMem->delFav($this->member['member_id'],$nGid);
            }
        }else{
            $oMem->delAllFav($this->member['member_id']);
        }
    }

    function notify($nPage=1){
        $oMem = &$this->system->loadModel('member/member');
        $aData = $oMem->getNotify($this->member['member_id']);
        $this->pagedata['notify'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'notify');

        $setting['buytarget'] = $this->system->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->_output();
    }

    function delNotify($nId){
        $oMem = &$this->system->loadModel('member/member');
        if($oMem->delNotify($this->member['member_id'], $nId)){
            $this->redirect('member','notify');
        }
        $this->_output();
    }

    //之前的商品评论 －－ 闲
    function review(){
        foreach($this->pagedata['data'] as $key=>$val){
            $oGoods = &$this->system->loadModel('trading/goods');
            $goodsName = $oGoods->getFieldById($val['object_id'],array('name'));
            $this->pagedata['data'][$key]['goodsname'] = $goodsName['name'];
        }
        $this->_output();
    }

    //评论
    function comment($nPage=0){
        $objComment= &$this->system->loadModel('comment/comment');
        $aData = $objComment->getMemberCommentList($this->member['member_id'], $nPage);
        $aId = array();
        foreach($aData['data'] as $rows){
            $aId[] = $rows['comment_id'];
        }
        if(count($aId)) $aReply = $objComment->getCommentsReply($aId, true);
        reset($aData['data']);
        foreach($aData['data'] as $key => $rows){
            foreach($aReply as $rkey => $rrows){
                if($rows['comment_id'] == $rrows['for_comment_id']){
                    $aData['data'][$key]['items'][] = $aReply[$rkey];
                }
            }
            reset($aReply);
        }
        $this->pagedata['commentList'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'comment');
        $this->_output();
    }

    //收件箱
    function inbox($nPage=1) {
        $oMsg = &$this->system->loadModel('resources/msgbox');
        if($this->member['member_id']){
            $filter['to_id'] = $this->member['member_id'];
            $filter['to_type'] = 0;
            $filter['folder'] = 'inbox';
            $filter['del_status'] = 1;

            $aData = $oMsg->getMsgList($filter, $nPage-1);
            $this->pagedata['message'] = $aData['data'];
            $this->pagedata['total_msg'] = $aData['total'];
            $this->pagination($nPage,$aData['page'],'inbox');
        }else{
            echo  __('读不到会员用户名！');
        }
        $this->_output();
    }

    //草稿箱
    function outbox($nPage=1) {
        $oMsg = &$this->system->loadModel('resources/msgbox');
        if($this->member['member_id']){
            $filter['from_id'] = $this->member['member_id'];
            $filter['from_type'] = 0;
            $filter['folder'] = 'outbox';
            $aData = $oMsg->getMsgList($filter,$nPage-1);
            $this->pagedata['message'] = $aData['data'];
            $this->pagedata['total_msg'] = $aData['total'];
            $this->pagination($nPage,$aData['page'],'outbox');
        }else{
            echo  __('读不到会员用户名！');
            $this->redirect('member');
        }
        $this->_output();
    }

    //已发送
    function track($nPage=1) {
        $oMsg = &$this->system->loadModel('resources/msgbox');
        if($this->member['member_id']){
            $filter['from_id'] = $this->member['member_id'];
            $filter['from_type'] = 0;
            $filter['folder'] = 'inbox';
            $filter['del_status'] = 2;
            $aData = $oMsg->getMsgList($filter,$nPage-1);
            $this->pagedata['message'] = $aData['data'];
            $this->pagedata['total_msg'] = $aData['total'];
            $this->pagination($nPage,$aData['page'],'track');
        }else{
            echo  __('读不到会员用户名！');
            $this->redirect('member');
        }
        $this->_output();
    }

    function viewMsg($nMsgId){
        $oMsg = &$this->system->loadModel('resources/msgbox');
        $aMsg = $oMsg->getMsgById($nMsgId, $this->member['member_id']);
        echo $aMsg['message'];
        if($aMsg['to_id'] == $this->member['member_id'] && $aMsg['to_type'] == 0 && $aMsg['folder'] == 'inbox' && $aMsg['unread'] == '0')
            $oMsg->setReaded($nMsgId);
    }

    function delInBoxMsg(){
        if(!empty($_POST['delete'])){
            $oMsg = &$this->system->loadModel('resources/msgbox');
            $oMsg->delInBoxMsg($_POST['delete']);
            $this->splash('success', $this->system->mkUrl("member","index"), __('删除成功'));
        }else{
            $this->splash('failed', $this->system->mkUrl("member","index"), __('删除失败: 没有选中任何记录！'));
        }
    }

    function delTrackMsg() {
        if(!empty($_POST['deltrack'])){
            $oMsg = &$this->system->loadModel('resources/msgbox');
            $oMsg->delTrackMsg($_POST['deltrack']);
            $this->splash('success', $this->system->mkUrl("member","track"), __('删除成功'));
        }else{
            $this->splash('failed', $this->system->mkUrl("member","track"), __('删除失败: 没有选中任何记录！'));
        }
    }

    function delOutBoxMsg() {
        if(!empty($_POST['deloutbox'])){
            $oMsg = &$this->system->loadModel('resources/msgbox');
            $oMsg->delOutBoxMsg($_POST['deloutbox']);
            $this->splash('success', $this->system->mkUrl("member","outbox"), __('删除成功'));
        }else{
            $this->splash('failed', $this->system->mkUrl("member","outbox"), __('删除失败: 没有选中任何记录！'));
        }
    }

    function send($nMsgId=false, $status='send') {
        if($nMsgId){
            $oMsg = &$this->system->loadModel('resources/msgbox');
            $this->pagedata['init'] = $oMsg->getMsgInfo($nMsgId, $status);
            $this->pagedata['msg_id'] = $nMsgId;
        }
        $this->_output();
    }

    function message($nMsgId=false, $status='send') { //给管理员发信件
        $oMem = &$this->system->loadModel('member/member');
        if($nMsgId){
            $oMsg = &$this->system->loadModel('resources/msgbox');
            $this->pagedata['init'] = $oMsg->getMsgInfo($nMsgId, $status);
            $this->pagedata['msg_id'] = $nMsgId;
        }
        $this->pagedata['mem_info'] = $oMem->getMemberInfo($this->member['member_id']);
        $this->_output();
    }

    function sendMsgToOpt(){ //给管理员发信息(老系统中的留言功能)

        $_POST['message'] = htmlspecialchars($_POST['message']);
        $oMsg = &$this->system->loadModel('resources/msgbox');
        $nOpId = $oMsg->getOpId();
        $aTemp = array( 'subject'=>$_POST['subject'],
                        'msg_from'=>$this->member['uname'],
                        'from_type'=>0,
                        'to_type'=>1,
                        'msg_id'=>$_POST['msg_id']!=''?$_POST['msg_id']:false,
                        'folder'=>isset($_POST['outbox']) && $_POST['outbox']==1?'outbox':'inbox'
            );
        if($oMsg->sendMsg($this->member['member_id'],$nOpId,$_POST['message'],$aTemp,1)){
            $this->splash('success',$this->system->mkUrl('member','message'),__('发送成功，请等待管理员回复！'));
            //$this->redirect('member','message');
        }else{
            echo __('留言提交失败！');
        }
    }

    function sendMsg(){
        foreach($_POST as $ke=>$ve){
            $_POST[$ke] = htmlspecialchars($ve);
        }
        if($_POST['msg_to'] && $_POST['subject'] && $_POST['message']) {
            $oMsg = &$this->system->loadModel('resources/msgbox');
            $sToId = $oMsg->getMemIdByUName($_POST['msg_to']);
            if($sToId) {
                $aTemp = array( 'subject'=>$_POST['subject'],
                                'msg_from'=>$this->member['uname'],
                                'from_type'=>0,
                                'to_type'=>0,
                                'msg_id'=>$_POST['msg_id']!=''?$_POST['msg_id']:false,
                                'folder'=>isset($_POST['outbox']) && $_POST['outbox']==1?'outbox':'inbox'
                    );
                if($oMsg->sendMsg($this->member['member_id'],$sToId,$_POST['message'],$aTemp)) {
                    $this->splash('success',$this->system->mkUrl('member','index'),__('发送成功！'));
                } else {
                    echo  __('发送失败！'); //todo:转向错误页面
                }
            } else {
                echo  __('找不到你填写的用户！');
            }
        } else {
            echo  __('必填项不能为空！');
        }
    }

    function security($type = ''){
        $passport = &$this->system->loadModel('member/passport');
        if ($obj=$passport->function_judge('ServerClient')){
            $obj->ServerClient('security');
        }
        $oMem = &$this->system->loadModel('member/member');
        $this->pagedata['mem'] = $oMem->getFieldById($this->member['member_id'], array('pw_question'));
        $this->pagedata['type'] = $type;
        $this->_output();
    }

    function saveSecurity(){
        $this->begin($this->system->mkUrl('member','security'));
        $oMem = &$this->system->loadModel('member/account');
        
        // 安全过滤
        array_key_filter($_POST, 'old_passwd,passwd,passwd_re');
        
        $result = $oMem->saveSecurity($this->member['member_id'],$_POST,$msg);
        $this->end($result,__($msg));
    }

    function saveSecurityIssue(){
        $this->begin($this->system->mkUrl('member','security',array("1")));
        $oMem = &$this->system->loadModel('member/account');
        $this->end($oMem->saveSecurity($this->member['member_id'],$_POST),__('安全问题修改成功'));

/*         $this->begin($this->system->mkUrl('member','couponExchange')); */
/*         $this->end(true, __('添加成功'), $this->system->mkUrl('member','couponExchange'));         */
    }

    function receiver(){
        $oMem = &$this->system->loadModel('member/member');
        $this->pagedata['receiver'] = $oMem->getMemberAddr($this->member['member_id']);
        $this->pagedata['is_allow'] = (count($this->pagedata['receiver'])<5 ? 1 : 0);
        $this->_output();
    }

    //添加收货地址
    function addReceiver(){
        $oMem = &$this->system->loadModel('member/member');
        if($oMem->isAllowAddr($this->member['member_id'])){
        $this->_output();
        }else{
            echo __('不能新增收货地址');
        }
    }

    function insertRec(){
        $oMem = &$this->system->loadModel('member/member');
        if(!$oMem->isAllowAddr($this->member['member_id'])){
            echo __('不能新增收货地址');
            return false;
        }
        foreach($_POST as $ke=>$ve){
            $_POST[$ke] = strip_tags($ve);
        }
        if($oMem->insertRec($_POST,$this->member['member_id'],$message)){
            $this->redirect('member','receiver');
        }
        $this->_output();
    }

    //设置默认地址
    function setDefault($addrId,$disabled){
        $this->begin($this->system->mkUrl('member','receiver'));
        $oMem = &$this->system->loadModel('member/member');
        $member_id = $this->member['member_id'];
        if($oMem->setToDef($addrId,$member_id,$message,$disabled)){
            $this->redirect('member','receiver');
        }
        trigger_error($message,E_USER_ERROR);
        $this->end(false,__('修改失败'),$this->system->mkUrl('member','receiver'));

    }
    //修改收货地址
    function modifyReceiver($addrId){
        $oMem = &$this->system->loadModel('member/member');
        if($aRet = $oMem->getAddrById($addrId, $this->member['member_id'])){
            $aRet['defOpt'] = array('0'=>__('否'), '1'=>__('是'));
            $this->pagedata = $aRet;
        }else{
            $this->system->error(404);
            exit;
        }

        $this->_output();
    }

    function saveRec(){
        $this->begin($this->system->mkUrl('member','modifyReceiver',array($_POST['addr_id'])));
        $oMem = &$this->system->loadModel('member/member');
        foreach($_POST as $ke=>$ve){
            $_POST[$ke] = strip_tags($ve);
        }
        if($oMem->saveRec($_POST,$this->member['member_id'],$message)){
            $this->redirect('member','receiver');
        }
        trigger_error($message, E_USER_ERROR);
        $this->end(false,__('修改失败'),$this->system->mkUrl('member','modifyReceiver',array($_POST['addr_id'])));
    }

    //删除收货地址
    function delRec($addrId){
        $oMem = &$this->system->loadModel('member/member');
        if($oMem->delRec($addrId)){
            $this->redirect('member','receiver');
        }
        $this->_output();
    }

    function score(){
        $this->_output();
    }

    function init(){
        $account = &$this->system->loadModel('member/account');
        $this->_restoreAction();
    }

    function saveMember(){
        foreach($_POST as $kec=>$kev){
            $_POST[$kec] = strip_tags($kev);
        }
 
        for($i=0;$i<=100;$i++){
            if(preg_match('/^(19|20)[0-9]{2}-([1-9]|0[1-9]|1[012])-([1-9]|0[1-9]|[12][0-9]|3[01])+$/',$_POST[$i])){
                $_POST[$i] = strtotime($_POST[$i]);
            }
        }

        $post = array_keys($_POST);
        for($i=0;$i<count($post);$i++){
           if(is_numeric($post[$i])){
                $custom[] = $post[$i];
           }
        }
        
        array_key_filter($_POST, 'area,addr,name,mobile,tel,zip,sex,date,pw_question,pw_answer,cur,email,birthday,b_year,b_month,b_day,is_register,def_addr,plugUrl,'.implode(',',$custom));
        
        $memc_da = $_POST;
       
        $memc_da['uname'] = $_COOKIE['UNAME'];
       
        $this->system->setcookie('CUR',$_POST['cur'],null);
        $toolsMdl = &$this->system->loadModel('utility/tools');
        $oMem = &$this->system->loadModel('member/member');
        if ( $_POST['name'] && !$toolsMdl->check_name($_POST['name']) ){
            $this->splash('failed', $this->system->mkUrl("member","setting"), __('姓名包含非法字符!'));
        }
        if( $_POST['email'] && !$toolsMdl->is_email($_POST['email']) ){
            $this->splash('failed', $this->system->mkUrl("member","setting"), __('请填写正确格式的电子邮件地址'));
        }
        
        if($_POST['birthday']){
            list($_POST['b_year'], $_POST['b_month'],$_POST['b_day']) = explode('-', $_POST['birthday']);
        }
       
        $mobile = $oMem->getBasicInfoById($this->member['member_id']); //获取数据库的注册信息
        
        if( !$oMem->save($this->member['member_id'],$_POST) ) {
          $this->splash('failed', $this->system->mkUrl("member","setting"), __('提交失败'));
        }

        // 发送短信和邮件通知
        if( !$_POST['is_register'] && $memc_da['passwd'] ) {
           $this->system->loadModel("member/account")->fireEvent('register',$memc_da,$this->member['member_id']);
        }

        //如果和注册符合收货条件的，存为默认收货地址
        if($_POST['is_register'] && $_POST['name'] && ($_POST['tel'] || $_POST['mobile']) && $_POST['addr']){
            if($_POST['area']){  //检查注册的地址中是否包含省县区信息
                list($a,$area,$c) = explode(':',$_POST['area']);
                list($state,$city,$district) = explode('/',$area);
                if(!strpos($_POST['addr'],$state) && !strpos($_POST['addr'],$city) && !strpos($_POST['addr'],$district)){

                    if( false === strpos($city,$state) ){   //省跟市名称一样时 地址中不需要取省份
                        $_POST['addr'] = $state.$city.$district.$_POST['addr'];
                    }else{
                        $_POST['addr'] = $city.$district.$_POST['addr'];
                    }
                }
            }
            $_POST['def_addr'] = 1;
            $member = &$this->system->loadModel('member/member');
            $member->insertRec($_POST, $this->member['member_id']);
        }
        
        $allkeys = array_keys($_POST);
        $count = 0;
        for($i=0;$i<count($allkeys);$i++){
            if(is_numeric($allkeys[$i])){
                if(!is_array($_POST[$allkeys[$i]])){
                    $memattr[$count]['member_id'] = $this->member['member_id'];
                    $memattr[$count]['attr_id'] = $allkeys[$i];
                    $memattr[$count]['value'] = htmlspecialchars($_POST[$allkeys[$i]]);
                    $oMem->updateMemAttr($this->member['member_id'],$allkeys[$i],$memattr[$count]);
                    $count++;
                }else{
                    $tmp = $_POST[$allkeys[$i]];
                    $oMem->deleteMattrvalues($allkeys[$i],$this->member['member_id']);
                    for($j=0;$j<count($tmp);$j++){
                        $tmpdate['member_id'] = $this->member['member_id'];
                        $tmpdate['attr_id'] = $allkeys[$i];
                        $tmpdate['value'] = htmlspecialchars($tmp[$j]);
                        $oMem->saveMemAttr($tmpdate);
                    }
                }
            }
        }
        
        $this->splash('success', $this->system->mkUrl("member") , __('提交成功'));
    }

    function _output(){

        if($GLOBALS['runtime']['member_lv']){
            $oLevel = &$this->system->loadModel('member/level');
            $aLevel = $oLevel->getFieldById($GLOBALS['runtime']['member_lv']);
            if($aLevel['disabled']=='false'){
                $this->member['levelname'] = $aLevel['name'];
            }
        }
        
        $oSex = &$this->system->loadModel('member/member');
        $aSex = $oSex->getFieldById($this->member['member_id']);

        $this->member['name'] = $aSex['name'];
        $this->pagedata['member'] = $this->member;
        
        $this->pagedata['sex'] = $aSex['sex'];
        $this->pagedata['cpmenu'] = $this->map;
        $this->pagedata['current'] = $this->_action;
        $this->pagedata['_PAGE_']=$this->pagedata['_PAGE_']?'member/'.$this->pagedata['_PAGE_']:'member/'.$this->_tmpl;


        $this->pagedata['_MAIN_'] = 'member/main.html';

        parent::output();
    }

    function exchange($cpnsId) {
        $this->begin($this->system->mkUrl('member','couponExchange'));

        $oCoupon = &$this->system->loadModel('trading/coupon');
        $memberId = intval($this->member['member_id']);//会员id号

        if ($memberId) {
            if (!$oCoupon->exchange($memberId, $cpnsId)) {
                trigger_error(__('兑换失败,原因:积分不足/兑换购物券无效...'));
            }
        }else {
            trigger_error(__('没有登录'),E_USER_ERROR);
        }
        $this->end(true, __('添加成功'), $this->system->mkUrl('member','couponExchange'));
    }

    function downloadAdvanceLog(){
        $charset = &$this->system->loadModel('utility/charset');
        $oMem = &$this->system->loadModel('member/advance');
        $aData = $oMem->getListByMemId($this->member['member_id']);
        header('Pragma: no-cache, no-store');
        header("Expires: Wed, 26 Feb 1997 08:21:57 GMT");
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=advance_".date("Ymd").".csv");
        $out = __("事件,存入金额,支出金额,当前余额,时间\n");
        foreach($aData as $v){
            $out .= $v['memo'].",".$v['import_money'].",".$v['explode_money'].",".$v['member_advance'].",".date("Y-m-d H:m",$v['mtime'])."\n";
        }
        echo $charset->utf2local($out,'zh');
  }

    function addOrderMsg( $orderId, $msgType = 0 ){
        $objOrder = $this->system->loadModel('trading/order');
        $aOrder = $objOrder->load($orderId);
        $this->_verifyMember($aOrder['member_id']);
        $timeHours = array();
        for($i=0;$i<24;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeHours[$v] = $v;
        }
        $timeMins = array();
        for($i=0;$i<60;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeMins[$v] = $v;
        }
        $this->pagedata['orderId'] = $orderId;
        $this->pagedata['msgType'] = $msgType;
        $this->pagedata['timeHours'] = $timeHours;
        $this->pagedata['timeMins'] = $timeMins;
        $this->_output();
    }

    function toAddOrderMsg(){
        $this->begin($this->system->mkUrl('member','orderdetail',array($_POST['msg']['orderid'])));
        $oOrder = &$this->system->loadModel('trading/order');
        $data = array();
        if($_POST['msg']['msgType'] == 1){
            $data['subject'] = __('订单 ').$_POST['msg']['orderid'].__(' 付款通知，请核实');
            $data['message'] = __('我已经于 ').$_POST['msg']['paydate'][0].' '.$_POST['msg']['paydate'][1].':'.$_POST['msg']['paydate'][2].__(' 通过 ').$_POST['msg']['payments'].__(' 支付 ').$_POST['msg']['paymoney'].__(' 元，订单号码：').$_POST['msg']['orderid'].__(' ，请尽快核实。').__("\n备注：").$_POST['msg']['message'];
            $data['msg_type'] = 'payment';
        }else{
            $data['subject'] = $_POST['msg']['subject'];
            $data['message'] = $_POST['msg']['message'];
        }
        $data['rel_order'] = $_POST['msg']['orderid'];
        $data['date_line'] = time();
        $data['msg_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['msg_from'] = $this->member['uname'];
        $data['from_id'] = $this->member['uid'];

        $aOrder = $oOrder->getFieldById($_POST['msg']['orderid'], array('total_amount','is_tax','member_id'));
        $eventData['order_id'] = $_POST['msg']['orderid'];
        $eventData['total_amount'] = $aOrder['total_amount'];
        $eventData['is_tax'] = $aOrder['is_tax'];
        $eventData['member_id'] = $aOrder['member_id'];
        $oOrder->fireEvent('add_message', $eventData);
        $data['message'] = htmlspecialchars($data['message']);
        $data['subject'] = htmlspecialchars($data['subject']);
        $this->end($oOrder->addOrderMsg($data),__('留言成功'));
    }

    function _mkform($arr,&$result,$depth){
        foreach($arr as $k=>$v){
            $newDepth = array_merge($depth,array($k));
            if(is_array($v)){
                $this->_mkform($v,$result,$newDepth);
            }else{
                if(count($newDepth)>1)
                    $result[array_shift($newDepth).'['.implode('][',$newDepth).']'] = $v;
                else
                    $result[$k] = $v;
            }
        }
    }

    function _restoreAction(){

        if(isset($_REQUEST['url']))
            $query = $this->system->request['base_url'].'m/'.$_REQUEST['url'];
        else{
            $query = $this->system->request['base_url'].'m/';
        }
        //将登录前的url改为登录后的url，todo:抽象出系统方法，目前情况需要验证 /zh_CN/时是否正确
        //      $query = $actmapper->appendUrl('m/'.substr($query,max(array(strrpos($query,'?'), strrpos($query,'/')))+1));

        if(!isset($_POST['form'])){
            echo "<header><meta http-equiv=\"refresh\" content=\"0; url={$query}\"></header>";
            exit();
        }else{
            $this->_mkform(unserialize(get_magic_quotes_gpc()?stripcslashes($_POST['form']):$_POST['form']),$form,array());
            foreach($form as $k=>$v){
                $post .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
            }

            $html=<<<EOF
<html><head><title>Redirecting...</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/><meta name=”robots” content=”noindex,noarchive,follow” /></head>
<body>
    <form method="post" action="{$query}" id="redirect" >
    {$post}
    </form>
    <script>document.getElementById('redirect').submit();</script>
</body>
</html>
EOF;
            echo $html;
            exit();
        }
    }

    function rec_htmlspecialchars($aData){
        if(is_array($aData)){
            foreach ($aData as $key => $value){
                if (!is_array($value)){
                    $aData[$key] = htmlspecialchars($value);
                }
                else{
                    $this->rec_htmlspecialchars($value);
                }
            }
        }
        else
            $aData = htmlspecialchars($aData);
        return $aData;
    }


}

