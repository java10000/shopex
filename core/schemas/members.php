<?php
/**
* @table members;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['members']=array (
  'columns' =>
  array (
    'member_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => __('ID'),
      'width' => 110,
      'editable' => false,
    ),
    'member_lv_id' =>
    array (
      'required' => true,
      'default' => 0,
      'label' => __('会员等级'),
      'width' => 75,
      'type' => 'object:member/level',
      'editable' => true,
      
      'filtertype' => 'yes',
      'filterdefalut'=>'true',
    ),
    'uname' =>
    array (
      'type' => 'varchar(50)',
      'label' => __('用户名'),
      'width' => 75,
      'required' => 1,
      'searchtype' => 'head',
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefalut'=>'true',
    ),
    'name' =>
    array (
      'type' => 'varchar(50)',
      'label' => __('姓名'),
      'width' => 75,
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefalut'=>'true',
    ),
    'lastname' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'firstname' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'password' =>
    array (
      'type' => 'varchar(32)',
      'editable' => false,
    ),
    'area' =>
    array (
      'label' => __('地区'),
      'width' => 110,
      'type' => 'region',
      'editable' => false,
      'filtertype'=>'yes',
      'filterdefalut'=>'true',
    ),
    'mobile' =>
    array (
      'type' => 'varchar(30)',
      'label' => __('手机'),
      'width' => 75,
      'fuzzySearch' => 1,
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefalut'=>'true',
      'escape_html'=>true,
    ),
    'tel' =>
    array (
      'type' => 'varchar(30)',
      'label' => __('固定电话'),
      'width' => 110,
      'fuzzySearch' => 1,
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefalut'=>'true',
      'escape_html'=>true,
    ),
    'email' =>
    array (
      'type' => 'varchar(200)',
      'label' => __('EMAIL'),
      'width' => 110,
      'required' => 1,
      'fuzzySearch' => 1,
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefalut'=>'true',
      'escape_html'=>true,
    ),
    'zip' =>
    array (
      'type' => 'varchar(20)',
      'label' => __('邮编'),
      'width' => 110,
      'editable' => true,
      'filtertype' => 'normal',
      'escape_html'=>true,
    ),
    'addr' =>
    array (
      'type' => 'varchar(255)',
      'label' => __('地址'),
      'width' => 110,
      'editable' => true,
      'filtertype' => 'normal',
      'escape_html'=>true,
    ),
    'province' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
    ),
    'city' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
    ),
    'order_num' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => __('订单数'),
      'width' => 110,
      'editable' => false,
      'hidden'=>true,
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
      'type' => 'varchar(200)',
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
      'type' => 'varchar(200)',
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
    'b_year' =>
    array (
      'type' => 'smallint unsigned',
      'width' => 30,
      'editable' => false,
    ),
    'b_month' =>
    array (
      'label'=>'生月',
      'type' => 'tinyint unsigned',
      'width' => 30,
      'editable' => false,
      'hidden'=>true,
    ),
    'b_day' =>
    array (
      'label'=>'生日',
      'type' => 'tinyint unsigned',
      'width' => 30,
      'editable' => false,
      'hidden'=>true,
    ),
    'sex' =>
    array (
      'type' =>
      array (
        0 => __('女'),
        1 => __('男'),
      ),
      'default' => '1',
      'required' => true,
      'label' => __('性别'),
      'width' => 30,
      'editable' => true,
      'filtertype' => 'yes',
    ),
    'addon' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'wedlock' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'education' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
    ),
    'vocation' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'interest' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'advance' =>
    array (
      'type' => 'money',
      'default' => '0.00',
      'required' => true,
      'label' => __('预存款'),
      'width' => 110,
      'searchable' => true,
      'editable' => false,
      'filtertype' => 'number',
    ),
    'advance_freeze' =>
    array (
      'type' => 'money',
      'default' => '0.00',
      'required' => true,
      'editable' => false,
    ),
    'point_freeze' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'point_history' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'point' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' => __('积分'),
      'width' => 110,
      'searchable' => true,
      'editable' => false,
      'filtertype' => 'number',
    ),
    'score_rate' =>
    array (
      'type' => 'decimal(5,3)',
      'editable' => false,
    ),
    'reg_ip' =>
    array (
      'type' => 'varchar(16)',
      'label' => __('注册IP'),
      'width' => 110,
      'editable' => false,
    ),
    'regtime' =>
    array (
      'label' => __('注册时间'),
      'width' => 75,
      'type' => 'time',
      'editable' => false,
      'searchable' => true,
      'filtertype' => 'number',
      'filterdefalut'=>'true',
    ),
    'state' =>
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'required' => true,
      'label' => __('验证状态'),
      'width' => 110,
      'editable' => false,
    ),
    'pay_time' =>
    array (
      'type' => 'number',
      'editable' => false,
    ),
    'biz_money' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
    ),
    'pw_answer' =>
    array (
      'type' => 'varchar(250)',
      'editable' => false,
    ),
    'pw_question' =>
    array (
      'type' => 'varchar(250)',
      'editable' => false,
    ),
    'fav_tags' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'custom' =>
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'cur' =>
    array (
      'type' => 'varchar(20)',
      'label' => __('货币'),
      'width' => 110,
      'editable' => false,
    ),
    'lang' =>
    array (
      'type' => 'varchar(20)',
      'label' => __('语言'),
      'width' => 110,
      'editable' => false,
    ),
    'unreadmsg' =>
    array (
      'type' => 'smallint unsigned',
      'default' => 0,
      'required' => true,
      'label' => __('未读信息'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'number',
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'remark' =>
    array (
      'label'=>__('备注'),
      'type' => 'text',
      'width' => 75,
      'modifier' => 'row',
    ),
    'remark_type' =>
    array (
      'type' => 'varchar(2)',
      'default' => 'b1',
      'required' => true,
      'editable' => false,
    ),
    'login_count'=>array(
        'type'=>'int(11)',
        'default'=>0,
        'required'=>true,
        'editable'=>false
    ),
    'experience'=>array(
        'label'=>__('经验值'),
        'type'=>'int(10)',
        'default'=>0,
        'editable'=>false
    ),
    'foreign_id'=>array(
        'type'=>'varchar(255)',
    ),
    'member_refer'=>array(
        'type' => array (
            'local' => __('本地'),
            'mobile' => __('移动商城'),
            'qzone' => __('QQ'),
            'alipayquick' => __('支付宝'),
            'taobao' => __('淘宝'),
            'sina' => __('新浪'),
            'baidu' => __('百度'),
            'renren' => __('人人网'),
            'tencent' => __('腾讯微博'),
            'netease163' => __('网易'),
            'windowslive' => __('MSN'),
            'yahoo' => __('Yahoo!'),
            'sohu' => __('搜狐微博'),
        ),
        'label' => __('会员来源'),
        'hidden'=>true,
        'default'=>'local',
        
    ),
  ),
  'comment' => '商店会员表',
  'index' =>
  array (
    'ind_email' =>
    array (
      'columns' =>
      array (
        0 => 'email',
      ),
    ),
    'uni_user' =>
    array (
      'columns' =>
      array (
        0 => 'uname',
      ),
    ),
    'ind_regtime' =>
    array (
      'columns' =>
      array (
        0 => 'regtime',
      ),
    ),
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
  ),
);