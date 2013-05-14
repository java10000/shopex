<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
include_once('objectPage.php');
class ctl_salescount extends adminPage{

    var $workground ='analytics';

    function index(){
        $sales = &$this->system->loadModel('utility/salescount');
        //$this->pagedata['year']=date("Y");
        //$this->pagedata['month']=date("m");
        $value_year=date("Y")-2000;
        $value_month=date("m");
        if($_GET['search_year']){
            $_GET['search_year']=intval($_GET['search_year']);
            $_GET['search_month']=intval($_GET['search_month']);
            $value_year=$_GET['search_year'];
            $value_month=$_GET['search_month'];
            $month_st=$_GET['search_year'].'-01-01';
            $month_en=$_GET['search_year'].'-12-01';
            $day_st=$_GET['search_year'].'-'.($_GET['search_month']+1).'-01';
            $day_en=date("Y-m-01",strtotime("+1 month",strtotime($day_st)));
        }else{

            $month_st=date("Y-01-01");
            $month_en=date("Y-12-01");
            $day_st=date("Y-m-01");
            $day_en=date("Y-m-01",strtotime("+1 month",strtotime($day_st)));
        }
        $d_year=array();
        for($i=2000;$i<=date("Y");$i++){
            array_push($d_year,$i);
        }
        $d_month=array();
        for($i=1;$i<=12;$i++){
            array_push($d_month,$i);
        }
        $month_search=$sales->mdl_dosearch($month_st,$month_en,"","","month");
        $day_search=$sales->mdl_dosearch($day_st,$day_en,"","","day");
        $this->pagedata['day']=$day_search;
        $this->pagedata['month']=$month_search;
        $this->pagedata['d_year']=$d_year;
        $this->pagedata['d_month']=$d_month;
        $this->pagedata['value_month']=$value_month;
        $this->pagedata['value_year']=$value_year;
        $this->page('sale/count/salescount.html');
    }
    function countall(){

        if($_GET['ordertype'] && $_GET['method']){
                $order['order']=intval($_GET['ordertype']);
                $order['method']=intval($_GET['method']);
        }
        if($_GET['dosearch']){
            $this->begin('index.php?ctl=sale/salescount&act=countall');
            if($_GET['searchfrom']==''||$_GET['searchto']==''){
               $this->end(false,__('请要选择查找的日期'));
            }
            $dateFrom=strtotime($_GET['searchfrom']);
            $dateTo=strtotime($_GET['searchto']);
            $search=$_GET['dosearch'];
            $item=$_GET['searchitem'];
            $sales=&$this->system->loadModel('utility/salescount');
            $result=$sales->count_all($dateFrom,$dateTo,$item,$search,$order);
            $this->pagedata['plug']='&dosearch='.$_GET['dosearch'].'&searchitem='.$_GET['searchitem'].'&searchfrom='.$_GET['searchfrom'].'&searchto='.$_GET['searchto'];
        }else {
            $dateFrom=strtotime(date("Y").'-01-01');
            $dateTo=strtotime("+1 year",$dateFrom);
            $sales=&$this->system->loadModel('utility/salescount');
            $result=$sales->count_all($dateFrom,$dateTo,'','',$order);
        }
        if($_GET['genexml']==1){
            $dataio = $this->system->loadModel('system/dataio');
            $io="xls";

            $name=array('商品名称','销售量','销售额');
            $dataio->export_begin("xls",$name,'商品销售量(额)',count($result));
            $dataio->export_rows($io,$result);
            $dataio->export_finish('xls');
            exit();
        }
        $this->pagedata['data']=$result;
        $this->pagedata['method']=$_GET['method'];
        $this->page('sale/count/salescountall.html');
    }

    function do_search(){
        $sales = &$this->system->loadModel('utility/salescount');
        $results=$sales->mdl_dosearch($_POST['dateFrom'],$_POST['dateTo'],$_POST['dateCompareFrom'],$_POST['dateCompareTo'],$_POST['ptype']);
        echo $results;
    }

    function membercount(){

        if($_GET['ordertype'] && $_GET['method'] ){
            $order['order']=intval($_GET['ordertype']);
            $order['method']=intval($_GET['method']);
        }
        if($_GET['searchfrom'] && $_GET['searchto']){
            $dateFrom=strtotime($_GET['searchfrom']);
            $dateTo=strtotime($_GET['searchto']);
            $memberinfo=&$this->system->loadModel('utility/salescount');
            $result=$memberinfo->member_count($dateFrom,$dateTo,$order);
        }else{
            $dateFrom=strtotime(date("Y").'-01-01');
            $dateTo=strtotime("+1 year",$dateFrom);
            $memberinfo=&$this->system->loadModel('utility/salescount');
            $result=$memberinfo->member_count($dateFrom,$dateTo,$order);

        }
        if($_GET['genexml']==1){
            $addons = &$this->system->loadModel('system/addons');
            $io= $addons->load('xls','io');
            $name=array(__('用户名'),__('姓名'),__('销售量'),__('销售额'));
            $io->export_begin($name,__("会员购物量(额)"),count($name));
            $io->export_rows($result);
            $io->export_finish();
            return;
        }
        $this->pagedata['data']=$result;
        $this->pagedata['method']=$_GET['method'];
        $this->page('sale/count/membercount.html');
    }
    function salesguide(){
        if($_GET['searchfrom'] && $_GET['searchto']){
            $dateFrom=strtotime($_GET['searchfrom']);
            $dateTo=strtotime($_GET['searchto']);
            $vcompare=&$this->system->loadModel('utility/salescount');
            $result['ordersales']=$vcompare->average_order_sales($dateFrom,$dateTo);
            $result['ordermember']=$vcompare->have_order_member($dateFrom,$dateTo);
            $result['allmember']=$vcompare->all_member();
            $result['visit']=$vcompare->count_all_visite($dateFrom,$dateTo);
        }else{
            $dateFrom=strtotime(date("Y").'-01-01');
            $dateTo=strtotime("+1 year",$dateFrom);
            $vcompare=&$this->system->loadModel('utility/salescount');
            $result['ordersales']=$vcompare->average_order_sales($dateFrom,$dateTo);
            $result['ordermember']=$vcompare->have_order_member($dateFrom,$dateTo);
            $result['allmember']=$vcompare->all_member();
            $result['visit']=$vcompare->count_all_visite($dateFrom,$dateTo);
        }
        $this->pagedata['data']=$result;
        $this->pagedata['method']=$_GET['method'];
        $this->page('sale/count/salesguide.html');
    }
    function visitsalecompare(){
        if($_GET['ordertype'] && $_GET['method'] ){
            $order['order']=intval($_GET['ordertype']);
            $order['method']=intval($_GET['method']);
        }
        if($_GET['searchfrom'] && $_GET['searchto']){
            $dateFrom=strtotime($_GET['searchfrom']);
            $dateTo=strtotime($_GET['searchto']);
            $vcompare=&$this->system->loadModel('utility/salescount');
            $result=$vcompare->visit_sale_compare($dateFrom,$dateTo,$order);
        }else{
            $dateFrom=strtotime(date("Y").'-01-01');
            $dateTo=strtotime("+1 year",$dateFrom);
            $vcompare=&$this->system->loadModel('utility/salescount');
            $result=$vcompare->visit_sale_compare($dateFrom,$dateTo,$order);
        }
        if($_GET['genexml']==1){

            $addons = &$this->system->loadModel('system/addons');
            $io= $addons->load('xls','io');
            $name=array(__('商品名称'),__('访问次数'),__('购买次数'));
            $io->export_begin($name,__("商品访问购买次数"),count($name));
            $io->export_rows($result);
            return;
        }
        $this->pagedata['method']=$_GET['method'];
        $this->pagedata['data']=$result;
        $this->page('sale/count/visitsalecompare.html');
    }
}
?>
