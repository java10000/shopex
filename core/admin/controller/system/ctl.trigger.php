<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_trigger extends objectPage{

    var $workground = 'tools';
    var $name = '';
    var $object = 'system/trigger';
    var $finder_action_tpl = 'system/trigger/finder_action.html';
    var $finder_default_cols = '_cmd,filter_str,action_str,trigger_event,trigger_memo,active';
    var $filterUnable = true;


    function _views(){
        $ret[__('所有')] = array();
        foreach($this->model->trigger_points as $k=>$v){
            $ret[$v] = array('target'=>$k);
        }
        return $ret;
    }

    function _filter_types(){
        return $this->model->param_types();
    }

    function edit($trigger_id){
        $trigger = $this->model->instance($trigger_id);
        $trigger['trigger_define'] = unserialize($trigger['trigger_define']);
        list($target,$trigger['event']) = explode(':',$trigger['trigger_event']);
        $this->pagedata['trigger'] = &$trigger;
        $this->pagedata['target'] = $target;
        $this->pagedata['target_name'] = $this->model->trigger_points[$target];
        $this->pagedata['actions'] = $this->_all_actions($target);
        if($target=='system'){

        }else{
            $this->_init_filter($target,$trigger['event']);
        }
        $this->display('system/trigger/item.html');
    }

    function addNew($target='system'){
        $this->path[] = array('text'=>__('新建').$this->pagedata['target_name'].__('网店机器人'));
        $this->pagedata['actions'] = $this->_all_actions($target);
        $this->pagedata['target'] = $target;
        $this->pagedata['target_name'] = $this->model->trigger_points[$target];
        if($target=='system'){

            $first_action_grp = current($this->pagedata['actions']);
            $this->pagedata['trigger']['trigger_define']['actions'] =array(array('act'=>key($first_action_grp)));
            unset($first_action_grp);
        }else{
            $this->_init_filter($target);
        }
        $this->display('system/trigger/item.html');
    }

    function _all_actions($target){
        $addons = &$this->system->loadModel('system/addons');
        $global_actions = array();
        $actions = array();
        foreach($addons->getList('plugin_ident,plugin_struct',array('plugin_type'=>'action')) as $item){
            $struct = unserialize($item['plugin_struct']);
            if(!$struct['props']['action_for'] || $struct['props']['action_for']==$target){
                $action_item = $addons->load($item['plugin_ident'],'action');
                foreach($action_item->actions() as $func=>$act){
                    if(($group = $act['group']) || ($group = $this->model->trigger_points[$struct['props']['action_for']])){
                        $actions[$group][$item['plugin_ident'].':'.$func] = $act;
                    }else{
                        $global_actions[$item['plugin_ident'].':'.$func] = $act;
                    }
                }
            }
        }
        if($global_actions){
            $actions[__('通用')] = &$global_actions;
        }
        return $actions;
    }

    function showAction($func){
        $addons = &$this->system->loadModel('system/addons');
        list($belong,$action) = explode(':',$func);
        $plugin = $addons->load($belong,'action');
        $actions = $plugin->actions();
        $this->pagedata['args'] = $actions[$action]['args'];
        $this->display('system/trigger/action_row.html');
    }

    function showfilter($target_event){
        list($target,$event) = explode(':',$target_event);
        $this->_init_filter($target,$event);
        $this->display('system/trigger/filter.html');
    }

    function _init_filter($target,$event=null){
        $obj = &$this->system->loadModel($target);
        $events = $this->pagedata['events'] = $obj->events();
        if(!$event) $event = key($events);
        $this->pagedata['trigger']['trigger_event'] = $event;

        $this->pagedata['filter_types'] = $this->_filter_types();
        $this->pagedata['current_event'] = $events[$event];
        //$this->pagedata['current_event']['params']['_event_date_'] = array('label'=>__('日期'),'type'=>'date');

        if(!$this->pagedata['trigger']['trigger_define']['filter']){
            $first_prop = current($events[$event]['params']);
            $this->pagedata['trigger']['trigger_define']['filter'] = array(
                array('key'=>key($events[$event]['params'])
                ,'test'=>key($this->pagedata['filter_types'][$first_prop['type']])
            ));
        }

        if(!$this->pagedata['trigger']['trigger_define']['actions']){
            $first_action_grp = current($this->pagedata['actions']);
            $this->pagedata['trigger']['trigger_define']['actions'] =array(array('act'=>key($first_action_grp)));
            unset($first_action_grp);
        }
    }


    function save(){
        $this->begin('index.php?ctl=system/trigger&act=edit&p[0]='.$_POST['trigger_id']);
        foreach($_POST['filter'] as $k=>$v){
            $filter[$k] = array(
                'key'=>$v,
                'test'=>$_POST['filter_type'][$k],
                'val'=>$_POST['filter_value'][$k],
            );
        }
        $trigger = $_POST['trigger'];
        $trigger['filter_str'] = $_POST['filter_str'];
        $trigger['action_str'] = $_POST['action_str'];
        if($_POST['system_trigger']){
            $trigger['trigger_event'] = 'system:'.$trigger['trigger_event'];
        }
        if($trigger['trigger_define']['filter_mode']!='every'){
            $trigger['trigger_define']['filter']=&$filter;
        }
        $actions = array();
        foreach($_POST['actions'] as $k=>$v){
            $actions[$k] = array(
                'act'=>$v,
                'args'=>$_POST['act_args'][$k],
            );
        }
        $trigger['trigger_define']['actions']=&$actions;
        
        $trigger['active'] = $_POST['active'];

        if($_POST['trigger_id']){
            $status = $this->model->update($trigger,array('trigger_id'=>$_POST['trigger_id']));
        }else{
            $status = $this->model->insert($trigger);
        }
        $this->end($status);
    }

}
