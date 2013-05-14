/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:                                            */
/*   last Modified:  2009/07/23                                */
/*=============================================================*/

/*=============================================================*/
/* Create tables                                               */
/*=============================================================*/
CREATE TABLE `sdb_image_sync` (
  `img_sync_id` int(10) unsigned NOT NULL auto_increment,
  `type` enum('gimage','spec_value','udfimg','brand_logo') NOT NULL default 'gimage',
  `supplier_id` int(10) unsigned NOT NULL,
  `supplier_object_id` mediumint(8) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `command_id` int(10) unsigned NOT NULL,
  `failed` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`img_sync_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_job_apilist` (
  `job_id` int(10) unsigned NOT NULL auto_increment,
  `supplier_id` int(10) unsigned NOT NULL,
  `api_name` varchar(100) NOT NULL,
  `api_params` text,
  `api_version` varchar(10) NOT NULL,
  `api_action` varchar(100) NOT NULL,
  `page` mediumint(8) unsigned NOT NULL,
  `limit` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`job_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_job_data_sync` (
  `job_id` int(10) unsigned NOT NULL auto_increment,
  `from_time` int(10) unsigned NOT NULL,
  `to_time` int(10) unsigned NOT NULL,
  `page` mediumint(8) unsigned NOT NULL,
  `limit` mediumint(8) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `supplier_pline` text,
  `auto_download` enum('true','false') NOT NULL default 'false',
  `to_cat_id` mediumint(8) unsigned default NULL,
  PRIMARY KEY  (`job_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_job_goods_download` (
  `job_id` int(10) unsigned NOT NULL auto_increment,
  `supplier_id` int(10) unsigned NOT NULL,
  `supplier_goods_id` mediumint(8) unsigned NOT NULL,
  `supplier_goods_count` int(10) unsigned NOT NULL default '1',
  `command_id` int(10) unsigned NOT NULL,
  `failed` enum('true','false') NOT NULL default 'false',
  `to_cat_id` mediumint(8) unsigned default NULL,
  PRIMARY KEY  (`job_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_supplier` (
  `sp_id` mediumint(8) unsigned NOT NULL auto_increment,
  `supplier_id` int(10) unsigned NOT NULL,
  `supplier_brief_name` varchar(30) default NULL,
  `status` tinyint(4) NOT NULL default '1',
  `supplier_pline` text,
  `sync_time` int(10) unsigned NOT NULL default '0',
  `domain` varchar(255) NOT NULL,
  `has_new` enum('true','false') NOT NULL default 'true',
  `sync_time_for_plat` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`sp_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_supplier_pdtbn` (
  `sp_id` mediumint(8) unsigned NOT NULL,
  `local_bn` varchar(200) NOT NULL,
  `source_bn` varchar(200) NOT NULL,
  `default` enum('true','false') NOT NULL default 'true',
  `supplier_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`sp_id`,`local_bn`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_sync_tmp` (
  `tmp_id` int(10) unsigned NOT NULL auto_increment,
  `s_type` enum('goods_type','spec','brand') NOT NULL,
  `ob_id` mediumint(8) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `s_data` text,
  PRIMARY KEY  (`tmp_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

/*=============================================================*/
/* New columns                                                 */
/*=============================================================*/
ALTER TABLE `sdb_delivery` ADD COLUMN `supplier_id` int(10) unsigned default NULL ;
ALTER TABLE `sdb_delivery` ADD COLUMN `supplier_delivery_id` int(10) unsigned default NULL ;
ALTER TABLE `sdb_gimages` ADD COLUMN `supplier_id` int(10) unsigned default NULL ;
ALTER TABLE `sdb_gimages` ADD COLUMN `supplier_gimage_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_gimages` ADD COLUMN `sync_time` int(10) unsigned NOT NULL default '0' ;
ALTER TABLE `sdb_order_items` ADD COLUMN `supplier_id` int(10) unsigned default NULL ;

ALTER TABLE `sdb_products` ADD COLUMN `is_local_stock` enum('true','false') NOT NULL default 'true' ;
ALTER TABLE `sdb_spec_values` ADD COLUMN `supplier_id` int(10) unsigned default NULL ;
ALTER TABLE `sdb_spec_values` ADD COLUMN `supplier_spec_value_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_specification` ADD COLUMN `supplier_id` int(10) unsigned default NULL ;
ALTER TABLE `sdb_specification` ADD COLUMN `supplier_spec_id` mediumint(8) unsigned default NULL ;

/*=============================================================*/
/* Modify columns                                              */
/*=============================================================*/
ALTER TABLE `sdb_brand` CHANGE COLUMN `supplier_id` `supplier_id` int(10) unsigned default NULL ;
ALTER TABLE `sdb_brand` CHANGE COLUMN `supplier_brand_id` `supplier_brand_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_goods` CHANGE COLUMN `supplier_id` `supplier_id` int(10) unsigned default NULL ;
ALTER TABLE `sdb_goods_cat` CHANGE COLUMN `supplier_id` `supplier_id` int(10) unsigned default NULL ;
ALTER TABLE `sdb_goods_type` CHANGE COLUMN `supplier_id` `supplier_id` int(10) unsigned default NULL ;
ALTER TABLE `sdb_operators` CHANGE COLUMN `lastip` `lastip` varchar(20) default NULL ;
ALTER TABLE `sdb_operators` CHANGE COLUMN `op_no` `op_no` varchar(50) default NULL ;
ALTER TABLE `sdb_operators` CHANGE COLUMN `department` `department` varchar(50) default NULL ;

/*=============================================================*/
/* Index                                                       */
/*=============================================================*/

/*=============================================================*/
/* Drop tables                                                 */
/*=============================================================*/
DROP TABLE `sdb_supplier_sync`;

/*=============================================================*/
/* Drop fields                                                 */
/*=============================================================*/

/*=============================================================*/
/* Drop index                                                  */
/*=============================================================*/
