<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_coupon extends objectPage{

    var $workground = 'sale';
    var $object = 'trading/coupon';
    var $finder_action_tpl = 'sale/coupon/finder_action.html'; //默认的动作html模板,可以为null
    var $finder_filter_tpl = 'sale/coupon/finder_filter.html'; //默认的过滤器html,可以为null
    var $finder_default_cols = '_cmd,cpns_name,cpns_prefix,pmt_time_begin,pmt_time_end,cpns_id_c,cpns_type,cpns_status,cpns_gen_quantity,cpns_point';
    var $allowImport = false;
    var $allowExport = false;
    var $noRecycle = true;
    var $filterUnable = true;

    function addCoupon($cpnsId=null) {
        $this->path[] = array('text'=>__('优惠券内容'));
        $_SESSION['SWP_PROMOTION'] = null;
        if ($cpnsId != null) {
            $this->pagedata['ediateable'] = 'true';
            $oCoupon = &$this->system->loadModel('trading/coupon');
            $oPromotion = &$this->system->loadModel('trading/promotion');
            $aData = $oCoupon->getCouponById($cpnsId);
            $_SESSION['SWP_PROMOTION']['couponInfo'] = array(
                    'cpns_name' => $aData['cpns_name'],
                    'cpns_prefix' => substr($aData['cpns_prefix'],1),
                    'cpns_status' => $aData['cpns_status'],
                    'cpns_type' => $aData['cpns_type'],
            );

            $_SESSION['SWP_PROMOTION']['writePromotionRule'] = array(
                    'pmt_solution' => unserialize($aData['pmt_solution']),
                    'pmt_ifcoupon' => $aData['pmt_ifcoupon'],
                    'pmt_time_begin' => dateFormat($aData['pmt_time_begin']),
                    'pmt_time_end' => dateFormat($aData['pmt_time_end']),
                    'pmt_describe' => $aData['pmt_describe']
            );
            $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'] = $aData['pmts_id'];
            $_SESSION['SWP_PROMOTION']['selectProduct']['pmt_bond_type'] = $aData['pmt_bond_type'];
            switch($aData['pmt_bond_type']) {
                case 1:
                    $_SESSION['SWP_PROMOTION']['selectProduct']['bind_goods'] = $oPromotion->getBondGoods($aData['pmt_id']);
                    break;
                case 2:
                    break;
                case 0:
                default:
                    break;
            }

            $_SESSION['SWP_PROMOTION']['basic']['cpns_id'] = $cpnsId;
            $_SESSION['SWP_PROMOTION']['basic']['pmt_id'] = $aData['pmt_id'];
        }
        $this->couponInfo();
    }

    function couponInfo() {
        if (!empty($_SESSION['SWP_PROMOTION']['couponInfo'])) {
            $this->pagedata['cpns'] = $_SESSION['SWP_PROMOTION']['couponInfo'];
            if ($_SESSION['SWP_PROMOTION']['basic']['cpns_id']) {
                $this->pagedata['sys']['act'] = 'edit';
            }
        }else {
            $this->pagedata['cpns']['cpns_enabled'] = 'true';
            $this->pagedata['cpns']['cpns_type'] = 0;
        }
        $this->page('sale/coupon/couponInfo.html');
    }

     function doCouponInfo() {
        $oCoupon = &$this->system->loadModel('trading/coupon');
        $cup=array(0=>"A",1=>"B");
        if($_POST['ediateable']!="true"){
        if (!$_SESSION['SWP_PROMOTION']['basic']['cpns_id']){
                if($oCoupon->checkPrefix($cup[$_POST['cpns_type']].$_POST['cpns_prefix'])){
                    $this->begin('index.php?ctl=sale/coupon&act=addCoupon');
                    $this->end(false,'该号码已经存在');
                }
        }
        }

        $this->path[] = array('text'=>__('优惠券促销规则选择'));
        $this->checkInput();
        $_SESSION['SWP_PROMOTION']['couponInfo'] = &$_POST;
        $this->selectPromotionRule();
    }

//-------------------------------------------

    function selectPromotionRule(){
        $oPromotion = &$this->system->loadModel('trading/promotion');
        $oPromotionScheme = &$this->system->loadModel('trading/promotionScheme');
        $this->pagedata['scheme']['list'] = $oPromotionScheme->getList(array('pmts_type'=>1),false);
        if (!empty($_SESSION['SWP_PROMOTION']['selectPromotionRule'])) {
            $this->pagedata['scheme'] = array_merge($this->pagedata['scheme'], $_SESSION['SWP_PROMOTION']['selectPromotionRule']);
        }else{
            //设置初始值
            list($pmtsId,) = each($this->pagedata['scheme']['list']);
            $this->pagedata['scheme']['pmts_id'] = $pmtsId;
        }
        $this->page('sale/coupon/selectPromotionRule.html');
    }

    function doSelectPromotionRule() {
        $this->path[] = array('text'=>__('优惠券促销规则配置'));
        $this->checkInput();
        $oPromotion = &$this->system->loadModel('trading/promotion');
        if (!empty($_POST['pmts_id']) && ($_POST['pmts_id'] != $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'])) {
            $_SESSION['SWP_PROMOTION']['writePromotionRule'] = null;
            $_SESSION['SWP_PROMOTION']['selectProduct'] = null;//将所有之后的流程制空
        }
        $_SESSION['SWP_PROMOTION']['selectPromotionRule'] = &$_POST;
        $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmt_type'] = 1;
        $this->writePromotionRule();

    }

    function writePromotionRule(){
        $pmtsId = $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'];
        if (!empty($_SESSION['SWP_PROMOTION']['writePromotionRule'])) {
            $pmtSolution = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution'];
            $this->pagedata['pmt']['pmt_ifcoupon'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_ifcoupon'];
            $this->pagedata['pmt']['pmt_time_begin'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_begin'];
            $this->pagedata['pmt']['pmt_time_end'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_end'];
            $this->pagedata['pmt']['pmt_describe'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_describe'];

        } else {
            $this->pagedata['pmt']['pmt_ifcoupon'] = 'true';
            $this->pagedata['pmt']['pmt_time_begin'] = $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_time_begin'];
            $this->pagedata['pmt']['pmt_time_end'] = $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_time_end'];

            $oPromotionScheme = &$this->system->loadModel('trading/promotionScheme');
            $pmtSolution = $oPromotionScheme->getParams($pmtsId,false);
            $pmtSolution = $pmtSolution['pmts_solution'];
        }

        //----------------------------------------------------------------------
        $this->pagedata['solution']['type'] = $pmtSolution['type'];
        foreach($pmtSolution['condition'] as $condition) {
            $this->pagedata['solution']['condition'][$condition[0]] = true;
            switch ($condition[0]) {
                case 'mLev':
                    $oMember = &$this->system->loadModel('member/member');
                    $aMemberLevelList = $oMember->getLevelList(false);
                    foreach ($aMemberLevelList as $k => $v) {
                        $aTmpMList[$v['member_lv_id']] = $v['name'];
                    }
                    $this->pagedata['mLev'] = $aTmpMList;
                    $this->pagedata['pmt']['mLev'] = $condition[1];
                    break;
                default:
                    if($condition[0] == 'orderMoney_to' && !$condition[1]){
                        $condition[1] = 9999999;
                    }
                    $this->pagedata['pmt'][$condition[0]] = $condition[1];
                    break;
            }
        }

        foreach($pmtSolution['method'] as $method) {
            $this->pagedata['solution']['method'][$method[0]] = true;
            $this->pagedata['pmt'][$method[0]] = $method[1];
            switch ($method[0]) {
                case 'giveGift':
                    //todo 过滤掉没发布的赠品
                    $this->pagedata['pmt']['gift_filter'] = array('shop_iffb'=>1,'time_ifvalid'=>1, 'storage_ifenough'=>1);
                    break;
                case 'generateCoupon':
                    $this->pagedata['pmt']['coupon_filter'] = array('cpns_type'=>array(1),'ifvalid'=>1);//todo 过滤掉没发布的coupon
                    break;
                default:

                    break;
            }
        }
        $this->page('sale/coupon/writePromotionRule.html');
    }

    function select(){
        $args = func_get_args();
        $v = unserialize($_POST['data']);
        if($v['cpns_type']){
            $this->finder_filter_tpl=null;
        }
        parent::select();
    }

    function doWritePromotionRule() {
        $this->path[] = array('text'=>__('选择优惠券促销对象'));
        $this->checkInput();
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_ifcoupon'] = $_POST['pmt_ifcoupon'];
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_begin'] = $_POST['pmt_time_begin'];
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_end'] = $_POST['pmt_time_end'];
     //  $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution']['method'][0][1]=$_POST['discount'];

        $tmpPmtSolution = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution'];

        $pmtsId = $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'];
        $oPromotionScheme = &$this->system->loadModel('trading/promotionScheme');
        $aTmp = $oPromotionScheme->getParams($pmtsId,false);

        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution'] = $aTmp['pmts_solution'];
        $pmtSolution = &$_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution'];
        foreach($pmtSolution['condition'] as $k => $condition) {
            $pmtSolution['condition'][$k][1] = $_POST[$pmtSolution['condition'][$k][0]];
        }

        foreach($pmtSolution['method'] as $k => $method) {


            if (isset($tmpPmtSolution['method'][$k][1])){
               
                $pmtSlu=&$pmtSolution['method'];
                $pmtSolution['method'][$k][1]=$_POST[$pmtSlu[$k][0]];
                $pmethod = $_POST[$pmtSlu[$k][0]];
            
                foreach($pmtSlu[$k][1] as $pk => $pv){
  
                    if (isset($pmethod[$pk])){
                        if (!empty($pmethod[$pk]))
                            $pmtSlu[$k][1][$pk]=$pmethod[$pk];
                    }else{
                        unset($pmtSlu[$k][1][$pk]);
}
                }

                if (count($pmtSlu[$k][1])<count($pmethod)){
                    foreach($pmethod as $pmk => $pmkv)
                        if (!isset($pmtSlu[$k][1][$pmk]))
                            $pmtSlu[$k][1][$pmk]=$pmkv;
                }

            }
            else{
                $pmtSolution['method'][$k][1] = $_POST[$pmtSolution['method'][$k][0]];

            }
        }
         
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_describe'] = $_POST['pmt_describe'];
        $this->selectProduct();
    }

    function selectProduct() {
        if (!empty($_SESSION['SWP_PROMOTION']['selectProduct'])) {
            $this->pagedata['pmt'] = $_SESSION['SWP_PROMOTION']['selectProduct'];
        }
        $pmtType = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution']['type'];
        if ($pmtType == 'goods') {
            $this->pagedata['pmt']['pmt_bond_type'] = 1;
        }else if ($pmtType == 'order') {
            $this->pagedata['pmt']['pmt_bond_type'] = 0;
        }
        $this->pagedata['pmt']['type'] = $pmtType;
        $this->page('sale/coupon/selectProduct.html');
    }

    function doSelectProduct() {
        $this->path[] = array('text'=>__('优惠券促销规则配置确认'));
        $this->checkInput();
        $_SESSION['SWP_PROMOTION']['selectProduct'] = &$_POST;
        $_SESSION['SWP_PROMOTION']['selectProduct']['pmt_basic_type'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution']['type'];
        $this->publish();
    }

    function publish() {
        //todo 规则详细说明
        $this->pagedata['data']['cpns_name'] = $_SESSION['SWP_PROMOTION']['couponInfo']['cpns_name'];
        $this->pagedata['data']['pmt_time_begin'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_begin'];
        $this->pagedata['data']['pmt_time_end'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_end'];
        $this->pagedata['data']['pmt_describe'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_describe'];
        $this->pagedata['data']['cpns_type'] = $_SESSION['SWP_PROMOTION']['couponInfo']['cpns_type'];
        $this->page('sale/coupon/publish.html');
    }

    function doPublish() {
        //保存规则信息

        $oPromotion = &$this->system->loadModel('trading/promotion');
        $aPromotion = array_merge(
                            (array)$_SESSION['SWP_PROMOTION']['selectPromotionRule'],
                            (array)$_SESSION['SWP_PROMOTION']['writePromotionRule'],
                            (array)$_SESSION['SWP_PROMOTION']['selectProduct'],
                            (array)$_SESSION['SWP_PROMOTION']['basic']);
        $pmtId = $oPromotion->addPromotion($aPromotion);
        $oCoupon = &$this->system->loadModel('trading/coupon');
        $aCoupon['pmt_id'] = $pmtId;
        $aCoupon = array_merge((array)$aCoupon, (array)$_SESSION['SWP_PROMOTION']['couponInfo'], (array)$_SESSION['SWP_PROMOTION']['basic']);
        $nCpnsId = $oCoupon->addCoupon($aCoupon);
        $this->splash('success', 'index.php?ctl=sale/coupon&act=index');
    }

    function download($cpnsId,$nums){
        $addons = &$this->system->loadModel('system/addons');
        $exporter = $addons->load('csv','io');
        $oCoupon = &$this->system->loadModel('trading/coupon');
        if ($list = $oCoupon->downloadCoupon($cpnsId,$nums)) {
            $exporter->export_begin(array(__('优惠券代码')),'mcoupon',$nums);
            $exporter->export_rows($list);
            $exporter->export_finish();
        }else{
            header("Content-type: text/html; charset=UTF-8");
            echo __('<script>alert("当前优惠券未发布/时间未到,暂时不能下载")</script>');
        }
    }

    function _detail(){
        return array('show_detail'=>array('label'=>__('优惠券发放途径'),'tpl'=>'sale/coupon/generator.html'));
    }

    function show_detail($cpns_id) {
        $generator = &$this->system->loadModel('trading/couponGenerate');
        $this->pagedata['pmts'] = $generator->getList('sdb_pmt_gen_coupon.pmt_id,cpns_id,pmt_time_begin,pmt_time_end,pmt_describe',array('cpns_id'=>$cpns_id));
    }

    function checkInput() {
        if (!$_POST) {
            $this->index();
        }
    }

    function delActivity() {
        $oPromotionActivity = &$this->system->loadModel('trading/promotionActivity');
        $activityIds = $oPromotionActivity->finderResult($_POST['items']);
        if ($oPromotionActivity->delActivity($activityIds,$msg)) {
            $this->splash('success', 'index.php?ctl=sale/activity&act=index');
        } else {
            $this->splash('failed', 'index.php?ctl=sale/activity&act=index',  $msg);
        }
    }

}
?>
