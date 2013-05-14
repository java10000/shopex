<?php
/**
* @table sendbox;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['sendbox']=array (
  'columns' => 
  array (
    'out_id' => 
    array (
      'type' => 'int',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'tmpl_name' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'sender' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'creattime' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'target' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'sendcount' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'content' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
    ),
    'subject' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
    ),
  ),
  'index' => 
  array (
    'ind_sender' => 
    array (
      'columns' => 
      array (
        0 => 'sender',
      ),
    ),
  ),
);