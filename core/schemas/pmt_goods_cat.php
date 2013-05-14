<?php
/**
* @table pmt_goods_cat;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['pmt_goods_cat']=array (
  'columns' => 
  array (
    'cat_id' => 
    array (
      'type' => 'int(10)',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'brand_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'pmt_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
  ),
);