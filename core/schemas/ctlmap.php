<?php
/**
* @table type_brand;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['ctlmap']=array (
  'columns' =>
  array (
    'controller' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'pkey' => true,
      'editable' => false,
    ),
    'plugin' =>
    array (
      'type' =>'varchar(100)',
      'required' => true,
      'editable' => false,
    ),
  ),
);
