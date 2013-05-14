<?php
/**
* @table member_mattrvalue;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['member_mattrvalue']=array (
  'columns' => 
  array (
    'attr_id' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'member_id' => 
    array (
      'type' => 'object:member/member',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'value' => 
    array (
      'type' => 'varchar(100)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
  ),
);