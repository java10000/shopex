<?php
function widget_cfg_goods_show($system){
    $o=$system->loadModel('goods/products');
    return $o->orderBy();
}
?>