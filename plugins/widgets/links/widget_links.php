<?php
function widget_links(&$setting,&$system){
    $link=$o=$system->loadModel('content/frendlink');
    $results=$link->getList(null,array('disabled'=>'false'),0,$setting['limit']?$setting['limit']:10,$c);
    return $results;
}
?>