<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/12
 * Time: 11:22
 */

namespace Admin\Model;


use Think\Model;

class CountsModel extends Model
{
    protected $tableName = 'counts';

    protected function _initialize() {
        if(!M('counts')->where(array('date'=>date('Y-m-d')))->select()){
            $this->init_today();
        }
    }

    public function init_today(){
        $channel_map = M('channel')->getField('bundelid',true);

        array_unshift($channel_map,'all');
        $data_list = array();
        $time = time();
        foreach($channel_map as $value){
            $data_list[] = array(
                'date'=>date('Y-m-d',$time),
                'down'=>0,
                'register'=>0,
                'login'=>0,
                'viplogin'=>0,
                'money'=>0,
                'ciliu'=>0,
                'sanliu'=>0,
                'qiliu'=>0,
                'shiwuliu'=>0,
                'date_time'=>$time,
                'channel'=>$value,
                'active'=>0
            );
        }

        M('counts')->addAll($data_list);
    }
}