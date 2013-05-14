<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_advance extends objectPage {

    var $workground = 'analytics';
    var $object = 'member/advance';
    var $finder_action_tpl = 'member/advance_finder_action.html'; //默认的动作html模板,可以为null
    var $deleteAble=false; //是否可删除
    var $allowImport = false;
    var $allowExport = false;
    var $filterUnable = true;


    function index(){
        $this->pagedata['sfinddate'] = date("Y-m-",time()).'1';
        $this->pagedata['efinddate'] = date("Y-m-d",time());
        $this->_finder_common($_POST['sdtime']);
        parent::index($options);
    }

    function _finder_common($options){
        $sdtime = explode("/",$options);
        $oAdv = &$this->system->loadModel('member/advance');
        $advanceStatistics = $oAdv->getAdvanceStatistics($sdtime[0], $sdtime[1]);
        $statusStr = __('当前共').$advanceStatistics['count'].__('笔 总转入').$advanceStatistics['import_money'].__('元 总转出').$advanceStatistics['explode_money'].__('元 店内总余额').$oAdv->getShopAdvance().__('元 ');
        $this->pagedata['finder']['statusStr'] = $statusStr;
        $this->pagedata['finder']['select'] = false;
    }

    function finder($type,$view,$cols,$finder_id,$limit){
        $sdtime = explode("/",$_GET['sdtime']);
        $oAdv = &$this->system->loadModel('member/advance');
        $advanceStatistics = $oAdv->getAdvanceStatistics($sdtime[0], $sdtime[1]);
        $statusStr = __('当前共').$advanceStatistics['count'].__('笔 总转入').$advanceStatistics['import_money'].__('元 总转出').$advanceStatistics['explode_money'].__('元 店内总余额').$oAdv->getShopAdvance().__('元 ');

        $_GET['_finder']['statusStr'] =  $statusStr;
        parent::finder($type,$view,$cols,$finder_id,$limit);
    }

    function advancelist($nMId,$nPage=1){
        $oAdv = &$this->system->loadModel('member/advance');
        if($_GET['member_id'])
            $nMId = $_GET['member_id'];
        if($_GET['log_id']){
            $rs = $oAdv->getAdvanceLogByLogId($_GET['log_id']);
            $nMId = $rs['member_id'];
        }
        $advList = $oAdv->getFrontAdvList($nMId,$nPage-1,10);

        $advanceStatistics = $oAdv->getMemberAdvanceStatistics($nMId);
        $statusStr = __('<span class="colborder">当前共').$advanceStatistics['count'].__('笔</span> <span class="colborder">总转入').$advanceStatistics['import_money'].__('元</span> <span class="colborder">总转出').$advanceStatistics['explode_money'].__('元</span> 余额').$oAdv->get($nMId).__('元');
        $this->pagedata['items'] = $advList['data'];
        $this->pagedata['page'] = $nPage;
        $this->pagedata['totalpage'] = $advList['page'];
        $this->pagedata['member_id'] = $nMId;
        $this->pagedata['statusStr'] = $statusStr;
        $this->display('member/advancelist.html');
    }


}
?>
