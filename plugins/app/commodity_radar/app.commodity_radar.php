<?php
class app_commodity_radar extends app{
    var $ver = 1.0;
    var $name = '商品雷达';
    var $outname = '商品雷达';
    var $_app_id = 'commodity_radar';
    var $_col_id = '_commodity_radar';
    var $author = 'shopex';
    var $help_tip = ''; 
    var $html_url = '/app/commodity_radar/view/finder_name.html';
    
    function output_modifiers(){
        return array(
            'admin:default:index' => 'default_modifiers:index',
            'admin:goods/product:index' => 'default_modifiers:product_list'
        );
    }
}
