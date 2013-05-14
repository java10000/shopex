<?PHP

include_once CORE_INCLUDE_DIR.'/adminPage.php';

class admin_syj extends adminPage{
 
    function index(){
        $statMdl = $this->system->loadModel('plugins/shopex_stat/syj');
        
        // 未开通 则开通
        if ( !$account_ = $statMdl->account() ) {
            // 开通失败
            if ( (!$account_ = $statMdl->open_service($errmsg)) || ('succ' != $account_['rsp']) ) {
                $this->pagedata['errmsg'] = $errmsg;
                $this->display('file:'.dirname(__FILE__).'/view/index.html');
                return;
            }
            $account_ = $account_['data'];
            $statMdl->account($account_);
        }
        $params = array(
            'uid'=>$account_['uid'],
            'timestamp'=>time(),
        );
        
        $params['sign'] = $statMdl->_create_sign($params,$account_['token']);
        $state = $statMdl->_state_encode($params);
        $this->pagedata['stat_login_url'] = STAT_ACCESS_ADDR."?c=member&m=login&state=$state";
        $this->display('file:'.dirname(__FILE__).'/view/index.html');
    }
    
    
}
