<?php
/**
* MySQLdumper is a small PHP class that lets you generate a dump of a MySQL database
* with just 2 lines of code. The dump can be used as a database backup. The dump
* is a valid MySQL-query in plain text. It doesn't depend on the 'mysqldump' command
* line utility, so you won't encounter a problem if this isn't available on the server.
*
* Example 1: Create a database drump.
* <code>
* $dumper = new MysqlDumper("localhost", "user", "password", "databasename");
* $dumpstring = $dumper->createDump();
* </code>
*
* Example 2: Create a database drump with a 'DROP TABLE IF EXISTS'-statement for each table.
* <code>
* $dumper = new MysqlDumper("localhost", "user", "password", "databasename");
* $dumper->setDroptables(true);
* $dumpstring = $dumper->createDump();
* </code>
*
* Example 3: Create dumps of different databases.
* <code>
* $dumper = new MysqlDumper("localhost", "user", "password", "database1");
* $dumpstring1 = $dumper->createDump();
* $dumper->setDBname"("database2");
* $dumpstring2 = $dumper->createDump();
* </code>
*
* @package  MySQLdumper
* @version  1.0
* @author   Dennis Mozes <opensource@mosix.nl>
* @url        http://www.mosix.nl/mysqldumper
* @since    PHP 4.0
* @copyright Dennis Mozes
* @license GNU/LGPL License: http://www.gnu.org/copyleft/lgpl.html
**/
define('MYSQL_CHARSET_NAME','utf8');
class Mysqldumper {
    var $_host;
    var $_dbuser;
    var $_dbpassword;
    var $_dbname;
    var $_isDroptables;
    var $tableid;    //数据表ID
    var $startid;


    function Mysqldumper($host = "localhost", $dbuser = "", $dbpassword = "", $dbname = "") {
        $this->setHost($host);
        $this->setDBuser($dbuser);
        $this->setDBpassword($dbpassword);
        $this->setDBname($dbname);
        // Don't drop tables by default.
        $this->setDroptables(false);
    }

    function setHost($host) {
        $this->_host = $host;
    }

    function getHost() {
        return $this->_host;
    }

    function setDBname($dbname) {
        $this->_dbname = $dbname;
    }

    function getDBname() {
        return $this->_dbname;
    }

    function getDBuser() {
        return $this->_dbuser;
    }

    function setDBpassword($dbpassword) {
        $this->_dbpassword = $dbpassword;
    }

    function getDBpassword() {
        return $this->_dbpassword;
    }

    function setDBuser($dbuser) {
        $this->_dbuser = $dbuser;
    }

    // If set to true, it will generate 'DROP TABLE IF EXISTS'-statements for each table.
    function setDroptables($state) {
        $this->_isDroptables = $state;
    }

    function isDroptables() {
        return $this->_isDroptables;
    }



    function multiDump($filename,$fileid,$sizelimit,$backdir,$ignoreList=null) {
        // Set line feed
        $ret = true;
        $lf = "\r\n";
        $lencount = 0;
        $bakfile = $backdir."/multibak_".$filename."_".($fileid+1).".sql";
        if($ignoreList){
            $ignoreList = array_flip($ignoreList);
        }

        $fw = @fopen($bakfile, "wb");
        if(!$fw) exit("备份目录{$backdir}不可写");
        $resource = mysql_connect($this->getHost(), $this->getDBuser(), $this->getDBpassword(),true);
        mysql_select_db($this->getDbname(), $resource);
        if(!constant("DB_OLDVERSION"))
                mysql_query("SET NAMES '".MYSQL_CHARSET_NAME."'",$resource);

        $result = mysql_query("SHOW TABLES");
        $tables = $this->result2Array(0, $result);
        $filter_tables = array(DB_PREFIX."op_sessions", DB_PREFIX."operators", DB_PREFIX."admin_roles");
        foreach ($tables as $tblval) {
            if(substr($tblval,0,strlen(DB_PREFIX))==DB_PREFIX && !in_array($tblval, $filter_tables)){
                $tablearr[] = $tblval;
            }
        }
        // Set header
        fwrite($fw, "#". $lf);
        fwrite($fw,  "# SHOPEX SQL MultiVolumn Dump ID:".($fileid+1) . $lf);
        fwrite($fw,  "# Version ". $GLOBALS['SHOPEX_THIS_VERSION']. $lf);
        fwrite($fw,  "# ". $lf);
        fwrite($fw,  "# Host: " . $this->getHost() . $lf);
        fwrite($fw,  "# Server version: ". mysql_get_server_info() . $lf);
        fwrite($fw,  "# PHP Version: " . phpversion() . $lf);
        fwrite($fw,  "# Database : `" . $this->getDBname() . "`" . $lf);
        fwrite($fw,  "#");

        // Generate dumptext for the tables.
        $i=0;
        for($j=$this->tableid;$j<count($tablearr);$j++){
            $tblval = $tablearr[$j];
            $table_prefix = constant('DB_PREFIX');
            $subname = substr($tblval,strlen($table_prefix));
            $written_tbname = '{shopexdump_table_prefix}'.$subname;
            if($this->startid ==-1)
            {
                fwrite($fw,  $lf . $lf . "# --------------------------------------------------------" . $lf . $lf);
                $lencount += strlen($lf . $lf . "# --------------------------------------------------------" . $lf . $lf);
                fwrite($fw,  "#". $lf . "# Table structure for table `$tblval`" . $lf);
                $lencount += strlen("#". $lf . "# Table structure for table `$tblval`" . $lf);
                fwrite($fw,  "#" . $lf . $lf);
                $lencount += strlen("#". $lf . "# Table structure for table `$tblval`" . $lf);
                // Generate DROP TABLE statement when client wants it to.
                mysql_query("ALTER TABLE `$tblval` comment ''");
                if($this->isDroptables()) {
                    fwrite($fw,  "DROP TABLE IF EXISTS `$written_tbname`;" . $lf);
                    $lencount += strlen("DROP TABLE IF EXISTS `$written_tbname`;" . $lf);
                }
                $result = mysql_query("SHOW CREATE TABLE `$tblval`");
                $createtable = $this->result2Array(1, $result);
                $tmp_value = str_replace("\n", '', $this->formatcreate($createtable[0]));
                $pos = strpos($tmp_value,$tblval);
                $tmp_value = substr($tmp_value,0,$pos).$written_tbname.substr($tmp_value,$pos+strlen($tblval));
                fwrite($fw,  $tmp_value. $lf.$lf);
                $lencount += strlen($tmp_value. $lf.$lf);
                $this->startid = 0;
            }
            if($lencount>$sizelimit*1000)
            {
                $this->tableid = $j;
                $this->startid = 0;
                $ret = false;
                break;
            }
            if(isset($ignoreList['sdb_'.$subname])){
                $this->startid = -1;
                continue;
            }
            fwrite($fw,  "#". $lf . "# Dumping data for table `$tblval`". $lf . "#" . $lf);
            $lencount += strlen("#". $lf . "# Dumping data for table `$tblval`". $lf . "#" . $lf);
            $result = mysql_query("SELECT * FROM `$tblval`");
            if(!@mysql_data_seek($result,$this->startid))
            {
                $this->startid = -1;
                continue;
            }

            while ($row = mysql_fetch_object($result)) {
                    $insertdump = $lf;
                    $insertdump .= "INSERT INTO `$written_tbname` VALUES (";
                    $arr = $this->object2Array($row);

                    foreach($arr as $key => $value) {
                        if(!is_null($value))
                        {
                            $value = $this->utftrim(mysql_escape_string($value));
                            $insertdump .= "'$value',";
                        }
                        else
                            $insertdump .= "NULL,";
                    }
                    $insertline = rtrim($insertdump,',') . ");";
                    fwrite($fw,  $insertline);
                    $lencount += strlen($insertline);
                    $this->startid++;
                    if($lencount>$sizelimit*1000)
                    {
                        $ret = false;
                        $this->tableid = $j;
                        break 2;
                    }
            }
            $this->startid = -1;
            $i++;
//            if ($i== 5) break;
        }
        mysql_close($resource);
        fclose($fw);
        chmod($bakfile, 0666);
        return $ret;
    }


    // Private function object2Array.
    function object2Array($obj) {
        $array = null;
        if(is_object($obj)) {
            $array = array();
            foreach (get_object_vars($obj) as $key => $value) {
                if(is_object($value))
                    $array[$key] = $this->object2Array($value);
                else
                    $array[$key] = $value;
            }
        }
        return $array;
    }

    // Private function loadObjectList.
    function loadObjectList($key='', $resource) {
        $array = array();
        while ($row = mysql_fetch_object($resource)) {
            if ($key)
                $array[$row->$key] = $row;
            else
                $array[] = $row;
        }
        mysql_free_result($resource);
        return $array;
    }

    // Private function result2Array.
    function result2Array($numinarray = 0, $resource) {
        $array = array();
        while ($row = mysql_fetch_row($resource)) {
            $array[] = $row[$numinarray];
        }
        mysql_free_result($resource);
        return $array;
    }

    function formatcreate($str)
    {
            $body = substr($str,0,strrpos($str,")")+1);
            $tail = strtolower(substr($str,strrpos($str,")")-strlen($str)));
            if(strstr($tail,'memory')||strstr($tail,'heap')){
                return $body." TYPE=HEAP{shopexdump_create_specification};";
            }else{
                return $body." TYPE=MyISAM{shopexdump_create_specification};";
            }
    }

    //截最后一个是否是半个UTF-8中文
    function utftrim($str)
    {
        $found = false;
        for($i=0;$i<4&&$i<strlen($str);$i++)
        {
            $ord = ord(substr($str,strlen($str)-$i-1,1));
            //UTF-8中文分{四/三/二字节码},第一位分别为11110xxx(>192),1110xxxx(>192),110xxxxx(>192);接下去的位数都是10xxxxxx(<192)
            //其他ASCII码都是0xxxxxxx
            if($ord> 192)
            {
                $found = true;
                break;
            }
            if ($i==0 && $ord < 128){
                break;
            }
        }

        if($found)
        {
            if($ord>240)
            {
                if($i==3) return $str;
                else return substr($str,0,strlen($str)-$i-1);
            }
            elseif($ord>224)
            {
                if($i>=2) return $str;
                else return substr($str,0,strlen($str)-$i-1);
            }
            else
            {
                if($i>=1) return $str;
                else return substr($str,0,strlen($str)-$i-1);
            }
        }
        else return $str;
    }
}
?>
