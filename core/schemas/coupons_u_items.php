<?php
/**
* @table coupons_u_items;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['coupons_u_items']=array (
  'columns' =>
  array (
    'order_id' =>
    array (
      'type' => 'object:trading/order',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'comment' => __('应用订单号'),
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
    'memc_code' =>
    array (
      'type' => 'varchar(255)',
      'comment' => __('使用的优惠券号码'),
      'editable' => false,
    ),
    'cpns_type' =>
    array (
      'type' =>
      array (
        0 => 0,
        1 => 1,
        2 => 2,
      ),
      'comment' => __('优惠券类型0全局 1用户 2外部优惠券'),
      'editable' => false,
    ),
  ),
  'comment' => '优惠券使用记录',
);