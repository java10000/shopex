<?php
/**
* @table point_history;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['point_history']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'member_id' => 
    array (
      'type' => 'object:member/member',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'point' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'time' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'reason' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'related_id' => 
    array (
      'type' => 'bigint unsigned',
      'editable' => false,
    ),
    'type' => 
    array (
      'type' => 'tinyint(1)',
      'required' => true,
      'default' => 1,
      'editable' => false,
    ),
    'operator' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
  ),
  'comment' => '积分历史',
);