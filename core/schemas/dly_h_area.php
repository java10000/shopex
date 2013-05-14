<?php
/**
* @table dly_h_area;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['dly_h_area']=array (
  'columns' =>
  array (
    'dha_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'dt_id' =>
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'area_id' =>
    array (
      'type' => 'mediumint(6) unsigned',
      'default' => 0,
      'editable' => false,
    ),
    'price' =>
    array (
      'type' => 'varchar(100)',
      'default' => 0,
      'editable' => false,
    ),
    'has_cod' =>
    array (
      'type' => 'tinyint(1) unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'areaname_group' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'areaid_group' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'config' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'expressions' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'ordernum' =>
    array (
      'type' => 'smallint(4) unsigned',
      'editable' => true,
    ),
  ),
  'comment' => '配送地区运费配置表',
);