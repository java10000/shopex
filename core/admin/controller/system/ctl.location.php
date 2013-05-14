<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_payment
 *
 * @uses pageFactory
 * @package
 * @version $Id: ctl.passport.php 1867 2008-04-23 04:00:24Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author bryant <bryant@shopex.cn>
 * @license Commercial
 */
class ctl_location extends adminPage {

    var $workground = 'setting';

    function index(){
        $model = &$this->system->loadModel('system/local');
        $this->pagedata['area_depth'] = $this->system->getConf('system.area_depth');
        $this->pagedata['packages'] = $model->getList();
        $RGrade = $model->getRegionGrad(key($this->pagedata['packages']));
        if ($RGrade)
            $this->pagedata['packages'][key($this->pagedata['packages'])]['maxdepth']=$RGrade['grade'];
        $this->pagedata['using_local'] = $model->get_default();

        $this->path[] = array('text'=>__('本地化管理'));
        $this->path[] = array('text'=>__('地区配置'));
        $this->page('system/location/index.html');
    }

    function save_depth(){
        $this->begin('index.php?ctl=system/location&act=index');
        $rs = $this->system->setConf('system.area_depth',$_POST['area_depth']);
        $this->end($rs);
    }

    function install($package){
        set_time_limit(0);
        $this->begin('index.php?ctl=system/location&act=index');
        $model = &$this->system->loadModel('system/local');
        $rs = $model->use_package($package);
        $this->end($rs);
    }
    function setDefault($package){
        $this->clearOldData($package);
        $this->install($package);
    }
    function clearOldData($package){
        $model = &$this->system->loadModel('system/local');
        $model->clearOldData($package);
    }
}
?>
