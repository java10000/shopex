<?php
/**
* @table status;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['status']=array (
  'columns' => 
  array (
    'status_key' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
    ),
    'date_affect' => 
    array (
      'type' => 'date',
      'default' => '0000-00-00',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'status_value' => 
    array (
      'type' => 'varchar(100)',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'last_update' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
  ),
);