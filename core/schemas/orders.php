<?php
/**
* @table orders;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['orders']=array (
  'columns' =>
  array (
    'order_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'label' => __('订单号'),
      'width' => 110,
      'primary' => true,
      'searchtype' => 'has',
      'editable' => false,
      'filtertype'=>'custom',
      'filtercustom'=>array('head'=>'开头等于'),
      'filterdefalut'=>true,
    ),
    'member_id' =>
    array (
      'type' => 'object:member/member',
      'label' => __('会员用户名'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'confirm' =>
    array (
      'type' => 'tinybool',
      'default' => 'N',
      'required' => true,
      'label' => __('确认状态'),
      'width' => 75,
      'hidden'=>true,
      'editable' => false,
    ),
    'status' =>
    array (
      'type' =>
      array (
        'active' => __('活动订单'),
        'dead' => __('死单'),
        'finish' => __('已完成'),
      ),
      'default' => 'active',
      'required' => true,
      'label' => __('订单状态'),
      'width' => 75,
      'editable' => false,
    ),
    'pay_status' =>
    array (
      'type' =>
      array (
        0 => __('未支付'),
        1 => __('已支付'),
        2 => __('已支付至担保方'),
        3 => __('部分付款'),
        4 => __('部分退款'),
        5 => __('全额退款'),
      ),
      'default' => '0',
      'required' => true,
      'label' => __('付款状态'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'ship_status' =>
    array (
      'type' =>
      array (
        0 => __('未发货'),
        1 => __('已发货'),
        2 => __('部分发货'),
        3 => __('部分退货'),
        4 => __('已退货'),
      ),
      'default' => '0',
      'required' => true,
      'label' => __('发货状态'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'user_status' =>
    array (
      'label' => __('用户反馈'),
      'type' =>
      array (
        'null' => __('无反馈'),
        'payed' => __('已支付'),
        'shipped' => __('已到收货'),
      ),
      'hidden'=>true,
      'default' => 'null',
      'required' => true,
      'editable' => false,
    ),
    'is_delivery' =>
    array (
      'type' => 'tinybool',
      'default' => 'Y',
      'required' => true,
      'editable' => false,
    ),
    'shipping_id' =>
    array (
      'type' => 'smallint(4) unsigned',
      'editable' => false,
    ),
    'shipping' =>
    array (
      'type' => 'varchar(100)',
      'label' => __('配送方式'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'shipping_area' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'payment' =>
    array (
      'type' => 'object:trading/paymentcfg',
      'label' => __('支付方式'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'yes',
      'filterdefalut'=>true,
    ),
    'weight' =>
    array (
      'type' => 'money',
      'editable' => false,
    ),
    'tostr' =>
    array (
      'label' => __('商品信息'),
      'type' => 'longtext',
      'editable' => false,
      'width' => 75,
    ),
    'itemnum' =>
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'acttime' =>
    array (
      'label'=>'更新时间',
      'type' => 'time',
      'label' => __('更新时间'),
      'width' => 110,
      'editable' => false,
    ),
    'delivery_time' =>
    array (
      'label'=>'发货时间',
      'type' => 'time',
      'label' => __('发货时间'),
      'width' => 110,
      'editable' => false,
    ),
    'delivery_delay_time' =>
    array (
      'label'=>'交易延迟时间',
      'type' => 'time',
      'label' => __('交易延迟时间'),
      'width' => 110,
      'editable' => false,
    ),
    'createtime' =>
    array (
      'type' => 'time',
      'label' => __('下单时间'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'time',
      'filterdefalut'=>true,
    ),
    'refer_id' =>
    array (
      'type' => 'varchar(50)',
      'label' => __('首次来源ID'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'refer_url' =>
    array (
      'type' => 'text',
      'label' => __('首次来源URL'),
      'width' => 150,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'refer_time' =>
    array (
      'type' => 'time',
      'label' => __('首次来源时间'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'time',
    ),
    'c_refer_id' =>
    array (
      'type' => 'varchar(50)',
      'label' => __('本次来源ID'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'c_refer_url' =>
    array (
      'type' => 'text',
      'label' => __('本次来源URL'),
      'width' => 150,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'c_refer_time' =>
    array (
      'type' => 'time',
      'label' => __('本次来源时间'),
      'width' => 110,
      'editable' => false,
      'filtertype'=>'time',
    ),
    'ip' =>
    array (
      'type' => 'varchar(15)',
      'editable' => false,
    ),
    'ship_name' =>
    array (
      'type' => 'varchar(50)',
      'label' => __('收货人'),
      'width' => 75,
      'searchtype' => 'head',
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'ship_area' =>
    array (
      'type' => 'region',
      'label' => __('收货地区'),
      'searchable' => true,
      'width' => 180,
      'editable' => false,
      'filtertype'=>'yes',
    ),
    'ship_addr' =>
    array (
      'type' => 'varchar(100)',
      'label' => __('收货地址'),
      'searchtype' => 'has',
      'width' => 180,
      'editable' => false,
      'filtertype'=>'normal',
    ),
    'ship_zip' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
    ),
    'ship_tel' =>
    array (
      'type' => 'varchar(30)',
      'label' => __('收货人电话'),
      'searchtype' => 'has',
      'width' => 75,
      'editable' => false,
      'filtertype'=>'normal',
      'filterdefalut'=>true,
    ),
    'ship_email' =>
    array (
      'type' => 'varchar(150)',
      'editable' => false,
    ),
    'ship_time' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'ship_mobile' =>
    
    array (
      'width' => 75,      
      'label'=>__('收货人手机'),
      //'hidden'=>true,  //定义是否在列表项配置中出现
      'searchtype' => 'has',
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'cost_item' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'is_tax' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'cost_tax' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'tax_company' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'cost_freight' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('配送费用'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'number',
    ),
    'is_protect' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'cost_protect' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'cost_payment' =>
    array (
      'type' => 'money',
      'editable' => false,
    ),
    'currency' =>
    array (
      'type' => 'varchar(8)',
      'editable' => false,
    ),
    'cur_rate' =>
    array (
      'type' => 'decimal(10,4)',
      'default' => '1.0000',
      'editable' => false,
    ),
    'score_u' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'score_g' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'score_e' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'advance' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
    ),
    'discount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'use_pmt' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'total_amount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => __('订单总额'),
      'width' => 75,
      'editable' => false,
      'filtertype'=>'number',
      'filterdefalut'=>true,
    ),
    'final_amount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'pmt_amount' =>
    array (
      'type' => 'money',
      'editable' => false,
    ),
    'payed' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
    ),
    'markstar' =>
    array (
      'type' => 'tinybool',
      'default' => 'N',
      'editable' => false,
    ),
    'memo' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'print_status' =>
    array (
      'type' => 'tinyint unsigned',
      'default' => 0,
      'required' => true,
      'label' => __('打印'),
      'width' => 150,
      'editable' => false,
    ),
    'mark_text' =>
    array (
      'type' => 'longtext',
      'label' => __('订单备注'),
      'width' => 50,
      'html'=>'order/order_remark.html',
      'editable' => false,
      'searchtype' => 'has',
      'filtertype'=>'normal',
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'last_change_time' =>
    array (
      'type' => 'int(11)',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'use_registerinfo' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'mark_type' =>
    array (
      'type' => 'varchar(2)',
      'default' => 'b1',
      'required' => true,
      'label' => __('订单备注图标'),
      'hidden'=>true,
      'width' => 150,
      'editable' => false,
    ),
    'extend'=>array(
        'type'=>'varchar(255)',
        'default'=>'false',
        'editable'=>false
    ),
    'is_has_remote_pdts'=>array(
        'type'=>array(
            'true'=>__(''),
            'false'=>__(''),
        ),
        'required' => true,
        'default'=>'false',
    ),
    'order_refer'=>array(
        'required' =>true,
        'default'=>'local',
        'type' => array (
            'local' => __('本地'),
            'taobao' => __('淘宝'),
            'paipai' => __('拍拍'),
            'mobile' => __('移动商城'),
        ),
        'width' => 75,
        'filtertype'=>'yes',
        'label' => __('订单来源'),
        'filterdefalut'=>true,
    ),
    
    'print_id'=>array(
        'type'=>'varchar(20)',
        'required'=>false,
        'label'=>'订单打印编号',

    ),
    'cancel_time' =>
    array (
      'type' => 'int(11)',
      'label' => __('作废时间'),
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
  ),
  'index' =>
  array (
    'ind_ship_status' =>
    array (
      'columns' =>
      array (
        0 => 'ship_status',
      ),
    ),
    'ind_pay_status' =>
    array (
      'columns' =>
      array (
        0 => 'pay_status',
      ),
    ),
    'ind_status' =>
    array (
      'columns' =>
      array (
        0 => 'status',
      ),
    ),
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_print_id' =>
    array (
      'columns' =>
      array (
        0 => 'print_id',
      ),
    ),
  ),
);