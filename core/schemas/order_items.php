<?php
/**
* @table order_items;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['order_items']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'order_id' => 
    array (
      'type' => 'object:trading/order',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'product_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'dly_status' => 
    array (
      'type' => 
      array (
        'storage' => __('库存'),
        'shipping' => __('发送中'),
        'return' => __('退货中'),
        'customer' => __('客户'),
        'returned' => __('已退回'),
      ),
      'default' => 'storage',
      'required' => true,
      'editable' => false,
    ),
    'type_id' => 
    array (
      'type' => 'int(10)',
      'editable' => false,
    ),
    'bn' => 
    array (
      'type' => 'varchar(40)',
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
    ),
    'cost' => 
    array (
      'type' => 'money',
      'editable' => false,
    ),
    'price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'amount' => 
    array (
      'type' => 'money',
      'editable' => false,
    ),
    'score' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'nums' => 
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
    ),
    'minfo' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'sendnum' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'is_type' => 
    array (
      'type' => 
      array (
        'goods' => __('商品'),
        'pkg' => __('捆绑商品'),
      ),
      'default' => 'goods',
      'required' => true,
      'editable' => false,
    ),
    'point' => 
    array (
      'type' => 'mediumint',
      'editable' => false,
    ),

    'supplier_id' =>
    array (
      'type' => 'int unsigned',
    ),
  ),
);