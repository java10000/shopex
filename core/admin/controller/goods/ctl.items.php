<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_items extends objectPage{
    var $finder_filter_tpl = 'product/finder_products_filter.html';
    var $workground = 'goods';
    var $object = 'goods/finderPdt';
    
    /**
     * 选择器
     *
     */
    function select(){
        /* <{finder disabledCols="_tools_" var="_select_" struct="finder/browser.html" params=$options select=$smarty.get.s_type}> */
        $params = unserialize(stripslashes($_POST['data']));

        // b2c-plat 如果为"筛选绑定商品"
        if(preg_match('/ctl=goods\/package&act=showAddPackage/',$_SERVER['HTTP_REFERER'])) {
            $params['is_local'] = 1;
        }
        $this->_finder_common(array('params'=>$params,'select'=>'checkbox'));
        $this->pagedata['options'] = $params;
        $this->pagedata['_finder']['rowselect'] = false;
        $this->setView('finder/browser.html');
        $this->output();
    }
}
?>
