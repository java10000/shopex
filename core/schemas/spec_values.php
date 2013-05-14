<?php
/**
* @table spec_values;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['spec_values']=array (
  'columns' => 
  array (
    'spec_value_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'spec_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'spec_value' => 
    array (
      'type' => 'varchar(100)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'alias' =>
    array (
      'type' => 'varchar(255)',
      'default' => '',
      'label' => __('规格别名'),
      'width' => 180,
    ),
    'spec_image' => 
    array (
      'type' => 'varchar(255)',
      'default' => '',
      'required' => true,
      'editable' => false,
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'default' => 50,
      'required' => true,
      'editable' => false,
    ),
    
    'supplier_id' =>
    array (
      'type' => 'int unsigned',
    ),
    
    'supplier_spec_value_id' =>
    array (
      'type' => 'number',
    ),

  ),
  'comment' => '商店中商品规格值',
);