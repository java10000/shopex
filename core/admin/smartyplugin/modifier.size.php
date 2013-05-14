<?php
function tpl_modifier_size($size)
{
    $size=intval($size);
    if($size>1048576){
        return round($size/1048576,2).' M';
    }elseif($size>1024){
        return round($size/1024,2).' K';
    }else{
        return $size.' Bytes';
    }
}
?>
