/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 12659 to 13035                       */
/*   last Modified:  2008/07/29                                */
/*=============================================================*/

ALTER TABLE `sdb_sitemaps` ADD COLUMN `hidden` enum('true','false') NOT NULL default 'false' ;
ALTER TABLE `sdb_sitemaps` ADD INDEX `index_1`(`hidden`);
ALTER TABLE `sdb_sitemaps` AUTO_INCREMENT = 151;
UPDATE sdb_payment_cfg SET pay_type = 'alipay' WHERE pay_type = 'ALIPAY';
UPDATE sdb_payment_cfg SET pay_type = 'alipaytrad' WHERE pay_type = 'ALIPAYTRAD';
UPDATE sdb_promotion set pmts_id = substring(pmts_id,5) where pmts_id like "pmt_%";