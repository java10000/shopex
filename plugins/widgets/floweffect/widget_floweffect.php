<?php
function widget_floweffect(&$setting,&$system){

    $gimage = &$system->loadModel('goods/gimage');
    $o = &$system->loadModel('goods/products');
    $xml = &$system->loadModel('utility/xml');
    parse_str($setting['filter'],$filter);
    $list = $o->getList(null,$filter,0,$setting['limit']?$setting['limit']:10,$count);
    $aTmp = $list;
    foreach($aTmp as $k=>$v){
        $list[$k]['picture'] =$gimage->get_resource_by_id($v['image_default'],'small');
        unset($list[$k]['image_default']);
        unset($list[$k]['thumbnail_pic']);
        unset($list[$k]['pdt_desc']);
    }
    unset($aTmp);
    return array('xml'=>$xml->array2xml(array('products'=>&$list)),'count'=>$count,'haystack'=>time());
}
?>
