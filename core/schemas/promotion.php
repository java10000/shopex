<?php
/**
* @table promotion;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['promotion']=array (
  'columns' => 
  array (
    'pmt_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('促销ID'),
      'width' => 110,
      'editable' => false,
    ),
    'pmts_id' => 
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'pmta_id' => 
    array (
      'type' => 'object:trading/promotionActivity',
      'label' => __('促销活动'),
      'width' => 150,
      'editable' => false,
    ),
    'pmt_time_begin' => 
    array (
      'type' => 'time',
      'label' => __('开始时间'),
      'width' => 110,
      'editable' => false,
    ),
    'pmt_time_end' => 
    array (
      'type' => 'time',
      'label' => __('结束时间'),
      'width' => 110,
      'editable' => false,
    ),
    'order_money_from' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('订单最小满金额'),
      'width' => 110,
      'editable' => false,
    ),
    'order_money_to' => 
    array (
      'type' => 'money',
      'default' => '9999999',
      'required' => true,
      'label' => __('订单最大满金额'),
      'width' => 110,
      'editable' => false,
    ),
    'seq' => 
    array (
      'type' => 'tinyint unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'pmt_type' => 
    array (
      'type' => 
      array (
        0 => __('普通方案'),
        1 => __('为优惠券方案'),
        2 => __('为生成优惠券方案'),
      ),
      'default' => '0',
      'required' => true,
      'label' => __('优惠类型'),
      'width' => 110,
      'editable' => false,
    ),
    'pmt_belong' => 
    array (
      'type' => 
      array (
        0 => __('系统'),
        1 => __('用户自定义'),
      ),
      'default' => '0',
      'required' => true,
      'label' => __('所属'),
      'width' => 110,
      'editable' => false,
    ),
    'pmt_bond_type' => 
    array (
      'type' => 
      array (
        0 => __('所有商品'),
        1 => __('绑定商品'),
        2 => __('绑定分类'),
      ),
      'required' => true,
      'default' => '0',
      'label' => __('关联商品方式'),
      'width' => 110,
      'editable' => false,
    ),
    'pmt_describe' => 
    array (
      'type' => 'longtext',
      'label' => __('规则描述'),
      'width' => 430,
      'editable' => false,
    ),
    'pmt_solution' => 
    array (
      'type' => 'longtext',
      'label' => __('方案 参数表'),
      'width' => 110,
      'editable' => false,
    ),
    'pmt_ifcoupon' => 
    array (
      'type' => 'tinyint unsigned',
      'default' => 1,
      'required' => true,
      'label' => __('是否允许使用优惠券'),
      'width' => 110,
      'editable' => false,
    ),
    'pmt_update_time' => 
    array (
      'type' => 'time',
      'default' => 0,
      'label' => __('促销规则更新时间'),
      'width' => 110,
      'editable' => false,
    ),
    'pmt_basic_type' => 
    array (
      'type' => 
      array (
        'goods' => __('商品促销规则'),
        'order' => __('订单促销规则'),
      ),
      'default' => 'goods',
      'label' => __('促销基本类型'),
      'width' => 110,
      'editable' => false,
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'pmt_ifsale' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'editable' => false,
    ),
    'pmt_distype' => 
    array (
      'type' => 'tinyint unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
  ),
  'comment' => '怒气',
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