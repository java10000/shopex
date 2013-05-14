<?php
function tpl_function_refer($params, &$smarty){
    switch($params['show']){
        case "id":
            $return=$params['id'];
        break;
        case "url":
            $return='<a href="'.$params['url'].'" target="_blank">'.$params['url'].'</a>';
        break;

    }
    return $return;
}
?>