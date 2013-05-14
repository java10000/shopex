<?php
function tpl_modifier_barcode($data){
    $system = &$GLOBALS['system'];
    $bcode = &$system->loadModel('utility/barcode');
    return $bcode->get($data);
}
?>