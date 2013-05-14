<?php
/**
* @table tag_rel;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['tag_rel']=array (
  'columns' => 
  array (
    'tag_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'rel_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
  ),
);