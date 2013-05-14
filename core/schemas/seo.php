<?php
/**
* @table type_brand;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['seo']=array (
  'columns' => 
  array (
    'seo_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'source_id' => 
    array (
      'type' =>'varchar(100)',
      'required' => true,
      'editable' => false,
    ),
    'type' => 
    array (
      'type' =>'varchar(50)',
      'required' => true,
      'editable' => false,
    ),
    'store_key' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'editable' => false,
    ),
    'value' => 
    array (
      'type' => 'text',
      'required' => true,
      'editable' => false,
    )
  ),
);