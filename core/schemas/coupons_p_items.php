<?php
/**
* @table coupons_p_items;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['coupons_p_items']=array (
  'columns' =>
  array (
    'order_id' =>
    array (
      'type' => 'object:trading/order',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'comment' => __('订单ID'),
      'editable' => false,
    ),
    'cpns_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'comment' => __('优惠券方案ID'),
      'editable' => false,
    ),
    'cpns_name' =>
    array (
      'type' => 'varchar(255)',
      'comment' => __('优惠券方案名称'),
      'editable' => false,
    ),
    'nums' =>
    array (
      'type' => 'number',
      'comment' => __('得到数量'),
      'editable' => false,
    ),
  ),
  'comment' => '优惠券生成记录',
);