<?php
/**
 * 存放着url地址的转换关系，可以使shopex处理其他系统的url地址格式。
 * 要使用本功能，请启用.htaccess。
 * 在本文件中设置map变量即可。
 */
$shopex47 = array(
    '/^catalog_([0-9]+)\.html/'    =>'gallery|index|$1',
    '/^catalog\.html/'                        =>'gallery|index',
    '/^list_([0-9]+)\.html/'            =>'gallery|index|$1',
    '/^list\.html/'                            =>'gallery|index',
    '/^member\.html/'                        =>'member|index',
    '/^feedback\.html/'                    =>'message|index',
    '/^feedback_([0-9]+)\.html/'    =>'message|index|$1',
    '/^product_([0-9]+)\.html/'    =>'product|index|$1',
    '/^bulletin_([0-9]+)\.html/'    =>'article|index|$1',
    '/^message_([0-9]+)\.html/'    =>'article|index|$1',
    '/^product\/([0-9]+)\.html/'    =>'product|index|$1',
    '/^catalog_([0-9]+)_([0-9]+)\.html/'=>'gallery|index|$1||0||$2',
    '/^([0-9]+)\.html/'                    =>'product|index|$1',
    '/^([0-9]+)_([^.]*)\.html/'            =>'product|index|$1',
    '/^bulletin\.html/'                    =>'artlist|index|1', //商店公告
);
$map = &$shopex47;
?>
