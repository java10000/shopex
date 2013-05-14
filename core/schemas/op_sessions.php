<?php
/**
* @table op_sessions;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['op_sessions']=array (
  'columns' =>
  array (
    'sess_id' =>
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
    ),
    'op_id' =>
    array (
      'type' => 'mediumint(6) unsigned',
      'editable' => false,
    ),
    'login_time' =>
    array (
      'type' => 'time',
      'editable' => false,
    ),
    'last_time' =>
    array (
      'type' => 'time',
      'editable' => false,
    ),
    'api_id' =>
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'sess_data' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'status' =>
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'editable' => false,
    ),
    'ip' =>
    array (
      'type' => 'varchar(17)',
      'editable' => false,
    ),
  ),
);