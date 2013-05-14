<?php
class action_system{

    var $name = '系统触发器可用动作';
    var $action_for = 'system';

    function actions(){
        return array(
                'changelv'=>array('label'=>'清除缓存'),
                'a'=>array('label'=>'维护数据库'),
                'a'=>array('label'=>'重新生成商品图片'),
            );
    }

}