<?php
/**
* @table member_lv;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['member_lv']=array (
  'columns' => 
  array (
    'member_lv_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 110,
      'editable' => false,
    ),
    'name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => __('等级名称'),
      'width' => 110,
      'editable' => true,
    ),
    'dis_count' => 
    array (
      'type' => 'decimal(5,2)',
      'default' => '1',
      'required' => true,
      'label' => __('优惠折扣率'),
      'width' => 110,
      'vtype' => 'positive',
      'editable' => true,
    ),
    'pre_id' => 
    array (
      'type' => 'mediumint',
      'editable' => false,
    ),
    'default_lv' => 
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'label' => __('是否默认'),
      'width' => 110,
      'editable' => false,
    ),
    'deposit_freeze_time' => 
    array (
      'type' => 'int',
      'default' => 0,
      'editable' => false,
    ),
    'deposit' => 
    array (
      'type' => 'int',
      'default' => 0,
      'editable' => false,
    ),
    'more_point' => 
    array (
      'type' => 'int',
      'default' => 1,
      'editable' => false,
    ),
    'point' => 
    array (
      'type' => 'mediumint(8)',
      'default' => 0,
      'required' => true,
      'label' => __('所需积分'),
      'width' => 110,
      'vtype' => 'positive',
      'editable' => false,
    ),
    'lv_type' => 
    array (
      'type' => 
      array (
        'retail' => __('零售'),
        'wholesale' => __('批发'),
        'dealer' => __('代理'),
      ),
      'default' => 'retail',
      'required' => true,
      'label' => __('等级类型'),
      'width' => 110,
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'show_other_price' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'editable' => false,
    ),
    'order_limit' => 
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'order_limit_price' => 
    array (
      'type' => 'money',
      'default' => '0.000',
      'required' => true,
      'editable' => false,
    ),
    'lv_remark' => 
    array (
      'type' => 'text',
      'editable' => false,
    ),
    'experience'=>array(
        'label'=>__('经验值'),
        'type'=>'int(10)',
        'default'=>0,
        'required'=>true,
        'editable'=>false
    )
  ),
  'index' => 
  array (
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
);