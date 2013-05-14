<?php defined('CORE_DIR') || exit('入口错误'); ?>
<?php
/**
 * ctl_discuss
 *
 * @uses adminPage
 * @package
 * @version $Id$
 * @copyright 2003-2007 ShopEx
 * @author Liujy <ever@shopex.cn>
 * @license Commercial
 */
include_once('objectPage.php');
class ctl_discuss extends objectPage{

    var $finder_action_tpl = 'product/discuss/finder_action.html'; //默认的动作html模板,可以为null
    var $finder_filter_tpl = 'product/discuss/finder_filter.html'; //默认的过滤器html,可以为null
    var $workground = 'member';
    var $object = 'comment/discuss';

    function setting(){
        $this->path[] = array('text'=>__('评论设置'));
        $comment = &$this->system->loadModel('comment/comment');
        $aOut = $comment->getSetting('discuss');
        if(!$aOut['verifyCode']['discuss']){
            $aOut['verifyCode']['discuss']='off';
        }
        $aOut['aSwitch']['discuss'] = array('on'=>__('开启'), 'off'=>__('关闭'));
        $aOut['aPower']['discuss'] = array('null'=>__('非会员可发表评论'), 'member'=>__('注册会员可发表评论'), 'buyer'=>__('只有购买过此商品的会员才可发表评论（并且订单状态为已支付状态）'));
         $aOut['verifyLCode']['discuss'] = array('on'=>__('开启'), 'off'=>__('关闭'));
        $this->pagedata['setting']= $aOut;
        $this->page('product/discuss/setting.html');
    }

    function toSetting(){
        $comment = &$this->system->loadModel('comment/comment');
        $comment->setSetting('discuss', $_POST);
        $this->splash('success','index.php?ctl=goods/discuss&act=setting',__('保存成功!'));
    }

    function _detail(){
        return array('show_detail'=>array('label'=>__('评论详细信息'),'tpl'=>'product/discuss/detail.html'));
    }

    function show_detail($comment_id){
        $Mem = &$this->system->loadModel('member/member');
        $objComment = &$this->system->loadModel('comment/comment');
        $aComment = $objComment->getCommentById($comment_id);
        $aComment['url']=$this->system->realUrl('product','index',array($aComment['goods_id']),null,$this->system->base_url());
        $this->pagedata['comment'] = $aComment;

        //取QQ,MSN等联系信息
        $data = $Mem->getMemIdByName($aComment['author']);

        $mem_id = $data[0]['member_id'];//
        $this->pagedata['reply'] = $objComment->getCommentReply($comment_id);

        $comment_ids = array($comment_id);
        foreach($this->pagedata['reply'] as $key => $replyItem){
            $comment_ids[] = $replyItem['comment_id'];
        }

        $tree = $Mem->getContactObject($mem_id);
        $this->pagedata['tree'] = $tree;

        $objComment->setReaded($comment_ids);
    }

    function delete(){
        if($_REQUEST['f_id']){
            $this->begin('index.php?ctl=goods/discuss&act=detail&p[0]='.$_REQUEST['f_id']);
        }else{
            $this->begin('index.php?ctl=goods/discuss&act=index');
        }

        $objComment = &$this->system->loadModel('comment/comment');
        if(is_array($_REQUEST['comment_id'])){
            foreach($_REQUEST['comment_id'] as $id){
                $objComment->toRemove($id);
            }
        }
       
        $this->end(true, __('操作成功!'));
    }

    function toDisplay($comment_id, $status='false', $f_id=0){
        $this->begin('index.php?ctl=goods/discuss&act=detail&p[0]='.($f_id?$f_id:$comment_id));
        $objComment = &$this->system->loadModel('comment/comment');
        if(intval($comment_id) > 0){
            if($status == 'true'){
                $status = 'false';
            }else{
                $status = 'true';
            }
            $this->end($objComment->toDisplay(intval($comment_id), $status), __('操作成功!'));
        }else{
            $this->end(false, __('操作失败: 传入参数丢失!'));
        }
    }

    function toReply($comment_id){
        $this->begin('index.php?ctl=goods/discuss&act=detail&p[0]='.$comment_id);
        $objComment = &$this->system->loadModel('comment/comment');
        $aComment = $objComment->getFieldById($comment_id, array('*'));
        $aData['comment'] = $_POST['reply_content'];
        $aData['for_comment_id'] = $comment_id;
        $aData['goods_id'] = $aComment['goods_id'];
        $aData['object_type'] = $aComment['object_type'];
        $aData['author_id'] = $this->system->op_id;
        $aData['author'] = __('管理员').'['.$this->system->op_name.']';
        $aData['time'] = time();
        $aData['lastreply'] = time();
        $aData['display'] = 'true';
        $aData['ip'] = remote_addr();
        $this->end($objComment->toReply($aData), __('回复成功!'));
    }

    function setIndexOrder(){
        $objComment = &$this->system->loadModel('comment/comment');

        if(count($_POST['comment_id']) > 0)
            foreach($_POST['comment_id'] as $id){
                $objComment->setIndexOrder($id);
            }
        unset($_POST);

    }
}
?>
