/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 12175 to 12659                       */
/*   last Modified:  2008/07/23                                */
/*=============================================================*/

drop table if exists sdb_package;

CREATE TABLE `sdb_package` (
  `pkg_id` varchar(100) NOT NULL,
  `disabled` enum('true','false') NOT NULL default 'false',
  `dbver` mediumint(8) unsigned default NULL,
  `adminschema` text,
  `shopaction` text,
  `installed` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`pkg_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;