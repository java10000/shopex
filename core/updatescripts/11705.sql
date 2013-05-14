/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 11639 to 11705                       */
/*   last Modified:  2008/07/09                                */
/*=============================================================*/

ALTER TABLE `sdb_gift` CHANGE COLUMN `insert_time` `insert_time` int(10) NOT NULL default '0' ;
ALTER TABLE `sdb_gift` CHANGE COLUMN `update_time` `update_time` int(10) NOT NULL default '0' ;