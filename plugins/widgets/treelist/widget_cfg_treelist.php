<?php
function widget_cfg_treelist($system){
    $o=$system->loadModel('content/sitemap');
    return $o->getList();
}
?>