<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_delivery
 *
 * @uses pageFactory
 * @package
 * @version $Id: ctl.delivery.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Likunpeng <leoleegood@zovatech.com>
 * @license Commercial
 */
include_once('delivercorp.php');
include_once('objectPage.php');
class ctl_delivery extends objectPage {

    var $workground ='setting';
    var $object = 'trading/delivery';
    var $finder_action_tpl = 'delivery/finder_action.html'; //默认的动作html模板,可以为null
    var $finder_default_cols = '_cmd,dt_name,dt_status,protect,has_cod,ordernum';
    var $allowImport = false;
    var $allowExport = false;
    var $filterUnable = true;

    function _weightunit(){
        return array(
            "500"=>__("500克"),
            "1000"=>__("1公斤"),
            "1200"=>__("1.2公斤"),
            "2000"=>__("2公斤"),
            "5000"=>__("5公斤"),
            "10000"=>__("10公斤"),
            "20000"=>__("20公斤"),
            "50000"=>__("50公斤")
        );
    }
    /**
    * newTypeStep
    *
    * @access public
    * @return void
    */
    function newTypeStep(){
        $this->path[] = array('text'=>__('添加配送方式'));
        $oObj = &$this->system->loadModel('trading/delivery');
        $oConsign=&$this->system->loadModel('trading/payment');
        $aTemp=$oConsign->getList();
        for($i=0;$i<count($aTemp);$i++){
            $aPayment[]=array('pid'=>$aTemp[$i]['payment_id'],'name'=>$aTemp[$i]['name']);
        }
        //配送地区
        //$aArea = $oObj->getDlAreaList();
        $this->pagedata['payment'] = $aPayment;
        //$this->pagedata['area'] = $aArea;
        $this->pagedata['hasCod'] = 0;
        $this->pagedata['price'] = $sPrice;
        //物流公司列表
        $corp=&$this->system->loadModel('trading/deliverycorp');
        $this->pagedata['clist'] = $corp->getCorpList();
        if(defined('SAAS_MODE')&&SAAS_MODE){
        $this->pagedata['clist'] = getdeliverycorplist();
        }

        //
        $this->pagedata['weightunit'] = $this->_weightunit();
        $this->pagedata['config']=array(
                'firstunit' => '1000',
                'continueunit'=>'1000'
        );
        $this->page('delivery/dtype_edit.html');
    }

    function showEdit($nDTid){
        if (!$nDTid) {
            $this->newTypeStep();
            return;
        }
        $oObj = &$this->system->loadModel('trading/delivery');
        $aData = $oObj->getDlTypeById($nDTid);
        $this->pagedata['config'] = unserialize($aData['dt_config']);
        $this->pagedata['dt_id'] = $nDTid;
        $this->pagedata['dt_name'] = $aData['dt_name'];
        $this->pagedata['dt_status'] = $aData['dt_status'];
        $this->pagedata['ordernum'] = $aData['ordernum'];
        $this->pagedata['protect'] = $aData['protect'];
        $this->pagedata['hasCod'] = $aData['has_cod'];
        $corp=&$this->system->loadModel('trading/deliverycorp');
        $this->pagedata['clist'] = $corp->getCorpList();
        if(defined('SAAS_MODE')&&SAAS_MODE){
        $this->pagedata['clist'] = getdeliverycorplist();
        }
        $this->pagedata['corp_id'] = $aData['corp_id'];
        $this->pagedata['detail'] = $aData['detail'];
        $this->pagedata['weightunit'] = $this->_weightunit();
        $area = $oObj->getAreaByDtId($nDTid);
        foreach($area as $key => $val){
            $area[$key]['config'] = unserialize($val['config']);
        }
        $this->pagedata['area'] = $area;
        $this->path[] = array('text'=>__('编辑配送方式'));
        $this->page('delivery/dtype_edit.html');
    }
    /**
    * saveDlType
    *
    * @access public
    * @return void
    */
    function saveDlType(){
        $this->begin('index.php?ctl=trading/delivery&act=index');
        if ($_POST['protect']=="1"){
            if ($_POST['protectrate']==""){
                $this->splash('failed','index.php?ctl=trading/delivery&act=showEdit&p[0]='.$_POST['dt_id'],__('保价百分率不能为空！'));
            }
            if ($_POST['minprotectprice']==""){
                $this->splash('failed','index.php?ctl=trading/delivery&act=showEdit&p[0]='.$_POST['dt_id'],__('最低保价金额不能为空！'));
            }
        }
        $oObj = &$this->system->loadModel('trading/delivery');
        $this->end($oObj->saveDlType($_POST), __('保存成功！'));
    }

    /**
    * delDlType
    *
    * @access public
    * @return void
    */
    function delDlType($sId){
        $oObj = &$this->system->loadModel('trading/delivery');
        if (empty($sId)) {
            $this->splash('failed','index.php?ctl=trading/delivery&act=index',__('请选择要删除的项！'));
        }
        if ($oObj->deleteDlType($sId)) {
            $this->splash('success','index.php?ctl=trading/delivery&act=index',__('删除成功'));
        } else {
            $this->splash('failed','index.php?ctl=trading/delivery&act=index',__('删除失败'));
        }
    }

    /**
    * showRateHelp
    *
    * @access public
    * @return void
    */
    function showRateHelp(){
        $this->page('delivery/help_rate.html');
    }

    /**
    * showMantesHelp
    *
    * @access public
    * @return void
    */
    function showMantesHelp(){
        $this->page('delivery/help_mantes.html');
    }

    /**
    * dlAreaList
    *
    * @access public
    * @return void
    */
    function dlAreaList(){
        $this->path[] = array('text'=>__('配送地区'));
        $oObj = &$this->system->loadModel('trading/delivery');
        $this->pagedata['items'] = $oObj->getDlAreaList();
        $this->page('delivery/area_list.html');
    }

    /**
    * detailDlArea
    *
    * @access public
    * @return void
    */
    function detailDlArea($aRegionId){
        $this->path[] = array('text'=>__('配送地区编辑'));
        $oObj = &$this->system->loadModel('trading/delivery');
        $this->pagedata['area'] = $oObj->getDlAreaById($aRegionId);
        $this->page('delivery/area_edit.html');
    }

    /**
    * saveDlArea
    *
    * @access public
    * @return void
    */
    function saveDlArea(){
        $oObj = &$this->system->loadModel('trading/delivery');
        if(!$oObj->updateDlArea($_POST,$msg)){
            $this->message = array('string'=>__('保存失败，').$msg,'type'=>MSG_ERROR);
            $this->splash('failed','index.php?ctl=trading/delivery&act=detailDlArea&p[0]='.$_POST['region_id'],$this->message['string']);
        }else
            $this->splash('success','index.php?ctl=trading/delivery&act=detailDlArea&p[0]='.$_POST['region_id']);

    }

    /**
    * showNewArea
    *
    * @access public
    * @return void
    */
    function showNewArea(){
        $this->path[] = array('text'=>__('添加配送地区'));
        $this->page('delivery/area_new.html');
    }

    /**
    * addDlArea
    *
    * @access public
    * @return void
    */
    function addDlArea(){
        $oObj = &$this->system->loadModel('trading/delivery');
        if(!$oObj->insertDlArea($_POST,$msg)){
            $this->message = array('string'=>__('保存失败，').$msg,'type'=>MSG_ERROR);
            $this->splash('failed','index.php?ctl=trading/deliveryarea&act=index',$this->message['string']);
        }else
            $this->splash('success','index.php?ctl=trading/deliveryarea&act=index');

    }

    /**
    * delDlArea
    *
    * @access public
    * @return void
    */
    function delDlArea($sId){
        $oObj = &$this->system->loadModel('trading/delivery');
        if (empty($sId)) {
            $this->splash('failed','index.php?ctl=trading/delivery&act=dlAreaList',__('请选择要删除的项！'));
        }
        if ($oObj->deleteDlArea($sId)) {
            $this->splash('success','index.php?ctl=trading/delivery&act=dlAreaList',__('删除成功'));
        } else {
            $this->splash('failed','index.php?ctl=trading/delivery&act=dlAreaList',__('删除失败'));
        }
    }


    /**
    * dlCorpList
    *
    * @access public
    * @return void
    */
    function dlCorpList(){
        $this->path[] = array('text'=>__('物流公司'));
        $this->pagedata['ctype'] = json_encode($this->_getAreaType());
        $oObj = &$this->system->loadModel('trading/delivery');
        $aCorp = $oObj->getCropList();
        if($aCorp){
            $aRel = $this->_getAreaTypeRel();
            foreach($aCorp as $key=>$val) {
                $aCorp[$key]['type'] = $aRel[$val['type']];
            }
        }
        $this->pagedata['items'] = $aCorp;
        $this->page('delivery/corp_list.html');
    }

    /**
    * detailDlCorp
    *
    * @access public
    * @return void
    */
    function detailDlCorp($nCorpId){
        $this->path[] = array('text'=>__('物流公司编辑'));
        $oObj = &$this->system->loadModel('trading/delivery');
        $this->pagedata['corp'] = $oObj->getCorpById($nCorpId);
        $this->pagedata['ctype'] = $this->_getAreaType();
        $this->page('delivery/corp_edit.html');
    }

    /**
    * _getAreaType
    *
    * @access public
    * @return void
    */
    function _getAreaType(){
        return array(
            0=>array("CNEMS",__("中国邮政EMS"),'http://www.ems.com.cn/'),
            1=>array("CNST",__("申通快递"),'http://www.sto.cn/'),
            2=>array("CNTT",__("天天快递"),'http://www.ttkd.cn/'),
            3=>array("CNYT",__("圆通速递"),'http://www.yto.net.cn/'),
            4=>array("CNSF",__("顺丰速运"),'http://www.sf-express.com/'),
            5=>array("CNYD",__("韵达快递"),'http://www.yundaex.com/'),
            6=>array("CNZT",__("中通速递"),'http://www.zto.cn/'),
            7=>array("CNLB",__("龙邦物流"),'http://www.lbex.com.cn/'),
            8=>array("CNZJS",__("宅急送"),'http://www.zjs.com.cn/'),
            9=>array("CNQY",__("全一快递"),'http://www.apex100.com/'),
            10=>array("CNHT",__("汇通速递"),'http://www.htky365.com/'),
            11=>array("CNMH",__("民航快递"),'http://www.cae.com.cn/'),
            12=>array("CNYF",__("亚风速递"),'http://www.airfex.cn/'),
            13=>array("CNKJ",__("快捷速递"),'http://www.fastexpress.com.cn/'),
            14=>array("DDS",__("DDS快递"),'http://www.qc-dds.net/'),
            15=>array("CNHY",__("华宇物流"),'http://www.hoau.net/'),
            16=>array("CNZY",__("中铁快运"),'http://www.cre.cn/'),
            17=>array("FEDEX",__("FedEx"),'http://www.fedex.com/cn/'),
            18=>array("UPS","UPS",'http://www.ups.com/'),
            19=>array("DHL",__("DHL"),'http://www.cn.dhl.com/'),
            20=>array("OTHER",__("其它")),
            21=>array("LBKD",__("联邦快递"),'http://cndxp.apac.fedex.com/dxp.html'),
        );
    }
    function _getAreaTypeRel(){
        return array(
            "CNEMS"=>__("中国邮政EMS"),
            "CNST"=>__("申通快递"),
            "CNTT"=>__("天天快递"),
            "CNYT"=>__("圆通速递"),
            "CNSF"=>__("顺丰速运"),
            "CNYD"=>__("韵达快递"),
            "CNZT"=>__("中通速递"),
            "CNLB"=>__("龙邦物流"),
            "CNZJS"=>__("宅急送"),
            "CNQY"=>__("全一快递"),
            "CNHT"=>__("汇通速递"),
            "CNMH"=>__("民航快递"),
            "CNYF"=>__("亚风速递"),
            "CNKJ"=>__("快捷速递"),
            "DDS"=>__("DDS快递"),
            "CNHY"=>__("华宇物流"),
            "CNZY"=>__("中铁快运"),
            "FEDEX"=>__("FedEx"),
            "UPS"=>"UPS",
            "DHL"=>__("DHL"),
            "OTHER"=>__("其它"),
            "LBKD"=>__("联邦快递"),
        );
    }
    /**
    * saveCorp
    *
    * @access public
    * @return void
    */
    function saveCorp(){
        $oObj = &$this->system->loadModel('trading/delivery');
        if(!$oObj->updateCorp($_POST,$msg)){
            $this->message = array('string'=>__('保存失败，').$msg,'type'=>MSG_ERROR);
            $this->splash('failed','index.php?ctl=trading/deliverycorp&act=detail&p[0]='.$_POST['corp_id']);
        }else
            $this->splash('success','index.php?ctl=trading/deliverycorp&act=detail&p[0]='.$_POST['corp_id']);
    }

    /**
    * showNewCorp
    *
    * @access public
    * @return void
    */
    function showNewCorp(){
        $this->path[] = array('text'=>__('添加物流公司'));
        $this->pagedata['ctype'] = $this->_getAreaType();
        $this->page('delivery/corp_new.html');
    }

    /**
    * addCorp
    *
    * @access public
    * @return void
    */
    function addCorp(){
        $oObj = &$this->system->loadModel('trading/delivery');
        if(!$oObj->insertCorp($_POST,$msg)){
            $this->message = array('string'=>__('保存失败，').$msg,'type'=>MSG_ERROR);
            $this->splash('failed','index.php?ctl=trading/deliverycorp&act=index', $this->message['string']);
        }else
            $this->splash('success','index.php?ctl=trading/deliverycorp&act=index');

    }

    /**
    * delCorp
    *
    * @access public
    * @return void
    */
    function delCorp($sId){
        $oObj = &$this->system->loadModel('trading/delivery');
        if (empty($sId)) {
            $this->message = array('string'=>__('请选择要删除的项！'), 'type'=>MSG_ERROR);
        }
        if ($oObj->deleteCorp($sId)) {
            $this->message = array('string'=>__('Delete succeed!'),'type'=>MSG_OK);
            $this->splash('success','index.php?ctl=trading/delivery&act=dlCorpList');
        } else {
            $this->message = array('string'=>__('Delete failed!'),'type'=>MSG_ERROR);
            $this->splash('failed','index.php?ctl=trading/delivery&act=dlCorpList');
        }
    }

    /**
    * addCorpToType
    *
    * @access public
    * @return void
    */
    function addAreaToType(){
        $oObj = &$this->system->loadModel('trading/delivery');
        $insertId = $oObj->assistantInsertArea($_POST);
        echo $insertId;
    }

    function checkExp(){
        $oObj = &$this->system->loadModel('trading/delivery');
        $this->pagedata['expressions'] = $_GET['expvalue'];
        $this->display('delivery/check_exp.html');
    }
    function getRegionById($pregionid){
        $delivery = &$this->system->loadModel('trading/delivery');
        echo json_encode($delivery->getRegionById($pregionid));
    }
     function showRegionTreeList($serid,$multi=false){
         if($serid){
         $this->pagedata['sid'] = $serid;
         }else{
         $this->pagedata['sid'] = substr(time(),6,4);
         }
         $this->pagedata['multi'] =  $multi;
         $this->display('regionSelect.html');
    }
    function save_cell_value($id,$key){
        if($key == "ordernum"){
            if(intval($_POST['data']) <= 0){
                echo "排序必须为正整数";
                exit;
            }
        }
        parent::save_cell_value($id,$key);
     }
}
?>
