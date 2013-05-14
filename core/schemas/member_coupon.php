<?php
/**
* @table member_coupon;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['member_coupon']=array (
  'columns' => 
  array (
    'memc_code' => 
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
    ),
    'cpns_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'member_id' => 
    array (
      'type' => 'object:member/member',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'memc_gen_orderid' => 
    array (
      'type' => 'varchar(15)',
      'editable' => false,
    ),
    'memc_source' => 
    array (
      'type' => 
      array (
        'a' => __('全体优惠券'),
        'b' => __('会员优惠券'),
        'c' => __('ShopEx优惠券'),
      ),
      'default' => 'a',
      'required' => true,
      'editable' => false,
    ),
    'memc_enabled' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'editable' => false,
    ),
    'memc_used_times' => 
    array (
      'type' => 'mediumint',
      'default' => 0,
      'editable' => false,
    ),
    'memc_gen_time' => 
    array (
      'type' => 'time',
      'editable' => false,
    ),
  ),
);