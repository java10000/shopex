<?php
/**
* @table order_log;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['order_log']=array (
  'columns' => 
  array (
    'log_id' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'order_id' => 
    array (
      'type' => 'object:trading/order',
      'editable' => false,
    ),
    'op_id' => 
    array (
      'type' => 'mediumint(8)',
      'editable' => false,
    ),
    'op_name' => 
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'log_text' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'acttime' => 
    array (
      'type' => 'time',
      'editable' => false,
    ),
    'behavior' => 
    array (
      'type' => 'varchar(20)',
      'default' => '',
      'editable' => false,
    ),
    'result' => 
    array (
      'type' => 
      array (
        'success' => __('成功'),
        'failure' => __('失败'),
      ),
      'default' => 'success',
      'editable' => false,
    ),
  ),
);