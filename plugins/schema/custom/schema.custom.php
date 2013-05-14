<?php
class schema_custom{
    var $name='自定义商品类型';
    var $version='$Id: schema.php 11689 2008-06-30 10:34:09Z qingo $';
    var $use_brand = true;
    var $use_params = false;
    var $use_props = true;
    var $use_minfo = false;

    function init(&$post){
        if(!isset($post['is_physical']))$post['is_physical'] = true;
        return true;
    }

}
?>