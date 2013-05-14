<?php

$db['supplier'] = array (
  'columns' =>
  array(
      'sp_id' => 
      array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      ),
      'supplier_id' => 
      array (
        'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
      ),
      'supplier_brief_name' => 
      array (
        'type' => 'varchar(30)',
      ),
      'status' => 
      array (
        'type' => 'tinyint(4)',
            'required' => true,
        'default' => '1',
      ),
      'supplier_pline' => 
      array (
        'type' => 'longtext',
      ),
      'sync_time' => 
      array (
        'type' => 'int unsigned',
            'required' => true,
        'default' => '0',
      ),
      'domain' => 
      array (
        'type' => 'varchar(255)',
            'required' => true,
      ),
      'has_new' => 
      array (
        'type' => array(
            'true' => __(''),
            'false' => __(''),
        ),
            'required' => true,
        'default' => 'true',
      ),
      'has_cost_new' => 
      array (
        'type' => array(
            'true' => __(''),
            'false' => __(''),
        ),
        'required' => true,
        'default' => 'false',
      ),
      'sync_time_for_plat' => 
      array (
        'type' => 'int unsigned',
            'required' => true,
        'default' => '0',
      ),
    ),
    
  'index' => 
  array (
    'supplier_id' => 
    array (
      'columns' => 
      array (
        0 => 'supplier_id',
      ),
    ),
  ),

);