<?php
/**
* @table delivery_item;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['delivery_item']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'delivery_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'item_type' => 
    array (
      'type' => 
      array (
        'goods' => __('商品'),
        'gift' => __('赠品'),
        'adjunct' => __('配件'),
        'pkg' => __('捆绑商品'),
      ),
      'default' => 'goods',
      'required' => true,
      'editable' => false,
    ),
    'product_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'product_bn' => 
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'product_name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
    ),
    'number' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
  ),
  'comment' => '发货/退货单明细表',
);