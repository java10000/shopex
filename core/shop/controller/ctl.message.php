<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
class ctl_message extends shopPage{

    function index($nPage=1){
        $objBBS = &$this->system->loadModel('resources/shopbbs');
        $aData = $objBBS->getFrontMsg($nPage-1);
        for($i=0;$i<count($aData['data']);$i++){
            $aID[]=$aData['data'][$i]['msg_id'];
        }

        $aTemp=$objBBS->getFrontMsgReply($aID);
        if(count($aData['data'])){
            foreach($aData['data'] as $k=>$v){
                $msg_id=$v['msg_id'];
                for($i=0;$i<count($aTemp);$i++){
                    if($aTemp[$i]['for_id']==$msg_id){
                        $aData['data'][$k]['reply'][]=$aTemp[$i];
                    }
                }
            }
        }

        if(!isset($_COOKIE['UNAME'])){
           $this->pagedata['nomember'] = 'on';
        }
        $this->title = __('客户留言');
        $this->path[]=array('title'=>__('客户留言'));
        $this->pagedata['msg'] = $aData['data'];
        $this->pagedata['msg_from'] = $this->member['uname'];
        $this->pagedata['pager'] = array(
                'current'=> $nPage,
                'total'=> $aData['page'],
                'link'=> $this->system->mkUrl('message','index', array($tmp = time())),
                'token'=> $tmp);
        $this->pagedata['msgshow'] = $this->system->getConf('comment.verifyCode.msg');
        $this->output();

    }

    function sendMsgToOpt(){
        foreach($_POST as $key=>$val){
            $_POST[$key]=strip_tags($val);
        }
        if ($this->system->getConf('comment.verifyCode.msg')=='on'){
            if (md5(trim($_POST['verifyCode']))!=$_COOKIE['RANDOM_CODE'])
                $this->splash('failed','back',__('验证码录入错误，请重新输入'));
        }
        $this->_verifyMember(false);
        $oMsg = &$this->system->loadModel('resources/shopbbs');
        $nOpId = $oMsg->getOpId();
        $aTemp = array( 'subject'=>$_POST['subject'],
                        'msg_from'=>((empty($_POST['msg_from']) && empty($this->member['member_id'])) ? __('游客') : $_POST['msg_from']),
                        'from_type'=>isset($this->member)?0:2,
                        'to_type'=>1,
                        'folder'=>'inbox'
                    );
        if(!$this->member['member_id']){
          $aTemp['email'] =  $_POST['email'];
        }

        $aTemp['msg_ip'] = remote_addr();

        if($this->system->getConf('system.message.open') == 'on'){
            $aTemp['is_sec'] = 'false';
        }else{
            $aTemp['is_sec'] = 'true';
        }
        $from=$this->member['member_id']?$this->member['member_id']:0;
        if($oMsg->sendMsg($from,$nOpId,$_POST['message'],$aTemp)){
            $this->splash('success', $this->system->mkUrl("message","index"), __('提交成功，请等待管理员回复！'));
        }else{
            $this->splash('failed', $this->system->mkUrl("message","index"), __('留言提交失败！'));
        }
    }
}
?>