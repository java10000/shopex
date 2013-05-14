<?php

$db['supplier_pdtbn'] = array (
  'columns' =>
  array (
      'sp_id' => 
      array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      ),
      'local_bn' => 
      array (
        'type' => 'varchar(200)',
      'required' => true,
      'pkey' => true,
      ),
      'source_bn' => 
      array (
        'type' => 'varchar(200)',
      'required' => true,
      ),
      'default' => 
      array (
        'type' => array(
            'true' => __(''),
            'false' => __(''),
        ),
      'required' => true,
        'default' => 'true',
      ),
      'supplier_id' => 
      array (
        'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      ),
    ),

    'index' =>
  array (
    'sp_srcbn' =>
    array (
      'columns' =>
      array (
        0 => 'source_bn',
        1 => 'supplier_id',
      ),
   ),
  'sp_source_bn' =>
    array (
      'columns' =>
      array (
        0 => 'source_bn',
      ),
    ),
   'sp_local_bn' =>
    array (
      'columns' =>
             array (
        0 => 'local_bn',
      ),
    ),
  )

);
