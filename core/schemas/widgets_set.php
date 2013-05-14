<?php
/**
* @table widgets_set;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['widgets_set']=array (
  'columns' => 
  array (
    'widgets_id' => 
    array (
      'type' => 'int',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'base_file' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'base_slot' => 
    array (
      'type' => 'tinyint unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'base_id' => 
    array (
      'type' => 'varchar(20)',
      'editable' => false,
    ),
    'widgets_type' => 
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'widgets_order' => 
    array (
      'type' => 'tinyint unsigned',
      'default' => 5,
      'required' => true,
      'editable' => false,
    ),
    'title' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'domid' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'border' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'classname' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'tpl' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
    'params' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'modified' => 
    array (
      'type' => 'time',
      'editable' => false,
    ),
    'vary' => 
    array (
      'type' => 'varchar(250)',
      'editable' => false,
    ),
  ),
  'index' => 
  array (
    'ind_wgbase' => 
    array (
      'columns' => 
      array (
        0 => 'base_file',
        1 => 'base_id',
        2 => 'widgets_order',
      ),
    ),
    'ind_wginfo' => 
    array (
      'columns' => 
      array (
        0 => 'base_file',
        1 => 'base_slot',
        2 => 'widgets_order',
      ),
    ),
  ),
);