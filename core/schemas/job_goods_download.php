<?php

$db['job_goods_download'] = array (
  'columns' =>
  array(
      'job_id'=> 
      array (
        'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
        'extra' => 'auto_increment',
      ),
      'supplier_id' => 
      array (
        'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
      ),
      'supplier_goods_id' => 
      array (
        'type' => 'number',
            'required' => true,
            'default' => 0,
      ),
      'supplier_goods_count' => 
      array (
        'type' => 'int unsigned',
            'required' => true,
        'default' => '1',
      ),
      'command_id' => 
      array (
        'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
      ),
      'failed' => 
      array (
        'type' => array(
            'true' => __(''),
            'false' => __(''),
        ),
        'default' => 'false',
            'required' => true,
      ),
      'to_cat_id' => 
      array (
        'type' => 'number',
      ),
      'status' =>
      array (
        'type' => 'tinyint(1)',
        'default' => 0,
        'required' => true,
        'editable' => false,
      ),
    )
);
