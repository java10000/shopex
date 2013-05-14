<?php
/**
* @table pmt_gen_coupon;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['pmt_gen_coupon']=array (
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
    'cpns_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'label' => __('促销ID'),
      'width' => 110,
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
  ),
  'comment' => '通过促销所生成优惠券',
);