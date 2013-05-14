<?php
function widget_brand($setting,&$system){
    $oGoods=$system->loadModel('goods/brand');
    return $oGoods->getAll();
}
?>