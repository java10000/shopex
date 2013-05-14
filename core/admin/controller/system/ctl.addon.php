<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
require_once('objectPage.php');
class ctl_addon extends objectPage{

    var $workground = 'tools';
    var $object = 'system/addons';
    var $allowImport = false;
    var $allowExport = false;
    var $finder_action_tpl = 'system/addons/finder_action.html';
    var $deleteAble = false;
    var $filterUnable = true;

    function _views(){
        $views = array();
        foreach($this->model->alltypes as $k=>$v){
            $views[$v] = array('plugin_type'=>$k);
        }
        
        return $views;
    }

    function refresh(){
        $this->begin('index.php?ctl=system/addon&act=index');
        $this->model = &$this->system->loadModel('system/addons');
        $this->end($this->model->refresh());
    }

    function plugin($type='payment'){
        $_GET['p'][0] = $type;
        $this->pagedata['allow_disable'] = false;
        $model = &$this->system->loadModel('system/addons');

        $tpList = $model->getType();
        $this->path[] = array('text'=>__('插件'));
        $this->path[] = array('text'=>$tpList[$type]['text']);
        $model->plugin_type = $tpList[$type]['type'];
        $model->prefix = $tpList[$type]['prefix'];
        $model->plugin_name = $type;
        $model->plugin_case = $tpList[$type]['case'];

        $this->pagedata['type'] = &$tpList;
        $list =  $model->getList(null,null,true);

        $this->pagedata['items'] = &$list;
        $this->pagedata['infoPage'] = "system/addons/{$_GET['act']}-{$_GET['p'][0]}.html";
        $this->page('system/addons/page.html');
    }

    function widget(){
        $this->path[] = array('text'=>__('板块'));
        $model = &$this->system->loadModel('content/widgets');
        $items = $model->getLibs();
        foreach($items as $key=>$item){
            $items[$key]['name'] = $item['label'];
            $items[$key]['file'] = 'plugins/widgets/'.$key;
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['allow_disable'] = false;
        $this->pagedata['infoPage'] = "system/addons/widgets.html";
        $this->page('system/addons/page.html');
    }

    function package(){
        $this->path[] = array('text'=>__('功能包'));
        $this->pagedata['allow_disable'] = true;
        $this->page('system/addons/page.html');
    }

}
?>
