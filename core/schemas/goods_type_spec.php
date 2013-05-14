<?php
/**
* @table goods_type_spec;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['goods_type_spec']=array (
  'columns' => 
  array (
    'spec_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
    ),
    'type_id' => 
    array (
      'type' => 'int(10)',
      'default' => 0,
      'editable' => false,
    ),
    'spec_style' => 
    array (
      'type' => 
      array (
        'select' => __('下拉'),
        'flat' => __('平面'),
        'disabled' => __('禁用'),
      ),
      'default' => 'flat',
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '类型 规格索引表',
);