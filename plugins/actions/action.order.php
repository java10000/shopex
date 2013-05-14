<?php
class action_order{

    var $name = '订单触发器可用动作';
    var $action_for = 'trading/order';
    function action_order(){
        $this->system=&$GLOBALS['system'];
    }
    function actions(){
        return array(
                'addPoint'=>array('label'=>'增加积分','args'=>array('点数'=>array('type'=>'number'))),
                'delPoint'=>array('label'=>'扣除积分','args'=>array('点数'=>array('type'=>'number'))),
                'addCoupon'=>array('label'=>'送订单优惠券','args'=>array('优惠券'=>array('required'=>true,'type'=>'object:trading/coupon','filter'=>'cpns_type=1&ifvalid=1'))),
                'addAdvance'=>array('label'=>'增加预存款','args'=>array('金额'=>array('type'=>'money'))),
                'setTag'=>array('label'=>'设置订单标签','args'=>array('标签'=>array('required'=>true,'type'=>'object:system/tag','filter'=>'tag_type=order'))),
            );
    }

    function addPoint($data,$point){
        $order=$this->system->loadModel('trading/order');
        if($data['member_id']){
            $aOrder['member_id'] = $data['member_id'];
        }else{
            $aOrder = $order->getFieldById($data['order_id'], array('member_id', 'score_g'));
        }

        $actPoint = $this->system->loadModel('trading/memberPoint');
        $row=$actPoint->chgPoint($aOrder['member_id'], $point, 'operator_adjust',1);
    }

    function delPoint($data,$point){
        $order=$this->system->loadModel('trading/order');
        if($data['member_id']){
            $aOrder['member_id'] = $data['member_id'];
        }else{
            $aOrder = $order->getFieldById($data['order_id'], array('member_id', 'score_g'));
        }
        $point = 0 - $point;

        $actPoint = $this->system->loadModel('trading/memberPoint');
        $row=$actPoint->chgPoint($aOrder['member_id'], $point, 'operator_adjust',1);
    }

    function addCoupon($data, $coupon){
        $memberId = $data['member_id'];
        $oCoupon = $this->system->loadModel('trading/coupon');
        $oCoupon->generateCoupon($coupon, $memberId, 1, $data['order_id']);
    }

    function addAdvance($data, $money){
        $advance = $this->system->loadModel('member/advance');
        $advance->add($data['member_id'],$money,'sys:触发器',$errMsg, '', $data['order_id'] ,'sys:触发器' , '系统自动充值',1);
    }

    function setTag($data,$tagId){
        $tagM = $this->system->loadModel('system/tag');
        if (!$tagM->getTagRel($tagId,$data['order_id'])){
            $tagM->addTag($tagId,$data['order_id']);
        }
    }
}