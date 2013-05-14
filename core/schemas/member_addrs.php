<?php
/**
* @table member_addrs;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['member_addrs']=array (
  'columns' =>
  array (
    'addr_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'member_id' =>
    array (
      'type' => 'object:member/member',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'name' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'area' =>
    array (
      'type' => 'region',
      'editable' => false,
    ),
    'country' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'province' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'city' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'addr' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'zip' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
    ),
    'tel' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'mobile' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'def_addr' =>
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'editable' => false,
    ),
  ),
);