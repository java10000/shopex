<?php
/**
* @table goods_rate;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['goods_rate']=array (
  'columns' => 
  array (
    'goods_1' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'goods_2' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'manual' => 
    array (
      'type' => 
      array (
        'left' => __('单向'),
        'both' => __('关联'),
      ),
      'editable' => false,
    ),
    'rate' => 
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '商品购买率统计表',
);