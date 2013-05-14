<?php
define('BASE_DIR', dirname(dirname(__FILE__)));
define('CORE_DIR', BASE_DIR.'/core');

define('RANDOM_HOME',false);

require('install.core.php');

new installCore();
