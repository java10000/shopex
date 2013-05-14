<?php
/**
* @table delivery;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['delivery']=array (
  'columns' =>
  array (
    'delivery_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'label'=>__('发货单号'),
      'extra' => 'auto_increment',
      'comment' => __('配送流水号'),
      'editable' => false,
      'searchtype' => 'has',
      'filtertype'=>'yes',
    ),
    'order_id' =>
    array (
      'type' => 'object:trading/order',
      'searchable' => true,
      'label' => __('订单号'),
      'comment' => __('订单号'),
      'editable' => false,
      'searchtype' => 'tequal',
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'member_id' =>
    array (
      'type' => 'object:member/member',
      'label' => __('会员用户名'),
      'comment' => __('订货会员ID'),
      'editable' => false,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'money' =>
    array (
      'type' => 'money',
      'required' => true,
      'default' => 0,
      'label' => __('物流费用'),
      'comment' => __('配送费用'),
      'editable' => false,
      'filtertype'=>'number',
    ),
    'type' =>
    array (
      'type' =>
      array (
        'return' => __('退货'),
        'delivery' => __('发货'),
      ),
      'default' => 'delivery',
      'required' => true,
      'label'=>__('配送类型'),
      'comment' => __('配送单类型'),
      'editable' => false,
    ),
    'is_protect' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label'=>__('是否保价'),
      'comment' => __('是否保价'),
      'editable' => false,
      'filtertype'=>'yes',
    ),
    'delivery' =>
    array (
      'type' => 'varchar(20)',
      'label'=>__('配送方式'),
      'comment' => __('配送方式(货到付款、EMS...)'),
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'logi_id' =>
    array (
      'type' => 'varchar(50)',
      'comment' => __('物流公司ID'),
      'editable' => false,
    ),
    'logi_name' =>
    array (
      'type' => 'varchar(100)',
      'label'=>__('物流公司'),
      'comment' => __('物流公司名称'),
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'logi_no' =>
    array (
      'type' => 'varchar(50)',
      'label' => __('物流单号'),
      'searchable' => true,
      'comment' => __('物流单号'),
      'editable' => false,
      'searchtype' => 'tequal',
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'ship_name' =>
    array (
      'type' => 'varchar(50)',
      'label' => __('收货人'),
      'comment' => __('收货人姓名'),
      'editable' => false,
      'searchtype' => 'tequal',
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'ship_area' =>
    array (
      'type' => 'region',
      'label' => __('收货地区'),
      'comment' => __('收货人地区'),
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'ship_addr' =>
    array (
      'type' => 'varchar(100)',
      'label' => __('收货地址'),
      'comment' => __('收货人地址'),
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'ship_zip' =>
    array (
      'type' => 'varchar(20)',
      'label' => __('收货邮编'),
      'comment' => __('收货人邮编'),
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'ship_tel' =>
    array (
      'type' => 'varchar(30)',
      'label' => __('收货人电话'),
      'comment' => __('收货人电话'),
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'ship_mobile' =>
    array (
      'type' => 'varchar(50)',
      'label' => __('收货人手机'),
      'comment' => __('收货人手机'),
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'ship_email' =>
    array (
      'type' => 'varchar(150)',
      'label' => __('收货人Email'),
      'comment' => __('收货人Email'),
      'editable' => false,
      'filtertype'=>'normal',
    ),
    't_begin' =>
    array (
      'type' => 'time',
      'label' => __('单据创建时间'),
      'comment' => __('单据生成时间'),
      'editable' => false,
      'filtertype'=>'time',
    ),
    't_end' =>
    array (
      'type' => 'time',
      'comment' => __('单据结束时间'),
      'editable' => false,
    ),
    'op_name' =>
    array (
      'type' => 'varchar(50)',
      'label' =>__("操作员"),
      'comment' => __('操作者'),
      'editable' => false,
      'searchtype'=>'tequal',
      'filtertype'=>'normal',
    ),
    'status' =>
    array (
      'type' =>
      array (
        'succ' => __('成功到达'),
        'failed' => __('发货失败'),
        'cancel' => __('已取消'),
        'lost' => __('货物丢失'),
        'progress' => __('运送中'),
        'timeout' => __('超时'),
        'ready' => __('准备发货'),
      ),
      'default' => 'ready',
      'required' => true,
      'comment' => __('状态'),
      'editable' => false,
    ),
    'memo' =>
    array (
      'type' => 'longtext',
      'label' =>__('备注'),
      'comment' => __('备注'),
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'comment' => __('无效'),
      'editable' => false,
    ),
    
    'supplier_id' =>
    array (
      'type' => 'int unsigned',
    ),
    
    'supplier_delivery_id' =>
    array (
      'type' => 'varchar(30)',
    ),

  ),
  'comment' => '发货/退货单表',
  'index' =>
  array (
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_logi_no' =>
    array (
      'columns' =>
      array (
        0 => 'logi_no',
      ),
    ),
  ),
);