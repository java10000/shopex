<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_autosync
 *
 * @uses pageFactory
 * @package
 * @version $Id: ctl.autosync.php 1867 2009-09-01 18:35:24Z wubin $
 * @copyright 2003-2009 ShopEx
 * @author 
 * @license Commercial
 */
include_once('objectPage.php');
class ctl_autosync extends objectPage {
    var $name = '同步配置';
    var $workground ='distribution';
    var $object = 'distribution/autosync';
    var $finder_action_tpl = 'distribution/auto_finder_action.html'; //默认的动作html模板,可以为null
    var $filterUnable = true;
    var $allowExport = false;
    
    /**
    * addNew
    *
    * @access public
    * @return void
    */
    function addNew() {
        $this->path[] = array('text'=>'添加配置');
        $oAutoSync = $this->system->loadModel('distribution/autosync');
        if(!$sync_rules = $oAutoSync->get_single_rules()){
            $sync_rules['local_op_id']=11;
        }

        $this->pagedata['rule_detail']= $oAutoSync->getGoodsSyncRule($sync_rules);
        $this->pagedata['rule_info']= $sync_rules;
        // 默认一条规则
        $this->pagedata['rule_list'] = array(
                                           array(
                                               'rule_relation_id' => 0,
                                               'supplier_id'      => 0,
                                               'pline_id'         => 0
                                           )
                                       );
       
        $this->_common();
    }
    
    
    
    /**
     * 修改配置
     *
     * @param int $rule_id
     * @access public
     * @return void
     */
    function showEdit($rule_id) {
        $this->path[] = array('text'=>'修改配置');
        
        // 获取配置信息
        $oAutoSync = $this->system->loadModel('distribution/autosync');
        $aInfo = $oAutoSync->getRuleInfo($rule_id);
        
        // 获取规则信息
        $aRuleList =  $oAutoSync->getRuleRelationInfo($rule_id);
        
        // 获取本地操作信息
        $aTemp = $oAutoSync->getSupplierOP($aInfo['supplier_op_id']);
        foreach($aTemp['op_items'] as $row){
            $aLocalOPList[$row]['op_id'] = $row;
            $aLocalOPList[$row]['op_name'] = $oAutoSync->getLocalOP($row);
            $aLocalOPList[$row]['checked'] = ($row == $aInfo['local_op_id'])? 1 : 0;
        }
        
        $this->pagedata['rule_id'] = $rule_id;
        $this->pagedata['rule_info'] = $aInfo;
        $this->pagedata['rule_list'] = $aRuleList;
        $this->pagedata['local_op_list']   = $aLocalOPList;
        
        $this->_common();
    }
    
    /**
     * 添加和修改配置共用部分
     * 
     * @access public
     * @return void
     */
    function _common(){
        // 供应商列表
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $aSupplierList = $oSupplier->getList('*','',0,1000); // 默认是20每一页,现在获取1000个供应商,理论上不可能达到这个数 2009-12-9 11:20 wubin 
        
        // 供应商操作列表
        $oAutoSync = $this->system->loadModel('distribution/autosync');
        $aSupplierOPList = $oAutoSync->getSupplierOPList();
        
        $this->pagedata['supplier_op_list'] = $aSupplierOPList;
        $this->pagedata['supplier_list']    = $aSupplierList;

        $this->page('distribution/autosync_edit.html');
    }
    
    /**
     * 添加一条规则行
     * 
     * @access public
     * @return void
     */
    function addRow() {
        // 供应商列表
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $aSupplierList = $oSupplier->getList('*','',0,1000); // 默认是20每一页,现在获取1000个供应商,理论上不可能达到这个数 2009-12-9 11:20 wubin 
        
        $this->pagedata['supplier_list']    = $aSupplierList;
        
        // $this->pagedata['spec_default_pic'] = $this->system->getConf('spec.default.pic');
        $this->__tmpl = 'distribution/autosync_rule_row.html';
        $this->output();
    }
    
    /**
     * 获取指定供应商操作所对应的本地操作选项
     * 
     * @access public
     * @return void
     */
    function changeSupplierOP() {
        $oAutoSync = $this->system->loadModel('distribution/autosync');
        $aTemp = $oAutoSync->getSupplierOP($_POST['supplier_op_id']);
        
        foreach($aTemp['op_items'] as $row){
            $aList[$row]['op_id'] = $row;
            $aList[$row]['op_name'] = $oAutoSync->getLocalOP($row);
            $aList[$row]['checked'] = ($row == $aTemp['checked'])? 1 : 0;
        }

        $this->pagedata['local_op_list'] = $aList;
        $this->__tmpl = 'distribution/autosync_local_op_row.html';
        $this->output();
    }
    
    /**
     * 获取指定供应商的产品线信息
     *
     * @access public
     * @return void
     */
    function changeSupplier() {
        // 供应商列表
        $oSupplier = $this->system->loadModel('distribution/supplier');
        $aInfo =  $oSupplier->getSupplierInfo($_POST['supplier_id']);
        
        $oDataSync = $this->system->loadModel('distribution/autosync');
        
        if($aInfo['supplier_pline']) {
            $aPline = unserialize($aInfo['supplier_pline']);
            
            // 产品线不为空
            if(!empty($aPline)) {
                // 检测是否存在分销关系 (临时) 2009-09-27 14:59:42
                $oDataSync = $this->system->loadModel('distribution/datasync');
                $aList = $oDataSync->getProductLine($_POST['supplier_id']);
                
                if($aList) {
                    $oAutoSync = $this->system->loadModel('distribution/autosync');
                    
                    // 是否存在产品线名称,没有则更新数据库中产品线记录
                    if(!$oAutoSync->isExistPlineName($aPline)) {
                        
                         // 修正产品线数据(多获取产品线名称,并保存入库)
                         $aPline = $oAutoSync->fillPlineName($_POST['supplier_id'],$aList);
                    }
                } 
            }
        } else {
            $aPline = array();
        }
        
        $this->pagedata['pline_list'] = $aPline;
        $this->__tmpl = 'distribution/autosync_pline_list.html';
        $this->output();
    }
    
    /**
     * 清除回收站(一并删除规则)
     * 
     * @access public
     * @return void
     */
    function delete(){
        // 删除规则
        $oDataSync = $this->system->loadModel('distribution/autosync');
        $oDataSync->deleteRuleRelation($_POST['rule_id']);
        
        parent::delete();
    }
    
    function addSave(){
        $oDataSync = $this->system->loadModel('distribution/autosync');
        $this->begin('index.php?ctl=distribution/autosync&act=addNew');
        $this->end($oDataSync->updateAutoSync($_POST),__('保存成功'));
    }
    
    function updateSyncRule(){
       $oDataSync = $this->system->loadModel('distribution/autosync');
       
       if(!$sync_rules = $oDataSync->get_single_rules()){
           $_POST['local_op_id']=11;
           $_POST['memo']= '';
       }else{
           $_POST['local_op_id']=$sync_rules['local_op_id'];
           $_POST['memo']= $sync_rules['memo'];
       }
       $this->begin('index.php?ctl=distribution/supplier&act=syncPlatformDataList&supplier_id='.$_GET['supplier_id']);
       $this->end($oDataSync->updateAutoSync($_POST),__('保存成功'));
    }

}
?>
