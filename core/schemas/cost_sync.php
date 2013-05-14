<?php

$db['cost_sync'] = array (
  'columns' =>
  array(
      'supplier_id' => 
      array (
        'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
        'default' => '0',
      ),
      'bn' => 
      array (
        'type' => 'varchar(30)',
      'required' => true,
      'pkey' => true,
      ),
      'cost' =>
        array (
          'type' => 'money',
      'required' => true,
          'default' => '0',
        ),
      'version_id' => 
      array (
        'type' => 'int unsigned',
      'required' => true,
        'default' => '0',
      ),
      'product_id' => 
      array (
        'type' => 'number',
      'required' => true,
        'default' => '0',
      ),
      'goods_id' => 
      array (
        'type' => 'number',
      'required' => true,
        'default' => '0',
      ),

    ),
    
  'index' =>
  array (
    'spid_gid' =>
    array (
      'columns' =>
      array (
        0 => 'supplier_id',
        1 => 'goods_id',
      ),
    ),
  )

);