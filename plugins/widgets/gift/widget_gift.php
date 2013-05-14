<?php
function widget_gift(&$setting,&$system){
    //$o=$system->loadModel('content/article');
    $gift = &$system->loadModel('trading/gift');
    $result=$gift->getGiftList(0,$setting['giftnum']?$setting['giftnum']:5,$giftCount,array('ifrecommend'=>1));

//    $oGift->getGiftList(($page-1)*$pageLimit,$pageLimit,$giftCount,$filter)

    /*
    $result = $gift->getList('gift_id,name,thumbnail_pic',array('shop_iffb'=>1,'disabled'=>'false','ifrecommend'=>1,'storage_ifenough'=>1,'time_ifvalid'=>1),0,$setting['giftnum'],$count);
    */
    return $result;
    
}
?>
