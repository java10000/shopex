<?php
$db['dapi']=array (
  'columns' => 
  array (
    'func' => 
    array (
      'type' => 'varchar(60)',
      'required' => true,
      'pkey' => true,
    ),
    'last_update'=>array (
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'checksum' => 
    array (
      'type' => 'varchar(32)',
      'editable' => false,
    ),
    'code' => 
    array (
      'type' => 'text',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
  ),
);
