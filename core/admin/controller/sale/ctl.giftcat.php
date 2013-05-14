<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_giftcat extends objectPage{

    var $object = 'trading/giftcat';
    var $workground = 'sale';
    var $finder_action_tpl = 'sale/giftcat/finder_action.html'; //默认的动作html模板,可以为null
    var $finder_filter_tpl = null; //默认的过滤器html,可以为null
    var $filterUnable = true;

    function getTypeList() {
        $this->page('sale/giftcat/list.html',true);
    }

    function addType(){
        if($_POST['giftcat_id']){
            $this->begin('index.php?ctl=sale/giftcat&act=detail&p[0]='.$_POST['giftcat_id']);
        }else{
            $this->begin('index.php?ctl=sale/giftcat&act=index');
        }
        $oGiftCat = &$this->system->loadModel('trading/giftcat');
        $this->end($oGiftCat->addType($_POST), '保存完成');
    }

    function showAddType($catId){
        $oGiftCat = $this->system->loadModel('trading/giftcat');
        if ($catId) {
            $this->pagedata['giftcat'] = $oGiftCat->getTypeById($catId);
        } else {
            $this->pagedata['giftcat']['shop_iffb'] = 0;
            $this->pagedata['giftcat']['orderlist'] = $oGiftCat->getInitOrder();
        }
        $this->page('sale/giftcat/addType.html');
    }

    function _detail(){
        return array('show_detail'=>array('label'=>__('赠品分类详情'),'tpl'=>'sale/giftcat/addType.html'));
    }

    function show_detail($catId){
        $oGiftCat = &$this->system->loadModel('trading/giftcat');
        if ($catId) {
            $this->pagedata['giftcat'] = $oGiftCat->getTypeById($catId);
        } else {
            $this->pagedata['giftcat']['shop_iffb'] = 0;
            $this->pagedata['giftcat']['orderlist'] = $oGiftCat->getInitOrder();
        }
    }

    function recycle(){
        $oGift = &$this->system->loadModel('trading/gift');
        $varGoto = true;
        foreach($_POST['giftcat_id'] as $cat_id){
            $count = $oGift->count(array('giftcat_id'=>array($cat_id)));
            if($count){
                echo __('该赠品分类下还有赠品，请先删除赠品后再删除分类！');
                $varGoto = false;
                break;
            }
        }
        if($varGoto){
            parent::recycle();
        }
    }
}
?>
