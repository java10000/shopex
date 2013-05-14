<?php
/**
* @table themes;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['themes']=array (
  'columns' => 
  array (
    'theme' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'stime' => 
    array (
      'type' => 'int unsigned',
      'editable' => false,
    ),
    'author' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'site' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'version' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'info' => 
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'config' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'update_url' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'template' => 
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
  ),
);