<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?
class ctl_kft extends adminPage{
    var $workground ='setting';
    function ctl_kft(){
        parent::adminPage();
        $this->kft=&$this->system->loadModel('service/kft');
        $this->certi_id=$this->kft->getCerti();
        $this->token=$this->kft->getToken();
        $this->API_URL='http://api-client.shopex.cn/api.php';
    }
    function index(){
        /**
            @kft_action:
                @TOCHECK    :    检测
                @TOREG    :    注册
                @TOOPEN    :    开通
                @TOBIND    :    已经绑定
        */
        set_time_limit(0);
        $this->pagedata['kft']=$this->kft->checkLicense();
        switch($_POST['action']){
            case 'toapply':
                $return=$this->toapply();
                break;
            case 'toreg':
                $return=$this->toreg();
                break;
            case 'topen':
                $return=$this->topen();
                break;
            case 'toclear':
                $return=$this->toclear();
                break;
            default :
                break;
        }
        if(false){
            echo $return;
        }
        $this->pagedata['action']=$this->kft->getAction();
        $this->page('service/kft.html');
    }
    function toclear(){
        /**
            @清除状态
        */
        $this->kft->setAction('TOAPPLY');
    }
    function toapply(){
        $this->pagedata['kft']=$this->kft->checkLicense();
        $url=$this->url();
        $result=$this->kft->apply($url,'TOCHECK',$aS);
        $this->kft->setAction($this->kft->checkstr($result));
    }
    function toreg(){
        $aS = array('email'=>$_POST['email']);
        $result=$this->kft->apply($this->url(),'TOREG',$aS);
        $this->kft->setAction($this->kft->checkstr($result));
    }
    function topen(){
        $result=$this->kft->apply($this->url(),'TOPEN',$aS);
        $this->kft->setAction($this->kft->checkstr($result));
    }
    function url(){
        if(!$this->kft->getKftUrl()){
            $url=$this->kft->apply($this->API_URL,'ShopExKFT',$aS);
            $this->kft->setKftUrl($url);
        }
        return $url=$this->kft->getKftUrl();
    }
}
?>