<?php
/**
* @table dly_type;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['dly_type']=array (
  'columns' => 
  array (
    'dt_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('配送ID'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'dt_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => __('配送方式'),
      'width' => 180,
      'editable' => true,
    ),
    'dt_config' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'dt_expressions' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'detail' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'price' => 
    array (
      'type' => 'longtext',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'type' => 
    array (
      'type' => 'intbool',
      'default' => '1',
      'required' => true,
      'editable' => false,
    ),
    'gateway' => 
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
    ),
    'protect' => 
    array (
      'type' => 'intbool',
      'default' =>'0',
      'required' => true,
      'label' => __('物流保价'),
      'width' => 75,
      'editable' => false,
    ),
    'protect_rate' => 
    array (
      'type' => 'float(6,3)',
      'editable' => false,
    ),
    'ordernum' => 
    array (
      'type' => 'smallint(4)',
      'default' =>'0',
      'label' => __('排序'),
      'width' => 110,
      'editable' => true,
    ),
    'has_cod' => 
    array (
      'type' => 'intbool',
      'default' =>'0',
      'required' => true,
      'label' => __('货到付款'),
      'width' => 110,
      'editable' => false,
    ),
    'minprice' => 
    array (
      'type' => 'float(10,2)',
      'default' => '0.00',
      'required' => true,
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'corp_id' => 
    array (
      'type' => 'time',
      'editable' => false,
    ),
    'dt_status' => 
    array (
      'type' => 
      array (
        0 => '关闭',
        1 => '启用',
      ),
      'label' => __('状态'),
      'width' => 75,
      'editable' => false,
      'default' => '1',
    ),
  ),
  'comment' => '商店配送方式表',
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