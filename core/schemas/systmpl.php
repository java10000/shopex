<?php
/**
* @table systmpl;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['systmpl']=array (
  'columns' => 
  array (
    'tmpl_name' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
    ),
    'content' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'edittime' => 
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'active' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '存储模板表',
);