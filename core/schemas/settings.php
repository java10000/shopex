<?php
/**
* @table settings;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['settings']=array (
  'columns' => 
  array (
    's_name' => 
    array (
      'type' => 'varchar(16)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
    ),
    's_data' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    's_time' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => '0',
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
);