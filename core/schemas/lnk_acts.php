<?php
/**
* @table lnk_acts;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['lnk_acts']=array (
  'columns' => 
  array (
    'role_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'action_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
  ),
);