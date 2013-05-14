<?php
/**
* @table pages;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['pages']=array (
  'columns' => 
  array (
    'page_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'page_name' => 
    array (
      'type' => 'varchar(90)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'page_title' => 
    array (
      'type' => 'varchar(90)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'page_content' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'page_time' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
    ),
  ),
  'index' => 
  array (
    'uni_pagename' => 
    array (
      'columns' => 
      array (
        0 => 'page_name',
      ),
    ),
    'uni_pagetitle' => 
    array (
      'columns' => 
      array (
        0 => 'page_title',
      ),
    ),
  ),
);