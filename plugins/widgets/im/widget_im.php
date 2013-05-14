<?php
function widget_im(&$setting,&$system){

    if($setting['align']=='1'){
        $setting['plug']='<br>';
    }
    return $setting['im'];
}
?>