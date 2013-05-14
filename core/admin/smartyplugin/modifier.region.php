<?php
function tpl_modifier_region($r,$space='-'){
    list($pkg,$regions,$region_id) = explode(':',$r);
    if(is_numeric($region_id)){
        return str_replace('/',$space,$regions);
    }else{
        return $r;
    }
}
?>