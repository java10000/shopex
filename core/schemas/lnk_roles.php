<?php
/**
* @table lnk_roles;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['lnk_roles']=array (
  'columns' => 
  array (
    'op_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'role_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
  ),
);