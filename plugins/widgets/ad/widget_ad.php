<?php
function widget_ad(&$setting,&$system){

    $output = &$system->loadModel('system/frontend');
    if($theme=$output->theme){
        $theme_dir = $system->base_url().'themes/'.$theme;
    }else{
        $theme_dir = $system->base_url().'themes/'.$system->getConf('system.ui.current_theme');
    }
    foreach($setting['ad'] as $ad){
        $ad['link'] = str_replace('%THEME%',$theme_dir,$ad['link']);
        $data[] = $ad;
    }
    return $data;

}

?>
