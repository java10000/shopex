<?php

$db['autosync_rule'] = array (
  'columns' =>
  array(
      'rule_id' => 
      array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'editable' => false,
      ),
      'supplier_op_id' => 
      array (
      'required' => true,
        'default' => '0',
        'type' => 'tinyint(3)',
      ),
      'local_op_id' => 
      array (
      'required' => true,
        'default' => '0',
        'type' => 'tinyint(3)',
      ),
      'disabled' => 
      array (
        'type' => array(
            'true' => __(''),
            'false' => __(''),
        ),
      'required' => true,
        'default' => 'false',
      ),
      'memo' => 
      array (
        'type' => 'varchar(255)',
      ),
      'rule_name' => 
      array (
        'type' => 'varchar(255)',
        'required' => true,
      ),
    ),
  'index' =>
  array (
    'index_1' =>
    array (
      'columns' =>
      array (
        0 => 'rule_id',
        1 => 'local_op_id',
        2 => 'disabled',
      ),
    ),
  )
);