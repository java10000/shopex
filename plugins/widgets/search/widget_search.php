<?php
function widget_search(&$setting,&$system){
    $setting['search']=$GLOBALS['search'];
    $data=$system->getConf('search.show.range');
    return $data;
}

