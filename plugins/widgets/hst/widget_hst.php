<?php
function widget_hst(&$setting,&$system){
    $storager = $system->loadModel('system/storager');
    $result['default_thumbnail_pic'] = $storager->getUrl($system->getConf('site.default_thumbnail_pic'));
    return $result;
}
?>