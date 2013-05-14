/*=============================================================*/
/* Manual ever                                                  */
/*=============================================================*/
UPDATE `sdb_products` SET goods_id = 0 WHERE goods_id IS NULL;
UPDATE `sdb_members` SET addr = CONCAT(province,city,addr);
UPDATE `sdb_member_addrs` SET addr = CONCAT(country,province,city,addr);