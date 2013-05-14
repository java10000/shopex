<?php
/**
* @table gift_items;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['gift_items']=array (
  'columns' => 
  array (
    'order_id' => 
    array (
      'type' => 'object:trading/order',
      'required' => true,
      'default' => '0',
      'pkey' => true,
      'editable' => false,
    ),
    'gift_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => '0',
      'pkey' => true,
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
    ),
    'point' => 
    array (
      'type' => 'int(8)',
      'editable' => false,
    ),
    'nums' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'amount' => 
    array (
      'type' => 'int unsigned',
      'editable' => false,
    ),
    'sendnum' => 
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
    ),
    'getmethod' => 
    array (
      'type' => 
      array (
        'present' => __('赠送'),
        'exchange' => __('兑换'),
      ),
      'default' => 'present',
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '赠品订单明细表',
);