<?php
/**
* @table goods_memo;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['goods_memo']=array (
  'columns' => 
  array (
    'goods_id' => 
    array (
      'type' => 'object:goods/products',
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
  'comment' => '商品扩展信息'
);