<?php
function widget_topbar(&$setting,&$system){
    $o=$system->loadModel('system/cur');

    $data['cur'] = json_encode($o->curAll());
    return $data;
}
