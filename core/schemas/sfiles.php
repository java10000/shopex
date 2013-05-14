<?php
/**
* @table sfiles;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['sfiles']=array (
  'columns' => 
  array (
    'file_id' => 
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
    ),
    'file_name' => 
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'usedby' => 
    array (
      'type' => 'varchar(32)',
      'editable' => false,
    ),
    'file_type' => 
    array (
      'type' => 'varchar(32)',
      'editable' => false,
    ),
    'file_size' => 
    array (
      'type' => 'int(9)',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'cdate' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'misc' => 
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
  ),
  'index' => 
  array (
    'ind_usedby' => 
    array (
      'columns' => 
      array (
        0 => 'usedby',
      ),
    ),
  ),
);