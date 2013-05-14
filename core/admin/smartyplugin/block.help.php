<?php
function tpl_block_help($params, $content, &$smarty){
    if(null!==$content){
        $help_types = array(
                'info'=>array('size'=>18,'icon'=>'images/tips_info.gif'),
                'dialog'=>array('size'=>18,'icon'=>'images/tips_info.gif','dialog'=>1),
                'link'=>array('size'=>15,'icon'=>'images/tips_help.gif'),
                'link-mid'=>array('size'=>14,'icon'=>'images/tips_help_mid.gif'),
                'link-small'=>array('size'=>12,'icon'=>'images/tips_help_small.gif'),
            );
        $params['dom_id'] = $smarty->new_dom_id();
        if($content=trim($content)){
            $params['text'] = preg_replace('/\n/', '', $content);
        }
        $params['type'] = isset($help_types[$params['type']])?$help_types[$params['type']]:$help_types['info'];
        $vars = $smarty->_vars;
        unset( $smarty->_vars['docid'] );
        $smarty->_tpl_vars = $params;
        return $smarty->_fetch_compile_include('helper.html', $params);
//        $smarty->_tpl_vars = $vars;
         
    }
}

?>
