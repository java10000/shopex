<?php
function widget_menu_lv1($setting,&$system){

    $sitemap = &$system->loadModel('content/sitemap');
    $result=$sitemap->getMap(1);
    $setting['max_leng']=$setting['max_leng']?$setting['max_leng']:7;
    $setting['showinfo']=$setting['showinfo']?$setting['showinfo']:"更多";
    
    return $result;
}
