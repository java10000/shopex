<?php
/**
* @table type_brand;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['type_brand']=array (
  'columns' => 
  array (
    'type_id' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'brand_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'brand_order' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
  ),
);