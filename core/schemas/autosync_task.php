<?php

$db['autosync_task'] = array (
  'columns' =>
  array(
      'supplier_id' => 
      array (
        'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
        'default' => '0',
      ),
      'command_id' => 
      array (
        'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
        'default' => '0',
      ),
      'local_op_id' => 
      array (
        'type' => 'number',
        'default' => '0',
      ),
    ),
);