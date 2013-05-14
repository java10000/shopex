<?php
class action_member{

    var $name = '会员触发器可用动作';
    var $action_for = 'member/account';
    function action_member(){
        $this->system=&$GLOBALS['system'];
    }
    function actions(){
        return array(
                //'changelv'=>array('label'=>'设置会员等级','args'=>array('会员等级'=>array('type'=>'object:member/level'))),
                'addPoint'=>array('label'=>'增加积分','args'=>array('点数'=>array('type'=>'number'))),
                'delPoint'=>array('label'=>'扣除积分','args'=>array('点数'=>array('type'=>'number'))),
                'addAdvance'=>array('label'=>'增加预存款','args'=>array('金额'=>array('type'=>'money'))),
                //'delAdvance'=>array('label'=>'扣除预存款','args'=>array('金额'=>array('type'=>'money'))),
                'setTag'=>array('label'=>'设置会员标签','args'=>array('会员标签'=>array('type'=>'object:system/tag','filter'=>'tag_type=member'))),
                'sendcoupon'=>array('label'=>'送优惠券','args'=>array('优惠券'=>array('required'=>true,'type'=>'object:trading/coupon','filter'=>'cpns_type=1&ifvalid=1'))),
                //'sendgift'=>array('label'=>'送赠品','args'=>array('赠品名称'=>array('type'=>'number'))),
            );
    }
    function changelv($data,$actlevel){
        $actlevelM = $this->system->loadModel('member/level');
        $actMember=$this->system->loadModel('member/member');
        if (!is_numeric($actlevel)){
            $actlevel = $actlevelM->checkLevel(array('lv_name'=>$actlevel),'INSERT',1);
        }
        $actMember->setLevel($actlevel,array('items'=>array($data['member_id'])));
    }
    function addPoint($data,$point){
        $actMember=$this->system->loadModel('member/member');
        $actlevelM = $this->system->loadModel('trading/memberPoint');
        $row=$actMember->getFieldById($data['member_id']);
        $data['point'] = intval($row['point'] + $point);
        //$actMember->save($data['member_id'],array('point'=>$data['point']));
        $actlevelM->chgPoint($data['member_id'], $point,'fire_event',null,1);
        if (!$this->system->getConf('site.level_switch')){
            if($data['member_id']){
                $actlevelM->toUpdatelevel($data['member_id']);
            }
        }
    }
    function delPoint($data,$point){
        $actMember=$this->system->loadModel('member/member');
        $row=$actMember->getFieldById($data['member_id']);
        $data['point'] = intval($row['point'] - $point);
        $data['point'] = $data['point']<0?0:$data['point'];
        $actMember->save($data['member_id'],array('point'=>$data['point']));
    }
    function addAdvance($data, $money){
        $advance = $this->system->loadModel('member/advance');
        $advance->add($data['member_id'],$money,'sys:触发器',$errMsg, '', '' ,'sys:触发器' , '系统自动充值',1);
    }
    function delAdvance($data,$advance){
         $actMember=$this->system->loadModel('member/member');
        $row=$actMember->getFieldById($data['member_id']);
        $data['advance'] = intval($row['advance'] - $advance);
        $actMember->save($data['member_id'],array('advance'=>$data['advance']));
    }
    function sendcoupon($data, $coupon){
        $memberId = $data['member_id'];
        $oCoupon = $this->system->loadModel('trading/coupon');
        $oCoupon->generateCoupon($coupon, $memberId, 1, $data['order_id']);
    }
    function setTag($data,$tagId){
        $tagM = $this->system->loadModel('system/tag');
        if (!$tagM->getTagRel($tagId,$data['member_id'])){
            $tagM->addTag($tagId,$data['member_id']);
        }
    }
}