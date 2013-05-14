<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * 采购单控制器
 * @copyright 2003-2009 ShopEx
 * @author gesion
 * @license Commercial
 */

include_once 'objectPage.php';

class ctl_po extends objectPage {
    
    var $name       = '采购单';
    var $workground = 'distribution';
    var $object     = 'purchase/po';
    var $filterView = 'po/filter.html';
    var $detail_title = 'po/detail_title.html';
    var $deleteAble = false;
    
    function index() {
        set_error_handler(array(&$this,'_pageErrorHandler'));
        $mdl = $this->system->loadModel('purchase/po');
        $this->pagedata['suppliers'] = $mdl->getSupplierList();
        parent::index();
    }
    
    function _detail() {
        return array(
            'detail_info'=>array('label'=>__('基本信息'),'tpl'=>'po/detail_info.html'),
            'detail_money'=>array('label'=>__('财务往来'),'tpl'=>'po/detail_money.html'),
            'detail_ship'=>array('label'=>__('物流往来'),'tpl'=>'po/detail_ship.html'),
       );
    }
    
    function filterActions(&$row){
        $return = $this->actions;
        if ($row['status'] == 'pending') {
            $return['pause'] = '_none_';
        } elseif ($row['status'] == 'active' and $row['__pay_status'] == 1 and $row['__ship_status'] == 0) {
            $return['cancel_pause'] = '_none_';
        } else {
            $return['pause'] = '_none_';
            $return['cancel_pause'] = '_none_';
        }
        return $return;
    }
    
    function detail_info($order_id) {
        $api = $this->system->loadModel('purchase/po');
        $POrder = $api->getOrder($order_id);
        $this->pagedata['POrder'] = $POrder;
    }
    
    function detail_money($order_id) {
        $api = $this->system->loadModel('purchase/po');
        $POrder = $api->getOrder($order_id);
        $this->pagedata['POrder'] = $POrder;
    }
    
    function detail_ship($order_id) {
        $api = $this->system->loadModel('purchase/po');
        $POrder = $api->getOrder($order_id);
        $this->pagedata['POrder'] = $POrder;
    }
    
    /*
    function detail_items($order_id) {
        $api = $this->system->loadModel('purchase/po');
        $this->pagedata['POrder'] = $api->getPOList($order_id);
        $this->__tmpl = 'po/order_items.html';
        $this->output();
    }*/
    
    /**
     * 暂停发货
     * 
     * @param int $order_id 采购单号
     * @return void
     */
    function pause($order_id) {
        $this->begin('index.php?ctl=order/po&act=index');
        $api = $this->system->loadModel('purchase/po');
        $this->end($api->pausePOrder($order_id), __('操作成功'));
    }
    
    /**
     * 取消暂停发货
     * 
     * @param int $order_id 采购单号
     * @return void
     */
    function cancel_pause($order_id) {
        $this->begin('index.php?ctl=order/po&act=index');
        $api = $this->system->loadModel('purchase/po');
        $this->end($api->cancelPausePOrder($order_id), __('操作成功'));
    }
    
    /**
     * 撤消采购单
     * 
     * @param int $order_id 采购单号
     * @return void
     */
    function cancel($order_id) {
        $this->begin('index.php?ctl=order/po&act=detail&p[0]='.$order_id);
        $api = $this->system->loadModel('purchase/po');
        $res = $api->cancelPOrder($order_id);
        if ($res) {
            $this->setError(10001);
            trigger_error('撤销采购单成功', E_USER_NOTICE);
        } else {
            $this->setError(10002);
            trigger_error('撤销采购单失败', E_USER_ERROR);
        }
        $this->end();
    }
    
    /**
     * 向客户退款
     * 
     * @param int $order_id 采购单号
     * @param int $dealer_order_id 本地订单号
     * @return void
     */
    function refund($order_id, $dealer_order_id) {
        $arr['order_id'] = $dealer_order_id;
        $arr['opid'] = $this->op->opid;
        $arr['opname'] = $this->op->loginName;
        $this->begin('index.php?ctl=order/po&act=detail&p[0]='.$order_id);
        $obj = $this->system->loadModel('trading/order');
        $obj->op_id = $this->op->opid;
        $obj->op_name = $this->op->loginName;
        if ($obj->refund($arr)) {
            $this->setError(10001);
            trigger_error('退款成功', E_USER_NOTICE);
        } else {
            $this->setError(10002);
            trigger_error('退款失败', E_USER_ERROR);
        }
        $this->end();
    }
}
?>