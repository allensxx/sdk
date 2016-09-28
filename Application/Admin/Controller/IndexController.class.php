<?php
namespace Admin\Controller;
use Admin\Model\CountsModel;
use Think\Controller;
class IndexController extends BaseController{

        private $channel;
        private $game;
        private $is_root;
        public function _initialize(){
              parent::_initialize();
              $this->channel = M("channel");
              $this->game = M("app_config");
              $this->is_root = $_SESSION['is_root'];
              if(I('get.game')||I('get.game')==="0"){
                  $_SESSION['game']=I('get.game');
              }
              $games=$this->game->order('id')->select();
              $this->assign("games",$games);
        }
      	public function index(){
            $this->display();
      	}
        public function totals(){
            if($this->is_root==""){
                $channel_id = $_SESSION['user']['channel_id'];
                $ch = M("channel")->where(array('id'=>$channel_id))->find();
                $_SESSION['bid'] = $ch['bundelid'];
                $map['channel'] = $ch['bundelid'];
            }else{
              unset($_SESSION['bid']);
              $channel = I('channel');
              if(!empty($channel)){
                $map['channel'] = $channel;
              }else{
                $map['channel'] = 'all';
              }
            }
            $stime = I("post.stime");
            $etime = I("post.etime");
            $stime1 = strtotime($stime);
            $etime1 = strtotime($etime)+86399;
            if(!empty($stime) && !empty($etime)){
                $map['date_time'] = array('between',array($stime1,$etime1));
            }else if(!empty($stime)){
                $map['date_time'] = array('egt',$stime1);
            }else if(!empty($etime)){
                $map['date_time'] = array('elt',$etime1);
            }else{
                $etime2 = time();
                $stime2 = time()-3600*24*7;
                $map['date_time'] = array('between',array($stime2,$etime2));
            }
            $count_model =new CountsModel();
            $count = $count_model->where($map)->order('id desc')->count();
            //$count = M("order")->where($map)->order('sp_order.id')->count();
            $Page       = new \Think\Page($count,3);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page -> setConfig('header','共%TOTAL_ROW%条');
            $Page -> setConfig('first','首页');
            $Page -> setConfig('last','共%TOTAL_PAGE%页');
            $Page -> setConfig('prev','上一页');
            $Page -> setConfig('next','下一页');
            $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
            $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $show       = $Page->show();// 分页显示输出
           /* $page = I("num");
            $num = 10;
            if($page>0){
              $page = $page-1;
              $count = $num*$page;
            }else{
              $count = 0;
            }*/
            $userList = $count_model->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
            //$totalpage = $count_model->where($map)->order('id desc')->count();
            //print_r($userList);
          // if(IS_AJAX){
             // $this->ajaxReturn(array('status'=>0,'list'=>$userList,'channel'=>$channel,'countpage'=>$totalpage));
          // }else{
              // echo $count_model->getLastSql();
              $this->assign("channel_list",$this->channel_list());
              $this->assign("userList",$userList);
             //  print_r($userList);
             // $this->assign("countpage",$totalpage);
             // $this->assign("countpage",$count);
              $this->assign('page',$show);// 赋值分页输出
              $this->assign("ch",$channel);
              $this->assign('channels',M('channel')->getField('bundelid,name'));
              $this->assign('where',array('stime'=>$stime,'etime'=>$etime,'channel'=>$channel));
              $this->display('total');
          // }
        }

       /**
        * 单日
        */

        public function day(){
            $field =array('id,amount,pay_date,state,uid,ordertime');
            $order_list = M("order")->join('__USER__ on __ORDER__.uid=__USER__.id')->where(array('state'=>1))->order('pay_date')->limit(0,10)->select();
            $arr = array();
            $arr1= array();
            foreach ($order_list as $key => $value) {
              if(in_array($value['username'], $arr)){
                 continue;
              }
              $arr[] = $value['username'];
              $arr1[] = $value;
            }
            //$this->assign('orderlist',$order_list);
            $this->assign('orderlist',$arr1);
            $this->display();
        }

        /**
         * 列表
         */

        public function showlist(){
            $field =array('id,amount,pay_date,state,uid,ordertime');
            $order_list = M("order")->join('__USER__ on __ORDER__.uid=__USER__.id')->where(array('state'=>1))->order('pay_date')->limit(0,10)->select();
            $arr = array();
            $arr1= array();
            foreach ($order_list as $key => $value) {
              if(in_array($value['username'], $arr)){
                 continue;
              }
              $arr[] = $value['username'];
              $arr1[] = $value;
            }
            //$this->assign('orderlist',$order_list);
            $this->assign("channel_list",$this->channel_list());
            $this->assign('orderlist',$arr1);
            $this->display('list');
        }



        /**
         * 游戏列表查看
         */

        public function conf(){
           if($id = I("get.game_id")){
               $game = $this->game->where(array('id'=>$id))->find();
               $this->assign('game',$game);// 赋值分页输出
           }
            $where=array();
            if(!empty(I("post.name"))){
               $name = I("post.name");
                $where['game_name'] = array('like','%'.$name.'%');
               $this->assign('name',$name);// 赋值分页输出
            }
            $count      = $this->game->where($where)->count();// 查询满足要求的总记录数
            $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
            $Page -> setConfig('header','共%TOTAL_ROW%条');
            $Page -> setConfig('first','首页');
            $Page -> setConfig('last','共%TOTAL_PAGE%页');
            $Page -> setConfig('prev','上一页');
            $Page -> setConfig('next','下一页');
            $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
            $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $show       = $Page->show();// 分页显示输出
            $list = $this->game->where($where)->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
            //echo  $this->game->getLastSql();
            $this->assign('list',$list);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出
            $this->display(); // 输出模板
        }

    /**
     * 添加游戏平台
     * @author by fanxiaochang
     */
    public function insert(){
        $name = I("post.game_name");
        $call_back = I("post.call_back");
        $package = I("post.package");
        if(empty($name) || empty($call_back) || empty($package)){
            $this->error("参数不能为空");
        }
        $data['game_name'] = $name;
        $data['call_back'] = $call_back;
        $data['package'] = $package;
        //$data['appid'] = $this->appid();
        $data['appid'] = $this->_getAppid();
        $data['appkey'] = $this->appkey();
        if(I("post.id")){
            $id=intval(I("post.id"));
            $result=$this->game->where('id='.$id)->save($data);
            if($result){
                $this->success("编辑成功");
            }else{
                $this->error("编辑失败");
            }
        }else{
            $result = $this->game->add($data);
            if($result){
                $this->success("添加成功");
            }else{
                $this->error("添加失败");
            }
        }
    }

    /**
     * 删除游戏
     * @author fanxiaochang
     */
    public function del_game(){
        $id = I("get.id");
        if(!empty($id)){
            $result = $this->game->where(array('id'=>$id))->delete();
            if($result){
                $this->success("删除成功");
            }else{
                $this->error("删除失败");
            }
        }
    }


    /**
     * 渠道列表查看
     *  @author fanxiaochang
     */

    public function channel(){

        $name = I("post.name");
        if(!isset($_GET['page'])){
            $page=1;
        }
        if(!empty($name)){
            $where['name'] = array('like','%'.$name.'%');
            $where['bundelid'] = array('like','%'.$name.'%');
            $where['_logic'] = 'or';
            $this->assign('name',$name);// 赋值分页输出
        }
        $count      = $this->channel->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,3);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show       = $Page->show();// 分页显示输出
        $list = $this->channel->where($where)->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign("list",$list);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }



    /**
     * @author by fanxiaochang
     * 添加游戏平台
     */
    public function insert_channel(){
        $name = I("post.name");
        $bundelid = I("post.bundelid");
        if(empty($bundelid) || empty($name)){
            $this->error("参数不能为空");
        }
        $data['name'] = $name;
        $data['bundelid'] = $bundelid;
        $result = $this->channel->add($data);
        if($result){
            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }
    }
    /**
         * 生成appid
         */

      /*  public function appid(){
          $string = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';
          $str = '';

          for ($i=0; $i < 10; $i++) {
              $str.= $string[rand(0,strlen($string)-1)];
          }

            $this->game->where(array('appid'=>$str))->find();

          return $str;
        }*/

    /**
     * 私有方法生成appid
     * @author by fanxiaochang
     * @date 2016-9-27
     */
        private function _getAppid(){
            $string = 'abcdefghkmnprstuvwxyz123456789';
            $str = '';
            for ($i=0; $i < 10; $i++) {
                $str.= $string[rand(0,strlen($string)-1)];
            }
            if(!$this->game->where(array('appid'=>$str))->find()){
                return $str;
            }else{
                $this->_getAppid();
            }
        }

        /**
         * 生成appkey
         */

        public function appkey(){
          $string = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';
          $str = '';
          for ($i=0; $i < 23; $i++) {
              $str.= $string[rand(0,strlen($string)-1)];
          }
          return $str;
        }


        /**
         * 删除
         * @author cai
         * @param id
         */

       public function del(){
           $id=I('get.id');
           $res=M('games')->where(array('id'=>$id))->delete();
           if($res){
               $this->success('删除成功','conf');
           }else{
               $this->success('删除失败','conf');
           }
       }
        /**
         * 补上首冲
         */
        public function first_pay(){
            $bid = $_SESSION['bid'];
            $stime = strtotime(I("stime"));
            $etime = strtotime(I("stime"));
            $channel = I("channel");
           /* $page = I("num");
            $num = 10;
            if($page>0){
              $page = $page-1;
              $count = $num*$page;
            }else{
              $count = 0;
            }*/
            $fpay = I("fpay");
            $map = "sp_order.state=1";
            if(!empty($stime)){
               $map .=" AND ordertime > $stime AND ordertime < $etime";
            }
            if(!empty($fpay)){
               $map .=" AND amount = $fpay";
            }
            if(!empty($bid)){
              $map .= "  AND sp_order.order_channel='$bid'";
            }else{
              if(!empty($channel)){
                $map .= "  AND sp_order.order_channel='$channel'";
              }
            }
            if($_SESSION['game']){
                $map .= "  AND sp_order.game_id=".$_SESSION['game'];
            }
            if(!empty($bid)){
              $map .= "  AND sp_user.channel='$bid'";
            }
            $map .= "  AND sp_order.first_pay=1";//表示首次充值
            $count = M("order")->where($map)->order('sp_order.id')->count();
            $Page       = new \Think\Page($count,3);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page -> setConfig('header','共%TOTAL_ROW%条');
            $Page -> setConfig('first','首页');
            $Page -> setConfig('last','共%TOTAL_PAGE%页');
            $Page -> setConfig('prev','上一页');
            $Page -> setConfig('next','下一页');
            $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
            $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $show       = $Page->show();// 分页显示输出
           // $list = $this->game->where($where)->order('id')->limit($Page->firstRow.','.$Page->listRows)->select();
           // print_r($count);
            //$list = M("order")->join('__USER__ on __ORDER__.uid = __USER__.id')->where($map)->order('sp_order.id')->limit($count,$num)->select();
            $list = M("order")->join('__USER__ on __ORDER__.uid = __USER__.id','left')->where($map)->order('sp_order.id')->limit($Page->firstRow.','.$Page->listRows)->select();
            //$countpage = M("order")->join('__USER__ on __ORDER__.uid = __USER__.id')->where($map)->count();
            //print_r($list);
          // echo M("order")->getLastSql();
            /*$arr = array();
            $u = array();
            foreach ($list as $key => $value) {
                if(in_array($value['uid'], $u)){
                    
                }else{
                  $u[] = $value['uid'];
                  $arr[] = $value;
                }
            }*/
            //  print_r($list);
              $this->assign("channel_list",$this->channel_list());
              $this->assign('page',$show);// 赋值分页输出
             // $this->assign('result',$arr);
              $this->assign('result',$list);
              //$this->assign('countpage',$countpage);
              $this->display();
        }


        public function recharge(){
            $list = $this->first_change();
            foreach ($list as $key => $value) {
                  $data['first_recharge'] = $value['amount'];
                  $where['id'] = $value['uid'];
                  M("user")->where($where)->save($data); 
                  echo M("user")->getLastSql()."<br>";
            }

           
        }


        /**
         * 添加管理员
         */

        public function adduser(){
            $username = I('username');
            $password = I('psd');
            $users = I('users');
            $level = I('level');
            if(IS_POST){
              if(!empty($username) && !empty($password)){
                $data = array(
                    'username'=>$username,
                    'password'=>md5($password),
                    'channel_id'=>$users,
                    'level'=>$level
                  );
                $result = M("admin")->add($data);
                if($result){
                  $this->success("添加成功");
                }else{
                  $this->error("添加失败");
                }
              }
            }else{
              $list = M("channel")->select();
              //print_r($list);
              $this->assign("channel",$list);
              $this->display();
            }
            
        }

        /**
         * 查询
         */

        public function query_order(){
            $bid = $_SESSION['bid'];
            $stime = strtotime(I("stime"));
            $etime = strtotime(I("stime"))+3600*24;
            $channel = I("channel");
            $userid = I("userid");
            $order = I("order");
            
            $isdown = I("isdown");

            
            $map = "state = 1";
            if(!empty($stime)){
               $map .=" AND ordertime > $stime AND ordertime < $etime";
            }

            if(!empty($userid)){
               $map .=" AND uid = $userid";
            }

            if(!empty($order)){
               $map .=" AND orderh = $order";
            }

            if(!empty($bid)){
              $map .= "  AND sp_order.order_channel='$bid'";
            }else{
              if(!empty($channel)){
                $map .= "  AND sp_order.order_channel='$channel'";
              }
            }
          if($_SESSION['game']){
             $map .= "  AND sp_order.game_id=".$_SESSION['game'];
          }
          $list = M("order")->where($map)->select();
          
          
          if($isdown==1){
          	
          	$str = "下订单时间,用户ID,订单号,名称,金额,状态,支付时间\n";
          	$str = iconv('utf-8','gb2312',$str);        	
          	
          	
          	foreach($list as $k=>$v){
          		$ordertime=date('Y-m-d H:i:s',$v['ordertime']);
          		$uid=$v['uid'];
          		
          		$orderh='order-'.$v['orderh'];
          		$tradename=iconv('utf-8','gb2312',$v['tradename']);
          		$amount=$v['amount'];
          		$state=iconv('utf-8','gb2312',$v['state']);
          		$pay_date=$v['pay_date'];
          		
          		$str .= $ordertime.",".$uid.",".$orderh.",".$tradename.",".$amount.",".$state.",".$pay_date."\n";
          		     
          	}
          	$filename = date('Ymd').'.csv'; //设置文件名
          	$this->export_csv($filename,$str);
          }else{
          	//echo M("order")->getLastSql();
          	$this->assign("channel_list",$this->channel_list());
          	$this->assign('list',$list);
          	$this->display('query');
          }          
           
        }
        
        function export_csv($filename,$data)
        {
        	header("Content-type:text/csv");
        	header("Content-Disposition:attachment;filename=".$filename);
        	header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        	header('Expires:0');
        	header('Pragma:public');
        	echo $data;
        }


        /**
         * 充值排行榜
         */

        public function recharge_top(){
            $bid = $_SESSION['bid'];
            $map = "money_top > 0";
            $stime = strtotime(I("stime"));
            $etime = strtotime(I("etime"));
            $channel = I("channel");
            if(!empty($stime)){
               $map .=" AND ordertime > $stime";
            }

            if(!empty($stime)){
               $map .=" AND ordertime < $etime";
            }
            // if(!empty($bid)){
            //     $map['channel'] = $bid;
            // } 
              if(!empty($bid)){
                $map .= "  AND sp_user.channel='$bid'";
              }else{
                if(!empty($channel)){
                  $map .= "  AND sp_user.channel='$channel'";
                }
              }
              if($_SESSION['game']){
                $map .= "  AND sp_order.game_id=".$_SESSION['game'];
              }
              $u = array();
              $data = array();
              $rank = M("user")->join('LEFT JOIN __ORDER__ on __USER__.id = __ORDER__.uid')->where($map)->order('money_top desc')->limit(50)->select();
              //echo M("user")->getLastSql();
              foreach ($rank as $key => $value) {
                      if(in_array($value['uid'], $u)){

                      }else{
                          $u[] = $value['uid'];
                          $data[] = $value;
                      }
              }
              $this->assign("channel_list",$this->channel_list());
              $this->assign("rank",$data);
              $this->display("show_list");
        }

        /**
         * 分配渠道
         */

        public function channel_list(){
            return M("channel")->select();
        }


        /**
         * 删除渠道
         */

        public function del_channel(){
            $id = I("get.id");
            if(!empty($id)){
                $result = M("channel")->where(array('id'=>$id))->delete();
                if($result){
                  $this->success("删除成功");
                }else{
                  $this->error("删除失败");
                }
            } 
        }


        /**
         * 管理员列表
         */

        public function admin_list(){
            $list = M("admin")->select();
            $this->assign('list',$list);
            $this->display();
        }

        /**
         * 删除管理员
         */

        public function admin_del(){
            $id = I("id");
            if(!empty($id)){
              $res = M("admin")->where(array('id'=>$id))->delete();
              if($res){
                $this->success("删除成功");
              }else{
                $this->error("删除失败");
              }
            }
        }


}
