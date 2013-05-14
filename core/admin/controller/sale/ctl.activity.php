<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_activity extends objectPage{

    var $workground = 'sale';
    var $object = 'trading/promotionActivity';
    var $finder_action_tpl = 'sale/activity/finder_action.html'; //默认的动作html模板,可以为null
    var $finder_default_cols = '_cmd,pmta_id,pmta_name,pmta_time_begin,pmta_time_end,pmta_enabled,pmta_describe';
    var $allowImport = false;
    var $allowExport = false;
    var $noRecycle = true;
    var $filterUnable = true;

    function activityInfo($pmtaId=NULL){
        $this->path[] = array('text'=>__('促销活动内容'));
        $_SESSION['SWP_ACTIVITY'] = null;
        if (!empty($pmtaId)&&intval($pmtaId)!=0) {

            $oPromotionActivity = &$this->system->loadModel('trading/promotionActivity');
            $this->pagedata['pmta']= $oPromotionActivity->getActivityById($pmtaId);
            $this->pagedata['pmta']['pmta_time_begin'] = dateFormat($this->pagedata['pmta']['pmta_time_begin']);
            $this->pagedata['pmta']['pmta_time_end'] = dateFormat($this->pagedata['pmta']['pmta_time_end']);
            $this->pagedata['_S']['act'] = 'edit';
        } else {
            $this->pagedata['pmta']['pmta_enabled'] = 'true';
            $this->pagedata['_S']['act'] = 'add';
        }
        $this->page('sale/activity/activityInfo.html');
    }

    function jumpTo($act='index',$ctl=null,$args=null){
        $_GET['act'] = $act;
        if($ctl) $_GET['ctl'] = $ctl;
        if($args) $_GET['p'] = $args;

        if(!is_null($ctl)){

            if($pos=strpos($_GET['ctl'],'/')){
                $domain = substr($_GET['ctl'],0,$pos);
            }else{
                $domain = $_GET['ctl'];
            }
            $ctl = &$this->system->getController($ctl);
            $ctl->message = $this->message;
            $ctl->pagedata = &$this->pagedata;
            $this->system->callAction($ctl,$act,$args);
        }else{
            $this->system->callAction($this,$act,$args);
        }
    }

    function doActivityInfo($action) {
        $this->path[] = array('text'=>__('促销活动配置完成'));
        if ($action=='add'&& count($_POST)>0) {
            $oPromotionActivity = &$this->system->loadModel('trading/promotionActivity');
            $oPromotion = &$this->system->loadModel('trading/promotion');
            //保存活动信息
            unset($_POST['pmta_id']);
            $nPmtaId = $oPromotionActivity->saveActivity($_POST);
            $_SESSION['SWP_ACTIVITY']['pmta_id'] = $nPmtaId;
            $_SESSION['SWP_ACTIVITY']['pmta_name'] = $_POST['pmta_name'];
            $_SESSION['SWP_PROMOTION'] = NULL;
        }else if ($action='edit'&& count($_POST)>0){
            $oPromotionActivity = &$this->system->loadModel('trading/promotionActivity');
            $oPromotionActivity->saveActivity($_POST);
        }
        $this->completeActivity($action);
    }

    function completeActivity($action) {
        $this->pagedata['pmta'] = $_SESSION['SWP_ACTIVITY'];
        $this->pagedata['action'] = $action;
        $this->page('sale/activity/completeActivity.html');
    }

    function _detail($nMId){
        return array(
            'show_detail'=>array('label'=>__('促销规则'),'tpl'=>'sale/activity/promotion.html'),
            );
    }

    function show_detail($active_id) {
        $promotion = $this->system->loadModel('trading/promotion');
        $this->pagedata['active_id'] = $active_id;
        $this->pagedata['pmts'] = $promotion->getList('pmt_id,pmt_describe,pmt_update_time,pmt_time_begin,pmt_time_end',array('pmta_id'=>$active_id));
    }

    function rm_pmts($active_id) {
        $promotion = $this->system->loadModel('trading/promotion');
        $this->pagedata['active_id'] = $active_id;
        $this->pagedata['pmts'] = $promotion->getList('pmt_id,pmt_describe,pmt_update_time,pmt_time_begin,pmt_time_end',array('pmta_id'=>$active_id));
    }
}
?>