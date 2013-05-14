<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_gift extends shopPage{
    var $customer_template_type='gift';
    function showTypeList($catId=0) {
        $oGift = &$this->system->loadModel('trading/gift');
        $this->pagedata['giftType'] = $oGift->getTypeList();
        $this->output();
    }

    function showList($catId=0,$page=1) {
        if($catId){
            $filter['gid']=$catId;
        }
        $this->customer_template_id=$catId;
        $this->path[]=array('title'=>__('赠品'));
        $pageLimit = 10;
        $oGift = &$this->system->loadModel('trading/gift');
        $oGiftCat = &$this->system->loadModel('trading/giftcat');

        if ($aGift = $oGift->getGiftList(($page-1)*$pageLimit,$pageLimit,$giftCount,$filter)) {

            $storager = &$this->system->loadModel('system/storager');
            while (list($k,) = each($aGift)) {
                $aGift[$k]['image']['default'] = $storager->getUrl($aGift['image_default']);
                if ($oGift->isOnSale($aGift[$k], $_COOKIE['MLV'])){
                    $aGift[$k]['sale_status'] = 1;
                }else {
                    $aGift[$k]['sale_status'] = 0;
                }
            }
            if ($catId) {
                $this->title = $aGift[0]['cat'];
            }else{
                $this->title = __('所有赠品');
            }
            $this->pagedata['giftList'] = $aGift;
        }

        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($giftCount/$pageLimit),
            'link'=>$this->system->mkUrl('gift','showList',array($catId,($tmp = time()))),
            'token'=>$tmp);

        $this->output();
    }

    function index($giftId) {
        $oGift = &$this->system->loadModel('trading/gift');
        $oLev = &$this->system->loadModel('member/level');

        $aGift = $oGift->getGiftById($giftId);
        if(!$aGift){
            trigger_error(__('找不到此赠品'),E_USER_NOTICE);
        }
        if ($oGift->isOnSale($aGift, $_COOKIE['MLV'])){
            $aGift['sale_status'] = 1;
        }else {
            $aGift['sale_status'] = 0;
        }
        $this->title = $aGift['name'];
        $this->customer_template_type = 'gift';
        $this->customer_template_id = $giftId;
        $storager = &$this->system->loadModel('system/storager');

        $aGift['image']['default'] = $storager->getUrl($aGift['image_default']);
        foreach(explode(',',$aGift['image_file']) as $id){
            $aImageFile[] = $storager->getUrl($id);
        }
        $aGift['image']['file'] = $aImageFile;
        if(!$aGift['limit_level']) $aGift['limit_level'] = array(0);
        $aGift['limit_level'] = $oLev->getMLevel($aGift['limit_level']);
        $this->pagedata['details'] = $aGift;
        $this->output();
    }
}
?>
