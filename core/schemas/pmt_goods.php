<?php
/**
* @table pmt_goods;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['pmt_goods']=array (
  'columns' => 
  array (
    'pmt_id' => 
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
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'count' => 
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
    ),
  ),
);