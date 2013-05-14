/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 11705 to 12149                       */
/*   last Modified:  2008/07/14                                */
/*=============================================================*/

ALTER TABLE `sdb_advance_logs` CHANGE COLUMN `money` `money` decimal(20,3) NOT NULL default 0 ;