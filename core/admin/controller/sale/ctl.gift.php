<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_gift extends objectPage{

    var $object = 'trading/gift';
    var $finder_action_tpl = 'sale/gift/finder_action.html'; //默认的动作html模板,可以为null
    var $finder_filter_tpl = 'sale/gift/finder_filter.html'; //默认的过滤器html,可以为null
    var $finder_default_cols = '_cmd,gift_bn,name,giftcat_id,limit_start_time,limit_end_time,point,storage,shop_iffb,orderlist,ifrecommend,limit_num';
    var $workground = 'sale';
    var $filterUnable = true;

    function getTypeList() {
        $this->page('sale/giftcat/list.html',true);
    }

    function addGift(){
        $this->begin('index.php?ctl=sale/gift&act=index');
        $oGift = &$this->system->loadModel('trading/gift');
        if($oGift->saveGift($_POST)){
            $this->end(true,__('赠品添加成功'));
        }else{
            $this->end(false,__('赠品添加失败'));

        }
    }

    function addType(){
        $oGiftCat = &$this->system->loadModel('trading/giftcat');
        if(!$oGiftCat->addType($_POST)){
            //todo 出错信息
        }
        $this->splash('success','index.php?ctl=sale/giftcat&act=index');
    }

    function showAddType($catId){
        $this->path[] = array('text'=>__('赠品分类内容页'));
        $oGiftCat = &$this->system->loadModel('trading/giftcat');
        if ($catId) {
            $this->pagedata['giftcat'] = $oGiftCat->getTypeById($catId);
        } else {
            $this->pagedata['giftcat']['shop_iffb'] = 0;
            $this->pagedata['giftcat']['orderlist'] = $oGiftCat->getInitOrder();
        }
        $this->page('sale/giftcat/addType.html');
    }

    function showAddGift($giftId=null) {
        $this->path[] = array('text'=>__('赠品内容页'));
        $oGift = &$this->system->loadModel('trading/gift');
        $oMember = &$this->system->loadModel('member/member');
        $this->pagedata['catList'] = $oGift->getTypeArr();
        if(count($this->pagedata['catList'])<1){
            $this->splash('failed','index.php?ctl=sale/gift&act=showAddType',__('缺少赠品类别，无法添加赠品。转到添加赠品类别'));
        }
        if ($giftId) {
            $this->pagedata['gift'] = $oGift->getGiftById($giftId);
            $this->pagedata['gift']['limit_end_time'] = dateFormat($this->pagedata['gift']['limit_end_time']);
            $this->pagedata['gift']['limit_start_time'] = dateFormat($this->pagedata['gift']['limit_start_time']);
            $this->pagedata['gift']['mLev'] = explode(',', $this->pagedata['gift']['limit_level']);
        } else {
            $this->pagedata['gift']['giftcat_id'] = $aType[0][0]['giftcat_id'];
            $this->pagedata['gift']['shop_iffb'] = 1;
            $this->pagedata['gift']['ifrecommend'] = 1;
            $this->pagedata['gift']['limit_num'] = 1;
            $this->pagedata['gift']['orderlist'] = $oGift->getInitOrder();
        }
        $aMemberLevelList = $oMember->getLevelList(false);
        foreach ($aMemberLevelList as $k => $v) {
            $aTmpMList[$v['member_lv_id']]['name'] = $v['name'];
            if (in_array($v['member_lv_id'],$this->pagedata['gift']['mLev']))
                $aTmpMList[$v['member_lv_id']]['checked'] = 'checked';
        }
        $this->pagedata['mLev'] = $aTmpMList;

        $this->page('sale/gift/addGift.html');
    }

    function delType(){
        $oGiftCat = &$this->system->loadModel('trading/giftcat');
        $giftCatIds = $oGiftCat->finderResult($_POST['items']);

        if ($oGiftCat->delType($giftCatIds,$msg)) {
            $this->splash('success', 'index.php?ctl=sale/giftcat&act=index');
        } else {
            $this->splash('failed', 'index.php?ctl=sale/giftcat&act=index', $msg);
        }
    }
}
?>
