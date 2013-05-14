<?php
/**
* @table goods_spec_index;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['goods_spec_index']=array (
  'columns' => 
  array (
    'type_id' => 
    array (
      'type' => 'int(10)',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'spec_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'spec_value_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'spec_value' => 
    array (
      'type' => 'varchar(100)',
      'default' => '',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'goods_id' => 
    array (
      'type' => 'object:goods/products',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'product_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
  ),
  'comment' => '商品规格索引表',
  'index' => 
  array (
    'type_specvalue_index' => 
    array (
      'columns' => 
      array (
        0 => 'type_id',
        1 => 'spec_value_id',
        2 => 'goods_id',
      ),
    ),
  ),
);