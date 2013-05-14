<?php
function widget_cfg_virtualcat($system){
    $objCat = $system->loadModel('goods/virtualcat');
    return $objCat->getMapTree(0,'');
}
?>