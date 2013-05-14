<?php
/**
* @table order_pmt;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['order_pmt']=array (
  'columns' => 
  array (
    'pmt_id' => 
    array (
      'type' => 'bigint(20) unsigned',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'order_id' => 
    array (
      'type' => 'object:trading/order',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'pmt_amount' => 
    array (
      'type' => 'money',
      'editable' => false,
    ),
    'pmt_memo' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'pmt_describe' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
  ),
  'comment' => '订单优惠方案',
);