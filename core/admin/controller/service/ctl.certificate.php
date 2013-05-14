<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_certificate extends adminPage{
    var $workground ='setting';
    var $object = 'service/certificate';
    
    function ctl_certificate(){
        parent::adminPage();
        $this->lang = 'zh-cn';
        $this->base_url = 'http://service.shopex.cn/info.php';
        $this->sess_id = $this->system->session->sess_id;
        $this->license_url="index.php?ctl=service/certificate&act=download";
    }

    function showIndex(){
        if(constant('SAAS_MODE')){
            exit;
        }
        $this->path[] = array('text'=>__('ShopEx证书'));
        $this->certi_model = &$this->system->loadModel('service/certificate');
        $this->Certi = $this->certi_model->getCerti();
        $this->Token = $this->certi_model->getToken();
        $this->Nodeid = $this->certi_model->getNodeid();
        $this->ent_id = $this->certi_model->getent_id();
        $this->ent_email = $this->certi_model->getent_email();
        
        if(empty($this->Certi) ||empty($this->Token)){
            $this->pagedata['license']=false;
        }else{
            $this->pagedata['license']=true;
            $this->pagedata['license_url']=$this->license_url;
        }
        $this->pagedata['ent_id'] = $this->ent_id;
        $this->pagedata['node_id'] = $this->Nodeid;
        $this->pagedata['ent_email'] = $this->ent_email;
        $this->pagedata['certi_id']=$this->Certi;
        $this->pagedata['debug']=false;
        $this->page('service/index.html');
    }

    function reset_shopexid_pwd() {
        $pwd = $_POST['password'];
        
        $certiMdl = &$this->system->loadModel('service/certificate');
        $pwd = $certiMdl->shopex_pwd_encrypt($pwd);
        
        $certiMdl->setent_ac($pwd);
        
        $this->splash('success','index.php?ctl=service/certificate&act=showIndex',__('保存成功'));
    }
    
    function upLicense(){
        $this->certi_model = &$this->system->loadModel('service/certificate');
        $result1=$this->certi_model->checkFile($_FILES['license']['tmp_name']);
        if(!$result1){
            $this->splash('failed','index.php?ctl=service/certificate&act=showIndex',__('重置证书失败，请先上传文件'));
        }
        $result = $this->certi_model->upload($_FILES['license']['tmp_name']);
        if(!$result){
            $this->splash('failed','index.php?ctl=service/certificate&act=showIndex',__('证书重置失败,请先上传文件'));
        }
        
        $this->splash('success','index.php?ctl=service/certificate&act=showIndex',__('证书重置成功'));
    }
    function inputto(){
        $this->certi_model = &$this->system->loadModel('service/certificate');
        $this->certi_model->inputto();
    }

    function download(){
        header("Content-type:application/octet-stream;charset=utf-8");
        header("Content-Type: application/force-download");
        $this->certi_model = &$this->system->loadModel('service/certificate');
        $charset = &$this->system->loadModel('utility/charset');
        $this->fileName = $charset->utf2local($this->certi_model->getName().'CERTIFICATE.CER','zh');
        header("Content-Disposition:filename=".$this->fileName);
        $this->Certi = $this->certi_model->getCerti();
        $this->Token = $this->certi_model->getToken();
        $this->Nodeid = $this->certi_model->getNodeId();
        echo $this->Certi,'|||',$this->Token,'|||',$this->Nodeid;
    }

    function del(){
        $this->certi_model = &$this->system->loadModel('service/certificate');
        if(!$this->certi_model->checkPass($_POST)){
            $this->splash('failed','index.php?ctl=service/certificate&act=checkPass',__('请输入正确的用户名和密码'));
        }

        $this->certi_model->delLicense();
        $this->certi_model->delEnterprise();

        $this->clear_all_cache();

        echo '<script>window.location.href="index.php";</script>';exit;
    }
    
    function checkPass(){
        $this->page('service/checkp.html');
    }

    function node_binding($subact='accept') {
        $certiMdl = &$this->system->loadModel($this->object);
        $data['node_id'] = $certiMdl->getNodeid();
        $data['certi_id'] = $certiMdl->getCerti();
        $data['sess_id'] = $this->system->sess_id;
        $data['certi_ac'] = $certiMdl->make_shopex_ac($data, $certiMdl->getToken()); // 变态了 部分签名
        $data['api_url'] = $this->system->base_url().'api.php';
        $data['source'] = $subact;
        $data['callback'] = rawurlencode($this->system->base_url().'api.php');
        $data['api_v'] = '2.0';
        $data['from_api_v'] = '2.0';
        $data['to_api_v'] = '2.2';
        
        foreach( $data as $k=>$v ) {
            $query_string[] = "$k=$v";
        }
        
        $this->pagedata['matrix_url'] = MATRIX_NODE_BIND_URI.'?'.implode('&',$query_string);
        $this->page('service/node_binding.html');
    }
}
