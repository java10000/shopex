安装须知

本版本为shopex-singel-4.8.5.78660 
发布时间：2013.04.16

1、关于安装环境
服务器环境：Linux、Unix、Windows均可
Web环境：Apache、Ngix、IIS均可
语言环境：PHP 5.1.2 及以上
数据库：MySQL 5.0 及以上
其他：Zend Optimizer 2.5.7 及以上

特别说明：从本版本开始，安装环境不再兼容php4，只支持php5，请安装前一定要先确认php的版本

2、PHP环境下必须要启用的函数
在Php配置文件php.ini中设置开启如下函数或扩展库，如果不开启，则某些功能会有影响
allow_url_fopen、phpinfo、dir
GD扩展库
MySQL扩展库
mcrypt扩展库
mbstring扩展库
fsochopen扩展库
iconv扩展库
mcrypt扩展库
Json扩展库


特别说明：从本版本开始，必须开启mcrypt扩展库，否则会员相关功能不可使用

检测方法：解压下载包，单独上传文件phpinfo.php或install/svinfo.php文件到您的安装空间中，然后在浏览器中通过网址访问本文件，即会显示当前环境信息，符合要求时即可安装


3、主要特性：
连通淘宝，管理商品、订单与会员
强大的商品展示功能，可展示商品的所有特性
海量模板免费使用，模板可视化编辑，操作简单
16种信任登录，共享全网会员丰富的批量操作，设置更便捷
商品雷达插件版，适时关注全网商品情报
内置专为电商开启的生意经
订单、快递单批量打印
内置短信平台、EDM平台，可以群发短信与邮件
应用中心在线更新

4、更新内容：
火狐浏览器关于 sessionStorage 的报错问题
后台订单编辑时由js bug导致的操作问题
特定环境下，由SQL漏洞引起的安全问题
由cookie注入引起的登录安全问题

5、帮助平台：
ShopEx官方网站：www.shopex.cn
ShopEx官方论坛：bbs.shopex.cn
ShopEx在线帮助：help.shopex.cn