<?php
/**
* @table tags;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['tags']=array (
  'columns' => 
  array (
    'tag_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'tag_name' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
	'is_system' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'tag_type' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'rel_count' => 
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
  ),
  'index' => 
  array (
    'ind_type' => 
    array (
      'columns' => 
      array (
        0 => 'tag_type',
      ),
    ),
    'ind_name' => 
    array (
      'columns' => 
      array (
        0 => 'tag_name',
      ),
    ),
  ),
);