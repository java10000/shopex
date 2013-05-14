<?php
function widget_flashview(&$setting,&$system){
    $setting['allimg']="";
    $setting['allurl']="";
    $output = &$system->loadModel('system/frontend');
    if($theme=$output->theme){
        $theme_dir = $system->base_url().'themes/'.$theme;
    }else{
        $theme_dir = $system->base_url().'themes/'.$system->getConf('system.ui.current_theme');
    }
    if(!$setting['flash']){
       foreach($setting['img'] as $value){
            $setting['allimg'].=$rvalue."|";
            $setting['allurl'].=urlencode($value["url"])."|";
       }
    }else{
        foreach($setting['flash'] as $key=>$value){
            if($value['pic']){
                if($value["url"]){
                    $value["link"]=$value["url"];
                }
                $setting['allimg'].=$rvalue."|";
                $setting['allurl'].=urlencode($value["link"])."|";
                $setting['flash'][$key]['pic'] = str_replace('%THEME%',$theme_dir,$value['pic']);
            }
        }
        krsort($setting['flash']);
    }
    return $setting;
}
?>