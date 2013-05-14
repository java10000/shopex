<?php
function tpl_block_tab($params, $content, &$smarty,$s)
{
    if(null!==$content){
        for($i=count($smarty->_tag_stack);$i>0;$i--){
            if($smarty->_tag_stack[$i-1][0]=='tpl_block_tabber'){
                $id = $smarty->_tag_stack[$i-1][1]['_tabid'].'-'.intval($smarty->_tag_stack[$i-1][1]['_i']++);
                foreach($params as $k=>$v){
                    if($k!='name' && $k!='url'){
                        $attrs[] = $k.'="'.htmlspecialchars($v).'"';
                    }
                }
                $smarty->_tag_stack[$i-1][1]['items'][$id]=$params;
                if(!isset($smarty->_tag_stack[$i-1][1]['current']) || $params['current']){
                    $smarty->_tag_stack[$i-1][1]['current']=$id;
                }
                break;
            }
        }
        return '<div id="'.$id.'" style="display:none" '.implode(' ',$attrs).'>'.$content.'</div>';
    }
}

?>
