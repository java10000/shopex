/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 13035 to 13936                       */
/*   last Modified:  2008/08/29                                */
/*=============================================================*/

/*=============================================================*/
/* Create tables                                               */
/*=============================================================*/
CREATE TABLE `sdb_link` (
  `link_id` mediumint(8) unsigned NOT NULL auto_increment,
  `link_name` varchar(128) default NULL,
  `href` varchar(255) default NULL,
  `image_url` varchar(255) default NULL,
  `orderlist` mediumint(8) default NULL,
  `disabled` enum('true','false') default 'false',
  PRIMARY KEY  (`link_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

/*=============================================================*/
/* New columns                                                 */
/*=============================================================*/
ALTER TABLE `sdb_payments` ADD COLUMN `trade_no` varchar(30) default NULL ;
ALTER TABLE `sdb_promotion` ADD COLUMN `pmt_ifsale` enum('true','false') NOT NULL default 'true' ;
ALTER TABLE `sdb_promotion` ADD COLUMN `pmt_distype` tinyint(3) unsigned NOT NULL default '0' ;

/*=============================================================*/
/* Modify columns                                              */
/*=============================================================*/
ALTER TABLE `sdb_delivery_item` CHANGE COLUMN `item_type` `item_type` enum('goods','gift','pkg') NOT NULL default 'goods' ;
ALTER TABLE `sdb_event_hdls` CHANGE COLUMN `setting` `setting` longtext default NULL ;
ALTER TABLE `sdb_gift` CHANGE COLUMN `image_file` `image_file` longtext default NULL ;
ALTER TABLE `sdb_goods` CHANGE COLUMN `image_default` `image_default` longtext default NULL ;
ALTER TABLE `sdb_goods` CHANGE COLUMN `image_file` `image_file` longtext default NULL ;
ALTER TABLE `sdb_goods_cat` CHANGE COLUMN `tabs` `tabs` longtext default NULL ;
ALTER TABLE `sdb_goods_cat` CHANGE COLUMN `finder` `finder` longtext default NULL ;
ALTER TABLE `sdb_goods_type` CHANGE COLUMN `alias` `alias` longtext default NULL ;
ALTER TABLE `sdb_logs` CHANGE COLUMN `logforman` `logforman` longtext default NULL ;
ALTER TABLE `sdb_logs` CHANGE COLUMN `logforcmp` `logforcmp` longtext default NULL ;
ALTER TABLE `sdb_members` CHANGE COLUMN `interest` `interest` longtext default NULL ;
ALTER TABLE `sdb_msgqueue` CHANGE COLUMN `data` `data` longtext default NULL ;
ALTER TABLE `sdb_order_log` CHANGE COLUMN `log_text` `log_text` longtext default NULL ;
ALTER TABLE `sdb_orders` CHANGE COLUMN `tostr` `tostr` text default NULL ;
ALTER TABLE `sdb_orders` CHANGE COLUMN `memo` `memo` longtext default NULL ;
ALTER TABLE `sdb_package` CHANGE COLUMN `adminschema` `adminschema` longtext default NULL ;
ALTER TABLE `sdb_package` CHANGE COLUMN `shopaction` `shopaction` longtext default NULL ;
ALTER TABLE `sdb_pages` CHANGE COLUMN `page_content` `page_content` longtext default NULL ;
ALTER TABLE `sdb_payment_cfg` CHANGE COLUMN `des` `des` longtext default NULL ;
ALTER TABLE `sdb_products` CHANGE COLUMN `pdt_desc` `pdt_desc` longtext default NULL ;
ALTER TABLE `sdb_products` CHANGE COLUMN `props` `props` longtext default NULL ;
ALTER TABLE `sdb_refunds` CHANGE COLUMN `memo` `memo` longtext default NULL ;
ALTER TABLE `sdb_sendbox` CHANGE COLUMN `target` `target` longtext default NULL ;
ALTER TABLE `sdb_systmpl` CHANGE COLUMN `content` `content` longtext default NULL ;
ALTER TABLE `sdb_themes` CHANGE COLUMN `config` `config` longtext default NULL ;
