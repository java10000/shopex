/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 16587 to 18137                       */
/*   last Modified:  2009/01/13                                */
/*=============================================================*/

/*=============================================================*/
/* Create tables                                               */
/*=============================================================*/
CREATE TABLE `sdb_admin_roles` (
  `role_id` int(10) unsigned NOT NULL auto_increment,
  `role_name` varchar(100) NOT NULL default '',
  `role_memo` text NOT NULL,
  `disabled` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`role_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_goods_keywords` (
  `goods_id` mediumint(8) unsigned NOT NULL,
  `keyword` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`keyword`,`goods_id`),
  KEY `fk_idx_goods_keywords` (`goods_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_goods_spec_index` (
  `type_id` mediumint(8) unsigned NOT NULL default '0',
  `spec_id` mediumint(8) unsigned NOT NULL default '0',
  `spec_value_id` mediumint(8) unsigned NOT NULL default '0',
  `spec_value` varchar(100) default NULL,
  `goods_id` mediumint(8) unsigned NOT NULL default '0',
  `product_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`type_id`,`spec_id`,`spec_value_id`,`goods_id`,`product_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_goods_virtual_cat` (
  `virtual_cat_id` mediumint(8) unsigned NOT NULL auto_increment,
  `virtual_cat_name` varchar(100) NOT NULL,
  `filter` longtext,
  `addon` longtext,
  `type_id` mediumint(8) unsigned default NULL,
  `disabled` enum('false','true') NOT NULL default 'false',
  `parent_id` mediumint(8) unsigned default '0',
  `cat_id` mediumint(8) unsigned default NULL,
  `p_order` mediumint(8) unsigned default NULL,
  `cat_path` varchar(100) default ',',
  `child_count` mediumint(8) unsigned default '0',
  PRIMARY KEY  (`virtual_cat_id`),
  KEY `ind_disabled` (`disabled`),
  KEY `ind_p_order` (`p_order`),
  KEY `ind_cat_path` (`cat_path`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_lnk_acts` (
  `role_id` int(10) unsigned NOT NULL,
  `action_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`role_id`,`action_id`),
  KEY `fk_reference_11` (`role_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_lnk_roles` (
  `op_id` mediumint(8) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`op_id`,`role_id`),
  KEY `fk_reference_10` (`role_id`),
  KEY `fk_reference_8` (`op_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_member_attr` (
  `attr_id` int(10) unsigned NOT NULL auto_increment,
  `attr_name` varchar(20) NOT NULL default '',
  `attr_type` varchar(20) NOT NULL default '',
  `attr_required` enum('true','false') NOT NULL default 'false',
  `attr_search` enum('true','false') NOT NULL default 'false',
  `attr_option` text,
  `attr_valtype` varchar(20) NOT NULL default '',
  `disabled` enum('true','false') NOT NULL default 'false',
  `attr_tyname` varchar(20) NOT NULL default '',
  `attr_group` varchar(20) NOT NULL default '',
  `attr_show` enum('true','false') NOT NULL default 'true',
  `attr_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`attr_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_member_dealer` (
  `member_id` mediumint(8) NOT NULL auto_increment,
  `dealer_site` varchar(200) NOT NULL,
  `dealer_site_name` varchar(200) NOT NULL,
  `dealer_logo` varchar(200) NOT NULL,
  `dealer_consignee` varchar(200) NOT NULL,
  `dealer_phone` varchar(200) NOT NULL,
  `dealer_mobile` varchar(200) NOT NULL,
  `dealer_area` varchar(255) NOT NULL,
  `dealer_add` varchar(255) NOT NULL,
  `dealer_zip` varchar(20) NOT NULL,
  `dealer_email` varchar(255) NOT NULL,
  PRIMARY KEY  (`member_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_member_mattrvalue` (
  `attr_id` int(10) unsigned NOT NULL default '0',
  `member_id` mediumint(8) unsigned NOT NULL default '0',
  `value` varchar(100) NOT NULL default '',
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`),
  KEY `fk_reference_12` (`attr_id`),
  KEY `fk_reference_54` (`member_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_sell_logs` (
  `log_id` mediumint(8) NOT NULL auto_increment,
  `member_id` mediumint(8) NOT NULL default '0',
  `name` varchar(50) default '',
  `price` decimal(20,3) default '0.000',
  `product_id` mediumint(8) NOT NULL default '0',
  `goods_id` mediumint(8) unsigned NOT NULL,
  `product_name` varchar(200) default '',
  `pdt_desc` varchar(200) default '',
  `number` int(10) default '0',
  `createtime` int(10) default NULL,
  PRIMARY KEY  (`log_id`),
  KEY `idx_goods_id` (`member_id`,`product_id`,`goods_id`),
  KEY `fk_idx_goods_sell_logs` (`goods_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

/*=============================================================*/
/* New columns                                                 */
/*=============================================================*/
ALTER TABLE `sdb_advance_freeze` ADD COLUMN `freeze_money` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_advance_freeze` ADD COLUMN `thaw_money` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_advance_freeze` ADD COLUMN `created` int(10) NOT NULL default '' ;
ALTER TABLE `sdb_advance_freeze` ADD COLUMN `shop_message` varchar(255) default NULL ;
ALTER TABLE `sdb_advance_freeze` ADD COLUMN `disabled` enum('true','false') default NULL default 'false' ;
ALTER TABLE `sdb_goods` ADD COLUMN `cost` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_goods_cat` ADD COLUMN `child_count` mediumint(8) unsigned NOT NULL default '0' ;
ALTER TABLE `sdb_goods_type` ADD COLUMN `spec` longtext default NULL ;
ALTER TABLE `sdb_member_lv` ADD COLUMN `limit_order_money` int(11) NOT NULL default '0' ;
ALTER TABLE `sdb_member_lv` ADD COLUMN `apply_limit_quantity` enum('true','false') NOT NULL default 'true' ;
ALTER TABLE `sdb_member_lv` ADD COLUMN `show_other_price` enum('true','false') NOT NULL default 'true' ;
ALTER TABLE `sdb_member_lv` ADD COLUMN `order_limit` tinyint(1) NOT NULL default '0' ;
ALTER TABLE `sdb_member_lv` ADD COLUMN `order_limit_price` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_member_lv` ADD COLUMN `lv_remark` text default NULL ;
ALTER TABLE `sdb_members` ADD COLUMN `advance_freeze` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_members` ADD COLUMN `remark` text default NULL ;
ALTER TABLE `sdb_members` ADD COLUMN `role_type` enum('wholesale','dealer') NOT NULL default 'wholesale' ;
ALTER TABLE `sdb_message` ADD COLUMN `msg_ip` varchar(20) NOT NULL default '' ;
ALTER TABLE `sdb_message` ADD COLUMN `msg_type` enum('default','payment') NOT NULL default 'default' ;
ALTER TABLE `sdb_operators` ADD COLUMN `lastip` varchar(20) NOT NULL default '' ;
ALTER TABLE `sdb_operators` ADD COLUMN `logincount` mediumint(8) unsigned NOT NULL default '0' ;
ALTER TABLE `sdb_operators` ADD COLUMN `op_no` varchar(50) NOT NULL default '' ;
ALTER TABLE `sdb_operators` ADD COLUMN `department` varchar(50) NOT NULL default '' ;
ALTER TABLE `sdb_operators` ADD COLUMN `memo` text default NULL ;
ALTER TABLE `sdb_order_log` ADD COLUMN `behavior` varchar(20) default NULL default '' ;
ALTER TABLE `sdb_order_log` ADD COLUMN `result` enum('success','failure') default NULL default 'success' ;
ALTER TABLE `sdb_orders` ADD COLUMN `mark_text` longtext default NULL ;
ALTER TABLE `sdb_orders` ADD COLUMN `last_change_time` int(11) NOT NULL default '0' ;
ALTER TABLE `sdb_orders` ADD COLUMN `use_registerinfo` char(10) default NULL default 'false' ;

/*=============================================================*/
/* Modify columns                                              */
/*=============================================================*/
ALTER TABLE `sdb_advance_freeze` CHANGE COLUMN `member_id` `member_id` mediumint(8) NOT NULL default '' ;
ALTER TABLE `sdb_operators` CHANGE COLUMN `disabled` `disabled` enum('false','true') NOT NULL default 'false' ;
ALTER TABLE `sdb_return_product` CHANGE COLUMN `image_file` `image_file` varchar(255) NOT NULL default '' ;
ALTER TABLE `sdb_wholesale_single` CHANGE COLUMN `wss_name` `wss_name` varchar(255) default NULL ;

/*=============================================================*/
/* Index                                                       */
/*=============================================================*/
ALTER TABLE `sdb_spec_values` ADD INDEX `fk_reference_60`(`spec_id`);

/*=============================================================*/
/* Drop tables                                                 */
/*=============================================================*/
DROP TABLE `sdb_member_info`;

/*=============================================================*/
/* Drop fields                                                 */
/*=============================================================*/
ALTER TABLE `sdb_advance_freeze` DROP `money`;
ALTER TABLE `sdb_advance_freeze` DROP `start_time`;
ALTER TABLE `sdb_advance_freeze` DROP `end_time`;
ALTER TABLE `sdb_members` DROP `member_role`;
ALTER TABLE `sdb_members` DROP `member_role_state`;
ALTER TABLE `sdb_operators` DROP `role_id`;

/*=============================================================*/
/* Drop index                                                  */
/*=============================================================*/
ALTER TABLE `sdb_spec_values` DROP INDEX `fk_spec`;

