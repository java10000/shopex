/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 18137 to 21246                       */
/*   last Modified:  2009/03/14                                */
/*=============================================================*/

/*=============================================================*/
/* Create tables                                               */
/*=============================================================*/
CREATE TABLE `sdb_gimages` (
  `gimage_id` mediumint(8) unsigned NOT NULL auto_increment,
  `goods_id` mediumint(8) unsigned default NULL,
  `is_remote` enum('true','false') NOT NULL default 'false',
  `source` varchar(255) NOT NULL,
  `orderby` tinyint(3) unsigned NOT NULL default '0',
  `src_size_width` int(10) unsigned NOT NULL,
  `src_size_height` int(10) unsigned NOT NULL,
  `small` varchar(255) default NULL,
  `big` varchar(255) default NULL,
  `thumbnail` varchar(255) default NULL,
  `up_time` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`gimage_id`),
  KEY `fk_fk_gimages` (`goods_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

ALTER TABLE `sdb_gimages` CHANGE COLUMN `source` `source` varchar(255) NULL ;
ALTER TABLE `sdb_gimages` CHANGE COLUMN `small` `small` varchar(255) NULL ;
ALTER TABLE `sdb_gimages` CHANGE COLUMN `big` `big` varchar(255) NULL ;
ALTER TABLE `sdb_gimages` CHANGE COLUMN `thumbnail` `thumbnail` varchar(255) NULL ;
ALTER TABLE `sdb_gimages` CHANGE COLUMN `orderby` `orderby` tinyint(3) unsigned NOT NULL default '0' ;


CREATE TABLE `sdb_goods_type_spec` (
  `spec_id` mediumint(8) unsigned default '0',
  `type_id` mediumint(8) unsigned default '0',
  `spec_style` enum('select','flat','disabled') NOT NULL default 'flat',
  KEY `fk_spec_type` (`spec_id`),
  KEY `fk_type_spec` (`type_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_status` (
  `status_key` varchar(20) NOT NULL,
  `date_affect` date NOT NULL default '0000-00-00',
  `status_value` varchar(100) NOT NULL default '0',
  `last_update` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`status_key`,`date_affect`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

/*=============================================================*/
/* New columns                                                 */
/*=============================================================*/
ALTER TABLE `sdb_dly_h_area` DROP INDEX `PRIMARY`;
ALTER TABLE `sdb_dly_h_area` ADD COLUMN `dha_id` mediumint(8) unsigned NOT NULL auto_increment PRIMARY KEY;
ALTER TABLE `sdb_dly_h_area` ADD COLUMN `areaname_group` longtext default NULL ;
ALTER TABLE `sdb_dly_h_area` ADD COLUMN `areaid_group` longtext default NULL ;
ALTER TABLE `sdb_dly_h_area` ADD COLUMN `config` varchar(255) default NULL ;
ALTER TABLE `sdb_dly_type` ADD COLUMN `dt_config` longtext default NULL ;
ALTER TABLE `sdb_dly_type` ADD COLUMN `dt_expressions` longtext default NULL ;
ALTER TABLE `sdb_dly_type` ADD COLUMN `dt_status` mediumint(1) unsigned default NULL ;
ALTER TABLE `sdb_goods` ADD COLUMN `spec_desc` longtext default NULL ;
ALTER TABLE `sdb_members` ADD COLUMN `remark_type` varchar(2) NOT NULL default 'b1' ;
ALTER TABLE `sdb_orders` ADD COLUMN `mark_type` varchar(2) NOT NULL default 'b1' ;
ALTER TABLE `sdb_regions` ADD COLUMN `region_path` varchar(255) default NULL ;
ALTER TABLE `sdb_regions` ADD COLUMN `region_grade` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_regions` ADD COLUMN `ordernum` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_sitemaps` ADD COLUMN `child_count` mediumint(4) default NULL ;
ALTER TABLE `sdb_spec_values` DROP INDEX `PRIMARY`;
ALTER TABLE `sdb_spec_values` ADD COLUMN `spec_value_id` mediumint(8) unsigned NOT NULL auto_increment PRIMARY KEY;
ALTER TABLE `sdb_spec_values` ADD COLUMN `spec_image` varchar(255) NOT NULL default '' ;
ALTER TABLE `sdb_specification` ADD COLUMN `spec_show_type` enum('select','flat') NOT NULL default 'flat' ;
ALTER TABLE `sdb_specification` ADD COLUMN `spec_type` enum('text','image') NOT NULL default 'text' ;
ALTER TABLE `sdb_specification` ADD COLUMN `spec_memo` varchar(50) NOT NULL default '' ;
ALTER TABLE `sdb_specification` ADD COLUMN `disabled` enum('true','false') NOT NULL default 'false' ;

/*=============================================================*/
/* Modify columns                                              */
/*=============================================================*/
UPDATE `sdb_spec_values` SET `p_order` = '50' WHERE `p_order` IS NULL;
UPDATE `sdb_specification` SET `p_order` = '0' WHERE `p_order` IS NULL;

ALTER TABLE `sdb_coupons` CHANGE COLUMN `cpns_type` `cpns_type` enum('0','1','2') NOT NULL default '1' ;
ALTER TABLE `sdb_dly_h_area` CHANGE COLUMN `dt_id` `dt_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_dly_h_area` CHANGE COLUMN `area_id` `area_id` mediumint(6) unsigned default NULL default '0' ;
ALTER TABLE `sdb_dly_h_area` CHANGE COLUMN `price` `price` varchar(100) default NULL default '0' ;

ALTER TABLE `sdb_goods` DROP COLUMN `ws_policy`;
ALTER TABLE `sdb_goods` ADD COLUMN `ws_policy` enum('11','01') NOT NULL default '01' ;
ALTER TABLE `sdb_goods_spec_index` CHANGE COLUMN `spec_value` `spec_value` varchar(100) NOT NULL default '' ;
ALTER TABLE `sdb_orders` CHANGE COLUMN `use_registerinfo` `use_registerinfo` enum('true','false') default NULL default 'false' ;
ALTER TABLE `sdb_spec_values` CHANGE COLUMN `spec_id` `spec_id` mediumint(8) unsigned NOT NULL default '0' ;
ALTER TABLE `sdb_spec_values` CHANGE COLUMN `p_order` `p_order` mediumint(8) unsigned NOT NULL default '50' ;
ALTER TABLE `sdb_specification` CHANGE COLUMN `spec_id` `spec_id` mediumint(8) unsigned NOT NULL auto_increment;
ALTER TABLE `sdb_specification` CHANGE COLUMN `p_order` `p_order` mediumint(8) unsigned NOT NULL default '0' ;

/*=============================================================*/
/* Index                                                       */
/*=============================================================*/
ALTER TABLE `sdb_goods_spec_index` DROP INDEX `PRIMARY`;
ALTER TABLE `sdb_goods_spec_index` ADD CONSTRAINT  `PRIMARY` PRIMARY KEY CLUSTERED(`spec_value_id`,`product_id`);
ALTER TABLE `sdb_goods_spec_index` ADD INDEX `type_specvalue_index`(`type_id`,`spec_value_id`,`goods_id`);
ALTER TABLE `sdb_goods_spec_index` ADD INDEX `fk_spec_goods_index`(`goods_id`);
ALTER TABLE `sdb_goods_spec_index` ADD INDEX `fk_spec_index`(`spec_id`);
ALTER TABLE `sdb_goods_spec_index` ADD INDEX `fk_spec_products`(`product_id`);
ALTER TABLE `sdb_goods_spec_index` ADD INDEX `fk_spec_type_index`(`type_id`);
ALTER TABLE `sdb_goods_spec_index` ADD INDEX `fk_spec_value_index`(`spec_value_id`);
ALTER TABLE `sdb_spec_values` ADD INDEX `fk_spec_value`(`spec_id`);

/*=============================================================*/
/* Drop tables                                                 */
/*=============================================================*/

/*=============================================================*/
/* Drop fields                                                 */
/*=============================================================*/
ALTER TABLE `sdb_member_lv` DROP `limit_order_money`;
ALTER TABLE `sdb_member_lv` DROP `apply_limit_quantity`;

/*=============================================================*/
/* Drop index                                                  */
/*=============================================================*/
ALTER TABLE `sdb_spec_values` DROP INDEX `fk_reference_60`;

/*=============================================================*/
/* Manual anybody                                                  */
/*=============================================================*/
ALTER TABLE `sdb_products` CHANGE `cost` `cost` DECIMAL( 20, 3 ) NULL DEFAULT '0';
UPDATE `sdb_products` SET `cost` = 0 WHERE `cost` IS NULL;
