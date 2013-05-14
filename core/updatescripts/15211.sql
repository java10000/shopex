/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 13936 to 15211                       */
/*   last Modified:  2008/11/03                                */
/*=============================================================*/

/*=============================================================*/
/* Create tables                                               */
/*=============================================================*/
CREATE TABLE `sdb_command_list` (
  `cmd_action` varchar(100) NOT NULL,
  `supplier_goods_id` int(11) NOT NULL,
  `cmd_info` text,
  `supplier_id` int(11) NOT NULL,
  `cmd_lasttime` int(11) default NULL,
  `goods_name` varchar(255) default NULL,
  PRIMARY KEY  (`cmd_action`,`supplier_goods_id`,`supplier_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_order_tmpl` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL,
  `content` text,
  `intro` text,
  `create_time` int(10) unsigned default NULL,
  `update_time` int(10) unsigned default NULL,
  `disabled` enum('false','true') NOT NULL default 'false',
  PRIMARY KEY  (`id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_supplier_goods_delete` (
  `goods_id` int(11) NOT NULL,
  `sync_status` enum('0','1') default '0',
  `goods_name` varchar(255) default NULL,
  PRIMARY KEY  (`goods_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_supplier_sync` (
  `supplier_id` int(11) NOT NULL,
  `last_time` int(11) default NULL,
  PRIMARY KEY  (`supplier_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_wholesale` (
  `ws_id` mediumint(8) unsigned NOT NULL auto_increment,
  `ws_no` varchar(100) NOT NULL,
  `ws_name` varchar(200) default NULL,
  `ws_btime` int(10) default NULL,
  `ws_etime` int(10) default NULL,
  `ws_enable` enum('true','false') NOT NULL default 'true',
  `ws_belong` enum('0','1') NOT NULL default '0',
  `ws_bind` tinyint(3) unsigned NOT NULL default '0',
  `ws_params` longtext,
  `ws_object` enum('goods','order') NOT NULL default 'order',
  `ws_type` varchar(50) NOT NULL,
  `ws_desc` longtext,
  `ws_update_time` int(10) default '0',
  `ws_order` int(10) unsigned NOT NULL default '0',
  `disabled` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`ws_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_ws_goods` (
  `ws_id` mediumint(8) unsigned NOT NULL,
  `goods_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`goods_id`,`ws_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_ws_goods_cat` (
  `ws_id` mediumint(8) unsigned NOT NULL,
  `cat_id` mediumint(8) unsigned NOT NULL default '0',
  `brand_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cat_id`,`brand_id`,`ws_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

/*=============================================================*/
/* New columns                                                 */
/*=============================================================*/
ALTER TABLE `sdb_brand` ADD COLUMN `supplier_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_brand` ADD COLUMN `supplier_brand_id` mediumint(8) unsigned default NULL default '0' ;
ALTER TABLE `sdb_dly_area` ADD COLUMN `ordernum` smallint(4) unsigned default NULL ;
ALTER TABLE `sdb_dly_corp` ADD COLUMN `ordernum` smallint(4) unsigned default NULL ;
ALTER TABLE `sdb_dly_corp` ADD COLUMN `website` varchar(200) default NULL ;
ALTER TABLE `sdb_dly_h_area` ADD COLUMN `ordernum` smallint(4) unsigned default NULL ;
ALTER TABLE `sdb_dly_type` ADD COLUMN `corp_id` int(10) default NULL ;
ALTER TABLE `sdb_goods` ADD COLUMN `supplier_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_goods` ADD COLUMN `supplier_goods_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_goods` ADD COLUMN `goods_info_update_status` enum('true','false') default NULL default 'false' ;
ALTER TABLE `sdb_goods` ADD COLUMN `stock_update_status` enum('true','false') default NULL default 'false' ;
ALTER TABLE `sdb_goods` ADD COLUMN `marketable_update_status` enum('true','false') default NULL default 'false' ;
ALTER TABLE `sdb_goods` ADD COLUMN `img_update_status` enum('true','false') default NULL default 'false' ;
ALTER TABLE `sdb_goods_cat` ADD COLUMN `supplier_id` char(10) default NULL ;
ALTER TABLE `sdb_goods_cat` ADD COLUMN `supplier_cat_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_goods_type` ADD COLUMN `supplier_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_goods_type` ADD COLUMN `supplier_type_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_orders` ADD COLUMN `print_status` tinyint(3) unsigned NOT NULL default '0' ;
ALTER TABLE `sdb_payment_cfg` ADD COLUMN `orderlist` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_payments` ADD COLUMN `cur_money` decimal(20,3) NOT NULL default '0.000' ;

/*=============================================================*/
/* Modify columns                                              */
/*=============================================================*/
ALTER TABLE `sdb_dly_type` CHANGE COLUMN `ordernum` `ordernum` smallint(4) default NULL default '0' ;
ALTER TABLE `sdb_goods` CHANGE COLUMN `price` `price` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_goods` CHANGE COLUMN `count_stat` `count_stat` text default NULL ;
ALTER TABLE `sdb_link` CHANGE COLUMN `href` `href` varchar(255) default NULL ;
ALTER TABLE `sdb_order_items` CHANGE COLUMN `price` `price` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_orders` CHANGE COLUMN `cost_item` `cost_item` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_orders` CHANGE COLUMN `total_amount` `total_amount` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_orders` CHANGE COLUMN `final_amount` `final_amount` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_payments` CHANGE COLUMN `money` `money` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_payments` CHANGE COLUMN `status` `status` enum('succ','failed','cancel','error','progress','invalid','timeout','ready') NOT NULL default 'ready' ;
ALTER TABLE `sdb_products` CHANGE COLUMN `price` `price` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_refunds` CHANGE COLUMN `money` `money` decimal(20,3) NOT NULL default '0.000' ;

/*=============================================================*/
/* Index                                                       */
/*=============================================================*/

/*=============================================================*/
/* Drop tables                                                 */
/*=============================================================*/

/*=============================================================*/
/* Drop fields                                                 */
/*=============================================================*/
ALTER TABLE `sdb_brand` DROP `s_brand_id`;
ALTER TABLE `sdb_goods` DROP `s_goods_id`;
ALTER TABLE `sdb_goods_cat` DROP `s_cat_id`;
ALTER TABLE `sdb_goods_type` DROP `s_type_id`;

/*=============================================================*/
/* Drop index                                                  */
/*=============================================================*/
ALTER TABLE `sdb_gnotify` DROP INDEX `index_2`, ADD INDEX `index_2` USING BTREE(`goods_id`, `product_id`, `member_id`);
update sdb_payment_cfg set pay_type = LOWER(pay_type);
