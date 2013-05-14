<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_template extends adminPage{

    var $workground ='site';

    function ctl_template(){
        parent::adminPage();
        regex_file($_GET);
    }
    function index(){

        set_time_limit(360);
        $this->path[] = array('text'=>__("模板管理"));
        $o = $this->system->loadModel('system/template');

        $usedTpl = $o->getDefault();
        if($selected_css=$this->system->getConf('site.theme_'.$this->system->getConf('system.ui.current_theme').'_color')){
            $this->pagedata['selected_css'] = $selected_css;
        }

        $this->pagedata['themes'] = $o->getList();

        foreach($this->pagedata['themes'] as $k=>$theme){

            $this->pagedata['themes'][$k]['config']=unserialize($this->pagedata['themes'][$k]['config']);
            if($theme['theme']==$usedTpl){
                $this->pagedata['currentTheme'] = $this->pagedata['themes'][$k];
                unset($this->pagedata['themes'][$k]);
            }
        }

        $gimage = &$this->system->loadModel('system/storager');
        $max_upload= $gimage->get_pic_upload_max();
        $this->pagedata['install_url'] = constant('TPL_INSTALL_URL');
        $this->pagedata['max_upload'] = $max_upload;
        $this->pagedata['sysTheme'] = CORE_DIR.'/shop/view';
        $this->pagedata['base_url'] = $this->system->base_url();
        $this->pagedata['allowUpload'] = $o->allowUpload($msg);
        $this->pagedata['cantUploadMsg'] = $msg;
        $this->page('system/template/list.html');
    }
    function setColor($theme,$color){
        if($theme && $color){
            $this->system->setConf('site.theme_'.$theme.'_color',$color);
            $this->index();
        }
    }
    function doBak($theme){

        $this->begin('index.php?ctl=system/template&act=edit&p[0]='.$theme);
        $o = $this->system->loadModel('system/template');
        if($_POST['validtemplate']){
            $xml=$_POST['validtemplate'];
        }
        $o = $this->system->loadModel('system/template');

        if($o->reset($theme,$xml)){
            $this->end(true,'加载成功');
        }else{
            $this->end(false,'加载失败');
        }
    }
    function backTemplate(){
        $o = $this->system->loadModel('system/template');
        $name='theme-bak';
        $this->begin('index.php?ctl=system/template&act=edit&p[0]='.$_GET['template']);
        if($_GET['template'] &&  $o->makeXml($_GET['template'],$name)){
            $this->end(true,'备份成功');
        }else{
            $this->end(false,'备份失败');
        }
    }
    function tempalte_rename($old_name,$new_name,$theme){
        $o = $this->system->loadModel('system/template');
        if($o->tempalte_rename($old_name,$new_name,$theme)){
            echo 'success';
        }else{
            echo 'fail';
        }
        exit();
    }
    function copy_tpl($theme,$tpl,$type){
        $file=THEME_DIR.'/'.$theme.'/'.$tpl;

        if(file_exists($file)){
            $o = $this->system->loadModel('system/template');
            $content=file_get_contents($file);
            $new_tpl=substr($tpl,0,strpos($tpl,'.html'));
            $start=true;
            while($start){
                preg_match('/\(([0-9])\)/',$new_tpl,$searched);
                if($searched[1]){
                    $new_tpl=str_replace('('.$searched[1].')','('.($searched[1]+1).')',$new_tpl);
                    if(!file_exists(THEME_DIR.'/'.$theme.'/'.$new_tpl)){
                        $start=false;
                    }
                }else{
                    $new_tpl=$new_tpl.'(1).html';
                }
            }
            $o->setContent($theme,$new_tpl,$content);
            $copy_from=array(
                'theme'=>$theme,
                'tpl'=>$tpl,
                'type'=>'user'
            );
            $copy_to=array(
                'theme'=>$theme,
                'tpl'=>$new_tpl,
                'type'=>'user'
            );
            $o->copy_tpl($copy_from,$copy_to,$new_tpl,$type);
            $this->splash('success','index.php?ctl=system/template&act=edit&p[0]='.$theme);
        }else{
            $this->splash('failed','index.php?ctl=system/template&act=edit&p[0]='.$theme);
        }
    }

    function install(){
        $task = HOME_DIR.'/tmp/'.$_GET['download'];
        $temp_mess = file_get_contents($task.'/task.php');
        $down_data = unserialize($temp_mess);
        if($url = $down_data['download_list'][0]){
            $filename = substr($url,strrpos($url,"/")+1);
            $file_path = $task.'/'.$filename;
            if(file_exists($file_path)){
                $file['tmp_name'] = $file_path;
                $file['name'] = time();
                $file['error'] = '0';
                $file['size'] = filesize($file_path);
                $template = &$this->system->loadModel("system/template");
                $template->upload($file,$msg);
            }else{
                $msg = "找不到安装文件。安装失败";
            }
        }
        if(!$msg){
            echo $this->_fetch_compile_include('service/download_complete_handle.html',array('info'=>'模板安装成功，您可以在模板列表中启用它。'));
            //echo '安装成功';
        }else{
            echo $msg;
        }
    }

    function install_online(){
        //echo time();
        //exit();
        if(isset($_POST['url'])&&isset($_POST['tpl_name'])&&isset($_POST['fullsize'])){

            include(CORE_DIR.'/admin/controller/service/ctl.download.php');
            $download = new ctl_download();

            $_POST = array(
                'download_list'=>array($_POST['url']),
                'succ_url'=>'http://'.HTTP_HOST.dirname(PHP_SELF)
                .'/index.php?ctl=system/template&act=install'
            );

            $download->start();
        }
        exit;
    }

    function upload(){
        header('Content-Type: text/html; charset=utf-8');
        $o = $this->system->loadModel('system/template');
        if($theme = $o->upload($_FILES['Filedata'],$msg)){
            $this->pagedata['theme'] = $theme;
            $this->display('system/template/themeupload.html');
        }else{
            echo $msg;
        }
    }

    function setDefault($theme){
        $o = $this->system->loadModel('system/template');
        if($o->setDefault($theme)){
            $wights_border = $o->getBorderFromThemes($theme);
            foreach($wights_border as $key=>$value){
                $border_ary[] = $key;
            }
            $wights_info = $o->getWdigetsPage();
            foreach($wights_info as $key=>$value){
                if(@!in_array($value['border'], $border_ary)){
                    $value['border'] = $border_ary[0];
                    $o->updateWidgets($value);
                }
            }

            $this->splash('success','index.php?ctl=system/template&act=index');
        }else{
            $this->splash('failed','index.php?ctl=system/template&act=index',__('设置默认失败'));
        }
    }

    function remove($theme){
        $this->begin('index.php?ctl=system/template&act=index');
        $o = $this->system->loadModel('system/template');
        $this->end($o->remove($theme),__('模板').$theme.__('已删除'));
    }

    function preview($theme){
        require(CORE_INCLUDE_DIR.'/shopPreview.php');
        $this->system->__session_close();

        $s = new shopPreview();
        $s->view(array(
            'cache'=>null,
            'query'=>$_GET['url']?$_GET['url']:'index.html',
            'base_url'=>"index.php?ctl=system/template&act=preview&p[0]={$theme}&url=/",
            'url_prefix'=>"index.php?ctl=system/template&act=preview&p[0]={$theme}&url=/",
            'domain'=>'',
            'member'=>null,
            'cur'=>null,
            'lang'=>null
        ));
    }
    function reset($theme,$xml=''){

        $o = $this->system->loadModel('system/template');
        $o->reset($theme,$xml);
        $this->message=$theme.__('已经还原回安装状态。');
        $this->index();
    }

    function dlPkg($theme){
        $o = $this->system->loadModel('system/template');
        $o->outputPkg($theme);
    }

    function previewImg($theme,$pic='preview.jpg'){
        ob_clean();
        header('Content-Type:image/jpeg');

        $o = $this->system->loadModel('system/template');
        $o->previewImg($theme,$pic);
    }

    function templateList(){
        $o=$this->system->loadModel('system/template');
        $this->system->output(json_encode($o->templateList()));
    }

    function htmEditPage(){
        $o=$this->system->loadModel('content/page');
        $data=$o->htmEdit($this->in['src']);
        $this->pagedata['title']=$this->in['src'];
        $this->pagedata['name']=empty($data['page_name'])?$this->in['name']:$data['page_name'];
        $this->pagedata['html']=$data['page_content'];
        $this->page('system/template/htmEdit.html');
    }

    function editHtml(){
        $o=$this->system->loadModel('system/template');
        $o->editHtml($this->in['title'],array('page_name'=>$this->in['name'],'page_content'=>$this->in['html'],'page_time'=>time()));
    }

    function edit($theme){

        $this->path[] = array('text'=>__('模板编辑'));
        $o = $this->system->loadModel('system/template');
        $xmlTheme=$o->getThemes($theme);
        $o->geteditlist($theme);
        $info = $o->getThemeInfo($theme);
        //        $this->pagedata['viewSets'] = $info['views'];
        $this->pagedata['theme'] = $theme;
        $css_info=urlencode(serialize(array(
            'tmpid'=>array(
                'v'=>$theme,
                't'=>__('样式管理')
            ),
            'type'=>array(
                'v'=>'css',
                't'=>__('css')
            ))));
        $img_info=urlencode(serialize(array(
            'tmpid'=>array(
                'v'=>$theme,
                't'=>__('样式管理')
            ),
        )));
        $this->pagedata['css_info'] = $css_info;
        $this->pagedata['img_info'] = $img_info;
        $this->pagedata['themeslist'] = $xmlTheme;
        $this->pagedata['templetename'] = $info['name'];
        $this->pagedata['config']=$info['config']['config'];
        foreach($this->pagedata['config'] as $k=>$v){
            if($v['option']){
                $options = array();
                $images = array();
                foreach($v['option'] as $item){
                    $options[$item['value']] = $item['attr']['label']?$item['attr']['label']:$item['value'];
                    if($item['attr']['image']){
                        $images[$item['value']] = $item['attr']['image'];
                    }
                }
                $this->pagedata['config'][$k]['options'] = $options;
                if($images){
                    $this->pagedata['config'][$k]['images'] = $images;
                }
                if($v['type']=='select'){
                    $this->pagedata['config'][$k]['required'] = true;
                }
            }
        }
        $this->pagedata['template']=$o->templateList($theme);

        $tpl_info=$o->getname();
        foreach((array)$tpl_info as $key=>$value){
            $default_theme[$key]=$this->system->getConf('system.custom_template_'.$key);
        }
        $this->pagedata['page_list']=$tpl_info;
        $this->pagedata['default_theme']=$default_theme;
        $this->pagedata['show_list']=$o->get_template_list($theme);
        $this->pagedata['sysTheme'] = CORE_DIR.'/shop/view';
        unset($info);
        $this->page('system/template/edit.html');
    }
    function setTemplateDefault($theme,$type,$tpl){
        $this->system->setConf('system.custom_template_'.$type,$tpl);
        $this->splash('success','index.php?ctl=system/template&act=edit&p[0]='.$theme);
    }
    function saveConfig($theme){

        $o=$this->system->loadModel('system/template');

        $info=$o->getThemeInfo($theme);
        foreach($info['config']['config'] as $k=>$v){
            $key=$v['key'];
            $info['config']['config'][$k]['value']=$_POST['config'][$key];
        }

        $info['config']=array(
            'config'=>$info['config']['config'],
            'borders'=>$info['config']['borders'],
            'views'=>$info['config']['views']
        );

        unset($info['borders'],$info['views']);
        if($o->updateThemes($info)){
            $this->splash('success','index.php?ctl=system/template&act=edit&p[0]='.$theme);
        }else{
            $this->splash('failed','index.php?ctl=system/template&act=edit&p[0]='.$theme,__('设置失败'));
        }
    }
    function widgetsSet($theme,$view){
        header('Content-Type: text/html; charset=utf-8');
        $this->path[] = array('text'=>__('模板可视化编辑'));
        $widgets = $this->system->loadModel('content/widgets');
        $o = $this->system->loadModel('system/template');
        $this->pagedata['views'] = $o->getViews($theme);
        $this->pagedata['widgetsLib'] = $widgets->getLibs();
        $this->pagedata['theme'] = $theme;
        $this->pagedata['view'] =$view;
        $this->pagedata['viewname'] = $o->getListName($view);
        return $this->singlepage('system/template/templateEdit.html');
        $this->clear_all_cache();
        $this->display('system/template/templateEdit.html');
    }

    function widgetsSave(){
        $widgets = $this->system->loadModel('content/widgets');
        if(is_array($_POST['widgets'])){
            $this->clear_all_cache();
            
            foreach($_POST['widgets'] as $widgets_id=>$base){
                //$pos = strrpos($base,':');
                //$widgetsSet[$widgets_id] = array('base_file'=>substr($base,0,$pos),'base_slot'=>substr($base,$pos+1));
                $aTmp=explode(':',$base);
                $base_id=array_pop($aTmp);
                $base_slot=array_pop($aTmp);
                $base_file=implode(':',$aTmp);
                if($_POST['html'][$widgets_id]){
                    $widgetsSet[$widgets_id] = array('base_file'=>$base_file,'base_slot'=>$base_slot,'base_id'=>$base_id,'border'=>'__none__','params'=>array('html'=>stripslashes($_POST['html'][$widgets_id])));

                }else{
                    $widgetsSet[$widgets_id] = array('base_file'=>$base_file,'base_slot'=>$base_slot,'base_id'=>$base_id);
                     //$widgetsSet[$widgets_id] = array('base_file'=>$base_file,'base_slot'=>$base_slot,'base_id'=>$base_id,'border'=>'__none__','params'=>array('html'=>stripslashes($_POST['html'][$widgets_id])));
                }
            }
        }

        if(false !== ($map = $widgets->saveSlots($widgetsSet,$_POST['files']))){
            //$this->clear_all_cache();
            echo json_encode($map);
        }else{
            echo json_encode(false);
        }
    }

    function saveWg($widgets_type,$widgets_id,$theme,$domid){
        header('Content-Type: text/html;charset=utf-8');
        unSafeVar($_POST);
        
        $widgets = &$this->system->loadModel('content/widgets');
        if($widgets_type=='html')$widgets_type='usercustom';
        $set = array(
            'widgets_type'=>$widgets_type,
            'title'=>$_POST['__wg']['title'],
            'border'=>$_POST['__wg']['border'],
            'tpl'=>$_POST['__wg']['tpl'],
            'domid'=>$_POST['__wg']['domid'],
            'classname'=>$_POST['__wg']['classname'],
        );
        unset($_POST['__wg']);
        $set['params'] = $_POST;
        $set['_domid'] = $domid;

        if(is_numeric($widgets_id)){
            $this->clear_all_cache();
            $widgets->saveEntry($widgets_id,$set);
        }elseif(preg_match('/^tmp_([0-9]+)$/i',$widgets_id,$match)){
            $_SESSION['_tmp_wg'][$match[1]] = $set;
        }

        echo $widgets->adminWgBorder(array('title'=>$set['title'],'widgets_id'=>$widgets_id,'domid'=>$set['domid'],'border'=>$set['border'],'widgets_type'=>$set['widgets_type'],'html'=>$widgets->fetch($set,true,$widgets_id),'border'=>$set['border']),$theme,true);
    }

    function editWidgets($widgets_id,$theme){

        $widgets = $this->system->loadModel('content/widgets');
        if(is_numeric($widgets_id)){
            $widgetObj = $widgets->getWidget($widgets_id);
        }elseif(preg_match('/^tmp_([0-9]+)$/i',$widgets_id,$match)){
            $widgetObj = $_SESSION['_tmp_wg'][$match[1]];
        }
        //    $this->pagedata['widgetsType'] = $widgets_type;
        $this->pagedata['widgetEditor'] = $widgets->editor($widgetObj['widgets_type'],$theme,$widgetObj['params']);
        $this->pagedata['widgets_type'] = $widgetObj['widgets_type'];

        $this->pagedata['widgets_id'] = $widgets_id;

        //$this->pagedata['widgets_id'] =1209984198305;


        $this->pagedata['widgets_title'] = $widgetObj['title'];
        $this->pagedata['widgets_border']=$widgetObj['border'];
        $this->pagedata['widgets_classname']=$widgetObj['classname'];
        $this->pagedata['widgets_domid']=$widgetObj['domid'];

        //$this->pagedata['widgets_domid']=1209982722434;
        $this->pagedata['widgets_tpl']=$widgetObj['tpl'];

        //echo '####'.$widgetObj['classname'].'####';

        $this->pagedata['widgetsTpl'] = str_replace('\'','\\\'',$widgets->adminWgBorder(array('title'=>$widgetObj['title'],'html'=>'loading...'),$theme));

        $this->pagedata['theme']=$theme;
        header("Cache-Control:no-store, no-cache, must-revalidate"); //强制刷新IE缓存
        $this->display('system/template/saveWidgets.html');
    }

    function doAddWidgets($widgets_type,$theme){
        $widgets = $this->system->loadModel('content/widgets');
        $this->pagedata['widgetsType'] = $widgets_type;
        $this->pagedata['widgetEditor'] = $widgets->editor($widgets_type,$theme);

        $this->pagedata['theme'] = $theme;

        $this->pagedata['i']=is_array($_SESSION['_tmp_wg'])?count($_SESSION['_tmp_wg']):0;
        //$this->pagedata['widgetsTpl'] = str_replace('\'','\\\'',$widgets->adminWgBorder(array('title'=>'title','html'=>'loading...')));

        $this->display('system/template/doAddWidgets.html');
    }
    function addWidgetsPage($themes){

        $widgets = $this->system->loadModel('content/widgets');
        //$o = $this->system->loadModel('system/template');
        //$this->pagedata['views'] = $o->getViews($theme);
        $this->pagedata['themes'] = $themes;
        $this->pagedata['widgetsLib'] = $widgets->getLibs(null);
        $this->display('system/template/widgetsCenter.html');
    }

    function getWidgetsInfo($type=''){
        if($_GET['widgets']){
            $widgets = $this->system->loadModel('content/widgets');
            $this->pagedata['widgetsInfo'] = $widgets->getThisWidgetsInfo($_GET['widgets']);
            $this->pagedata['widgets'] =$_GET['widgets'];

        }

        $this->pagedata['themes'] = $this->system->getConf('system.ui.current_theme');
        if($type=='1'){
            $this->display('content/widgets/widgetsDetailRight.html');
        }else{
            $this->display('system/template/widgetsDetailRight.html');
        }
    }

    function addWidgetsPageExtend($themes,$type=''){

        $widgets = $this->system->loadModel('content/widgets');
        $this->pagedata['themes'] = $themes;
        $this->pagedata['widgetsLib'] = $widgets->getLibs($_POST['catalog']);
        if($type=='1'){
            $this->display('content/widgets/widgetsLeftDetail.html');
        }else{
            $this->display('system/template/widgetsLeftDetail.html');
        }

    }
    function insertWg($widgets_type,$domid,$theme){
        header('Content-Type: text/html;charset=utf-8');
        
        unSafeVar($_POST);
        $widgets = $this->system->loadModel('content/widgets');
        $set = array(
            'widgets_type'=>$widgets_type,
            'title'=>$_POST['__wg']['title'],
            'border'=>$_POST['__wg']['border'],
            'tpl'=>$_POST['__wg']['tpl'],
            'domid'=>$_POST['__wg']['domid'],
            'classname'=>$_POST['__wg']['classname'],
        );

        unset($_POST['__wg']);
        $set['params'] = $_POST;
        $set['_domid'] = $domid;
        $i=is_array($_SESSION['_tmp_wg'])?count($_SESSION['_tmp_wg']):0;
        $_SESSION['_tmp_wg'][$i] = $set;
        $data=$widgets->adminWgBorder(array('title'=>$set['title'],'domid'=>$set['domid'],'border'=>$set['border'],'widgets_type'=>$set['widgets_type'],'html'=>$widgets->fetch($set,true),'border'=>$set['border']),$theme,true);
        echo $data;
    }

    function copyWg($domid,$widgetid){
        $widgets = $this->system->loadModel('content/widgets');
        if(strstr($widgetid,'tmp_')){
            $widgetid=str_replace('tmp_','',$widgetid);
            $set=$_SESSION['_tmp_wg'][$widgetid];
        }else{
            $set=$widgets->getWidget($widgetid);
            unset($set['widgets_id']);
        }
        $set['_domid'] = $domid;
        $i=is_array($_SESSION['_tmp_wg'])?count($_SESSION['_tmp_wg']):0;
        $_SESSION['_tmp_wg'][$i] = $set;
        echo json_encode(array('widgetid'=>'tmp_'.$i));
    }

    function editor($theme,$file,$template=null,$file_name=null){
        $this->path[] = array('text'=>__('模板源码编辑'));
        $o = $this->system->loadModel('system/template');
        $usedTpl = $o->getDefault();
        $this->pagedata['type'] = substr($file,0,strpos($file,'.html'));;
        $this->pagedata['theme'] = $theme;
        if($template){
            $file=substr($file,0,strpos($file,'.')).'-'.$template.'.html';
        }

        $this->pagedata['file'] = $file;

        $this->pagedata['file_name'] = $file_name;
        $this->pagedata['template'] = $template;
        if(!($this->pagedata['content'] = $o->getContent($theme,$file))){
            $this->pagedata['content'] = $o->getContent($theme,'default.html');
        }

        $this->pagedata['bakfile'] = $o->get_bak_file($theme, $file);

        $this->page('system/template/editor.html');
    }
    function removePage($theme,$file){
        if(preg_match('/\.bak_\d\.html/',$file)){
            $this->begin('index.php?ctl=system/template&act=editor&p[0]='.$theme.'&p[1]='.$_POST['file']);
        }else{
            $this->begin('index.php?ctl=system/template&act=edit&p[0]='.$theme);
        }

        $o = $this->system->loadModel('system/template');
        $o->del_template_widgets($theme,$file);
        $o->remove_tpl($theme,$file);
        if($o->delFile($theme,$file)){
            $this->end(true,__('删除成功'));
        }
        $this->end(false,__('删除失败'));
    }
    function recoverSource($theme, $bakfile, $file){
        $this->begin('index.php?ctl=system/template&act=editor&p[0]='.$theme.'&p[1]='.$file);
        $o = &$this->system->loadModel('system/template');
        $this->end($o->recoverTpl($theme, $bakfile, $file), __('恢复成功'));
    }
    function saveContent(){
        if(strtolower(substr($_POST['file'],-5))!='.html') $_POST['file']='index.html';
        $this->begin('index.php?ctl=system/template&act=edit&p[0]='.$_POST['theme']);
        if(strstr($_POST['theme'],'/')||strstr($_POST['theme'],'\\')){
            $this->end(false,__('修改失败'));
            exit;
        }
        $o = $this->system->loadModel('system/template');
        $template=array(
            'tpl_name'=>$_POST['file_name'],
            'tpl_file'=>$_POST['file'],
            'tpl_theme'=>$_POST['theme'],
            'tpl_type'=>$_POST['type']
        );
        $exist = $o->getTemplateByType($template['tpl_type']);
        if(!$exist)
            $this->system->setConf('system.custom_template_'.$template['tpl_type'],$template['theme']);
        $o->insert_tpl($template);
        //if(!$this->system->getConf('system.custom_template_'.$_POST['type'])){
            //$this->system->setConf('system.custom_template_'.$_POST['type'],$_POST['file']);
        //}
        $ret = $o->setContent($_POST['theme'],$_POST['file'],$_POST['content'],$_POST['isbak']);
        if($ret){
            $this->end(true,__('修改成功'));
        }else{
            $this->end(false,__('修改失败'));
        }
    }

    function templetePreview($tpl,$file){
        header('Content-Type: text/html; charset=utf-8');
        $this->system->__session_close();
        $smarty = &$this->system->loadModel('system/frontend');
        $smarty->compile_dir = HOME_DIR.'/cache/admin_tmpl/';
        $smarty->pagedata['theme_dir']=$this->system->base_url().'themes/'.$this->system->getConf('system.ui.current_theme').'/';
        $smarty->theme = $tpl;
        $this->theme = $tpl;

        $smarty->register_prefilter(array(&$this,'_prefix_tpl'));
//        $smarty->_plugins['compiler']['require'] = array(&$this,'_require');
        $smarty->_plugins['compiler']['main'] = array(&$this,'_main');
        $smarty->_plugins['function']['link'] = array(&$this,'mkUrl');
        $smarty->_plugins['function']['footer'] = array(&$this,'_footer');
        $smarty->_plugins['function']['header'] = array(&$this,'_header');
        $smarty->_plugins['resource']['user']=array(array(&$this,"_get_template"),array(&$this,"_get_timestamp"));
        $this->_current_file='user:'.$tpl.'/'.urldecode($file);
        $smarty->_plugins['compiler']['widgets'] = array(&$this,'_widgets_bar');
        $smarty->display('user:'.$tpl.'/'.urldecode($file));
    }


    function _widgets_bar($tag_args, &$smarty){
        if($tag_args['id']){
            $id = ','.$tag_args['id'];
        }
        return '$s=$this->_files[0];$i+=0;
        echo \'<div class="shopWidgets_panel" base_file="\'.$s.\'" base_slot="\'.$i.\'" base_id='.$tag_args['id'].'  >\';
        $system = &$GLOBALS[\'system\'];
        $i = intval($this->_wgbar[$s]++);
        if(!$GLOBALS[\'_widgets_mdl\'])$GLOBALS[\'_widgets_mdl\'] = $system->loadModel(\'content/widgets\');
        $widgets = &$GLOBALS[\'_widgets_mdl\'];
        $widgets->adminLoad($s,$i'.$id.');echo \'</div>\';';

    }

    function _require($tag_args, &$smarty) {
        $attrs = $tag_args;
        $output = '';

        if (isset($assign_var)) {
            $output .= "ob_start();\n";
        }

        $output .=
            "\$_smarty_tpl_vars = \$this->_tpl_vars;\n";

        $_params = " $this->_get_resource('user:'.\$this->theme.'/'.{$attrs['file']})?('user:'.\$this->theme.'/'.{$attrs['file']}):('shop:'.{$attrs['file']}),array()";

        //$_params = "array('smarty_include_tpl_file' => 'user:'.\$this->theme.'/'.{$attrs['file']}, 'smarty_include_vars' => array())";

        $output .= "echo \$this->_fetch_compile_include($_params);\n" .
            "\$this->_tpl_vars = \$_smarty_tpl_vars;\n" .
            "unset(\$_smarty_tpl_vars);\n";

        if (isset($assign_var)) {
            $output .= "\$this->assign(" . $assign_var . ", ob_get_contents()); ob_end_clean();\n";
        }

        return $output;
    }

    function _get_secure(){return true;}
    function _get_trusted(){return true;}

    function _get_template($tpl_name, &$tpl_source, &$smarty) {
        $tpl_source = file_get_contents(THEME_DIR.'/'.$tpl_name);
        if (!is_bool($tpl_source)) {
            return true;
        } else {
            return false;
        }
    }

    function _get_timestamp($tpl_name, &$tpl_timestamp, &$smarty) {
        $tpl_timestamp = filemtime(THEME_DIR.'/'.$tpl_name);
        if (!is_bool($tpl_timestamp)) {
            return true;
        } else {
            return false;
        }
    }

    function _main($tag_args, &$smarty){
        return '?><div class="system-widgets-box">&nbsp;</div><?php';
    }

    function _prefix_tpl($tpl,&$smarty){
        if(isset($this->_in_widgets)){
            $tpl_res = $this->system->base_url().'plugins/widgets/'.$this->_in_widgets.'/';
            unset($this->_in_widgets);
        }else{
            $tpl_res = $this->system->base_url().'themes/'.$this->theme.'/';
        }

        $from = array(
            '/((?:background|src|href)\s*=\s*["|\'])(?:\.\/|\.\.\/)?(images\/.*?["|\'])/is',
            '/((?:background|background-image):\s*?url\()(?:\.\/|\.\.\/)?(images\/)/is',
            '/<!--[^<|>|{|\n]*?-->/'
        );
        $to = array(
            '\1'.$tpl_res.'\2',
            '\1'.$tpl_res.'\2',
            ''
        );

        $tpl = preg_replace($from,$to,$tpl);
        if(substr($tpl,0,3)=="\xEF\xBB\xBF")
            $tpl = substr($tpl,3);
        return $tpl;
    }

    function _header(){
        $ret='<base href="'.$this->system->base_url().'"/>';
        if( constant('DEBUG_CSS')){
            $ret.= '<link rel="stylesheet" href="statics/framework.css" type="text/css" />';
            $ret.='<link rel="stylesheet" href="statics/shop.css" type="text/css" />';
            $ret.='<link rel="stylesheet" href="statics/widgets.css" type="text/css" />';
            $ret.='<link rel="stylesheet" href="statics/widgets_edit.css" type="text/css" />';
        }elseif( constant('GZIP_CSS')){
            $ret.= '<link rel="stylesheet" href="statics/style.zcss" type="text/css" />';
            $ret.='<link rel="stylesheet" href="statics/widgets_edit.css" type="text/css" />';
        }else{
            $ret.= '<link rel="stylesheet" href="statics/style.css" type="text/css" />';
            $ret.='<link rel="stylesheet" href="statics/widgets_edit.css" type="text/css" />';
        }
        $tmp_path='http://'.HTTP_HOST.dirname(PHP_SELF);
        if( constant('DEBUG_JS')){
            $ret.= '<script src="'.$tmp_path.'/js_src/moo.js"></script>
                <script src="'.$tmp_path.'/js_src/moomore.js"></script>
        <script src="'.$tmp_path.'/js_src/mooadapter.js"></script>
        <script src="'.$tmp_path.'/js_src/jstools.js"></script>
        <script src="'.$tmp_path.'/js_src/coms/dragdropplus.js"></script>
        <script src="'.$tmp_path.'/js_src/coms/shopwidgets.js"></script>';
        }elseif( constant('GZIP_JS')){
            $ret.= '<script src="'.$tmp_path.'/js/package/tools.jgz"></script>
                <script src="'.$tmp_path.'/js/package/widgetsedit.jgz"></script>';
        }else{
            $ret.= '<script src="'.$tmp_path.'/js/package/tools.js"></script>
                <script src="'.$tmp_path.'/js/package/widgetsedit.js"></script>';
        }
        if($theme_info=($this->system->getConf('site.theme_'.$this->system->getConf('system.ui.current_theme').'_color'))){
            $theme_color_href=$this->system->base_url().'themes/'.$this->system->getConf('system.ui.current_theme').'/'.$theme_info;
            $ret.="<script>
            window.addEvent('domready',function(){
                new Element('link',{href:'".$theme_color_href."',type:'text/css',rel:'stylesheet'}).injectBottom(document.head);
             });
            </script>";
        }
        return $ret;
    }

    function _footer(){
        return '<div id="drag_operate_box" class="drag_operate_box" style="visibility:hidden;">
            <div class="drag_handle_box">
            <table cellpadding="0" cellspacing="0" width="100%">
            <tr>
            <td><span class="dhb_title">标题</span></td>
            <td width="40"><span class="dhb_edit">编辑</span></td>
            <td width="40"><span class="dhb_del">删除</span></td>
            </tr>
            </table>
            </div>
            </div>

            <div id="drag_ghost_box" class="drag_ghost_box" style="visibility:hidden">
            </div>';
    }
}

?>
