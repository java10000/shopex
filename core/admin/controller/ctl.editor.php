<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_editor extends adminPage{

    function link(){
        $sitemap = &$this->system->loadModel('content/sitemap');
        $this->pagedata['linked'] = $sitemap->getLinkNode();
        foreach($this->pagedata['linked']['page'] as $k=>$p){
            $pos = strpos($p['action'],':');
            $ident = substr($p['action'],$pos+1);
            $this->pagedata['linked']['page'][$k]['url'] = $this->system->realUrl('page',$ident,null,'html',$this->system->base_url());
        }

        if($_POST['goods']){
            $mod = &$this->system->loadModel('goods/products');
            $rows = $mod->getList('name',array('goods_id'=>$_POST['goods']));
            $this->pagedata['goodsInfo'] = $rows[0]['name'].'<input type="hidden" name="goods" value="'.$_POST['goods'].'" />';
        }
        if($_POST['article']){
            $mod = &$this->system->loadModel('content/article');
            $rows = $mod->getList('title',array('article_id'=>$_POST['article']));
            $this->pagedata['articleInfo'] = $rows[0]['title'].'<input type="hidden" name="article" value="'.$_POST['article'].'" />';
        }

        $this->display('editor/dlg_lnk.html');
    }

    function find($type,$keywords){
        if(!$keywords){
            echo __('请输入关键字。');
            return;
        }
        if($type=='goods'){
            $mod = &$this->system->loadModel('goods/products');
            foreach($mod->getList('goods_id,name',array('name'=>$keywords)) as $k=>$r){
                $list[] = array(
                    'url'=>$this->system->realUrl('product','index',array($r['goods_id']),'html',$this->system->base_url())
                    ,'label'=>$r['name']);
            }
            $this->pagedata['list'] = $list;
        }elseif($type=='article'){
            $mod = &$this->system->loadModel('content/article');
            foreach($mod->getList('article_id,title',array('keywords'=>$keywords)) as $k=>$r){
                $list[] = array(
                    'url'=>$this->system->realUrl('article','index',array($r['article_id']),'html',$this->system->base_url()),
                    'label'=>$r['title']);
            }
            $this->pagedata['list'] = $list;
        }
        if(count($list)>0){
            $this->pagedata['type'] = $type;
            $this->display('editor/dlg_result.html');
        }else{
            echo __('没有符合条件<b>"').$keywords.__('"</b>的记录。');
        }
    }

    function table(){
        $this->display('editor/dlg_table.html');
    }

    function image($showpicset=1){
        $tag = &$this->system->loadModel('system/tag');
        $this->pagedata['show_picset']=$showpicset;
        $this->pagedata['imgtags'] = $tag->tagList('image');
        header("Cache-Control:no-store, no-cache, must-revalidate"); //强制刷新IE缓存
        $this->display('editor/dlg_image.html');
    }
    function flash(){
        $tag = &$this->system->loadModel('system/tag');
        $this->pagedata['imgtags'] = $tag->tagList('image');
        $this->display('editor/dlg_flash.html');
    }
    function uploader(){
        $storager = &$this->system->loadModel('system/storager');
        set_error_handler(array(&$this,'_eH')); //如果上传图片时遇到trigger_error或者意外错误，执行_eH
        header('Content-Type: text/html; charset=utf-8');
        if($s = $storager->save_upload($_FILES['file'],'','',$msg)){
            restore_error_handler();
            $pubFile = &$this->system->loadModel('system/pubfile');
            $pubFile->insert(array(
                'file_name'=>$_FILES['file']['name'],
                'file_ident'=>$s,
                'cdate'=>time(),
                'memo'=>$_POST['memo'],
                'tags'=>space_split($_POST['tags']),
                'file_type'=>$_POST['file_type']
            ));
            $info = array('url'=>$storager->getUrl($s),'ident'=>$s);
            echo '<script>window.top.uploadCallback('.json_encode($info).')</script>';
        }else{
            restore_error_handler();
            echo '<script>window.top.uploadCallback("'.($msg?$msg:__('上传失败')).'")</script>';
        }
    }

    function _eH($errno, $errstr, $errfile, $errline){
        restore_error_handler();
        echo '<script>window.top.uploadCallback(false)</script>';
    }

    function gallery($tag=0,$page=1,$file_type=0){
        $pubFile = &$this->system->loadModel('system/pubfile');
        $p = 18;

        $result=$pubFile->getList(null,$filter=array('tag'=>$tag,'file_type'=>$file_type),$p*($page-1),$p);
        $c = $pubFile->count($filter);
        foreach($result as $k=>$v){
            if(preg_match('/\.swf/',$v['file_name'])){
                unset($result[$k]);
            }
        }
        $this->pagedata['images'] = $result;

        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>floor($c/$p)+1,
            'link'=>'javascript:showResLib(\''.$tag.'\',orz)',
            'token'=>'orz'
        );

        $this->display('editor/gallery_img.html');
    }

    function gallery_SWF($tag=0,$page=1,$file_type=0){
        //todo count
        $pubFile = &$this->system->loadModel('system/pubfile');
        $p = 18;
        $result = $pubFile->getList(null,array('tag'=>$tag,'file_type'=>$file_type),$p*($page-1),$p);
        foreach($result as $k=>$v){
            if(!preg_match('/\.swf/',$v['file_name'])){
                unset($result[$k]);
            }

        }
        $c=count($result);
        $this->pagedata['swfs'] = &$result;
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>floor($c/$p)+1,
            'link'=>'javascript:showResLib(\''.$tag.'\',orz)',
            'token'=>'orz'
        );

        $this->display('editor/gallery_swf.html');
    }


    function editHTML(){
        $this->display('dlg_mce.html');
    }

    function findobj(){
        $o = &$this->system->loadModel($_GET['mdl']);
        $key = $_GET['key']?$_GET['key']:$o->idColumn;

        $output = '';
        $filter = array();

        foreach($o->getList($key.' as id,'.$o->textColumn,$filter,0,15) as $row){
            $output.='<div val="'.htmlspecialchars($row['id']).'">'.$row[$o->textColumn].'</div>';
        }
        echo $output;
    }

    function object_rows(){
        if($_POST['data']){
            $obj = &$this->system->loadModel($_POST['object']);
            $this->pagedata['_input'] = array('items'=>$obj->getList($obj->idColumn.','.$_POST['cols'] , array($obj->idColumn=>$_POST['data'])),
                                                'idcol' => $obj->idColumn,
                                                'keycol' => ($_POST['key']?$_POST['key']:$obj->idColumn),
                                                'textcol' => $obj->textColumn,
                                                'name'=>$_POST['iptname'],
                                                'view'=> $_POST['view']);
            $this->display('finder/input-row.html');
        }
    }

    function _filter(){
        $obj = $this->system->loadModel($_GET['object']);// trading/order 
        $from = 'from';
        $data = &$obj->getColumns(null,$from);
        $filter_items = array();
        include("datatypes.php");
        foreach($data as $k=>$v){
            if($v['filtertype']){
                $data[$k]['searchparams'] = $datatypes[$v['filtertype']]['searchparams'];           
                if($v['filtertype']=='normal'){
                    $data[$k]['searchparams'] = $datatypes['email']['searchparams'];
                }
                if(is_array($v['type'])){
                    $data[$k]['options'] = $v['type'];
                    $data[$k]['type'] = 'select';
                }
                if($v['filtertype']=='custom'){
                    $data[$k]['searchparams'] =    $v['filtercustom'];
                }
                $filter_items[$k] = $data[$k];
            }

        }
        $has_tag = array('goods/products'=>'goods','trading/order'=>'order','member/member'=>'member');
        if(isset($has_tag[$_GET['object']])){
            $tag = $this->system->loadModel('system/tag');
            $tag_data = $tag->tagList($has_tag[$_GET['object']]);
            foreach($tag_data as $t_key =>$t_value){
                 $option[$t_value['tag_name']] = $t_value['tag_name'];
            }
            $filter_items['tag']['label'] = '标签';
            $filter_items['tag']['type'] = 'select';
            $filter_items['tag']['options'] = $option;  
            $filter_items['tag']['filtertype'] = 'yes';  
        }

        $this->pagedata['data']  = $filter_items;
    }

    function lista(){

        $_filter = unserialize($_GET['filter']);

        foreach($_POST as $k=>$v){
           
            if( ( $k{0}!='_' && $v ) || $v === false ){
               
                if($_POST['_'.$k.'_search']){
                    $filter['_'.$k.'_search']=$_POST['_'.$k.'_search'];
                }
         
                $filter[$k]=$v;
            }
            if(isset($_POST['sex'])&&($_POST['sex']=='0')){
              $filter['sex']=0;
            }
        }
        //exit;
        if(isset($_POST['marketable'])){
            $filter['marketable'] = ($_POST['marketable']=="")?"false":"true";
            $filter['_marketable_search'] = 'tequal';
        }

        $filter = array_merge((array)$filter,(array)$_filter);
        $this->_select_obj($filter);
        $this->display('editor/object_items.html');
    }

    function selectobj(){
        $filter = $_GET['filter'];
        $_GET['obj_id'] = substr(md5($_GET['object']),0,6);
        $this->_select_obj($filter);
        if($this->pagedata['data']){
            $this->pagedata['filter'] = true;
        }
        $this->display('editor/object_selector.html');
    }

    function _select_obj($filter){
       if(!isset($filter['marketable'])){
          $filter['marketable']="true";
       }
        $o = &$this->system->loadModel($_GET['object']);//  trading/package
        $limit = 10;
        if(!$_GET['page']){
            $_GET['page'] = 1;
        }
        $start = ($_GET['page']-1) * $limit;
        $this->pagedata['data'] = &$o->getColumns();
        if($_COOKIE['LOCALGOODS']){
            $this->pagedata['items'] = $o->getBindList($start,$limit,$count,$filter);
        }else{
            $this->pagedata['items'] = $o->getList($o->idColumn.','.$o->textColumn,$filter,$start,$limit);
            $count = $o->count($filter);
        }

        $this->pagedata['textColumn'] = $o->textColumn;
        $this->pagedata['idColumn'] = $o->idColumn;
        $this->pagedata['ipt_type'] = $_GET['select']=='checkbox'?'checkbox':'radio';

        $this->pagedata['pager'] = array(
            'current'=> $_GET['page'],
            'total'=> ceil($count/$limit),



            'link'=> 'javascript:update_'.$_GET['obj_id'].'(_PPP_)',
            'token'=> '_PPP_'
        );

        $this->_filter();
    }

    function filter(){
        $this->_filter();
        $this->display('finder/filter_show.html');
    }
}
