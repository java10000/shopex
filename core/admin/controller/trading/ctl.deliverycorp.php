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
include_once('objectPage.php');
class ctl_deliverycorp extends objectPage {

    var $workground ='setting';
    var $object = 'trading/deliverycorp';
    var $finder_action_tpl = 'delivery/corp_finder_action.html'; //默认的动作html模板,可以为null
    var $filterUnable = true;

    function _detail(){
        return array('show_detail'=>array('label'=>__('物流公司'),'tpl'=>'delivery/corp_edit.html'));
    }

    function show_detail($nCorpId){
        $oObj = &$this->system->loadModel('trading/delivery');
        $this->pagedata['corp'] = $oObj->getCorpById($nCorpId);
        $this->pagedata['ctype'] = $this->_getAreaType();
    }

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
}
?>
