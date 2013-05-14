<?php
/**
* @table currency;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['currency']=array (
  'columns' =>
  array (
    'cur_code' =>
    array (
      'type' => 'varchar(8)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'comment' => __('货币代码'),
      'editable' => false,
    ),
    'cur_name' =>
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'comment' => __('货币名称'),
      'editable' => true,
    ),
    'cur_sign' =>
    array (
      'type' => 'varchar(5)',
      'comment' => __('货币符号'),
      'editable' => true,
    ),
    'cur_rate' =>
    array (
      'type' => 'decimal(10,4)',
      'default' => '1.0000',
      'required' => true,
      'comment' => __('汇率'),
      'editable' => true,
    ),
    'def_cur' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'comment' => __('是否默认币别'),
      'editable' => false,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'comment' => __('失效'),
      'editable' => false,
    ),
  ),
  'comment' => '货币',
  'index' =>
  array (
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
  ),
);