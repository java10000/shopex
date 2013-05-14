<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_wholesale extends shopPage{

    function index($catId=0,$page=1) {
        $pageLimit = 20;
        $filter = array('cat_id'=>$catId, 'storage_ifenough'=>1,'mlevel'=>$GLOBALS['runtime']['member_lv']);
        $oFinderPdt = &$this->system->loadModel('goods/finderPdt');
        if ($aPdt = $oFinderPdt->getList('p.product_id,p.goods_id,cat_id,p.name,p.pdt_desc', $filter,($page-1)*$pageLimit,$pageLimit)) {
            $count = $oFinderPdt->count($filter);
            $this->pagedata['products'] = $aPdt;
        }else{
            trigger_error(__('查询失败'),E_USER_NOTICE);
        }

        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($count/$pageLimit),
            'link'=>$this->system->mkUrl('wholesale','index',array($catId,($tmp = time()))),
            'token'=>$tmp);
        if($page > $this->pagedata['pager']['total']){
            trigger_error(__('查询数为空'),E_USER_NOTICE);
        }

        $this->output();
    }
}
?>
