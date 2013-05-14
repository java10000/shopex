<?php
/**
* @table goods_lv_price;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['goods_lv_price']=array (
  'columns' => 
  array (
    'product_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'level_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'goods_id' => 
    array (
      'type' => 'object:goods/products',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'price' => 
    array (
      'type' => 'money',
      'editable' => false,
    ),
  ),
  'comment' => '商品会员等级价格',
);