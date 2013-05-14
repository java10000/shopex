<?php
function widget_cfg_goods($system){
    $o=$system->loadModel('goods/products');
    return $o->orderBy();
}
?>