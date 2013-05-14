/*=============================================================*/
/* ShopEx database update script                               */
/*                                                             */
/*         Version:  from 15211 to 16554                       */
/*   last Modified:  2008/12/04                                */
/*=============================================================*/

/*=============================================================*/
/* Create tables                                               */
/*=============================================================*/
CREATE TABLE `sdb_advance_freeze` (
  `freeze_id` mediumint(8) NOT NULL auto_increment,
  `member_id` mediumint(8) default NULL,
  `money` decimal(20,3) default NULL,
  `start_time` int(8) default NULL,
  `end_time` int(8) default NULL,
  `message` varchar(255) default NULL,
  PRIMARY KEY  (`freeze_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_dly_center` (
  `dly_center_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `address` varchar(200) default NULL,
  `region` varchar(100) default NULL,
  `zip` varchar(20) default NULL,
  `phone` varchar(100) default NULL,
  `uname` varchar(100) default NULL,
  `cellphone` varchar(100) default NULL,
  `sex` enum('male','famale') default NULL,
  `memo` longtext,
  `disabled` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`dly_center_id`),
  KEY `ind_disabled` (`disabled`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_member_info` (
  `member_id` mediumint(8) unsigned NOT NULL,
  `company_site` varchar(200) NOT NULL,
  `company_name` varchar(200) default NULL,
  `company_addr` varchar(200) default NULL,
  `company_fax` varchar(30) default NULL,
  `company_des` longtext,
  `qq` varchar(200) default NULL,
  `msn` varchar(200) default NULL,
  `sykpe` varchar(200) default NULL,
  `alibaba` varchar(200) default NULL,
  `site_name` varchar(200) default NULL,
  `site_logo` varchar(200) default NULL
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_print_tmpl` (
  `prt_tmpl_id` int(10) unsigned NOT NULL auto_increment,
  `prt_tmpl_title` varchar(100) NOT NULL,
  `shortcut` enum('false','true') default 'false',
  `disabled` enum('false','true') default 'false',
  `prt_tmpl_width` tinyint(3) unsigned NOT NULL default '100',
  `prt_tmpl_height` tinyint(3) unsigned NOT NULL default '100',
  `prt_tmpl_data` longtext,
  PRIMARY KEY  (`prt_tmpl_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_regions` (
  `region_id` int(10) unsigned NOT NULL auto_increment,
  `package` varchar(20) NOT NULL,
  `p_region_id` int(10) unsigned default NULL,
  `local_name` varchar(50) NOT NULL,
  `en_name` varchar(50) default NULL,
  `p_1` varchar(50) default NULL,
  `p_2` varchar(50) default NULL,
  PRIMARY KEY  (`region_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_return_product` (
  `order_id` bigint(20) unsigned NOT NULL default '0',
  `member_id` mediumint(8) unsigned NOT NULL default '0',
  `return_id` bigint(20) unsigned NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `content` longtext,
  `status` int(10) unsigned NOT NULL default '1',
  `image_file` varchar(255) NOT NULL default '',
  `product_data` longtext,
  `comment` longtext,
  `add_time` int(11) NOT NULL default '0',
  `disabled` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`return_id`),
  KEY `fk_order_ret_pdt` (`order_id`),
  KEY `fk_ret_pdt` (`member_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

CREATE TABLE `sdb_wholesale_single` (
  `wss_id` mediumint(9) NOT NULL auto_increment,
  `wss_name` varchar(511) default NULL,
  `wss_params` longtext,
  `wss_update_time` int(11) default NULL,
  `disabled` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY  (`wss_id`)
)type = MyISAM DEFAULT CHARACTER SET utf8;

/*=============================================================*/
/* New columns                                                 */
/*=============================================================*/
ALTER TABLE `sdb_advance_logs` ADD COLUMN `payment_id` varchar(20) default NULL ;
ALTER TABLE `sdb_advance_logs` ADD COLUMN `order_id` varchar(20) default NULL ;
ALTER TABLE `sdb_advance_logs` ADD COLUMN `paymethod` varchar(100) default NULL ;
ALTER TABLE `sdb_advance_logs` ADD COLUMN `memo` varchar(100) default NULL ;
ALTER TABLE `sdb_advance_logs` ADD COLUMN `import_money` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_advance_logs` ADD COLUMN `explode_money` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_advance_logs` ADD COLUMN `member_advance` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_advance_logs` ADD COLUMN `shop_advance` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_advance_logs` ADD COLUMN `disabled` enum('true','false') NOT NULL default 'false' ;
ALTER TABLE `sdb_brand` ADD COLUMN `ordernum` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_delivery` ADD COLUMN `ship_area` varchar(255) default NULL ;
ALTER TABLE `sdb_gnotify` ADD COLUMN `remark` longtext default NULL ;
ALTER TABLE `sdb_goods` ADD COLUMN `ws_policy` enum('mix','single') NOT NULL default 'mix' ;
ALTER TABLE `sdb_goods` ADD COLUMN `wss_params` longtext default NULL ;
ALTER TABLE `sdb_goods_cat` ADD COLUMN `addon` longtext default NULL ;
ALTER TABLE `sdb_member_addrs` ADD COLUMN `area` varchar(255) default NULL ;
ALTER TABLE `sdb_member_lv` ADD COLUMN `deposit_freeze_time` int(11) default NULL default '0' ;
ALTER TABLE `sdb_member_lv` ADD COLUMN `deposit` int(11) default NULL default '0' ;
ALTER TABLE `sdb_member_lv` ADD COLUMN `more_point` int(11) default NULL default '1' ;
ALTER TABLE `sdb_members` ADD COLUMN `member_role` enum('w','r','d') NOT NULL default 'r' ;
ALTER TABLE `sdb_members` ADD COLUMN `area` varchar(255) default NULL ;
ALTER TABLE `sdb_members` ADD COLUMN `member_role_state` enum('succ','pay','ready','failed') NOT NULL default 'ready' ;
ALTER TABLE `sdb_orders` ADD COLUMN `ship_area` varchar(255) default NULL ;
ALTER TABLE `sdb_promotion` ADD COLUMN `order_money_from` decimal(20,3) NOT NULL default '0.000' ;
ALTER TABLE `sdb_promotion` ADD COLUMN `order_money_to` decimal(20,3) NOT NULL default '9999999.000' ;

/*=============================================================*/
/* Modify columns                                              */
/*=============================================================*/
ALTER TABLE `sdb_command_list` CHANGE COLUMN `cmd_info` `cmd_info` longtext default NULL ;
ALTER TABLE `sdb_gift_items` CHANGE COLUMN `amount` `amount` int(10) unsigned default NULL ;
ALTER TABLE `sdb_goods` CHANGE COLUMN `count_stat` `count_stat` longtext default NULL ;
ALTER TABLE `sdb_goods_cat` CHANGE COLUMN `supplier_id` `supplier_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_goods_lv_price` CHANGE COLUMN `product_id` `product_id` mediumint(8) unsigned NOT NULL default '0' ;
ALTER TABLE `sdb_goods_lv_price` CHANGE COLUMN `level_id` `level_id` mediumint(8) unsigned NOT NULL default '' ;
ALTER TABLE `sdb_goods_lv_price` CHANGE COLUMN `goods_id` `goods_id` mediumint(8) unsigned NOT NULL default '0' ;
ALTER TABLE `sdb_member_lv` CHANGE COLUMN `member_lv_id` `member_lv_id` mediumint(8) unsigned NOT NULL auto_increment;
ALTER TABLE `sdb_member_lv` CHANGE COLUMN `lv_type` `lv_type` enum('retail','wholesale','dealer') NOT NULL default 'retail' ;
ALTER TABLE `sdb_members` CHANGE COLUMN `addr` `addr` varchar(255) default NULL ;
ALTER TABLE `sdb_op_sessions` CHANGE COLUMN `api_id` `api_id` mediumint(8) unsigned default NULL ;
ALTER TABLE `sdb_order_tmpl` CHANGE COLUMN `content` `content` longtext default NULL ;
ALTER TABLE `sdb_order_tmpl` CHANGE COLUMN `intro` `intro` longtext default NULL ;
ALTER TABLE `sdb_orders` CHANGE COLUMN `tostr` `tostr` longtext default NULL ;
ALTER TABLE `sdb_payments` CHANGE COLUMN `pay_type` `pay_type` enum('online','offline','deposit','recharge','joinfee') NOT NULL default 'online' ;
ALTER TABLE `sdb_products` CHANGE COLUMN `goods_id` `goods_id` mediumint(8) unsigned NOT NULL default '0' ;
ALTER TABLE `sdb_supplier_goods_delete` CHANGE COLUMN `goods_id` `goods_id` mediumint(8) unsigned NOT NULL default '' ;

/*=============================================================*/
/* Index                                                       */
/*=============================================================*/
ALTER TABLE `sdb_advance_logs` ADD INDEX `ind_mtime`(`mtime`);
ALTER TABLE `sdb_advance_logs` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_advance_logs` ADD INDEX `fk_m_adv_logs`(`member_id`);
ALTER TABLE `sdb_articles` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_articles` ADD INDEX `fk_article`(`node_id`);
ALTER TABLE `sdb_brand` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_comments` ADD INDEX `ind_goods`(`goods_id`);
ALTER TABLE `sdb_comments` ADD INDEX `ind_member`(`author_id`);
ALTER TABLE `sdb_comments` ADD INDEX `fk_comment`(`for_comment_id`);
ALTER TABLE `sdb_coupons` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_coupons` ADD INDEX `fk_p_c`(`pmt_id`);
ALTER TABLE `sdb_coupons_p_items` ADD INDEX `fk_coupons_items`(`order_id`);
ALTER TABLE `sdb_coupons_u_items` ADD INDEX `fk_o_c_u_i`(`order_id`);
ALTER TABLE `sdb_currency` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_delivery` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_delivery` ADD INDEX `fk_member_dly`(`member_id`);
ALTER TABLE `sdb_delivery` ADD INDEX `fk_order_dly`(`order_id`);
ALTER TABLE `sdb_delivery_item` ADD INDEX `fk_delivery_item`(`delivery_id`);
ALTER TABLE `sdb_dly_area` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_dly_corp` ADD INDEX `ind_type`(`type`);
ALTER TABLE `sdb_dly_corp` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_dly_h_area` ADD INDEX `fk_dlya_type`(`dt_id`);
ALTER TABLE `sdb_dly_h_area` ADD INDEX `fk_dlya_area`(`area_id`);
ALTER TABLE `sdb_dly_type` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_gift` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_gift` ADD INDEX `fk_gift_cat`(`giftcat_id`);
ALTER TABLE `sdb_gift_cat` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_gift_items` ADD INDEX `fk_order_items`(`order_id`);
ALTER TABLE `sdb_gnotify` ADD INDEX `ind_goods`(`goods_id`,`product_id`,`member_id`);
ALTER TABLE `sdb_gnotify` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_gnotify` ADD INDEX `fk_m_gnotify`(`member_id`);
ALTER TABLE `sdb_gnotify` ADD INDEX `fk_g_gnotify`(`goods_id`);
ALTER TABLE `sdb_gnotify` ADD INDEX `fk_pdt_gnotify`(`product_id`);
ALTER TABLE `sdb_goods` ADD INDEX `uni_bn`(`bn`);
ALTER TABLE `sdb_goods` ADD INDEX `ind_p_1`(`p_1`);
ALTER TABLE `sdb_goods` ADD INDEX `ind_p_2`(`p_2`);
ALTER TABLE `sdb_goods` ADD INDEX `ind_p_3`(`p_3`);
ALTER TABLE `sdb_goods` ADD INDEX `ind_p_4`(`p_4`);
ALTER TABLE `sdb_goods` ADD INDEX `ind_p_23`(`p_23`);
ALTER TABLE `sdb_goods` ADD INDEX `ind_p_22`(`p_22`);
ALTER TABLE `sdb_goods` ADD INDEX `ind_p_21`(`p_21`);
ALTER TABLE `sdb_goods` ADD INDEX `ind_frontend`(`disabled`,`goods_type`,`marketable`);
ALTER TABLE `sdb_goods` ADD INDEX `fk_type_b_g`(`type_id`,`brand_id`);
ALTER TABLE `sdb_goods` ADD INDEX `fk_g_type`(`type_id`);
ALTER TABLE `sdb_goods` ADD INDEX `fk_goods_cat`(`cat_id`);
ALTER TABLE `sdb_goods_cat` ADD INDEX `ind_cat_path`(`cat_path`);
ALTER TABLE `sdb_goods_cat` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_goods_cat` ADD INDEX `fk_goods_cat`(`parent_id`);
ALTER TABLE `sdb_goods_cat` ADD INDEX `fk_type_id`(`type_id`);
ALTER TABLE `sdb_goods_lv_price` ADD INDEX `fk_pdt_lv_prcie`(`product_id`);
ALTER TABLE `sdb_goods_lv_price` ADD INDEX `fk_m_lv_price`(`level_id`);
ALTER TABLE `sdb_goods_lv_price` ADD INDEX `fk_g_lv_price`(`goods_id`);
ALTER TABLE `sdb_goods_memo` ADD INDEX `fk_goods_memo`(`goods_id`);
ALTER TABLE `sdb_goods_rate` ADD INDEX `fk_goods_id_1`(`goods_1`);
ALTER TABLE `sdb_goods_rate` ADD INDEX `fk_goods_id_2`(`goods_2`);
ALTER TABLE `sdb_goods_type` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_logs` ADD INDEX `fk_member_logs`(`member_id`);
ALTER TABLE `sdb_logs` ADD INDEX `fk_goods_logs`(`goods_id`);
ALTER TABLE `sdb_logs` ADD INDEX `fk_o_logs`(`op_id`);
ALTER TABLE `sdb_member_addrs` ADD INDEX `fk_member_addr`(`member_id`);
ALTER TABLE `sdb_member_coupon` ADD INDEX `fk_m_coupons`(`cpns_id`);
ALTER TABLE `sdb_member_lv` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_members` ADD INDEX `uni_user`(`uname`);
ALTER TABLE `sdb_members` ADD INDEX `ind_email`(`email`);
ALTER TABLE `sdb_members` ADD INDEX `ind_regtime`(`regtime`);
ALTER TABLE `sdb_members` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_members` ADD INDEX `fk_members`(`member_lv_id`);
ALTER TABLE `sdb_message` ADD INDEX `ind_to_id`(`to_id`,`folder`,`from_type`,`unread`);
ALTER TABLE `sdb_message` ADD INDEX `ind_from_id`(`from_id`,`folder`,`to_type`);
ALTER TABLE `sdb_message` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_message` ADD INDEX `fk_m_msg`(`from_id`);
ALTER TABLE `sdb_message` ADD INDEX `fk_order_msg`(`rel_order`);
ALTER TABLE `sdb_msgqueue` ADD INDEX `ind_level`(`level`);
ALTER TABLE `sdb_operators` ADD INDEX `uni_username`(`username`);
ALTER TABLE `sdb_operators` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_order_items` ADD INDEX `fk_orders_items`(`order_id`);
ALTER TABLE `sdb_order_items` ADD INDEX `fk_order_pdt`(`product_id`);
ALTER TABLE `sdb_order_log` ADD INDEX `fk_order_log`(`order_id`);
ALTER TABLE `sdb_order_pmt` ADD INDEX `fk_order_pmt`(`order_id`);
ALTER TABLE `sdb_orders` ADD INDEX `ind_ship_status`(`ship_status`);
ALTER TABLE `sdb_orders` ADD INDEX `ind_pay_status`(`pay_status`);
ALTER TABLE `sdb_orders` ADD INDEX `ind_status`(`status`);
ALTER TABLE `sdb_orders` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_orders` ADD INDEX `fk_members_orders`(`member_id`);
ALTER TABLE `sdb_package` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_package_product` ADD INDEX `fk_goods_pkg`(`goods_id`);
ALTER TABLE `sdb_package_product` ADD INDEX `fk_pdt_pkg`(`product_id`);
ALTER TABLE `sdb_pages` ADD INDEX `uni_pagename`(`page_name`);
ALTER TABLE `sdb_pages` ADD INDEX `uni_pagetitle`(`page_title`);
ALTER TABLE `sdb_payments` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_payments` ADD INDEX `fk_order_payments`(`order_id`);
ALTER TABLE `sdb_payments` ADD INDEX `fk_payment_opt`(`op_id`);
ALTER TABLE `sdb_payments` ADD INDEX `fk_cfg_payments`(`payment`);
ALTER TABLE `sdb_payments` ADD INDEX `fk_payments`(`member_id`);
ALTER TABLE `sdb_pdt_actions` ADD INDEX `fk_pdt_actions`(`product_id`);
ALTER TABLE `sdb_pdt_actions` ADD INDEX `fk_mem_pdt_act`(`member_id`);
ALTER TABLE `sdb_pmt_gen_coupon` ADD INDEX `fk_pmt_g_c`(`pmt_id`);
ALTER TABLE `sdb_pmt_gen_coupon` ADD INDEX `fk_c_p_g`(`cpns_id`);
ALTER TABLE `sdb_pmt_goods` ADD INDEX `fk_pdt_pmt`(`goods_id`);
ALTER TABLE `sdb_pmt_goods` ADD INDEX `fk_pmt_goods`(`pmt_id`);
ALTER TABLE `sdb_pmt_goods_cat` ADD INDEX `fk_pmt_g_cat`(`pmt_id`);
ALTER TABLE `sdb_pmt_goods_cat` ADD INDEX `fk_goods_ca_pmtt`(`cat_id`);
ALTER TABLE `sdb_pmt_goods_cat` ADD INDEX `fk_brd_p_gcat`(`brand_id`);
ALTER TABLE `sdb_pmt_member_lv` ADD INDEX `fk_pmt_m_lv`(`pmt_id`);
ALTER TABLE `sdb_pmt_member_lv` ADD INDEX `fk_m_lv_pmt`(`member_lv_id`);
ALTER TABLE `sdb_product_memo` ADD INDEX `fk_product_id`(`product_id`);
ALTER TABLE `sdb_products` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_products` ADD INDEX `fk_goods_pdt`(`goods_id`);
ALTER TABLE `sdb_promotion` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_promotion` ADD INDEX `fk_act_pmt`(`pmta_id`);
ALTER TABLE `sdb_promotion_activity` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_pub_files` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_refunds` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_refunds` ADD INDEX `fk_order_refound`(`order_id`);
ALTER TABLE `sdb_refunds` ADD INDEX `fk_mem_refound`(`member_id`);
ALTER TABLE `sdb_refunds` ADD INDEX `fk_opt_refound`(`send_op_id`);
ALTER TABLE `sdb_sendbox` ADD INDEX `ind_sender`(`sender`);
ALTER TABLE `sdb_sendbox` ADD INDEX `fk_sendbox`(`tmpl_name`);
ALTER TABLE `sdb_sfiles` ADD INDEX `ind_usedby`(`usedby`);
ALTER TABLE `sdb_sitemaps` ADD INDEX `ind_hidden`(`hidden`);
ALTER TABLE `sdb_spec_values` ADD INDEX `fk_spec`(`spec_id`);
ALTER TABLE `sdb_tag_rel` ADD INDEX `fk_tag_rel`(`tag_id`);
ALTER TABLE `sdb_tags` ADD INDEX `ind_type`(`tag_type`);
ALTER TABLE `sdb_tags` ADD INDEX `ind_name`(`tag_name`);
ALTER TABLE `sdb_type_brand` ADD INDEX `fk_goods_type`(`type_id`);
ALTER TABLE `sdb_type_brand` ADD INDEX `fk_brand_type`(`brand_id`);
ALTER TABLE `sdb_wholesale` ADD INDEX `ind_disabled`(`disabled`);
ALTER TABLE `sdb_widgets_set` ADD INDEX `ind_wgbase`(`base_file`,`base_id`,`widgets_order`);
ALTER TABLE `sdb_widgets_set` ADD INDEX `ind_wginfo`(`base_file`,`base_slot`,`widgets_order`);
ALTER TABLE `sdb_ws_goods` ADD INDEX `fk_ws_goods`(`ws_id`);
ALTER TABLE `sdb_ws_goods_cat` ADD INDEX `fk_ws_goods_cat`(`ws_id`);

/*=============================================================*/
/* Drop tables                                                 */
/*=============================================================*/

/*=============================================================*/
/* Drop fields                                                 */
/*=============================================================*/
ALTER TABLE `sdb_promotion` DROP `order_money`;
ALTER TABLE `sdb_refunds` DROP `init_op_id`;

/*=============================================================*/
/* Drop index                                                  */
/*=============================================================*/
ALTER TABLE `sdb_advance_logs` DROP INDEX `mtime`;
ALTER TABLE `sdb_comments` DROP INDEX `idx_goods`;
ALTER TABLE `sdb_comments` DROP INDEX `idx_member`;
ALTER TABLE `sdb_delivery` DROP INDEX `index_3`;
ALTER TABLE `sdb_delivery_item` DROP INDEX `index_3`;
ALTER TABLE `sdb_dly_corp` DROP INDEX `type`;
ALTER TABLE `sdb_dly_h_area` DROP INDEX `dt_area`;
ALTER TABLE `sdb_dly_type` DROP INDEX `ordernum`;
ALTER TABLE `sdb_gnotify` DROP INDEX `index_2`;
ALTER TABLE `sdb_goods` DROP INDEX `bn`;
ALTER TABLE `sdb_goods` DROP INDEX `index_1`;
ALTER TABLE `sdb_goods` DROP INDEX `index_2`;
ALTER TABLE `sdb_goods` DROP INDEX `index_3`;
ALTER TABLE `sdb_goods` DROP INDEX `index_4`;
ALTER TABLE `sdb_goods` DROP INDEX `index_5`;
ALTER TABLE `sdb_goods` DROP INDEX `index_6`;
ALTER TABLE `sdb_goods` DROP INDEX `index_7`;
ALTER TABLE `sdb_goods` DROP INDEX `index_8`;
ALTER TABLE `sdb_goods` DROP INDEX `index_9`;
ALTER TABLE `sdb_goods` DROP INDEX `index_10`;
ALTER TABLE `sdb_goods` DROP INDEX `index_11`;
ALTER TABLE `sdb_goods` DROP INDEX `index_12`;
ALTER TABLE `sdb_goods` DROP INDEX `index_13`;
ALTER TABLE `sdb_goods` DROP INDEX `index_14`;
ALTER TABLE `sdb_goods` DROP INDEX `index_15`;
ALTER TABLE `sdb_goods` DROP INDEX `index_16`;
ALTER TABLE `sdb_goods` DROP INDEX `index_17`;
ALTER TABLE `sdb_goods` DROP INDEX `index_18`;
ALTER TABLE `sdb_goods` DROP INDEX `index_19`;
ALTER TABLE `sdb_goods` DROP INDEX `index_20`;
ALTER TABLE `sdb_goods` DROP INDEX `index_28`;
ALTER TABLE `sdb_goods` DROP INDEX `index_27`;
ALTER TABLE `sdb_goods` DROP INDEX `index_26`;
ALTER TABLE `sdb_goods` DROP INDEX `index_25`;
ALTER TABLE `sdb_goods` DROP INDEX `index_24`;
ALTER TABLE `sdb_goods` DROP INDEX `index_23`;
ALTER TABLE `sdb_goods` DROP INDEX `index_22`;
ALTER TABLE `sdb_goods` DROP INDEX `index_21`;
ALTER TABLE `sdb_goods_rate` DROP INDEX `idx_goods1`;
ALTER TABLE `sdb_goods_rate` DROP INDEX `idx_goods2`;
ALTER TABLE `sdb_goods_type` DROP INDEX `index_1`;
ALTER TABLE `sdb_members` DROP INDEX `idx_user`;
ALTER TABLE `sdb_members` DROP INDEX `idx_email`;
ALTER TABLE `sdb_members` DROP INDEX `regtime`;
ALTER TABLE `sdb_message` DROP INDEX `to_id`;
ALTER TABLE `sdb_message` DROP INDEX `from_id`;
ALTER TABLE `sdb_msgqueue` DROP INDEX `index_1`;
ALTER TABLE `sdb_operators` DROP INDEX `username`;
ALTER TABLE `sdb_orders` DROP INDEX `index_3`;
ALTER TABLE `sdb_orders` DROP INDEX `index_2`;
ALTER TABLE `sdb_orders` DROP INDEX `index_1`;
ALTER TABLE `sdb_pages` DROP INDEX `index_1`;
ALTER TABLE `sdb_pages` DROP INDEX `index_2`;
ALTER TABLE `sdb_payments` DROP INDEX `index_3`;
ALTER TABLE `sdb_products` DROP INDEX `idx_goodsid`;
ALTER TABLE `sdb_sendbox` DROP INDEX `index_1`;
ALTER TABLE `sdb_settings` DROP INDEX `idx_setkey`;
ALTER TABLE `sdb_sfiles` DROP INDEX `index_1`;
ALTER TABLE `sdb_sitemaps` DROP INDEX `index_1`;
ALTER TABLE `sdb_systmpl` DROP INDEX `index_1`;
ALTER TABLE `sdb_tags` DROP INDEX `type`;
ALTER TABLE `sdb_tags` DROP INDEX `name`;
ALTER TABLE `sdb_widgets_set` DROP INDEX `index_2`;
ALTER TABLE `sdb_widgets_set` DROP INDEX `index_1`;

/*=============================================================*/
/* Manual                                                      */
/*=============================================================*/
INSERT INTO `sdb_print_tmpl` (`prt_tmpl_id`,`prt_tmpl_title`,`shortcut`,`disabled`,`prt_tmpl_width`,`prt_tmpl_height`,`prt_tmpl_data`) VALUES 
 (1,'EMS','false','false',250,150,'<printer picposition=\"0:0\"><item><name>发货人-姓名</name><ucode>dly_name</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>132:126:91:24</position></item><item><name>网店名称</name><ucode>shop_name</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>189:154:219:23</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>143:180:266:68</position></item><item><name>发货人-邮编</name><ucode>dly_zip</ucode><font></font><fontsize>12</fontsize><fontspace>8</fontspace><border>0</border><italic>0</italic><align>left</align><position>323:249:91:20</position></item><item><name>√</name><ucode>tick</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>181:270:26:21</position></item><item><name>收货人-姓名</name><ucode>ship_name</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>488:126:101:24</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>490:181:293:68</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>658:124:122:20</position></item><item><name>订单-物品数量</name><ucode>order_count</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>339:316:75:54</position></item><item><name>测试内容-物品名称</name><ucode>text</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>center</align><position>75:330:207:21</position></item><item><name>订单-备注</name><ucode>order_memo</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>483:393:289:32</position></item><item><name>收货人-地区2级</name><ucode>ship_area_2</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>480:251:73:21</position></item><item><name>当日日期-年</name><ucode>date_y</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>474:371:42:22</position></item><item><name>当日日期-月</name><ucode>date_m</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>532:371:29:20</position></item><item><name>当日日期-日</name><ucode>date_d</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>584:371:26:21</position></item><item><name>收货人-邮编</name><ucode>ship_zip</ucode><font></font><fontsize>12</fontsize><fontspace>8</fontspace><border>0</border><italic>0</italic><align>left</align><position>672:251:112:21</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>289:122:120:20</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>289:138:120:20</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>658:144:124:20</position></item></printer>'),
 (2,'申通快递单','false','false',250,180,'<printer picposition=\"0:0\"><item><name>收货人-姓名</name><ucode>ship_name</ucode><font>黑体</font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>453:217:169:29</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>440:248:103:20</position></item><item><name>收货人-邮编</name><ucode>ship_zip</ucode><font></font><fontsize>12</fontsize><fontspace>12</fontspace><border>0</border><italic>0</italic><align>left</align><position>569:259:95:23</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font>宋体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>428:124:228:58</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>160:125:193:57</position></item><item><name>网店名称</name><ucode>shop_name</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>129:187:223:25</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>黑体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>142:220:187:28</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>136:268:105:20</position></item><item><name>发货人-邮编</name><ucode>dly_zip</ucode><font></font><fontsize>12</fontsize><fontspace>10</fontspace><border>0</border><italic>0</italic><align>left</align><position>267:261:100:20</position></item><item><name>订单-物品数量</name><ucode>order_count</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>714:231:62:25</position></item><item><name>收货人-地区2级</name><ucode>ship_area_1</ucode><font>黑体</font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>714:179:66:44</position></item><item><name>√</name><ucode>tick</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>508:361:25:20</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>136:248:106:20</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font>宋体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>432:267:116:20</position></item></printer>'),
 (3,'顺丰速运1','false','false',250,180,'<printer picposition=\"0:0\"><item><name>发货人-姓名</name><ucode>dly_name</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>287:127:78:26</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>88:155:279:57</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>223:222:144:20</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>130:223:87:20</position></item><item><name>√</name><ucode>tick</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>367:124:20:20</position></item><item><name>收货人-姓名</name><ucode>ship_name</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>286:254:75:31</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font></font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>81:285:281:91</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>125:384:90:20</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>219:384:140:21</position></item><item><name>订单-物品数量</name><ucode>order_count</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>302:445:62:28</position></item><item><name>网店名称</name><ucode>shop_name</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>103:125:158:29</position></item><item><name>√</name><ucode>tick</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>575:131:20:20</position></item><item><name>发货人-地区2级</name><ucode>dly_area_1</ucode><font>黑体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>570:74:64:38</position></item><item><name>收货人-地区2级</name><ucode>ship_area_1</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>637:73:60:39</position></item><item><name>当日日期-月</name><ucode>date_m</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>599:369:34:20</position></item><item><name>当日日期-日</name><ucode>date_d</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>640:369:33:20</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>center</align><position>592:340:157:24</position></item></printer>'),
 (4,'顺丰速运2','false','false',240,135,'<printer picposition=\"0:0\"><item><name>网店名称</name><ucode>shop_name</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>114:134:185:23</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>331:135:79:22</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>653:253:113:32</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>226:239:182:21</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>270:263:135:20</position></item><item><name>发货人-地区2级</name><ucode>dly_area_1</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>199:160:48:25</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font>undefined</font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>114:184:293:54</position></item><item><name>发货人-地区1级</name><ucode>dly_area_0</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>107:159:60:24</position></item><item><name>收货人-地区2级</name><ucode>ship_area_1</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>543:159:66:27</position></item><item><name>收货人-地区1级</name><ucode>ship_area_0</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>472:159:55:24</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>468:186:297:60</position></item><item><name>收货人-姓名</name><ucode>ship_name</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>692:134:75:23</position></item><item><name>发货人-地区2级</name><ucode>dly_area_1</ucode><font>黑体</font><fontsize>18</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>620:91:74:40</position></item><item><name>收货人-地区2级</name><ucode>ship_area_1</ucode><font>undefined</font><fontsize>18</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>696:90:70:41</position></item><item><name>√</name><ucode>tick</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>399:292:20:20</position></item><item><name>当日日期-月</name><ucode>date_m</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>402:454:22:20</position></item><item><name>当日日期-日</name><ucode>date_d</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>428:454:20:20</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>532:253:93:34</position></item></printer>'),
 (5,'天天快递','false','false',250,180,'<printer picposition=\"0:0\"><item><name>发货人-地区2级</name><ucode>dly_area_1</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>329:107:84:29</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>141:139:273:72</position></item><item><name>发货人-邮编</name><ucode>dly_zip</ucode><font></font><fontsize>12</fontsize><fontspace>8</fontspace><border>0</border><italic>0</italic><align>left</align><position>305:194:87:20</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>115:212:145:23</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>304:214:111:21</position></item><item><name>网店名称</name><ucode>shop_name</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>115:236:297:22</position></item><item><name>收货人-地区2级</name><ucode>ship_area_1</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>483:106:98:33</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>503:141:259:48</position></item><item><name>收货人-邮编</name><ucode>ship_zip</ucode><font></font><fontsize>12</fontsize><fontspace>8</fontspace><border>0</border><italic>0</italic><align>left</align><position>664:190:96:22</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>481:213:148:24</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>665:213:94:22</position></item><item><name>收货人-姓名</name><ucode>ship_name</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>500:260:242:25</position></item><item><name>当日日期-年</name><ucode>date_y</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>123:107:36:21</position></item><item><name>当日日期-月</name><ucode>date_m</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>158:107:20:21</position></item><item><name>当日日期-日</name><ucode>date_d</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>180:106:20:22</position></item><item><name>订单-物品数量</name><ucode>order_count</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>270:324:119:23</position></item></printer>'),
 (6,'圆通速递模板','false','false',250,150,'<printer picposition=\"0:0\"><item><name>收货人-姓名</name><ucode>ship_name</ucode><font>黑体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>481:112:87:23</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font>黑体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>419:198:366:37</position></item><item><name>当日日期-年</name><ucode>date_y</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>64:407:39:20</position></item><item><name>网店名称</name><ucode>shop_name</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>130:121:272:30</position></item><item><name>发货人-地区1级</name><ucode>dly_area_1</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>126:160:75:30</position></item><item><name>发货人-地区2级</name><ucode>dly_area_2</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>225:160:52:31</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>144:222:114:27</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>57:192:343:28</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>119:95:93:26</position></item><item><name>当日日期-月</name><ucode>date_m</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>115:407:21:20</position></item><item><name>收货人-地区1级</name><ucode>ship_area_1</ucode><font></font><fontsize>18</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>481:164:102:31</position></item><item><name>发货人-地区2级</name><ucode>dly_area_2</ucode><font></font><fontsize>18</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>603:163:58:31</position></item><item><name>√</name><ucode>tick</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>131:254:23:21</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>646:107:124:24</position></item><item><name>当日日期-日</name><ucode>date_d</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>153:406:20:20</position></item><item><name>收货人-邮编</name><ucode>ship_zip</ucode><font></font><fontsize>12</fontsize><fontspace>10</fontspace><border>0</border><italic>0</italic><align>left</align><position>693:230:105:20</position></item><item><name>订单-备注</name><ucode>order_memo</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>473:403:308:20</position></item><item><name>订单-物品数量</name><ucode>order_count</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>291:317:99:30</position></item><item><name>自定义内容（内件品名）</name><ucode>text</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>59:316:210:31</position></item><item><name>发货人-邮编</name><ucode>dly_zip</ucode><font></font><fontsize>12</fontsize><fontspace>8</fontspace><border>0</border><italic>0</italic><align>left</align><position>313:228:99:20</position></item><item><name>√</name><ucode>tick</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>246:348:20:20</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>646:125:125:20</position></item><item><name>发货人-地区2级</name><ucode>dly_area_1</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>275:95:122:23</position></item></printer>'),
 (7,'韵达快运单','false','false',250,150,'<printer picposition=\"0:0\"><item><name>收货人-地址</name><ucode>ship_addr</ucode><font></font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>424:160:338:86</position></item><item><name>收货人-地区1级</name><ucode>ship_area_0</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>468:116:76:22</position></item><item><name>收货人-地区2级</name><ucode>ship_area_1</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>542:115:78:24</position></item><item><name>收货人-姓名</name><ucode>ship_name</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>601:140:125:20</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>66:166:341:80</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>104:248:162:20</position></item><item><name>当日日期-月</name><ucode>date_m</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>163:111:20:25</position></item><item><name>当日日期-日</name><ucode>date_d</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>192:110:20:26</position></item><item><name>当日日期-年</name><ucode>date_y</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>121:110:32:25</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>227:140:180:25</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>461:246:141:20</position></item><item><name>收货人-邮编</name><ucode>ship_zip</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>8</fontspace><border>1</border><italic>0</italic><align>left</align><position>653:255:110:26</position></item><item><name>发货人-邮编</name><ucode>dly_zip</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>8</fontspace><border>1</border><italic>0</italic><align>left</align><position>315:259:90:22</position></item><item><name>√</name><ucode>tick</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>696:404:20:20</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>461:264:143:20</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>104:266:162:20</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>黑体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>128:338:110:35</position></item></printer>'),
 (8,'宅急送快件','false','false',250,180,'<printer picposition=\"0:0\"><item><name>发货人-姓名</name><ucode>dly_name</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>143:129:100:31</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>108:162:293:59</position></item><item><name>发货人-地区2级</name><ucode>dly_area_1</ucode><font></font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>270:129:126:30</position></item><item><name>收货人-姓名</name><ucode>ship_name</ucode><font></font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>513:127:98:31</position></item><item><name>收货人-地区2级</name><ucode>ship_area_1</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>633:127:130:31</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>469:160:294:58</position></item><item><name>网店名称</name><ucode>shop_name</ucode><font>黑体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>112:222:288:28</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>101:251:141:23</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>296:251:105:20</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>644:248:118:23</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>453:249:143:25</position></item><item><name>订单-物品数量</name><ucode>order_count</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>102:304:97:26</position></item><item><name>当日日期-月</name><ucode>date_m</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>100:464:20:20</position></item><item><name>当日日期-日</name><ucode>date_d</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>128:463:20:20</position></item><item><name>订单-备注</name><ucode>order_memo</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>469:376:174:42</position></item><item><name>√</name><ucode>tick</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>383:424:20:21</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>66:443:125:25</position></item></printer>'),
 (9,'宅急送普件','false','false',240,135,'<printer picposition=\"0:0\"><item><name>发货人-地区2级</name><ucode>dly_area_1</ucode><font>undefined</font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>272:132:130:31</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>75:446:103:33</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>106:165:294:59</position></item><item><name>网店名称</name><ucode>shop_name</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>107:225:289:27</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>271:254:131:25</position></item><item><name>收货人-姓名</name><ucode>ship_name</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>482:131:120:32</position></item><item><name>收货人-地区2级</name><ucode>ship_area_1</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>637:132:126:30</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font>undefined</font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>467:164:296:58</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>622:253:143:25</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>462:253:136:24</position></item><item><name>订单-物品数量</name><ucode>order_count</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>106:305:93:27</position></item><item><name>√</name><ucode>tick</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>383:426:20:20</position></item><item><name>当日日期-月</name><ucode>date_m</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>99:465:20:20</position></item><item><name>当日日期-日</name><ucode>date_d</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>125:466:20:21</position></item><item><name>订单-备注</name><ucode>order_memo</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>516:363:127:62</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>125:133:116:31</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>103:255:139:22</position></item></printer>'),
 (10,'中国邮政普件','false','false',240,135,'<printer picposition=\"0:0\"><item><name>收货人-姓名</name><ucode>ship_name</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>130:261:107:27</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>275:253:142:20</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font>undefined</font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>151:188:267:66</position></item><item><name>收货人-邮编</name><ucode>ship_zip</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>16</fontspace><border>1</border><italic>0</italic><align>left</align><position>95:160:159:20</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>152:291:266:66</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>276:356:129:20</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>undefined</font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>133:365:98:26</position></item><item><name>√</name><ucode>tick</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>576:259:20:20</position></item><item><name>订单-物品数量</name><ucode>order_count</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>505:174:93:75</position></item><item><name>√</name><ucode>tick</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>433:354:25:23</position></item><item><name>发货人-邮编</name><ucode>dly_zip</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>10</fontspace><border>0</border><italic>0</italic><align>left</align><position>305:400:112:21</position></item><item><name>测试内容物品</name><ucode>text</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>421:175:83:69</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>275:272:143:20</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>275:375:136:20</position></item></printer>'),
 (11,'中通速递1','false','false',250,180,'<printer picposition=\"0:0\"><item><name>发货人-地区2级</name><ucode>dly_area_1</ucode><font>Verdana</font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>299:113:110:26</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>121:238:156:20</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font></font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>150:115:100:27</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>88:142:321:89</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font>黑体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>424:148:328:89</position></item><item><name>收货人-地区2级</name><ucode>ship_area_1</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>654:111:98:31</position></item><item><name>收货人-邮编</name><ucode>ship_zip</ucode><font></font><fontsize>12</fontsize><fontspace>10</fontspace><border>0</border><italic>0</italic><align>left</align><position>657:253:101:20</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font></font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>457:240:163:20</position></item><item><name>订单-物品数量</name><ucode>order_count</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>315:281:114:29</position></item><item><name>当日日期-月</name><ucode>date_m</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>173:381:35:21</position></item><item><name>当日日期-日</name><ucode>date_d</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>205:380:20:20</position></item><item><name>发货人-邮编</name><ucode>dly_zip</ucode><font></font><fontsize>12</fontsize><fontspace>6</fontspace><border>0</border><italic>0</italic><align>left</align><position>315:252:97:21</position></item><item><name>√</name><ucode>tick</ucode><font></font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>506:407:20:20</position></item><item><name>收货人-姓名</name><ucode>ship_name</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>494:114:106:26</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>456:259:164:20</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>121:258:156:20</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>undefined</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>159:343:106:32</position></item></printer>'),
 (12,'中通速递2','false','false',240,135,'<printer picposition=\"0:0\"><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>137:107:102:29</position></item><item><name>发货人-地区2级</name><ucode>dly_area_1</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>285:107:116:30</position></item><item><name>发货人-地址</name><ucode>dly_address</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>137:139:263:57</position></item><item><name>网店名称</name><ucode>shop_name</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>130:199:269:34</position></item><item><name>发货人-电话</name><ucode>dly_tel</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>135:234:130:20</position></item><item><name>发货人-邮编</name><ucode>dly_zip</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>8</fontspace><border>0</border><italic>0</italic><align>left</align><position>297:249:104:23</position></item><item><name>收货人-地址</name><ucode>ship_addr</ucode><font>黑体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>482:139:272:58</position></item><item><name>收货人-电话</name><ucode>ship_tel</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>488:234:128:20</position></item><item><name>收货人-邮编</name><ucode>ship_zip</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>8</fontspace><border>0</border><italic>0</italic><align>left</align><position>659:248:95:22</position></item><item><name>收货人-姓名</name><ucode>ship_name</ucode><font>黑体</font><fontsize>18</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>483:107:116:31</position></item><item><name>收货人-地区2级</name><ucode>ship_area_1</ucode><font>黑体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>center</align><position>638:107:116:31</position></item><item><name>订单-物品数量</name><ucode>order_count</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>564:277:67:30</position></item><item><name>当日日期-月</name><ucode>date_m</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>142:381:20:20</position></item><item><name>当日日期-日</name><ucode>date_d</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>171:382:20:20</position></item><item><name>订单-备注</name><ucode>order_memo</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>416:342:108:56</position></item><item><name>发货人-手机</name><ucode>dly_mobile</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>135:254:128:20</position></item><item><name>收货人-手机</name><ucode>ship_mobile</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>486:253:131:20</position></item><item><name>√</name><ucode>tick</ucode><font>undefined</font><fontsize>12</fontsize><fontspace>0</fontspace><border>0</border><italic>0</italic><align>left</align><position>358:415:20:20</position></item><item><name>发货人-姓名</name><ucode>dly_name</ucode><font>黑体</font><fontsize>14</fontsize><fontspace>0</fontspace><border>1</border><italic>0</italic><align>left</align><position>141:346:95:32</position></item></printer>');