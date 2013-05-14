/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 33184 to 34365                       */
/*   last Modified:  2009/10/23                                */
/*=============================================================*/

/*=============================================================*/
/* Create tables                                               */
/*=============================================================*/
CREATE TABLE `sdb_autosync_rule` (
  `rule_id` mediumint(8) unsigned NOT NULL auto_increment,
  `supplier_op_id` tinyint(4) NOT NULL default '0',
  `local_op_id` tinyint(4) NOT NULL default '0',
  `disabled` enum('false','true') NOT NULL default 'false',
  `memo` varchar(255) default NULL,
  `rule_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`rule_id`),
  KEY `index_1` (`rule_id`,`local_op_id`,`disabled`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_autosync_rule_relation` (
  `rule_id` mediumint(8) unsigned NOT NULL default '0',
  `supplier_id` int(10) unsigned NOT NULL default '0',
  `pline_id` mediumint(8) unsigned NOT NULL default '0',
  KEY `rsp_index` (`rule_id`,`supplier_id`,`pline_id`),
  KEY `supplier_id` (`supplier_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_autosync_task` (
  `supplier_id` int(10) unsigned NOT NULL default '0',
  `command_id` int(10) unsigned NOT NULL default '0',
  `local_op_id` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`supplier_id`,`command_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_cost_sync` (
  `supplier_id` int(10) unsigned NOT NULL,
  `bn` varchar(30) NOT NULL,
  `cost` decimal(20,3) NOT NULL default '0.000',
  `version_id` int(10) unsigned NOT NULL default '0',
  `product_id` mediumint(8) unsigned NOT NULL default '0',
  `goods_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`supplier_id`,`bn`),
  KEY `spid_gid` (`supplier_id`,`goods_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

/*=============================================================*/
/* New columns                                                 */
/*=============================================================*/
ALTER TABLE `sdb_goods_type` ADD COLUMN `lastmodify` int(10) unsigned default NULL ;
ALTER TABLE `sdb_specification` ADD COLUMN `lastmodify` int(10) unsigned default NULL ;

/*=============================================================*/
/* Modify columns                                              */
/*=============================================================*/

/*=============================================================*/
/* Index                                                       */
/*=============================================================*/
ALTER TABLE `sdb_supplier_pdtbn` ADD INDEX `sp_srcbn`(`source_bn`,`supplier_id`);

/*=============================================================*/
/* Drop tables                                                 */
/*=============================================================*/

/*=============================================================*/
/* Drop fields                                                 */
/*=============================================================*/

/*=============================================================*/
/* Drop index                                                  */
/*=============================================================*/
