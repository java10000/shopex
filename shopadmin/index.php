<?php
define('RUN_IN','BACK_END');

if(!include('../config/config.php')){
    header('Location: ../install/'); exit;
}

require(CORE_DIR.'/include_v5/adminCore.php');
new adminCore();

