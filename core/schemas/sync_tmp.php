<?php

$db['sync_tmp']=array (
  'columns' =>
  array (
      'tmp_id' => 
      array (
        'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      ),
      's_type' => 
      array (
        'type' => array(
            'goods_type' => __(''),
            'spec' => __(''),
            'brand' => __(''),
            'goods_cat'=>__(''),
        ),
      'required' => true,
      'default' => 'goods_type',
      ),
      'ob_id' => 
      array (
        'type' => 'number',
      'required' => true,
      'default' => 0,
      ),
      'supplier_id' => 
      array (
        'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      ),
      's_data' => 
      array (
        'type' => 'longtext',
      ),
    )
);
