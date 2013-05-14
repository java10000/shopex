/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 21246 to 27449                       */
/*   last Modified:  2009/07/15                                */
/*=============================================================*/

/*=============================================================*/
/* Create tables                                               */
/*=============================================================*/
CREATE TABLE `sdb_globals` (
  `glob_id` mediumint(8) unsigned NOT NULL auto_increment,
  `glob_name` varchar(20) NOT NULL,
  `glob_var` varchar(100) NOT NULL,
  `glob_type` enum('system','custom') NOT NULL,
  `glob_remark` varchar(100) NOT NULL,
  `glob_value` text NOT NULL,
  `disabled` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`glob_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

/*=============================================================*/
/* New columns                                                 */
/*=============================================================*/
ALTER TABLE `sdb_orders` ADD COLUMN `extend` varchar(255) default NULL ;

/*=============================================================*/
/* Modify columns                                              */
/*=============================================================*/
ALTER TABLE `sdb_operators` CHANGE COLUMN `lastip` `lastip` varchar(20) default NULL ;
ALTER TABLE `sdb_operators` CHANGE COLUMN `op_no` `op_no` varchar(50) default NULL ;
ALTER TABLE `sdb_operators` CHANGE COLUMN `department` `department` varchar(50) default NULL ;

/*=============================================================*/
/* Index                                                       */
/*=============================================================*/

/*=============================================================*/
/* Drop tables                                                 */
/*=============================================================*/

/*=============================================================*/
/* Drop fields                                                 */
/*=============================================================*/

/*=============================================================*/
/* Drop index                                                  */
/*=============================================================*/
