<?php
function tpl_compiler_adv($params, &$smarty){
    if(!$params['id']){
        $ident = '';
    }elseif(strpos($params['id'],'$')===false){
        $ident = ($params['id']{0}=='"' || $params['id']{0}=='\'')?substr($params['id'],1,-1):$params['id'];
    }else{
        $ident = $params['id'];
    }
    unset($params['id']);
    
    return "\r\n?><?php \$advMdl=\$this->system->loadModel('system/adv');
        echo \$advMdl->loadadv($ident,{$params['index']});unset(\$advMdl);";
}
