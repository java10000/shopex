<?php
/**
* @table product_memo;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['product_memo']=array (
  'columns' => 
  array (
    'product_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'p_key' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
    ),
    'p_value' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
  ),
  'comment' => '物品扩展信息',
);