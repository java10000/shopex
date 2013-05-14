<?php
define('RUN_IN','FRONT_END');

define('PERPAGE',10); //rewiew: 检查是否去掉

if( !file_exists('config/config.php') || !require('config/config.php') ) {
    header('Location: install/'); exit;
}

filterData($_POST);

require CORE_DIR.'/include_v5/shopCore.php';
return new shopCore();

//过滤字段
function filterData(&$data) {
    static $black_list = array(
        'order_num','advance','advance_freeze','point_freeze','point_history','member_lv_id',
        'point','score_rate','state','role_type','advance_total','advance_consume',
        'experience','login_count',
    );
    foreach($black_list as $v) {
       unset($data[$v]);
    }
}
