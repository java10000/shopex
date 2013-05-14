<?php
/**
* @table tpl_source;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['tpl_source']=array (
  'columns' => 
  array (
    'tpl_source_id' => 
    array (
      'type' => 'int',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'hidden' => 1,
      'editable' => false,
    ),
    'tpl_type' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'width' => 300,
      'editable' => false,
    ),
    'tpl_name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'width' => 300,
      'editable' => false,
    ),
    'tpl_file' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'tpl_theme' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'editable' => false,
    ),
  ),
);