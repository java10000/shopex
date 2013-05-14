<?php
/**
* @table promotion_scheme;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['promotion_scheme']=array (
  'columns' => 
  array (
    'pmts_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'pmts_name' => 
    array (
      'type' => 'varchar(250)',
      'editable' => false,
    ),
    'pmts_memo' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'pmts_solution' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'pmts_type' => 
    array (
      'type' => 'tinyint(3)',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
  ),
);