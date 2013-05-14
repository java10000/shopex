<?php
/**
* @table package_product;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['package_product']=array (
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
    'goods_id' =>
    array (
      'type' => 'object:goods/products',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'discount' =>
    array (
      'type' => 'decimal(5,3)',
      'editable' => false,
    ),
    'pkgnum' =>
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
    ),
  ),
);