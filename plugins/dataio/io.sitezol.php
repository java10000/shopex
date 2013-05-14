<?php
class io_sitezol{

    var $name = 'ZOL数据导入(输入产品参数页链接)';
    var $importforObjects = 'goods';
    var $developing = true;

    function import_row(&$handle){
        if(!strstr($handle['url'],'http://detail.zol.com.cn')){
            trigger_error('输入的ZOL网址有误',E_USER_ERROR);
        }
        if($handle['count']==0){
            $system = $GLOBALS['system'];
            $net = &$system->loadModel('utility/http_client');
            if($result = $net->get($handle['url'])){
                $p = $this->charset->local2utf($results,'zh');
                $reg = "/var var_subcat_name  = '(.*)';/U";
                preg_match_all($reg,$p,$out);
                $type_name = trim($out[1][0]);
                $data['*:'.$type_name] = $type_name;
                $data['bn:商品货号'] = strtoupper(uniqid('G'));
                $data['i_bn:规格货号'] = '';
                $reg = "/var var_productname  = '(.*)';/U";
                preg_match_all($reg,$p,$out);
                $data['col:商品名称'] = $out[1][0];
                $reg = "/var var_manu_name    = '(.*)';/U";
                preg_match_all($reg,$p,$out);
                $data['col:品牌'] = trim($out[1][0]);


                //参数表
                $reg = '/<td bgcolor="#FFFFFF" width="100" align="left" valign="middle" class="param_td1">(.*)<\/td>\s*<td bgcolor="#FFFFFF" width="450" class="param_td2">(.*)<\/td>/U';
                preg_match_all($reg,$p,$out);

                foreach($out[1] as $k=>$paramname){
                    $data['params:'.strip_tags($paramname)] = strip_tags($out[2][$k]);
                    $data['props:'.strip_tags($paramname)] = strip_tags($out[2][$k]);
                }
                $handle['data'] = $data;
                foreach($data as $k=>$v){
                    $return[] = $k;
                }
                $handle['count']++;
                return $return;
            }else{
                trigger_error('网络错误或无法获取数据',E_USER_ERROR);
            }
        }
        elseif($handle['count']==1){
            foreach($handle['data'] as $k=>$v){
                $return[] = $v;
            }
            $handle['count']++;
            return $return;
        }else return false;
    }

    function import_rows($handle){
        return false;
    }
}
?>
