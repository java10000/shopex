<?php
function widget_member($setting,&$system){
    $aMember = $system->request['member'];

    $aMember['valideCode'] = $system->getConf('site.login_valide');
    return $aMember;
}

