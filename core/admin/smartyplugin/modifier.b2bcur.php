<?php
function tpl_modifier_b2bcur($money,$supplier_id,$currency=null)
{
    $system = &$GLOBALS['system'];
    $cur = &$system->loadModel('purchase/b2bcur');
    return $cur->getOrderDecimal($money,$supplier_id,$currency);
}
?>