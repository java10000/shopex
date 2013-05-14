<?php
/**
* @table sitemaps;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['sitemaps']=array (
  'columns' =>
  array (
    'node_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'p_node_id' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'node_type' =>
    array (
      'type' => 'varchar(30)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'depth' =>
    array (
      'type' => 'tinyint unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
    'path' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
    ),
    'title' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'action' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'manual' =>
    array (
      'type' => 'intbool',
      'default' => '1',
      'required' => true,
      'editable' => false,
    ),
    'item_id' =>
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'p_order' =>
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'hidden' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'child_count' =>
    array (
      'type' => 'mediumint(4)',
      'editable' => false,
    ),
  ),
  'comment' => '站点结构',
  'index' =>
  array (
    'ind_hidden' =>
    array (
      'columns' =>
      array (
        0 => 'hidden',
      ),
    ),
  ),
);