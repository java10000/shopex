<?php
function tpl_block_area($params, $content, &$ctl){
    if(null!==$content){
        $ctl->_update_areas[$params['inject']].=$content;
        return '';
    }
}

?>
