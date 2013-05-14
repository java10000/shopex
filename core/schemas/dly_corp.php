<?php
/**
* @table dly_corp;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['dly_corp']=array (
  'columns' => 
  array (
    'corp_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('物流公司ID'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
    ),
    'type' => 
    array (
      'type' => 'varchar(6)',
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'label' => __('物流公司'),
      'width' => 180,
      'editable' => true,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'ordernum' => 
    array (
      'type' => 'smallint(4) unsigned',
      'label' => __('排序'),
      'width' => 180,
      'editable' => true,
    ),
    'website' => 
    array (
      'type' => 'varchar(200)',
      'label' => __('网址'),
      'width' => 180,
      'editable' => true,
    ),
  ),
  'comment' => '物流公司表',
  'index' => 
  array (
    'ind_type' => 
    array (
      'columns' => 
      array (
        0 => 'type',
      ),
    ),
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
    'ind_ordernum' => 
    array (
      'columns' => 
      array (
        0 => 'ordernum',
      ),
    ),
  ),
);