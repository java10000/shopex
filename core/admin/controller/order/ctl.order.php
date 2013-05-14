<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_olist
 *
 * @uses adminPage
 * @package
 * @version $Id: ctl.order.php 2009 2008-04-28 11:27:56Z alex $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
include_once('delivercorp.php');
include_once('objectPage.php');
class ctl_order extends objectPage{
    var $filter = array('order_refer'=>array('local','mobile'));
    var $finder_action_tpl = 'order/finder_action.html';
    var $finder_filter_tpl = 'order/finder_filter.html';
    var $detail_title = 'order/detail_title.html';
    var $workground = 'order';
    var $ioType = 'order';
    var $object = 'trading/order';
    var $finder_default_cols = '_cmd,order_id,order_refer,createtime,total_amount,ship_name,pay_status,ship_status,shipping,print_status,payment,member_id';
    function index($operate){

        if($operate=='admin'){
            $this->system->set_op_conf('ordertime',time());
        }
        parent::index();
    }

    function do_export($io='csv'){
        $this->system->__session_close(0);
        if($io !='taobaoordercsv' && $io !='taobaogoodscsv'){
            parent::do_export($io);
            exit;
        }
        
        include_once('shopObject.php');
        $step = 20;
        $offset=0;
        $dataio = $this->system->loadModel('system/dataio');
        $taobaoordercsv = $this->system->loadModel('trading/taobaoordercsv');
        $cols = $dataio->columns($io);
        $list = $this->model->getList($cols,$_POST,$offset,$step,array('order_id','ASC'));
        $count = $this->model->count($_POST);
        if($io == 'taobaoordercsv'){//淘宝CSV格式 by yangbing 2009-11-4
            while($count>$offset){
                if($offset==0){
                    $keys = $taobaoordercsv->orderExportTitle();
                    $dataio->export_begin($io,$keys,'ExportOrderList',$count);
                }
                foreach($list as $v){
                    //解决导出付款时间不准确的问题 by fuxiudong 2013-04-11
                    if($v['acttime']){
                        $rows_ = $this->model->db->selectrow("SELECT max(t_end) as t_end FROM  sdb_payments WHERE order_id=".$this->model->db->quote($v['order_id'])." AND status='succ'");  
                        $v['acttime'] = $rows_['t_end'];
                    }                    
                    $data = $taobaoordercsv->getOrdersExportData($v);
                    $dataio->export_rows($io,$data);
                }
                $offset+=$step;
                $list = $this->model->getList($cols,$_POST,$offset,$step,array('order_id','ASC'));
            }
        }elseif($io == 'taobaogoodscsv'){
            while($count>$offset){
                if($offset==0){
                    $keys = $taobaoordercsv->goodsExportTitle();
                    $dataio->export_begin($io,$keys,'ExportOrderDetailList',$count);
                }
                $setMemo = true;
                foreach($list as $v){
                    $rows = $this->model->db->select('SELECT name, price, nums, addon FROM sdb_order_items WHERE order_id='.$this->model->db->quote($v['order_id']));
                    $order = $this->model->db->selectRow('SELECT memo FROM sdb_orders WHERE order_id='.$this->model->db->quote($v['order_id']));
                    $export['order_id'] = $v['order_id'];
                    foreach($rows as $delivery){
                        $export['name'] = $delivery['name'];
                        $export['price'] = $delivery['price'];
                        $export['nums'] = $delivery['nums'];
                        $export['sysid'] = '';
                        $addon = unserialize($delivery['addon']);
                        $export['pdt_desc'] = $addon['spec'][1];
                        $export['pdt_desc'] .= $addon['spec'][2]?'、'.$addon['spec'][2]:'';
                        $export['tinfo'] = '';
                        $export['memo'] = $setMemo?$order['memo']:'';
                        $setMemo = false;
                        $data[] = $export;
                    }
                    $dataio->export_rows($io,$data);
                    unset($data);
                }
                $offset+=$step;
                $list = $this->model->getList($cols,$_POST,$offset,$step,array('order_id','ASC'));
                 error_log(var_export($list,1),3,__FILE__.".b.log");
            }
        }else{
            ;
        }
         error_log(var_export($io,1),3,__FILE__.".io.log");
        $dataio->export_finish($io);
    }


    function new_order_message_list(){
        $oShopbbs = $this->system->loadModel('resources/shopbbs');
        $order_list = $oShopbbs->getNewOrderMessage(true);
        if(empty($order_list))
        $order_list = 0;
        $params['order_id'] = array('v'=>$order_list,'t'=>'最新留言订单');
        $_GET['filter'] = serialize($params);
        parent::index();
    }

    function _views(){
        return array(
            __('全部')=>"",
            __('未处理')=>array('pay_status'=>array('0'),'ship_status'=>array('0'),'status'=>'active'),
            __('已付款待发货')=>array('pay_status'=>array('1','2','3'),'ship_status'=>array('0','2'),'status'=>'active'),
            __('已发货')=>array('ship_status'=>array('1'),'status'=>'active'),
            __('已完成')=>array('status'=>'finish'),
            __('已退款')=>array('pay_status'=>array('4','5'),'status'=>'active'),
            __('已退货')=>array('ship_status'=>array('3','4'),'status'=>'active'),
            __('已作废')=>array('status'=>'dead')
        );
    }

    function save_addr($order_id){
        $data = array(
            'ship_name'=>$_POST['order']['ship_name'],
            'ship_area'=>$_POST['order']['ship_area'],
            'ship_zip'=>$_POST['order']['ship_zip'],
            'ship_addr'=>$_POST['order']['ship_addr'],
            'ship_mobile'=>$_POST['order']['ship_mobile'],
            'ship_tel'=>$_POST['order']['ship_tel'],
            'memo'=>$_POST['order']['order_memo'],
        );
        if($this->model->update($data,array('order_id'=>$order_id))){
            echo 'ok';
        }
    }

    function printing($type,$order_id){
        $order = &$this->system->loadModel('trading/order');
        $member = &$this->system->loadModel('member/member');
        $dbTmpl = &$this->system->loadModel('content/systmpl');
        $order->setPrintStatus($order_id,$type,true);
        $print_id = $order->getPrintId($order_id);

        $orderInfo = $order->getFieldById($order_id);
        $orderInfo['self'] = 0-$orderInfo['discount']-$orderInfo['pmt_amount'];
        $goodsItem = $order->getItemList($order_id);
        $memberInfo = $member->getFieldById($orderInfo['member_id'],array("point"));
        foreach($goodsItem as $k => $rows){
            $goodsItem[$k]['addon'] = unserialize($rows['addon']);
            if($rows['minfo'] && unserialize($rows['minfo'])){
                $goodsItem[$k]['minfo'] = unserialize($rows['minfo']);
            }else{
                $goodsItem[$k]['minfo'] = array();
            }
        }
        $giftsItem = $order->getGiftItemList($order_id);
        $orderSum = $order->sumOrder($orderInfo['member_id']);
        $orderSum['sum'] = $orderSum['sum']?$orderSum['sum']:0;

        $this->pagedata['goodsItem'] = $goodsItem;
        $this->pagedata['giftsItem'] = $giftsItem;
        $this->pagedata['orderSum'] = $orderSum;
        $this->pagedata['memberPoint'] = $memberInfo['point']?$memberInfo['point']:0;
        $this->pagedata['storeplace_display_switch'] = $this->system->getConf('storeplace.display.switch');

        $this->pagedata['shop'] = array(
            'name'=>$this->system->getConf('system.shopname'),
            'url'=>$this->system->getConf('store.shop_url'),
            'email'=>$this->system->getConf('store.email'),
            'tel'=>$this->system->getConf('store.telephone'),
            'logo'=>$this->system->getConf('site.logo')
        );

        switch($type){
            case ORDER_PRINT_CART:  /*购物清单*/
                $this->pagedata['printType'] = array("cart");
                $this->pagedata['printContent']['cart'] = true;
                $this->pagedata['print_head_title'] = '购物清单打印';
                $this->pagedata['print_head_title1'] = '购物清单打印';

                $this->pagedata['goodsItem'] = $goodsItem;
                $this->pagedata['giftsItem'] = $giftsItem;
                $this->pagedata['orderInfo'] = $orderInfo;

                $this->pagedata['orderSum'] = $orderSum;
                $this->pagedata['memberPoint'] = $memberInfo['point']?$memberInfo['point']:0;

                $this->pagedata['print_cart_content']=$dbTmpl->fetch('../admin/view/order/print_cart',$this->pagedata);

                $this->display('order/print.html');
                break;

            case ORDER_PRINT_SHEET:    /*配货单*/
                $this->pagedata['printContent']['sheet'] = true;
                $this->pagedata['print_head_title'] = '配货单打印';
                $this->pagedata['print_head_title2'] = '配货单打印';

                $this->pagedata['goodsItem'] = $goodsItem;
                $this->pagedata['giftsItem'] = $giftsItem;
                $this->pagedata['orderInfo'] = $orderInfo;

                $this->pagedata['orderSum'] = $orderSum;
                $this->pagedata['memberPoint'] = $memberInfo['point']?$memberInfo['point']:0;

                $this->pagedata['print_sheet_content']=$dbTmpl->fetch('../admin/view/order/print_sheet',$this->pagedata);

                $this->display('order/print.html');
                break;

            case ORDER_PRINT_MERGE:    /*联合打印*/
                $this->pagedata['printType'] = array("cart");
                $this->pagedata['printContent']['cart'] = true;
                $this->pagedata['print_head_title'] = '联合打印';

                $this->pagedata['printContent']['sheet'] = true;

                $this->pagedata['goodsItem'] = $goodsItem;
                $this->pagedata['giftsItem'] = $giftsItem;
                $this->pagedata['orderInfo'] = $orderInfo;
                $this->pagedata['print_head_title1'] = '购物清单打印';
                $this->pagedata['print_head_title2'] = '配货单打印';

                $this->pagedata['orderSum'] = $orderSum;
                $this->pagedata['memberPoint'] = $memberInfo['point']?$memberInfo['point']:0;

                $this->pagedata['print_cart_content']=$dbTmpl->fetch('../admin/view/order/print_cart',$this->pagedata);
                $this->pagedata['print_sheet_content']=$dbTmpl->fetch('../admin/view/order/print_sheet',$this->pagedata);

                $this->display('order/print.html');
                break;

            case ORDER_PRINT_DLY:    /*物流单打印*/
                $printer = &$this->system->loadModel('trading/dly_centers');
                $this->pagedata['dly_centers'] = $printer->getList('dly_center_id,name',array('disable'=>'false'),0,100);
                $this->pagedata['default_dc'] = $this->system->getConf('system.default_dc');
                $this->pagedata['orderInfo'] = $orderInfo;
                $this->pagedata['the_dly_center'] = $printer->instance($this->pagedata['default_dc']?$this->pagedata['default_dc']:$this->pagedata['dly_centers'][0]['dly_center_id']);

                $printer = &$this->system->loadModel('trading/dly_printer');
                $this->pagedata['printers'] = $printer->getList('prt_tmpl_id,prt_tmpl_title',array('shortcut'=>'true'));

                $this->display('order/print_dly.html');
                break;
            default:
                echo __('无效的打印类型');
                break;
        }
    }

    function _detail(){
        return array(
            'detail_info'=>array('label'=>__('基本信息'),'tpl'=>'order/order_detail.html'),
            'detail_items'=>array('label'=>__('商品'),'tpl'=>'order/od_items.html'),
            'detail_bills'=>array('label'=>__('收退款记录'),'tpl'=>'order/od_bill.html'),
            'detail_delivery'=>array('label'=>__('收发货记录'),'tpl'=>'order/od_delivery.html'),
            'detail_pmt'=>array('label'=>__('优惠方案'),'tpl'=>'order/od_pmts.html'),
            'detail_mark'=>array('label'=>__('订单备注'),'tpl'=>'order/od_mark.html'),
            'detail_logs'=>array('label'=>__('订单日志'),'tpl'=>'order/od_logs.html'),
            'detail_msg'=>array('label'=>__('顾客留言'),'tpl'=>'order/od_msg.html'),
        );
    }

    function detail_items($orderid){    //订单明细
        $order = &$this->system->loadModel('trading/order');
        $this->pagedata['orderid'] = $orderid;
        $aItems = $order->getItemList($orderid);
        foreach($aItems as $k => $rows){
            $aItems[$k]['addon'] = unserialize($rows['addon']);
            if($rows['minfo'] && unserialize($rows['minfo'])){
                $aItems[$k]['minfo'] = unserialize($rows['minfo']);
            }else{
                $aItems[$k]['minfo'] = array();
            }
            if($aItems[$k]['addon']['adjname']) $aItems[$k]['name'] .= __('<br>配件：').$aItems[$k]['addon']['adjname'];
        }
        $this->pagedata['goodsItems'] = $aItems;

        $aGiftItems = $order->getGiftItemList($orderid);
        $this->pagedata['giftItems'] = $aGiftItems;
    }

    function detail_bills($orderid){    //订单单据
        $objPayment = &$this->system->loadModel('trading/payment');
        $aBill = $objPayment->getSuccOrderBillList($orderid);
        $objRefund = &$this->system->loadModel('trading/refund');
        $aRefund = $objRefund->getOrderBillList($orderid);
        $this->pagedata['bills'] = $aBill;
        $this->pagedata['refunds'] = $aRefund;
        $this->pagedata['orderid'] = $orderid;
    }

    function detail_delivery($orderid){
        $objDelivery = &$this->system->loadModel('trading/delivery');
        $this->pagedata['consign'] = $objDelivery->getConsignList($orderid);
        $this->pagedata['reship'] = $objDelivery->getReshipList($orderid);
        $this->pagedata['orderid'] = $orderid;
    }

    function detail_pmt($orderid){    //订单促销
        $order = &$this->system->loadModel('trading/order');
        $aPmt = $order->getPmtList($orderid);
        $aItems = $order->getItemList($orderid);
//echo $aItems[0]['nums'];exit();
        foreach($aPmt as $key=>$val){
        $aPmt[$key]['pmt_amount']=$aPmt[$key]['pmt_amount'];
        }
        $this->pagedata['pmtlist'] = $aPmt;
        $this->pagedata['orderid'] = $orderid;
    }

    function detail_mark($orderid){    //订单备注
        $order = &$this->system->loadModel('trading/order');
        $aOrder = $order->getFieldById($orderid, array('mark_text','mark_type'));
        $this->pagedata['mark_text'] = $aOrder['mark_text'];
        $this->pagedata['mark_type'] = $aOrder['mark_type'];
        $this->pagedata['orderid'] = $orderid;
    }

    function saveMarkText(){
        $this->begin('index.php?ctl=order/order&act=detail&p[0]='.$_POST['orderid'].'&p[1]=detail_mark');
        $order = &$this->system->loadModel('trading/order');
        $this->end($order->saveMarkText($_POST['orderid'], $_POST),__('保存成功'));
    }

    function detail_logs($orderid, $page=1){    //订单LOG
        $order = &$this->system->loadModel('trading/order');
        $pageLimit = 30;
        $aLog = $order->getOrderLogList($orderid, $page-1, $pageLimit);
        
        $this->pagedata['logs'] = $aLog;
        $this->pagedata['result'] = array('success'=>__('成功'),'failure'=>__('失败'));
        $pager = array(
            'current'=> $page,
            'total'=> ceil($aLog['page']),
            'link'=> 'javascript:W.page(\'index.php?ctl=order/order&act=detail_logs&p[0]='.$orderid.'&p[1]=_PPP_\', {update:$E(\'.tableform\').parentNode, method:\'post\'});',
            'token'=> '_PPP_'
        );
        $this->pagedata['pager'] = $pager;
        $this->pagedata['pagestart'] = ($page-1)*$pageLimit;
    }

    function saveOrderMsgText(){
        $this->begin('index.php?ctl=order/order&act=detail&p[0]='.$_POST['orderid'].'&p[1]=detail_msg');
        $order = &$this->system->loadModel('trading/order');
        $oMsg = $this->system->loadModel('resources/message');
        $orderMsg = $oMsg->getOrderMessage($_POST['orderid']);
        foreach( $orderMsg as $mk => $mv){
            $oMsg->setReaded($mv['msg_id']);
        }
        $data = $_POST['msg'];
        $data['rel_order'] = $_POST['orderid'];
        $data['unread'] = '1';
        $data['date_line'] = time();
        $data['msg_from'] = __('管理员');
        $data['from_type'] = '1';
        $data['message'] = htmlspecialchars($data['message']);

        $aOrder = $order->getFieldById($_POST['orderid'], array('total_amount','is_tax','member_id'));
        $eventData['order_id'] = $_POST['orderid'];
        $eventData['total_amount'] = $aOrder['total_amount'];
        $eventData['is_tax'] = $aOrder['is_tax'];
        $eventData['member_id'] = $aOrder['member_id'];
        $order->fireEvent('reply_message', $eventData);

        $this->end($order->addOrderMsg($data),__('保存成功'));
    }

    function detail_msg( $orderid ){
        $order = &$this->system->loadModel('trading/order');
        $oMsg = &$this->system->loadModel('resources/message');
        $orderMsg = $oMsg->getOrderMessage($orderid);
        $oMsg->sethasreaded($orderid);
        $aItems = $order->getItemList($orderid);
        foreach($aItems as $k => $rows){
            $aItems[$k]['addon'] = unserialize($rows['addon']);
            if($rows['minfo'] && unserialize($rows['minfo'])){
                $aItems[$k]['minfo'] = unserialize($rows['minfo']);
            }else{
                $aItems[$k]['minfo'] = array();
            }
            if($aItems[$k]['addon']['adjname']) $aItems[$k]['name'] .= __('<br>配件：').$aItems[$k]['addon']['adjname'];
        }
        $this->pagedata['ordermsg'] = $orderMsg;
        $this->pagedata['goodsItems'] = $aItems;
        $this->pagedata['orderid'] = $orderid;

    }

    function get_m_d_y($tb_pay_time){
        $day =  floor($tb_pay_time/(24*3600));
        $tmp_hour  = $tb_pay_time%(24*3600);
        $hour = floor($tmp_hour/3600);
        $tmp_minute = floor($tmp_hour%3600);
        $minute =      floor($tmp_minute/60);
        $second =     floor($tmp_minute%60);
        $arr['day_time'] = $day."天".$hour."小时".$minute."分".$second."秒";
        $arr['day'] = $day;
        return $arr;
    }

    function detail_info($order_id){    //订单详细信息
        $order = &$this->system->loadModel('trading/order');
        $aOrder = $order->getFieldById($order_id);

        $oCur = &$this->system->loadModel('system/cur');
        $aCur = $oCur->getSysCur();
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];

        if (intval($aOrder['payment'])<0)
            $aOrder['payment'] = '货到付款';
        else{
            $payment = &$this->system->loadModel('trading/payment');
            $aPayment = $payment->getPaymentById($aOrder['payment']);
            $payid=$aOrder['payment'];
            $aOrder['payment'] = $aPayment['custom_name'];
            $aOrder['extendCon'] = $payment->getExtendCon($aOrder['extend'],$payid);
        }

        if($aOrder['member_id']){
            $member = &$this->system->loadModel('member/member');
            $aOrder['member'] = $member->getFieldById($aOrder['member_id'], array('name','uname','mobile','tel','addr','email','area','remark'));
        }
        $aItems = $order->getItemList($order_id);
        $gItems=$order->getGiftItemList($order_id);

        foreach($aItems as $k => $rows){
            $aItems[$k]['addon'] = unserialize($rows['addon']);
            if($rows['minfo'] && unserialize($rows['minfo'])){
                $aItems[$k]['minfo'] = unserialize($rows['minfo']);
            }else{
                $aItems[$k]['minfo'] = array();
            }
            if($aItems[$k]['addon']['adjname']) $aItems[$k]['name'] .= __('<br>配件：').$aItems[$k]['addon']['adjname'];
        }
        $this->pagedata['goodsItems'] = $aItems;
        $this->pagedata['giftItems'] = $gItems;

        $aOrder['discount'] = 0 - $aOrder['discount'];
        $this->pagedata['order'] = $aOrder;
        //+todo license权限----------
        $_is_all_ship = 1;
        $_is_all_return_ship = 1;

        foreach($aItems as $_item){
            if((!$_item['supplier_id']) && ($_item['sendnum'] < $_item['nums'] )){
                $_is_all_ship = 0;
            }
            if((!$_item['supplier_id']) && ($_item['sendnum'] > 0 )){
                $_is_all_return_ship = 0;
            }
        }
        foreach($gItems as $g_item){
            if($g_item['sendnum'] < $g_item['nums'] ){
                $_is_all_ship = 0;
            }
            if($g_item['sendnum'] > 0 ){
                $_is_all_return_ship = 0;
            }
        }
        $this->pagedata['order']['_is_all_ship'] = $_is_all_ship;
        $this->pagedata['order']['_is_all_return_ship'] = $_is_all_return_ship;

        $Mem = &$this->system->loadModel('member/member');
        $Memattr = &$this->system->loadModel('member/memberattr');
        $nowmember =  $Memattr->getAlloption($aOrder['member_id']);
        $tree = $Mem->getContactObject($aOrder['member_id']);
        $this->pagedata['tree'] = $tree;
        $this->pagedata['recy'] = $aOrder['disabled'];
        $this->pagedata['distribute'] = $this->system->getConf('certificate.distribute');

        $appmgr = &$this->system->loadModel('system/appmgr');//增加事务扩展，允许在完成现有业务逻辑情况下 再完成第三方业务逻辑,以及新增html页面上的输出信息。
        $objs = $appmgr->ex_load('order','_detail');
        foreach($objs as $k=>$vobj){
            $obj = $appmgr->loadComp($vobj['value']);
            if(method_exists($obj,'detail_info')){
               $obj->detail_info($aOrder,$output);
               $pagedata['output'][] = $output;
               $this->pagedata = array_merge($this->pagedata,$pagedata);
            }
            
        }

    }

    /**
     * toConfirm
     *
     * @param mixed $orderid
     * @access public
     * @return void
     */
    function toConfirm($orderid){
        $objOrder = &$this->system->loadModel('trading/order');
        if($orderid){
            $order = $objOrder->toConfirm($orderid);
            $this->status($orderid,$order);
            exit;
        }
        if(is_array($_POST['items']['items'])){
            foreach($_POST['items']['items'] as $orderid){
                $order = $objOrder->toConfirm($orderid);
            }
        }
        echo __('所选订单已确认完毕');
    }

    function status($orderid,$order=null){
        if(!$order){
            $order = &$this->system->loadModel('trading/order');
            $order = $order->load($orderid);
        }

        $this->pagedata['order'] = $order;

        $this->display('order/actbar.html');
    }

    /**
     * archive
     *
     * @param mixed $orderid
     * @access public
     * @return void
     */
    function archive($orderid){
        $objOrder = &$this->system->loadModel('trading/order');
        if($orderid){
            $objOrder->op_id = $this->system->op_id;
            $objOrder->op_name = $this->system->op_name;



            $order = $objOrder->toArchive($orderid);
            echo __('订单已确认完毕');
            exit;
        }
        echo __('<span failedSplash="true">订单确认失败</span>');
    }

    /**
     * remove
     *
     * @param mixed $orderid
     * @access public
     * @return void
     */
    function remove(){
        $objOrder = &$this->system->loadModel('trading/order');
        if(is_array($_POST['items']['items'])){
            foreach($_POST['items']['items'] as $orderid){
                if(!$objOrder->toRemove($orderid, $message)){
                    echo $message;
                    exit;
                }
            }
            echo __('删除成功;');
        }else{
            echo __('没有选中记录;');
        }
    }

    /**
     * cancel
     *
     * @param mixed $orderid
     * @access public
     * @return void
     */
    function cancel($orderid){
        $objOrder = &$this->system->loadModel('trading/order');
        if($orderid){
            $objOrder->op_id = $this->system->op_id;
            $objOrder->op_name = $this->system->op_name;
            $order = $objOrder->toCancel($orderid);
            echo __('订单已作废');
            exit;
        }
        echo __('<span failedSplash="true">订单作废操作失败</span>');
    }

    function toReply($orderid){
        $order = &$this->system->loadModel('trading/order');
        $data['object_type'] = 1;
        $data['object_id'] = $orderid;
        $data['comment'] = $_POST['reply'];
        $data['time'] = time();
        $data['member_id'] = 0;
        $order->toReply($data);
        $this->detail($orderid);
    }

    function showPayed($orderid){
        if(!$orderid) return false;

        $this->pagedata['orderid'] = $orderid;
        $objOrder = &$this->system->loadModel('trading/order');
        $aORet = $objOrder->getFieldById($orderid);
        
        $oCur = &$this->system->loadModel('system/cur');
        $aCur = $oCur->getSysCur();
        $aORet['cur_name'] = $aCur[$aORet['currency']];

        $objPayment = &$this->system->loadModel('trading/payment');
        $aPayment = $objPayment->getMethods();
        $this->pagedata['payment'] = $aPayment;
        $aPayid = $objPayment->getPaymentById($aORet['payment']);
        $this->pagedata['payment_id'] = $aORet['payment'];
        $this->pagedata['op_name'] = 'admin';
        $this->pagedata['typeList'] = array('online'=>__("在线支付"), 'offline'=>__("线下支付"), 'deposit'=>__("预存款支付"));
        $this->pagedata['pay_type'] = ($aPayid['pay_type'] == 'ADVANCE' ? 'deposit' : 'offline');

        if($aORet['member_id'] > 0){
            $objMember = &$this->system->loadModel('member/member');
            $aRet = $objMember->getMemberInfo($aORet['member_id']);
            $this->pagedata['member'] = $aRet;
        }else{
            $this->pagedata['member'] = array();
        }
        $math = $this->system->loadModel('system/math');
       
        $this->pagedata['pay_amount']=$aORet['total_amount']-$aORet['payed'];

        $this->pagedata['pay_amount'] = $math->getOperationNumber($this->pagedata['pay_amount']);

        $this->pagedata['order'] = $aORet;

        $aRet = $objPayment->getAccount();
        $aAccount = array(__('--使用已存在帐户--'));
        
        foreach ($aRet as $v){
            if ( empty($v['account']) || empty($v['bank']) ) continue;
            $aAccount[$v['bank']."-".$v['account']] = $v['bank']." - ".$v['account'];
        }

        $this->pagedata['pay_account'] = $aAccount;

        $this->display('order/orderpayed.html');
    }

    /**
     * toPayed
     * 后台手动到款
     *
     * @param mixed $orderid
     * @access public
     * @return void
     */
    function toPayed($orderid){
        if(!$orderid) $orderid = $_POST['order_id'];
        else $_POST['order_id'] = $orderid;

        $_POST['opid'] = $this->system->op_id;
        $_POST['opname'] = $this->system->op_name;
        $this->begin('index.php?ctl=order/order&act=detail&p[0]='.$orderid);
        $objOrder = &$this->system->loadModel('trading/order');
        $objOrder->op_id = $this->system->op_id;
        $objOrder->op_name = $this->system->op_name;
        if($objOrder->toPayed($_POST)){    //处理订单收款
            $this->end(true, __('支付成功'));
        }else{
            $this->end(false, __('支付失败'));
        }
    }

    function showRefund($orderid){/*{{{*/
        if(!$orderid) return false;

        $this->pagedata['orderid'] = $orderid;
        $objOrder = &$this->system->loadModel('trading/order');
        $aORet = $objOrder->getFieldById($orderid);

        $objPayment = &$this->system->loadModel('trading/payment');
        $aPayment = $objPayment->getMethods();
        $this->pagedata['payment'] = $aPayment;
        $aPayid = $objPayment->getPaymentById($aORet['payment']);
        $this->pagedata['payment_id'] = $aORet['payment'];
        $this->pagedata['op_name'] = 'admin';
        $this->pagedata['typeList'] = array('online'=>__("在线支付"), 'offline'=>__("线下支付"), 'deposit'=>__("预存款支付"));
        $this->pagedata['pay_type'] = ($aPayid['pay_type'] == 'ADVANCE' ? 'deposit' : 'offline');

        if($aORet['member_id'] > 0){
            $objMember = &$this->system->loadModel('member/member');
            $aRet = $objMember->getMemberInfo($aORet['member_id']);
            $this->pagedata['member'] = $aRet;
        }else{
            $this->pagedata['member'] = array();
        }
        $this->pagedata['order'] = $aORet;

        $aRet = $objPayment->getAccount();
        $aAccount = array(__('--使用已存在帐户--'));
        foreach ($aRet as $v){
            if ( empty($v['account']) || empty($v['bank']) ) continue;
            $aAccount[$v['bank']."-".$v['account']] = $v['bank']." - ".$v['account'];
        }

        $this->pagedata['pay_account'] = $aAccount;
        $oPointHistory = &$this->system->loadModel('trading/pointHistory');
        $this->pagedata['score_g'] = $oPointHistory->getOrderHistoryGetPoint($orderid);

        $this->display('order/orderrefund.html');
    }

    function toRefund($orderid){
        if(!$orderid) $orderid = $_POST['order_id'];
        else $_POST['order_id'] = $orderid;

        $_POST['opid'] = $this->system->op_id;
        $_POST['opname'] = $this->system->op_name;
        $this->begin('index.php?ctl=order/order&act=detail&p[0]='.$orderid);
        $objOrder = &$this->system->loadModel('trading/order');

        $objOrder->op_id = $this->system->op_id;
        $objOrder->op_name = $this->system->op_name;
        if($objOrder->refund($_POST)){    //处理订单收款
            $this->end(true, __('退款成功'));
        }else{
            $this->end(false, __('退款失败'));
        }
    }

    /**
     * showConsignFlow
     *
     * @param mixed $orderid
     * @access public
     * @return void
     */
    function showConsignFlow($orderid){
        if(!$orderid){
            echo __('发货错误：订单ID传递出错');
            return false;
        }
        $objOrder = &$this->system->loadModel('trading/order');
        $aShipping = $objOrder->getFieldById($orderid, array('order_id','ship_status','createtime','shipping_area','shipping_id','shipping','ship_name','is_delivery','ship_email','ship_tel','ship_mobile','ship_zip','ship_area','ship_addr','cost_freight','is_protect','cost_protect'));
        if(!$aShipping){
            echo __('发货错误：没有当前订单');
            return false;
        }

        $this->pagedata['order'] = $aShipping;
        $this->pagedata['order']['protectArr'] = array('false'=>__('否'), 'true'=>__('是'));

        $aItems = $objOrder->getItemList($orderid,'',true);//-b2c-plat需求
      
        $gItems = $objOrder->getGiftItemList($orderid,'',true);

        if (empty($aItems)&&(empty($gItems))) {
            echo __('订单里无货品或者都是远端数据,请点"采购并编辑按钮"进行发货处理');
            return false;
        }




        foreach($aItems as $k => $rows){
            $aItems[$k]['addon'] = unserialize($rows['addon']);
            if($rows['minfo'] && unserialize($rows['minfo'])){
                $aItems[$k]['minfo'] = unserialize($rows['minfo']);
            }else{
                $aItems[$k]['minfo'] = array();
            }
            if($aItems[$k]['is_type'] == 'goods'){
                $p = &$this->system->loadModel('goods/products');
                $aGoods = $p->getFieldById($aItems[$k]['product_id'], array('store'));
            }else{
                $g = &$this->system->loadModel('trading/goods');
                $aGoods = $g->getFieldById($aItems[$k]['product_id'], array('store'));
            }
            $aItems[$k]['store'] = $aGoods['store'];
        }
        $this->pagedata['items'] = $aItems;
        $this->pagedata['giftItems'] = $gItems;
        if ($this->pagedata['giftItems']) {
            foreach($this->pagedata['giftItems'] as $k=>$v){
                $this->pagedata['giftItems'][$k]['needsend'] = $v['nums'] - $v['sendnum'];
            }
        }
        
        $shipping = &$this->system->loadModel('trading/delivery');
        $this->pagedata['shippings'] = $shipping->getDlTypeList();
        $this->pagedata['corplist'] = $shipping->getCropList();
        if(defined('SAAS_MODE')&&SAAS_MODE){
            $this->pagedata['corplist'] = getdeliverycorplist();
            $this->pagedata['corplist'][] = array('corp_id'=>'other','name'=>__('其他'));
        }
        $corp = $shipping->getCorpByShipId($aShipping['shipping_id']);
        $this->pagedata['corp_id'] = $corp['corp_id'];

        $this->display('order/orderconsign.html');
    }

    //支付宝确认发货
    function toAlipayDelivery($orderid){
        $this->begin('index.php?ctl=order/order&act=detail&p[0]='.$orderid);
        if(!$orderid) $orderid = $_POST['order_id'];
        $delivery = &$this->system->loadModel('trading/delivery');
        $deli_data = $delivery->fetch_delivery_info($orderid);
        $deli_data['is_sync'] = 'true';
        $deli_data['order_id'] = $orderid;
        if(!class_exists('delivery_alipay_todelivery')){
            require_once(CORE_DIR.'/component/delivery/alipay/todelivery.php');
            $alipay = new delivery_alipay_todelivery();
        }
        $alipay->extends_delivery($deli_data);
        $this->end(true, __('已同步发货状态到支付宝,请注意该订单在支付宝那里的状态'));
    }

    function toDelivery($orderid){

        if(!$orderid) $orderid = $_POST['order_id'];
        else $_POST['order_id'] = $orderid;
        $this->begin('index.php?ctl=order/order&act=detail&p[0]='.$orderid);

        $_POST['opid'] = $this->system->op_id;
        $_POST['opname'] = $this->system->op_name;

        $objOrder = &$this->system->loadModel('trading/order');
        $aItems = $objOrder->getItemList($_POST['order_id'],'',true);
        foreach($_POST['send'] as $k=>$v){
            if( (NUll !== $aItems[0]['store']) && ('' !== $aItems[0]['store']) && (+$aItems[0]['store'] < +$ssv) ) {
                echo '发货失败,库存不足'; exit;
            }
        }

        $objOrder->op_id = $this->system->op_id;
        $objOrder->op_name = $this->system->op_name;
        if( !$objOrder->delivery($_POST) ) {
            $this->end(false, __('发货失败'));
        }
        
        //支付宝担保交易同步发货  by zhoulei 2013-4-9
        $objPayment = $this->system->loadModel('trading/payment');
        $objPayment->sync_ship_status_to_taobao($orderid);

        $oPro = &$this->system->loadModel('goods/products');
        $oPro->addSellLog($_POST);
        $this->end(true, __('发货成功'));
    }

    function showReturn($orderid){
        if(!$orderid){
            echo __('退货错误：订单ID传递出错');
            return false;
        }
        $objOrder = &$this->system->loadModel('trading/order');
        $aShipping = $objOrder->getFieldById($orderid, array('order_id','ship_status','createtime','is_delivery','shipping_area','shipping','ship_name','ship_email','ship_tel','ship_mobile','ship_zip','ship_area','ship_addr','cost_freight','is_protect','cost_protect'));
        if(!$aShipping){
            echo __('退货错误：没有当前订单');
            return false;
        }

        $this->pagedata['order'] = $aShipping;
        $this->pagedata['order']['protectArr'] = array('false'=>__('否'), 'true'=>__('是'));

        $aItems = $objOrder->getItemList($orderid,'',true);//-b2c-plat需求
        $gItems = $objOrder->getGiftItemList($orderid,'',true);
        if (empty($aItems)&&(empty($gItems))) {
            echo __('订单里无货品或者都是远端数据,请点"采购并编辑按钮"进行发货处理');
            return false;
        }
//        $aItems = $objOrder->getItemList($orderid);
        foreach($aItems as $k => $rows){
            $aItems[$k]['addon'] = unserialize($rows['addon']);
            $aItems[$k]['_send_'] = $objOrder->countReturnNum($orderid,$rows['product_id']);
            $aItems[$k]['_neednum_'] = ($rows['sendnum']-$aItems[$k]['_send_']) > 0 ? ($rows['sendnum']-$aItems[$k]['_send_']) : 0;
            if($rows['minfo'] && unserialize($rows['minfo'])){
                $aItems[$k]['minfo'] = unserialize($rows['minfo']);
            }else{
                $aItems[$k]['minfo'] = array();
            }
        }
        $this->pagedata['items'] = $aItems;

        $shipping = &$this->system->loadModel('trading/delivery');
        $this->pagedata['shippings'] = $shipping->getDlTypeList();
        $this->pagedata['corplist'] = $shipping->getCropList();
        if(defined('SAAS_MODE')&&SAAS_MODE){
        $this->pagedata['corplist'] = getdeliverycorplist();
        $this->pagedata['corplist'][] = array('corp_id'=>'other','name'=>__('其他'));
        }

        $this->display('order/orderreturn.html');
    }

    function toReturn($orderid){
        if(!$orderid) $orderid = $_POST['order_id'];
        else $_POST['order_id'] = $orderid;
        $this->begin('index.php?ctl=order/order&act=detail&p[0]='.$orderid);

        $_POST['opid'] = $this->system->op_id;
        $_POST['opname'] = $this->system->op_name;
        $objOrder = &$this->system->loadModel('trading/order');
        $objOrder->op_id = $this->system->op_id;
        $objOrder->op_name = $this->system->op_name;
        if($objOrder->toReship($_POST)){
            $this->end(true, __('退货成功'));
        }else{
            $this->end(false, __('退货失败'));
        }
    }

    function showAdd(){
        $this->singlepage('order/page.html');
    }

    /**
     * create
     * 切记要和shop/cart::checkout保持功能上的同步
     *
     * @access public
     * @return void
     */
    function create(){
        if(!empty($_POST['username'])){
            $objMember = &$this->system->loadModel('member/member');
            $aUser = $objMember->getList('member_id,member_lv_id',array('member_id'=>$_POST['username']),0,1);
            $aUser = $aUser[0];
            if(empty($aUser['member_id'])){
                echo __('<script>alert("不存在的会员名称!")</script>');
                exit;
            }
        }else{
            $aUser = array('member_id' => NULL, 'member_lv_id' => 0);
        }
        $_SESSION['tmp_admin_create_order'] = array();
        $_SESSION['tmp_admin_create_order']['member'] = $aUser;

        if($_POST['goods']){
            $aTmp['product_id'] = $_POST['goods'];
            $objPdt = &$this->system->loadModel('goods/finderPdt');
            $aPdt = $objPdt->getList('goods_id, product_id', $aTmp, 0, count($_POST['goods']));
            unset($aTmp);
            foreach($aPdt as $key => $row){
                $num = ceil($_POST['goodsnum'][$aPdt[$key]['product_id']]);
                if($num > 0){
                    $_SESSION['tmp_admin_create_order']['cart']['g']['cart'][$row['goods_id'].'-'.$aPdt[$key]['product_id'].'-na'] = $num;

                    $oPromotion = &$this->system->loadModel('trading/promotion');
                    if($pmtid = $oPromotion->getGoodsPromotionId($row['goods_id'], $aUser['member_lv_id'])){
                        $_SESSION['tmp_admin_create_order']['cart']['g']['pmt'][$row['goods_id']] = $pmtid;
                    }
                }
            }
        }
        if($_POST['package']){
            $aTmp['goods_id'] = $_POST['package'];
            $oPackage = &$this->system->loadModel('trading/package');
            $aPkg = $oPackage->getList('goods_id', $aTmp, 0, count($_POST['package']));
            unset($aTmp);
            foreach($aPkg as $key => $row){
                $num = ceil($_POST['pkgnum'][$aPkg[$key]['goods_id']]);
                if($num > 0){
                    $_SESSION['tmp_admin_create_order']['cart']['p'][$row['goods_id']]['num'] = $num;
                }
            }
        }

        if(!$_SESSION['tmp_admin_create_order']['cart']){
            echo __('<script>MessageBox.error("没有购买商品或者购买数量为0!");</script>');
            exit;
        }

        $objCart = &$this->system->loadModel('trading/cart');
        $aOut = $objCart->getCheckout($_SESSION['tmp_admin_create_order']['cart'], $aUser, '');
        $aOut['trading']['admindo'] = 1;
        $this->pagedata['has_physical'] = $aOut['has_physical'];
        $this->pagedata['minfo'] = $aOut['minfo'];
        $this->pagedata['areas'] = $aOut['areas'];
        $this->pagedata['currencys'] = $aOut['currencys'];
        $this->pagedata['currency'] = $aOut['currency'];
        $this->pagedata['payments'] = $aOut['payments'];
        $payment = $this->system->loadModel('trading/payment');
        $payment->showPayExtendCon($aOut['payments']);
        $this->pagedata['payments'] = $aOut['payments'];
        $this->pagedata['trading'] = $aOut['trading'];
        if ($this->pagedata['payments']){
            foreach($this->pagedata['payments'] as $key => $val){
                $this->pagedata['payments'][$key]['config']=unserialize($val['config']);
            }
        }
        if($aUser['member_id']){
            $member = &$this->system->loadModel('member/member');
            $addrlist = $member->getMemberAddr($aUser['member_id']);
            foreach($addrlist as $rows){
                if(empty($rows['tel'])){
                    $str_tel = __('手机：').$rows['mobile'];
                }else{
                    $str_tel = __('电话：').$rows['tel'];
                }
                $addr[] = array('addr_id'=> $rows['addr_id'],'def_addr'=>$rows['def_addr'],'addr_region'=> $rows['area'],
                                'addr_label'=> $rows['addr'].__(' (收货人：').$rows['name'].' '.$str_tel.__(' 邮编：').$rows['zip'].')');
            }
            $this->pagedata['trading']['receiver']['addrlist'] = $addr;
            $this->pagedata['is_allow'] = (count($addr)<5 ? 1 : 0);
        }
        $this->display('order/order_create.html');
    }

    function getAddr(){
        if($_GET['addr_id']){
            $oMem = &$this->system->loadModel('member/member');
            $this->pagedata['trading']['receiver'] = $oMem->getAddrById($_GET['addr_id']);
        }
        $this->pagedata['trading']['member_id'] = $_SESSION['tmp_admin_create_order']['member']['member_id'];
        $this->display('shop:common/rec_addr.html');
    }

    function shipping(){
        $aCart = $_SESSION['tmp_admin_create_order']['cart'];
        $aMember = $_SESSION['tmp_admin_create_order']['member'];
       
        $sale = &$this->system->loadModel('trading/sale');
        $trading = $sale->getCartObject($aCart,$aMember['member_lv_id'],true);
       
        $shipping = &$this->system->loadModel('trading/delivery');
        $aShippings = $shipping->getDlTypeByArea($_POST['area']);
        
        foreach($aShippings as $k=>$s){
            $aShippings[$k]['price'] = cal_fee($s['expressions'],$trading['weight'],$trading['pmt_b']['totalPrice'],$s['price']);
            $s['pad']==0?$aShippings[$k]['has_cod'] = 0:$aShippings[$k]['has_cod'] = 1;
            if($s['protect']==1){
                $aShippings[$k]['protect'] = max($trading['totalPrice']*$s['protect_rate'],$s['minprice']);
            }else{
                $aShippings[$k]['protect'] = false;
            }
        }

        $this->pagedata['shippings'] = $aShippings;
    
        $this->display('shop:cart/checkout_shipping.html');
    }

    function payment(){
        $payment = &$this->system->loadModel('trading/payment');
        $oCur = &$this->system->loadModel('system/cur');
        $this->pagedata['payments'] = $payment->getByCur($_POST['cur']);
        $this->pagedata['delivery']['has_cod'] = $_POST['d_pay'];
        $this->pagedata['order']['payment'] = $_POST['payment'];
        //todo 需要确定支付费率的需求
        $this->display('shop:common/paymethod.html');
    }

    function total(){
        $aCart = $_SESSION['tmp_admin_create_order']['cart'];
        $aMember = $_SESSION['tmp_admin_create_order']['member'];
        $tarea = explode(':', $_POST['area'] );
        $_POST['area'] = $tarea[count($tarea)-1];
        $objCart = &$this->system->loadModel('trading/cart');
        $this->pagedata['trading'] = $objCart->checkoutInfo($aCart, $aMember, $_POST);
        $this->display('shop:cart/checkout_total.html');
    }

    function doCreate(){
        $this->begin('index.php?ctl=order/order&act=index');

        $aCart = $_POST['aCart'];
        $aCart = $_SESSION['tmp_admin_create_order']['cart'];
        $aMember = $_POST['aMember'];
        $aMember = $_SESSION['tmp_admin_create_order']['member'];
        unset($_SESSION['tmp_admin_create_order']);

        $order = &$this->system->loadModel('trading/order');
        $order->op_id = $this->system->op_id;
        $order->op_name = $this->system->op_name;
        $_POST['delivery']['ship_addr'] = str_replace('^\s+|\s+$','',$_POST['delivery']['ship_addr_area'].$_POST['delivery']['ship_addr']);
        $orderid = $order->create($aCart,$aMember,$_POST['delivery'],$_POST['payment'],$_POST['minfo'],$_POST);

        $this->end($orderid,__('订单: ').$orderid.__(' 生成成功'));

        
    }

    function showEdit($orderid){

        $this->path[] = array('text'=>__('订单编辑'));
        $objOrder = &$this->system->loadModel('trading/order');
        
        if ( !$aOrder = $objOrder->getFieldById($orderid) ) return;
        
        $aOrder['discount'] = 0 - $aOrder['discount'];

        $oCur = &$this->system->loadModel('system/cur');
        $aCur = $oCur->getSysCur();
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];

        $aOrder['items'] = $objOrder->getItemList($orderid);
    
        
        $goodidarr=array();
        $goodidarrnum=array();
        if($aOrder['items']){
            foreach($aOrder['items'] as $key=>$value){
               $goodidarr[]=$value['bn'];
               $goodidarrnum[]=$value['nums'];
            }
        }
        

        
        $pweight=$objOrder->getweight($goodidarr);
        $arrweight=array();

        if(is_array($pweight)){
        foreach($pweight as $key=>$pval){
         $arrweight[]=$pval[0]['weight'];
        }
        }
        
        $goodidarrweight=array();
        foreach($arrweight as $wkey=>$wvalue){
            $goodidarrweight[]=$goodidarrnum[$wkey]*$wvalue;
        }
        
        $aOrder['pmt'] = $objOrder->getPmtList($orderid);

        if($aOrder['member_id'] > 0){
            $objMember = &$this->system->loadModel('member/member');
            $aOrder['member'] = $objMember->getFieldById($aOrder['member_id'], array('*'));
            $aOrder['ship_email'] = $aOrder['member']['email'];
        }else{
            $aOrder['member'] = array();
        }

        $objDelivery = &$this->system->loadModel('trading/delivery');
        $aArea = $objDelivery->getDlAreaList();
        foreach ($aArea as $v){
            $aTmp[$v['name']] = $v['name'];
        }
        $aOrder['deliveryArea'] = $aTmp;

        $aRet = $objDelivery->getDlTypeList();
        foreach ($aRet as $v){
            $aShipping[$v['dt_id']] = $v['dt_name'];
        }
        $aOrder['selectDelivery'] = $aShipping;

        $objPayment = &$this->system->loadModel('trading/payment');
        $aRet = $objPayment->getMethods();
        $aPayment[-1] = '货到付款';
        foreach ($aRet as $v){
            $aPayment[$v['id']] = $v['custom_name'];
        }
        $aOrder['extendCon'] = $objPayment->getExtendCon($aOrder['extend'],$aOrder['payment']);
        $aOrder['selectPayment'] = $aPayment;

        $objCurrency = &$this->system->loadModel('system/cur');
        $aRet = $objCurrency->curAll();
        foreach ($aRet as $v){
            $aCurrency[$v['cur_code']] = $v['cur_name'];
        }
        $aOrder['curList'] = $aCurrency;
        $shipping = &$this->system->loadModel('trading/delivery');
      $darea=$aOrder['ship_area'];
       $dt_id=$aOrder['shipping_id'];
        $aShippingsa = $shipping->getDlTypeByArea($darea, 0,$dt_id);
         foreach($aShippingsa as $k=>$s){
            $aShippingsa['price'] = cal_fee($s['expressions'],$aOrder['weight'],$aOrder['cost_item'],$s['price']);
            
            $s['pad']==0?$aShippingsa['has_cod'] = 0:$aShippingsa['has_cod'] = 1;
            if($s['protect']==1){
                $aShippingsa['protect'] = max($trading['totalPrice']*$s['protect_rate'],$s['minprice']);
            }else{
                $aShippingsa['protect'] = false;
            }
        }
        
        $this->pagedata['deliveryprice']=$aShippingsa['price'];
        $this->pagedata['order'] = $aOrder;
        $this->pagedata['arrweight'] = $arrweight;
        $this->pagedata['goodweight']=$goodidarrweight;
        
         $this->pagedata['goodnums']=count($goodidarrnum);
        
        $this->singlepage('order/page.html');
    }
    function toedit_dlyprice(){
        $objOrder   = &$this->system->loadModel('trading/order');
        $shipping   = &$this->system->loadModel('trading/delivery');
        $oPromotion = &$this->system->loadModel('trading/promotion');
        $objMember  = &$this->system->loadModel('member/member');
        $orderid=$_GET['orderid'];
        $aOrder = $objOrder->getFieldById($orderid);
        $aMember = $objMember->getBasicInfoById($aOrder['member_id']);
        $aPromotion = $oPromotion->getOrderPromotion($aOrder['final_amount'],$aMember['member_lv_id']);
        $darea=$_GET['ship_area'];
        $dt_id=$_GET['shipping_id'];
        $aOrderweight=$_GET['Orderweight'];
        $ordercost_item=$_GET['cost_item'];
        $aShippingsa = $shipping->getDlTypeByArea($darea, 0,$dt_id);
         foreach($aShippingsa as $k=>$s){
            $aShippingsa['price'] = cal_fee($s['expressions'],$aOrderweight,$ordercost_item,$s['price']);
            $s['pad']==0?$aShippingsa['has_cod'] = 0:$aShippingsa['has_cod'] = 1;
        }
        if(is_array($aPromotion)){//订单是否免运费2011-9-20 by zhangxuehui
            foreach($aPromotion as $p_k=>$p_v){
                $proInfo = unserialize($p_v['pmt_solution']);
                if(is_array($proInfo['method'])){
                    foreach($proInfo['method'] as $kp=>$vp){
                        if(($vp[0]=='exemptFreight')&&($vp[1]='1')){
                            $exemptFreight= 1;
                            break;
                        }
                    }
                }
            }
        }
        if(isset($exemptFreight)&&$exemptFreight==1){//订单是免运费，将运费置为0
            $aShippingsa['price'] = '0.000';
        }
        echo $aShippingsa['price'];
    }

    function addItem(){
        
        if($_POST['order_id']){
            $flag = true;
            while($flag){
                $randomValue = rand(1,200);
                if(!in_array($randomValue, array_keys($_POST['aItems']))){
                    $flag = false;
                }
            }
            $loopValue = count($_POST['aItems']) + 1;
            $objOrder = &$this->system->loadModel('trading/order');
            $productInfo = $objOrder->getProductInfo($_POST['order_id'], $_POST['newbn']);

            if($productInfo == 'none'){
                $aOrder['alertJs'] = __("商品货号输入不正确，没有该商品或者商品已经下架。\n注意：如果是多规格商品，请输入规格编号.");
            }elseif($productInfo == 'exist'){
                $aOrder['alertJs'] = __('订单中存在相同的商品货号。');
            }
            elseif($productInfo == 'understock'){
                $aOrder['alertJs'] = __('商品库存不足。');
            }
            if(in_array($_POST['newbn'],$_POST['add_bn'])){
                 $aOrder['alertJs'] = __('该商品货号已存在。');
            }
            if($aOrder['alertJs']){
                echo $aOrder['alertJs'];
                exit;
            }
            $pdt_desc=($productInfo['pdt_desc'])?'('.$productInfo['pdt_desc'].')':'';
            $returnValue = '<tr>';
            $returnValue .= '<input type="hidden" value="'.$productInfo['product_id'].'" name="aItems['.$randomValue.']">';
            $returnValue .= '<td>'.$productInfo['bn'].'<input type="hidden" name="add_bn[]" value="'.$productInfo['bn'].'"></td>';
            $returnValue .= '<td>'.$productInfo['name'].$pdt_desc.'</td>';
            $returnValue .= '<td><input type="text" size="8" value="'.$productInfo['mprice'].'" name="aPrice['.$randomValue.']" class="x-input itemrow" required="true" readonly="true"></td>';
            $returnValue .= '<td><input type="text" vtype="positive" size="4" value="1" name="aNum['.$randomValue.']" class="x-input itemrow" required="true" autocomplete="off"></td>';
            $returnValue .= "<td class='itemCount Colamount itemSub_{$randomValue}'>{$productInfo['mprice']}</td>";
            $returnValue .= '<td><span onclick="delgoods(this)" class="sysiconBtnNoIcon">删除</span></td>';
            $returnValue .= '</tr>';
            echo $returnValue;
        }
    }
    
    function toEdit(){
        $_POST['is_protect'] = isset($_POST['is_protect']) ? $_POST['is_protect'] : 'false';
        $_POST['is_tax'] = isset($_POST['is_tax']) ? $_POST['is_tax'] : 'false';
        $_POST['discount'] = 0 - $_POST['discount'];
        $_POST['pmt_amount'] = ($_POST['pmt_amount']>0) ? $_POST['pmt_amount'] : 0;
        $this->begin('index.php?ctl=order/order&act=index');
        if ($_POST['total_amount_tmp'] < 0) $this->end(false,__('订单总额不能小于0'));
        if (count($_POST['aItems'])){
            $objOrder = &$this->system->loadModel('trading/order');
            $objOrder->op_id = $this->system->op_id;
            $objOrder->op_name = $this->system->op_name;
            if($objOrder->editOrder($_POST )){

                $objOrder->changeOrder($_POST['order_id'],$_POST);

                $aOrder = $objOrder->getFieldById($_POST['order_id'], array('total_amount','is_tax','member_id'));
                $eventData['order_id'] = $_POST['order_id'];
                $eventData['total_amount'] = $aOrder['total_amount'];
                $eventData['is_tax'] = $aOrder['is_tax'];
                $eventData['member_id'] = $aOrder['member_id'];
                
                // ome 同步
                $appmgrMdl = $this->system->loadModel('system/appmgr');
                $ome = $appmgrMdl->load('ome');
                if ( $ome && (false !== $ome->is_active) ) {
                    $eventData['ome_sync_flag'] = true;
                    include_once PLUGIN_DIR.'/app/ome/ome_order_listener.php';
                    $listener = new ome_order_listener();
                    $resp = $listener->order_to_ome('editorder',array('order_id'=>$_POST['order_id']),false);
                    $ome_succ = (($resp = json_decode($resp,1)) && 'succ'==$resp['rsp']) ? 'true':'false';
                    $url = ('true' == $ome_succ) ? 'rsp=success&msg=同步成功' : 'rsp=fail&msg=同步失败';
                    $order_id = $eventData['order_id'];
                    $ome_url = base64_decode($_COOKIE['ome_notify_url']);
                    
                    echo "<script>
                        var href = '$ome_url',ome_succ=$ome_succ;
                        href = href.replace(/rsp=success&msg=同步成功|rsp=fail&msg=同步失败/g,'$url');
                        if ( -1 === href.indexOf('#') ) {
                            href = href+'#';
                        }
                        if ( -1 === href.indexOf('$url') ) {
                            href = href+'$url';
                        }
                        window.top.location.href = href;
                        \$E('span.iconbutton.savebutton').set('text',ome_succ?'同步成功':'订单同步异常，请重试！!');

                        if ( ome_succ ) {
                            window.top.close();
                        }
                        </script>";
                    $objOrder->fireEvent('editorder', $eventData);
                    return;
                }
                
                $objOrder->fireEvent('editorder', $eventData);
                $this->end(true,__('保存成功'));
            }else{
                trigger_error(__('库存不足，请确认！'),E_USER_ERROR);
            }
        }else{
            trigger_error(__('没有商品明细'),E_USER_ERROR);
        }
    }

    /**
     * toPrint
     *
     * @access public
     * @return void
     */
    function toPrint($orderid){
     if($_POST['order_id']){
        if(is_array($_POST['order_id'])){
            $aInput = $_POST['order_id'];
        }else{
            $aInput = array($_POST['order_id']);
        }
     }elseif($orderid){
            $aInput = array($orderid);
     }else{
            $this->begin('index.php?ctl=order/order&act=index');
            $this->end(false, __('打印失败：订单参数传递出错'));
            exit();
     }

        $oCur = &$this->system->loadModel('system/cur');
        $aCur = $oCur->getSysCur();

        $dbTmpl = &$this->system->loadModel('content/systmpl');

        foreach($aInput as $orderid){
            if(strlen($orderid)<14){
               continue;
            }
            $aData=array();
            $objOrder = &$this->system->loadModel('trading/order');
            $aData = $objOrder->getFieldById($orderid);
            $aData['currency'] = $aCur[$aData['currency']];

            $objMember = &$this->system->loadModel('member/member');
            $aMember = $objMember->getFieldById($aData['member_id'], array('uname','name','tel','mobile','email','zip','addr'));
            $aData['member'] = $aMember;

            $payment = &$this->system->loadModel('trading/payment');
            $aPayment = $payment->getPaymentById($aData['payment']);
            $aData['payment'] = $aPayment['custom_name'];

            $aData['shopname'] = $this->system->getConf('store.company_name');
            $aData['shopaddress'] = $this->system->getConf('store.address');
            $aData['shoptelphone'] = $this->system->getConf('store.quhao').'-'.$this->system->getConf('store.telephone');
            $aData['shopzip'] = $this->system->getConf('store.zip_code');

            $aItems = $objOrder->getItemList($orderid);


            foreach($aItems as $k => $rows){

                $aItems[$k]['addon'] = unserialize($rows['addon']);
                if($rows['minfo'] && unserialize($rows['minfo'])){
                    $aItems[$k]['minfo'] = unserialize($rows['minfo']);
                }else{
                    $aItems[$k]['minfo'] = array();
                }
                if($aItems[$k]['addon']['adjname']) $aItems[$k]['name'] .= __('<br>配件：').$aItems[$k]['addon']['adjname'];
                $aItems[$k]['catname'] = $objOrder->getCatByPid($rows['product_id']);
            }

$aData['goodsItems'] = $aItems;
            $aData['giftItems'] = $objOrder->getGiftItemList($orderid);
            $this->pagedata['pages'][] = $dbTmpl->fetch('misc/orderprint',array('order'=>$aData));
           
        }
        $this->pagedata['shopname'] = $aData['shopname'];
        
        $this->display('print.html');
    }

    /**
     * showOrderFlow
     *
     * @param mixed $tag pay, refund, consign, reshipping
     * @param mixed 1,0
     * @access public
     * @return void
     */
    function showOrderFlow(){
        $this->path[] = array('text'=>__('订单是否创建单据'));
        $this->page('order/order_flow.html');
    }

    /**
     * saveFlow
     *
     * @param mixed $tag pay, refund, consign, reshipping
     * @param mixed 1,0
     * @access public
     * @return void
     */
    function saveFlow($tag, $checkmark){
        $items = array('payed','refund','consign','reship');
        foreach($items as $item){
            if(!$_POST['aFlow'][$item]){
                $_POST['aFlow'][$item] = false;
            }
            $this->system->setConf('order.flow.'.$item, $_POST['aFlow'][$item]);
        }
        $this->splash('success','index.php?ctl=order/order&act=showOrderFlow');
    }

    /**
     * showPrintStyle
     *
     * @access public
     * @return void
     */
    function showPrintStyle(){
        $this->path[] = array('text'=>__('订单打印格式设置'));
        $dbTmpl = &$this->system->loadModel('content/systmpl');
        $filetxt = $dbTmpl->get('misc/orderprint');
        $cartfiletxt = $dbTmpl->get('../admin/view/order/print_cart');
        $sheetfiletxt = $dbTmpl->get('../admin/view/order/print_sheet');
        $this->pagedata['styleContent'] = $filetxt;
        $this->pagedata['styleContentCart'] = $cartfiletxt;
        $this->pagedata['styleContentSheet'] = $sheetfiletxt;
        $this->page('order/printstyle.html');
    }

    /**
     * savePrintStyle
     *
     * @access public
     * @return void
     */
    function savePrintStyle(){
        $this->begin('index.php?ctl=order/order&act=showPrintStyle');
        $dbTmpl = &$this->system->loadModel('content/systmpl');
        $dbTmpl->set('../admin/view/order/print_sheet', $_POST["txtcontentsheet"]);
        $dbTmpl->set('../admin/view/order/print_cart', $_POST["txtcontentcart"]);
        $this->end($dbTmpl->set('misc/orderprint', $_POST["txtcontent"]),__('订单打印模板保存成功'));
    }

    /**
     * rebackPrintStyle
     *
     * @access public
     * @return void
     */
    function rebackPrintStyle(){
        $this->begin('index.php?ctl=order/order&act=showPrintStyle');
        $dbTmpl = &$this->system->loadModel('content/systmpl');
        $dbTmpl->clear('../admin/view/order/print_sheet');
        $dbTmpl->clear('../admin/view/order/print_cart');
        $this->end($dbTmpl->clear('misc/orderprint'),__('恢复默认值成功'));
    }

    function delete(){
        $oOrder = &$this->system->loadModel("trading/order");
        $msg = '';
        if($_POST['order_id'][0]=='_ALL_'){
            $_POST['order_id'] = array();
            $dd = $oOrder->db->select("SELECT order_id FROM sdb_orders WHERE disabled = 'true';");
            foreach($dd as $_k =>$_v){
                $_POST['order_id'][] = $_v['order_id'];
            }
        }
        foreach($_POST['order_id'] as $v){
            $oOrder->toRemove($v ,  $msg);
            
        }
        $status = &$this->system->loadModel('system/status');
        $status->count_order_to_pay();
        $status->count_order_new();
        echo $msg;
    }
    function memberInfo($nMId){
        $this->pagedata['member_id'] = $nMId;
        $this->display('order/order_membertab.html');
    }

    function recycle() {
        $this->model->op_id = $this->system->op_id;
        $this->model->op_name = $this->system->op_name;
        parent::recycle();
        $oOrder = &$this->system->loadModel("trading/order");
        foreach($_POST['order_id'] as $v){
            $oOrder->toUnfreez($v);
        }

        $status = &$this->system->loadModel('system/status');
        $status->count_order_to_pay();
        $status->count_order_new();
        $status->count_order_to_dly();
    }
    function active() {
        $this->model->op_id = $this->system->op_id;
        $this->model->op_name = $this->system->op_name;
        parent::active();

        $status = &$this->system->loadModel('system/status');
        $status->count_order_to_pay();
        $status->count_order_new();
        $status->count_order_to_dly();
    }

    function edit_po($orderid){
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $this->path[] = array('text'=>'订单采购并编辑');
        $objOrder = $this->system->loadModel('trading/order');
        $aOrder = $objOrder->getFieldById($orderid);
        $aOrder['discount'] = 0 - $aOrder['discount'];

        $aOrder['cost_item'] = $objOrder->getCostItems($orderid);

        $oCur = $this->system->loadModel('system/cur');
        $aCur = $oCur->getSysCur();
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];

        $aOrder['pmt'] = $objOrder->getPmtList($orderid);

        if($aOrder['member_id'] > 0){
            $objMember = $this->system->loadModel('member/member');
            $aOrder['member'] = $objMember->getFieldById($aOrder['member_id'], array('*'));
            $aOrder['ship_email'] = $aOrder['member']['email'];
        }else{
            $aOrder['member'] = array();
        }

        $objDelivery = $this->system->loadModel('trading/delivery');
        $aArea = $objDelivery->getDlAreaList();
        foreach ($aArea as $v){
            $aTmp[$v['name']] = $v['name'];
        }
        $aOrder['deliveryArea'] = $aTmp;

        $aRet = $objDelivery->getDlTypeList();
        foreach ($aRet as $v){
            $aShipping[$v['dt_id']] = $v['dt_name'];
        }
        $aOrder['selectDelivery'] = $aShipping;

        $objPayment = $this->system->loadModel('trading/payment');
        $aRet = $objPayment->getMethods();
        foreach ($aRet as $v){
            $aPayment[$v['id']] = $v['custom_name'];
        }
        $aOrder['selectPayment'] = $aPayment;


        $objCurrency = $this->system->loadModel('system/cur');
        $aRet = $objCurrency->curAll();
        foreach ($aRet as $v){
            $aCurrency[$v['cur_code']] = $v['cur_name'];
        }
        $aOrder['curList'] = $aCurrency;

        $objPo = $this->system->loadModel('purchase/order_po');
        $aOrder['po'] = $objPo->getPoListByOrderId($orderid);

        $this->pagedata['order'] = $aOrder;
        $this->singlepage('order/page.html');
    }

    function _err_handler($errno, $errstr, $errfile, $errline){
        if($errno == E_USER_ERROR){
            $this->errorinfo = $errstr;
        }
        return true;
    }

    function inquiry($orderid){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $inq_bn = array();
        $objPo = $this->system->loadModel('purchase/order_po');
        $aData = $objPo->getPoListByOrderId($orderid);
        foreach($_POST['aItems']['dealer_bn'] as $k => $v){
            $inq_bn[$_POST['aItems']['supplier_bn'][$k]] = $_POST['aItems']['nums'][$k];
        }

        if($inq_bn){
            $aRet = $objPo->inquiry($_POST['supplier_id'], $orderid, $inq_bn);
            $this->pagedata['sItem']['name'] = $aData['supplier'][$_POST['supplier_id']]['name'];
            $this->pagedata['sItem']['local'] = $aRet['items'];
            $this->pagedata['order']['order_id'] = $orderid;
            $this->pagedata['sItem']['total_amount'] = $aRet['total_amount'];
            $this->pagedata['sItem']['store_status'] = $aRet['store_status'];
            $this->pagedata['supplier_id'] = $_POST['supplier_id'];
        }
        $this->__tmpl='order/po_items_local.html';
        $this->output();
    }

    function reInquiry($orderid, $supplier_id=0){
        $this->errorinfo = '';
        set_error_handler(array(&$this,'_err_handler'));
        $inq_bn = array();
        $objPo = $this->system->loadModel('purchase/order_po');
        $aData = $objPo->getPoListByOrderId($orderid);
        foreach($_POST['aItems']['dealer_bn'] as $k => $v){
            $inq_bn[$_POST['aItems']['supplier_bn'][$k]] = $_POST['aItems']['quiry_num'][$k];
            $tmp_arr[$_POST['aItems']['dealer_bn'][$k]] = $_POST['aItems']['nums'][$k];
        }
        if($inq_bn){
            $aRet = $objPo->inquiry($supplier_id, $orderid, $inq_bn);
            foreach($aRet['items'] as $k => $v){
                $aRet['items'][$k]['quiry_num'] = $v['nums'];
                $aRet['items'][$k]['nums'] = $tmp_arr[$k];
            }
            $this->pagedata['sItem']['name'] = $aData['supplier'][$supplier_id]['name'];
            $this->pagedata['poItem']['items'] = $aRet['items'];
            $this->pagedata['poItem']['_action_status'] = $aData['supplier'][$supplier_id]['po'][$_POST['po_id']]['_action_status'];
            $this->pagedata['order']['order_id'] = $orderid;
            $this->pagedata['po_id'] = $_POST['po_id'];
            $this->pagedata['supplier_id'] = $supplier_id;
        }
        if($this->errorinfo){
            echo '<script>MessageBox.error("'.str_replace('"','\"',$this->errorinfo).'");</script>';
        }
        $this->__tmpl='order/po_detail.html';
        $this->output();
    }

    function refresh_local($orderid){
        $objPo = $this->system->loadModel('purchase/order_po');
        $aData = $objPo->getPoListByOrderId($orderid);
        $this->pagedata['order']['po']['local_ship_status'] = $aData['local_ship_status'];
        $this->pagedata['order']['po']['local'] = $aData['local'];
        $this->pagedata['order']['order_id'] = $orderid;
        $this->__tmpl='order/edit_local_items.html';
        $this->output();
    }

    function refresh_po_local($orderid, $supplier_id=0){
        $objPo = $this->system->loadModel('purchase/order_po');
        $aData = $objPo->getPoListByOrderId($orderid);
        $this->pagedata['sItem']['name'] = $aData['supplier'][$_POST['supplier_id']]['name'];
        $this->pagedata['sItem']['local'] = $aData['supplier'][$_POST['supplier_id']]['local'];
        $this->pagedata['order']['order_id'] = $orderid;
        $this->pagedata['supplier_id'] = $_POST['supplier_id'];
        $this->__tmpl='order/po_items_local.html';
        $this->output();
    }

    function refresh_po_supplier($orderid, $supplier_id=0){
        $objPo = $this->system->loadModel('purchase/order_po');
        $aData = $objPo->getPoListByOrderId($orderid);
        $this->pagedata['sItem'] = $aData['supplier'][$supplier_id];
        $this->pagedata['order']['order_id'] = $orderid;
        $this->pagedata['supplier_id'] = $supplier_id;

        $this->__tmpl='order/po_items.html';
        $this->output();
    }

    function refresh_po_detail($orderid, $supplier_id=0){
        $objPo = $this->system->loadModel('purchase/order_po');
        $aData = $objPo->getPoListByOrderId($orderid);
        $this->pagedata['sItem']['name'] = $aData['supplier'][$supplier_id]['name'];
        $this->pagedata['poItem'] = $aData['supplier'][$supplier_id]['po'][$_POST['po_id']];
        $this->pagedata['order']['order_id'] = $orderid;
        $this->pagedata['po_id'] = $_POST['po_id'];
        $this->__tmpl='order/po_detail.html';
        $this->output();
    }

    /**
     * showConsignFlow
     *
     * @param mixed $orderid
     * @access public
     * @return void
     */
    function consignLocal($orderid){
        if(!$orderid){
            echo __('发货错误：订单ID传递出错');
            return false;
        }
        $objOrder = $this->system->loadModel('trading/order');
        $aShipping = $objOrder->getFieldById($orderid, array('order_id','ship_status','createtime','shipping_area','shipping_id','shipping','ship_name','is_delivery','ship_email','ship_tel','ship_mobile','ship_zip','ship_area','ship_addr','cost_freight','is_protect','cost_protect'));
        if(!$aShipping){
            echo __('发货错误：没有当前订单');
            return false;
        }

        $this->pagedata['order'] = $aShipping;
        $this->pagedata['order']['protectArr'] = array('false'=>__('否'), 'true'=>__('是'));

        $objPo = $this->system->loadModel('purchase/order_po');
        $aItems = $objPo->getPoListByOrderId($orderid);
        foreach($aItems['local'] as $k => $rows){
            $aItems['local'][$k]['addon'] = unserialize($rows['addon']);
            if($rows['minfo'] && unserialize($rows['minfo'])){
                $aItems['local'][$k]['minfo'] = unserialize($rows['minfo']);
            }else{
                $aItems['local'][$k]['minfo'] = array();
            }
            if($aItems['local'][$k]['is_type'] == 'goods'){
                $p = $this->system->loadModel('goods/products');
                $aGoods = $p->getFieldById($aItems['local'][$k]['product_id'], array('store'));
            }else{
                $g = $this->system->loadModel('trading/goods');
                $aGoods = $g->getFieldById($aItems['local'][$k]['product_id'], array('store'));
            }
            $aItems['local'][$k]['store'] = $aGoods['store'];
        }
        $this->pagedata['items'] = $aItems['local'];
        $this->pagedata['giftItems'] = $objOrder->getGiftItemList($orderid);
        if ($this->pagedata['giftItems']) {
            foreach($this->pagedata['giftItems'] as $k=>$v){
                $this->pagedata['giftItems'][$k]['needsend'] = $v['nums'] - $v['sendnum'];
            }
        }

        $shipping = $this->system->loadModel('trading/delivery');
        $this->pagedata['shippings'] = $shipping->getDlTypeList();
        $this->pagedata['corplist'] = $shipping->getCropList();
        if(defined('SAAS_MODE')&&SAAS_MODE){
        $this->pagedata['corplist'] = getdeliverycorplist();
        $this->pagedata['corplist'][] = array('corp_id'=>'other','name'=>'其他');
        }
        $corp = $shipping->getCorpByShipId($aShipping['shipping_id']);
        $this->pagedata['corp_id'] = $corp['corp_id'];
        $this->__tmpl = 'order/orderconsign.html';
        $this->output();
    }

    function saveOrder($orderid){
        foreach($_POST['aItems'] as $k => $v){
            $aItems[$v] = $_POST['aNum'];
        }
        $objPo = $this->system->loadModel('purchase/order_po');
        $objPo->modifyOrder($orderid, $_POST);
        $this->refresh_local($orderid);
    }

    function savePo($orderid, $supplier_id){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $this->begin('index.php?ctl=order/order&act=refresh_po');
        $aItems = array();
        foreach((array)$_POST['aItems']['dealer_bn'] as $k => $v){
            $aItems[] = array('dealer_bn' => $v,
                            'supplier_bn' => $_POST['aItems']['supplier_bn'][$k],
                            'price' => $_POST['aItems']['price'][$k],
                            'po_price' => $_POST['aItems']['po_price'][$k],
                            'nums' => ($_POST['aItems']['quiry_num'][$k] ? $_POST['aItems']['quiry_num'][$k] : $_POST['aItems']['nums'][$k]),
                            'product_id' => $_POST['aItems']['product_id'][$k]
                            );
        }
        $po_id = ($_POST['po_id'] ? $_POST['po_id'] : 0);
        $objPo = $this->system->loadModel('purchase/order_po');
        $this->end($objPo->modifyOrder($orderid, $aItems, $supplier_id, $po_id), __('操作成功'));
    }

    function makePo($orderid){
        set_error_handler(array(&$this,'_dialogErrorHandler'));
        // 如果传入的需采购的商品为空,触发相关错误 2009-12-16 13:36 wubin
        if(empty($_POST['aItems'])) trigger_error(__('需要采购的商品为空'),E_USER_ERROR);
        $objOrder = $this->system->loadModel('trading/order');
        $aOrder = $objOrder->getFieldById($orderid);
        $aOrder['discount'] = 0 - $aOrder['discount'];

        $oCur = $this->system->loadModel('system/cur');
        $aCur = $oCur->getSysCur();
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];

        $aOrder['pmt'] = $objOrder->getPmtList($orderid);

        if($aOrder['member_id'] > 0){
            $objMember = $this->system->loadModel('member/member');
            $aOrder['member'] = $objMember->getFieldById($aOrder['member_id'], array('*'));
            $aOrder['ship_email'] = $aOrder['member']['email'];
        }else{
            $aOrder['member'] = array();
        }

        $objShopInfo = $this->system->loadModel('trading/dly_centers');
        $dly_center_id = $this->system->getConf('system.default_dc');
        $this->pagedata['sender'] = $objShopInfo->instance($dly_center_id);

        $objPo = $this->system->loadModel('purchase/order_po');

        $area_list = $objPo->getSubRegions($_POST['supplier_id'],0);
        $aOrder['area_list'] = $area_list['data_info'];

        $aData = $objPo->getPoListByOrderId($orderid);
        $aOrder['items'] = $aData['supplier'][$_POST['supplier_id']]['local'];
        $aOrder['total_amount'] = 0;
        $aOrder['total_weight'] = 0;
        foreach($aOrder['items'] as $bn => $item){
            $k = array_search($bn, $_POST['aItems']['dealer_bn']);
            $aOrder['items'][$bn]['price'] = $_POST['aItems']['po_price'][$k];
            $aOrder['total_amount'] += ($_POST['aItems']['po_price'][$k] * $item['nums']);
            $aOrder['total_weight'] += ($item['weight'] * $item['nums']);

            $aOrder['items'][$bn]['amount'] = $_POST['aItems']['po_price'][$k] * $item['nums']; // 小计 2009-07-06 17:17 wubin
        }
/*
        $aShipping = $objPo->getDlyTypeByArea($_POST['supplier_id']);
        $aOrder['delivery'] = $aShipping['data_info'];
        foreach($aShipping['data_info'] as $k=>$s){
            $aOrder['delivery'][$k]['price'] = cal_fee($s['dt_expressions'],$aOrder['total_weight'],$aOrder['total_amount'],$s['price']);
            if($s['pad'] == 0 || $s['has_cod'] == 0) $aOrder['delivery'][$k]['has_cod'] = 0;
            if($s['protect']==1){
                $aOrder['delivery'][$k]['protect_fee'] = max($aOrder['total_amount']*$s['protect_rate'],$s['minprice']);
            }else{
                $aOrder['delivery'][$k]['protect_fee'] = 0;
            }
        }
*/
        $aOrder['payment'] = $objPo->getPaymentCfg($_POST['supplier_id']);

        $objCurrency = $this->system->loadModel('system/cur');
        $aRet = $objCurrency->curAll(); // b2c-485 2009-11-13 12:59 wubin
        foreach ($aRet as $v){
            $aCurrency[$v['cur_code']] = $v['cur_name'];
        }
        $aOrder['curList'] = $aCurrency;

        $order_setting = $objPo->getOrderSetting($_POST['supplier_id']);

        $decimal_digit = $order_setting['decimal_digit'];
        $decimal_type = $order_setting['decimal_type'];
        //$trigger_tax = $order_setting['trigger_tax'];
        $trigger_tax = 0;//暂时不考虑税金问题，产品gyf确认2009/10/19
        $tax_ratio = $order_setting['tax_ratio'];
        if(!is_null($trigger_tax) && $trigger_tax){
            $supplier_tax = ($tax_ratio/100)*$aOrder['total_amount'];
            $aOrder['is_tax'] = 'true';
            $aOrder['cost_tax'] = number_format($supplier_tax, 2, '.', '');
        }else{
            $aOrder['is_tax'] = 'false';
            $aOrder['cost_tax'] = 0;
        }

        $aOrder['po'] = $objPo->getPoListByOrderId($orderid);
        $aOrder['supplier_id'] = $_POST['supplier_id'];
        $this->pagedata['order'] = $aOrder;
        $this->pagedata['order_setting'] = $order_setting;

        $this->__tmpl = 'order/make_po.html'; // b2c-485 2009-11-13 13:00 wubin
        $this->output();
    }

    //下单
    function toMakePo($orderid){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $objPo = $this->system->loadModel('purchase/order_po');
        $poInfo = $_POST['shipinfo'];
        $poInfo['ship_time'] = $poInfo['ship_date'].' - '.$poInfo['ship_time'];
        unset($poInfo['ship_date']);
        $poInfo['sender_info'] = $_POST['sender_info'];
        $poInfo['is_tax'] = $_POST['is_tax'];
        $poInfo['tax_company'] = $_POST['tax_company'];
        $poInfo['is_protect'] = $_POST['delivery']['is_protect'][$_POST['delivery']['shipping_id']];
        $poInfo['currency'] = $_POST['currency'];
        $poInfo['member_memo'] = $_POST['member_memo'];
        $poInfo['shipping_id'] = $_POST['delivery']['shipping_id'];
        foreach($_POST['dealer_bn'] as $k => $v){
            $poItems[] = array('dealer_bn' => $v,
                               'supplier_bn' => $_POST['supplier_bn'][$k],
                               'price' => $_POST['price'][$k],
                               'nums' => $_POST['nums'][$k] );
        }

        $po_id = $objPo->createPo($_POST['supplier_id'], $orderid, $poInfo, $poItems);
        if($po_id){
            if($_POST['subtype'] == 2){
                if($_POST['payment']['pay_type'][$_POST['payment']['payment_id']] == 'deposit'){
                    if($objPo->payByDeposits($po_id, $_POST['payment']['payment_id'])){
                        //echo '采购单：'.$po_id.'已经成功生成，并支付成功！';
                        echo '[{\'type\' : 1,\'msg\' : \'采购单：'.$po_id.'已经成功生成，并支付成功！\'}]';
                    }else{
                        echo '[{\'type\' : 1,\'msg\' : \'采购单：'.$po_id.'已经成功生成，但支付失败！\'}]';
                        //echo '采购单：'.$po_id.'已经成功生成，但支付失败！';
                    }
                }else{
                    echo '[{\'type\' : 2, \'action\' : \''.$objPo->getSupplierDomain($_POST['supplier_id'],true).'/api.php\',\'info\' : \'<input type="hidden" name="act" value="online_pay_center" /><input type="hidden" name="order_id" value="'.$po_id.'" /><input type="hidden" name="pay_id" value="'.$_POST['payment']['payment_id'].'" /><input type="hidden" name="currency" value="CNY" /><input type="hidden" name="api_version" value="1.0" /><input type="submit" />\'}]';
                    /*
                    echo '<form action="'.$objPo->getSupplierDomain($_POST['supplier_id'],true).'/api.php" method="post" target="_blank">
                            <input type="hidden" name="act" value="online_pay_center" />
                            <input type="hidden" name="order_id" value="'.$po_id.'" />
                            <input type="hidden" name="pay_id" value="'.$_POST['payment']['payment_id'].'" />
                            <input type="hidden" name="currency" value="CNY" />
                            <input type="hidden" name="api_version" value="'.API_VERSION.'" />
                            <input type="submit" />
                            </form>';
                    */
                }
            }else{
                echo '采购单：'.$po_id.'已经成功生成！';
            }
        }
                if($this->errorinfo){
                    echo str_replace('"','\"',$this->errorinfo);
                }
    }

    function stoppo($poid){
        $objPo = $this->system->loadModel('purchase/order_po');
        $objPo->pendingPo($poid);
    }

    function activepo($poid){
        $objPo = $this->system->loadModel('purchase/order_po');
        $objPo->cancelPendingPo($poid);
    }

    function checkPay($poid){
        $this->begin();
        $objPo = $this->system->loadModel('purchase/order_po');
        $this->end($objPo->reconciliation($poid), __('对账成功'));
    }

    function addPoItem(){
        if($_POST['order_id']){
            $orderid = $_POST['order_id'];
            $objOrder = $this->system->loadModel('trading/order');
            $retMark = $objOrder->insertOrderItem($_POST['order_id'], $_POST['newbn'], 1);
            if($retMark == 'none'){
                $aOrder['alertJs'] = '<script>alert("商品货号输入不正确，没有该商品或者商品已经下架。\n注意：如果是多规格商品，请输入规格编号。");</script>';
            }elseif($retMark == 'exist'){
                $aOrder['alertJs'] = '<script>alert("订单中存在相同的商品货号。");</script>';
            }else{
                if(!$retMark){
                    $aOrder['alertJs'] = '<script>alert("插入数据库失败");</script>';
                }else{
                    $aOrder['alertJs'] = '<script>countF();</script>';
                }
            }

            $aOrder = $objOrder->getFieldById($orderid);
            $aOrder['discount'] = 0 - $aOrder['discount'];

            $oCur = $this->system->loadModel('system/cur');
            $aCur = $oCur->getSysCur();
            $aOrder['cur_name'] = $aCur[$aOrder['currency']];

            $aOrder['pmt'] = $objOrder->getPmtList($orderid);

            if($aOrder['member_id'] > 0){
                $objMember = $this->system->loadModel('member/member');
                $aOrder['member'] = $objMember->getFieldById($aOrder['member_id'], array('*'));
                $aOrder['ship_email'] = $aOrder['member']['email'];
            }else{
                $aOrder['member'] = array();
            }

            $objDelivery = $this->system->loadModel('trading/delivery');
            $aArea = $objDelivery->getDlAreaList();
            foreach ($aArea as $v){
                $aTmp[$v['name']] = $v['name'];
            }
            $aOrder['deliveryArea'] = $aTmp;

            $aRet = $objDelivery->getDlTypeList();
            foreach ($aRet as $v){
                $aShipping[$v['dt_id']] = $v['dt_name'];
            }
            $aOrder['selectDelivery'] = $aShipping;

            $objPayment = $this->system->loadModel('trading/payment');
            $aRet = $objPayment->getMethods();
            foreach ($aRet as $v){
                $aPayment[$v['id']] = $v['custom_name'];
            }
            $aOrder['selectPayment'] = $aPayment;


            $objCurrency = $this->system->loadModel('system/cur');
            $aRet = $objCurrency->curAll();
            foreach ($aRet as $v){
                $aCurrency[$v['cur_code']] = $v['cur_name'];
            }
            $aOrder['curList'] = $aCurrency;


            $objPo = $this->system->loadModel('purchase/order_po');
            $aOrder['po'] = $objPo->getPoListByOrderId($orderid);
            $this->pagedata['order'] = $aOrder;

            $this->__tmpl = 'order/edit_po.html';
            $this->output();
        }
    }

    function saveOrderInfo(){
        $orderid  = $_POST['order_id'];
        $objOrder = $this->system->loadModel('trading/order');
        $objOrder->op_id = $this->op->opid;
        $objOrder->op_name = $this->op->loginName;
        $_POST['is_protect'] = isset($_POST['is_protect']) ? $_POST['is_protect'] : 'false';
        $_POST['is_tax'] = isset($_POST['is_tax']) ? $_POST['is_tax'] : 'false';
        $_POST['discount'] = 0 - $_POST['discount'];
        $_POST['pmt_amount'] = ($_POST['pmt_amount']>0) ? $_POST['pmt_amount']:0;
        $objOrder->editOrder($_POST, false);

        $aOrder = $objOrder->getFieldById($orderid);
            $aOrder['discount'] = 0 - $aOrder['discount'];

            $oCur = $this->system->loadModel('system/cur');
            $aCur = $oCur->getSysCur();
            $aOrder['cur_name'] = $aCur[$aOrder['currency']];

            $aOrder['pmt'] = $objOrder->getPmtList($orderid);

            if($aOrder['member_id'] > 0){
                $objMember = $this->system->loadModel('member/member');
                $aOrder['member'] = $objMember->getFieldById($aOrder['member_id'], array('*'));
                $aOrder['ship_email'] = $aOrder['member']['email'];
            }else{
                $aOrder['member'] = array();
            }

            $objDelivery = $this->system->loadModel('trading/delivery');
            $aArea = $objDelivery->getDlAreaList();
            foreach ($aArea as $v){
                $aTmp[$v['name']] = $v['name'];
            }
            $aOrder['deliveryArea'] = $aTmp;

            $aRet = $objDelivery->getDlTypeList();
            foreach ($aRet as $v){
                $aShipping[$v['dt_id']] = $v['dt_name'];
            }
            $aOrder['selectDelivery'] = $aShipping;

            $objPayment = $this->system->loadModel('trading/payment');
            $aRet = $objPayment->getMethods();
            foreach ($aRet as $v){
                $aPayment[$v['id']] = $v['custom_name'];
            }
            $aOrder['selectPayment'] = $aPayment;


            $objCurrency = $this->system->loadModel('system/cur');
            $aRet = $objCurrency->curAll();
            foreach ($aRet as $v){
                $aCurrency[$v['cur_code']] = $v['cur_name'];
            }
            $aOrder['curList'] = $aCurrency;

        $this->pagedata['order'] = $aOrder;

        $this->__tmpl='order/order_info.html';
        $this->output();
    }

    function payPo($orderid, $supplierid, $poid){
        $objOrder = $this->system->loadModel('trading/order');
        $aOrder = $objOrder->getFieldById($orderid);
        $aOrder['discount'] = 0 - $aOrder['discount'];

        $oCur = $this->system->loadModel('system/cur');
        $aCur = $oCur->getSysCur();
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];

        $objCurrency = $this->system->loadModel('system/cur');
        $aRet = $objCurrency->curAll();
        foreach ($aRet as $v){
            $aCurrency[$v['cur_code']] = $v['cur_name'];
        }
        $aOrder['curList'] = $aCurrency;

        $objPo = $this->system->loadModel('purchase/order_po');

        $this->pagedata['payment'] = $objPo->getPaymentCfg($supplierid);

        $aOrder['po'] = $objPo->getPoListByOrderId($orderid);

        $aOrder['supplier'][$supplierid]['po'][$poid];
        $aOrder['supplier_id'] = $supplierid;
        $this->pagedata['order_id'] = $poid;

        $this->pagedata['po'] = $aOrder['po']['supplier'][$supplierid]['po'][$poid];
        $this->pagedata['delivery'] = $objPo->getDlyIsPay($supplierid, $this->pagedata['po']['shipping_id'], $this->pagedata['po']['ship_area']);

        $this->pagedata['payurl'] = $objPo->getSupplierDomain($supplierid,true).'/api.php';
        $this->pagedata['api_version'] = API_VERSION;
        $this->pagedata['order_setting'] = $objPo->getOrderSetting($supplierid);
        $this->pagedata['supplierid'] = $supplierid;
        $this->__tmpl = 'order/po_pay.html';
        $this->output();
    }

    function payByDeposits(){
        set_error_handler(array(&$this,'_err_handler'));
        $objPo = $this->system->loadModel('purchase/order_po');
        $objPo->payByDeposits($_POST['order_id'], $_POST['pay_id']);
        if($this->errorinfo){
            echo '<script>MessageBox.error("'.str_replace('"','\"',$this->errorinfo).'");</script>';
        }else{
            echo 'success';
        }
    }

    function payByAfter(){
        set_error_handler(array(&$this,'_err_handler'));
//        $objPo = $this->system->loadModel('purchase/order_po');
//        $objPo->payByAfter($_POST['order_id'], $_POST['pay_id']);
        if($this->errorinfo){
            echo '<script>MessageBox.error("'.str_replace('"','\"',$this->errorinfo).'");</script>';
        }else{
            echo 'success';
        }
    }

    function get_area($sid, $areaid=0){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $objPo = $this->system->loadModel('purchase/order_po');
        $arr_area = $objPo->getSubRegions($sid,$areaid);
        if($arr_area['data_info']){
            echo '<select name="shipinfo[area_list][]" onchange="get_b2b_area(this)">';
            echo '<option is_node="0" value="0">- 请选择 -</option>';
            foreach($arr_area['data_info'] as $rows){
                echo '<option value="'.$rows['region_id'].'" is_node="'.$rows['is_node'].'">'.$rows['local_name'].'</option>';
            }
            echo '</select>';
        }
    }

    function get_delivery($sid, $areaid=0){
        set_error_handler(array(&$this,'_ajaxErrorHandler'));
        $objPo = $this->system->loadModel('purchase/order_po');
        $aShipping = $objPo->getDlyTypeByArea($sid, $areaid);
        $aOrder['delivery'] = $aShipping['data_info'];

        $aOrder['total_amount'] = 0;
        $aOrder['total_weight'] = 0;
        $product = $this->system->loadModel('goods/products');
        foreach($_POST['dealer_bn'] as $k => $v){
            $aTmp = $product->getFieldByBn($v, array('weight'));
            $aOrder['total_amount'] += ($_POST['price'][$k] * $_POST['nums'][$k]);
            $aOrder['total_weight'] += ($aTmp['weight'] * $_POST['nums'][$k]);
        }
        foreach($aShipping['data_info'] as $k=>$s){
            $aOrder['delivery'][$k]['price'] = cal_fee($s['expressions'],$aOrder['total_weight'],$aOrder['total_amount'],$s['price']);
            if($s['protect']==1){
                $aOrder['delivery'][$k]['protect_fee'] = max($aOrder['total_amount']*$s['protect_rate'],$s['minprice']);
            }else{
                $aOrder['delivery'][$k]['protect_fee'] = 0;
            }
        }
        $this->pagedata['order'] = $aOrder;
        $this->__tmpl='order/po_delivery.html';
        $this->output();
    }

    /**
     * 页面级错误处理
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     */
    function _err_page_handler($errno, $errstr, $errfile = null, $errline = null) {
        switch($errno) {
            case E_USER_ERROR:
                die($errstr);
        }
    }
}
