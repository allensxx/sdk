<?php
namespace Api\Controller;
use Admin\Model\CountsModel;
use Think\Controller;

require_once TP_PUBLIC.'wxpay/lib/WxPay.Api.php';
require_once TP_PUBLIC.'wxpay/lib/WxPay.Notify.php';

class PayNotifyCallBack extends \WxPayNotify {
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		$notfiyOutput = array();
	
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		$out_trade_no = $data['out_trade_no'];
		//查询订单
		$order = M('order')->where(array('orderid'=>$out_trade_no))->find();
		if($order){
			//判断订单状态是否可支付
			if($order['order_state'] != 0){
				$msg = "该订单无法支付，可能您已经支付过了";
				$flag = false;
			}
			else{
				$map['id'] = $order['id'];
				$orderdata['pay_time'] = date('Y-m-d H:i:s');
				$orderdata['state'] = 1;//订单状态,待安装//待发货
				//$orderdata['transaction_id'] = $data['transaction_id'];//微信支付订单号
				$re = M('order')->where($map)->save($orderdata);
				file_put_contents("./Public/updateorder.txt", M("order")->getLastSql());
				if($re){
					update_first($re['uid'],$order['amount']); //更新首充或排行
		
					$arr['respCode'] = 200;
					$respCode = $arr['respCode'];
					$arr['respMsg'] = "交易完成";
					$respMsg = urlencode($arr['respMsg']);
					//$arr['signMethod'] = $signMethod;
					$arr['tradeType'] = "01";  //类型
					$tradeType = $arr['tradeType'];
					$arr['tradeStatus'] = "000"; //状态
					$tradeStatus = $arr['tradeStatus'];
					$arr['uid'] = $order['uid']; //用户id
					$uid = $arr['uid'];
					$arr['orderNumber'] = $order['orderh'];  //订单号
					$orderNumber = $arr['orderNumber'];
					$arr['cporderNumber'] = $order['cp_orderh'];  //订单号
					$cporderNumber = $arr['cporderNumber'];
					$arr['orderAmount'] = $order['amount']; //金额
					$orderAmount = $arr['orderAmount'];
					$arr['extInfo'] = "baecaaewfedsfafdsafdfda321AZa";
					$extInfo = $arr['extInfo'];
					$arr['payTime'] = strtotime($trade_relult['time_end']); //付款时间
					$payTime = $arr['payTime'];
					$key = "baecaaewfedsfafdsafdfda321AZa";
					$sign = $this->mksign($arr,$key);
					$url = "http://120.92.133.152:8080/g04-admin/recharge/chuangyou?";
					$url .="respCode=$respCode&respMsg=$respMsg&signMethod=md5&tradeType=$tradeType&tradeStatus=$tradeStatus&uid=$uid&orderNumber=$orderNumber&orderAmount=$orderAmount&cporderNumber=$cporderNumber&extInfo=$extInfo&payTime=$payTime&sign=$sign";
		
					$result = file_get_contents($url);
					file_put_contents("./Public/wechat_res.txt", $result);
					file_put_contents("./Public/wechat_url.txt", $url);
					$flag = true;
				}
				else{
					$flag = false;
				}
			}
		}else{
			$msg = "订单不存在";
			$flag = false;
		}
		file_put_contents("./Public/wechat_pay.txt", $return_code);
		file_put_contents("./Public/out_trade_no.txt", $out_trade_no);
		return $flag;
	}
}

class UserController extends Controller {
    private $user;
    private $order;
    private $countLogin;
    private $pay;
    private $app_config;
    private $counts;
    private $islogin;
    private $logger;
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
        $this->order = M("order");
        $this->countLogin = M("countlogin");
        $this->pay = M("countpay");
        $this->app_config = M("app_config");
        $this->counts = M("counts");
        $this->islogin = M("islogin");
        $this->user = M("user");
        
        $this->logger = M("logger");
    }

    public function index(){
        $this->display();
    }
    
    public function userlogger($uid,$channel,$islogin=0){
    	file_put_contents("./Public/freefish1.txt", $uid);
    	$tmp['uid']=$uid;
    	$tmp['channel']=$channel;
    	$tmp['islogin']=0;
    	
    	$regmdl = $this->logger->where($tmp)->find();
    	if(!$regmdl){
    		$regmdl['uid']=$uid;
    		$regmdl['channel']=$channel;
    		$regmdl['date']=date("Y-m-d");
    		$regmdl['regdate']=date("Y-m-d");
    		$regmdl['logindate']=date("Y-m-d");
    		$regmdl['dateline']=time();
    		$regmdl['islogin']=0;
    		$this->logger->add($regmdl);
    		
    	}   	
    	
    	if($islogin){  //登录    	
    		$tmp['date']=date("Y-m-d");
    		$tmp['islogin']=1;
    		$log = $this->logger->where($tmp)->find();
    		if(!$log){
    			$data['uid']=$uid;
    			$data['channel']=$channel;
    			$data['date']=date("Y-m-d");
    			$data['regdate']=$regmdl['regdate'];
    			$data['logindate']=date("Y-m-d");
    			$data['dateline']=time();
    			$data['islogin']=1;
    			$this->logger->add($data);
    		}
    	}
    	
    	
    	
    }

    /**
     * 手机注册
     * 名称register 参数userName、 passWord
     */

    public function register(){
        $user = M("user");
        $userchannel = M("userchannel");
    	$userName = I("post.username");
        $passWord = I("post.password");
        $token = I("post.token");
        $channel = I('post.channel');
        $date = date("Y-m-d");

        $result = $user->where(array('username'=>$userName))->find();

        if($result){
            $this->ajaxReturn(array('status'=>1,'msg'=>'用户名已经存在！'));
        }

        if($token!=md5('chuangyou')){
            $this->ajaxReturn(array('status'=>1,'msg'=>'token is error'));
        }
        if(empty($userName) || empty($passWord)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'用户名和密码不能为空'));
        }
        $data['username'] = $userName;
        $data['password'] = md5($passWord);
        $data['regTime'] = date("Y-m-d H:i:s");
        $data['channel'] = $channel;
        if($uid=$user->add($data)){     	
        	
        	$datachannel['uid']=$uid;
        	$datachannel['channel']=$channel;
        	$datachannel['regTime'] = $data['regTime'];
        	$userchannel->add($datachannel);
        	
        	$this->userlogger($uid,$channel,0);
        	
        	
            file_put_contents("./Public/register.txt", $user->getLastSql());
            $this->isdata($date,'register',$channel); //添加数据
            //$this->data_inc($date,'register',$channel);
            $this->ajaxReturn(array('status'=>0,'msg'=>'ok'));
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>'添加失败'));
        }

    }

    /**
     * 手机注册
     */

    public function phoneRegister(){
    	$phoneNumber = I("post.phoneNumber");
    	
    }

    /**
     * 取出appid 
     */

    public function getAppInfo($gameid){
        if(!empty($gameid)):
         $info = $this->app_config->where(array('appid'=>$gameid))->find();
         return $info;
        endif;
    }   


    /**
     * sdk初始化
     */

    public function init_api(){
        $token = I("post.token");
        $gamekey = I("post.gamekey");
        $gameid = I("post.gameid");
        $token1 = MD5('chuangyou');
        $appInfo = $this->getAppInfo($gameid);
        if(empty($token) || $token1!=$token){
            $this->ajaxReturn(array('status'=>1,'msg'=>'token error'));
        }

        if(empty($gamekey) || $gamekey!=$appInfo['appkey']){
            $this->ajaxReturn(array('status'=>1,'msg'=>'key error'));
        }

        if(empty($gameid) || $gameid!=$appInfo['appid']){
            $this->ajaxReturn(array('status'=>1,'msg'=>'gameid error'));
        }

        if($gameid==$appInfo['appid'] && $gamekey==$appInfo['appkey']){
            $this->active();
            $this->ajaxReturn(array('status'=>0,'msg'=>'ok'));
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>'gameidnot'));
        }
    } 


    /**
      * 发送验证码
      */ 

    public function sendMsg($to,$datas,$tempId){
        include_once TP_PATH.'Library/Vendor/sendMsg/Send.class.php';
        $s = new \SendMsg();
        return $s->send($to,$datas,$tempId);
    } 

    /**
     * 发送验证码
     */

    public function sendCode($phone=''){
        if($phone==''){
         $phone = I("post.phone");
        }
        if(empty($phone)){
            $this->ajaxReturn(array("status"=>1,"msg"=>"手机号码不能为空"));
        }

        $datas = rand(100000,999999);
        $_SESSION['code'] = $datas;
        $code = $this->sendMsg($phone,array($datas,'2'),C("tempId"));
        if($code['status']==0){
            $this->ajaxReturn(array("status"=>0,"msg"=>"验证码已经发送！"));
        }else{
            $this->ajaxReturn(array("status"=>1,"msg"=>"验证码发送失败！"));
        }
    }

    /**
     * 发送邮箱验证码
     */

    public function sendEmailCode($email){
        if(empty($email)){
            $this->ajaxReturn(array("status"=>1,"msg"=>"邮箱不能为空"));
        }else{
            $subject = "找回密码验证码";
            $datas = rand(100000,999999);
            $_SESSION['code'] = $datas;
            $body = "找回密码你的验证码是{$datas}"; 
            $res = sendEmail($email,$subject,$body);
            if($res['error']==0){
                $this->ajaxReturn(array("status"=>0,"msg"=>"验证码已经发送！"));
            }else{
                $this->ajaxReturn(array("status"=>1,"msg"=>"验证码发送失败！"));
            }
        }
    }


    /**
     * 手机注册
     */

    public function regphone(){
        $user = M("user");
        $phone = I("post.phone");
        $passWord = I("post.password");
        $code = I("post.code");
        $token = I("post.token");
        $channel = I('post.channel');

        $result = $user->where(array('username'=>$phone))->find();

        if($result){
            $this->ajaxReturn(array('status'=>1,'msg'=>'手机已经存在！'));
        }

        if(empty($code) || $_SESSION['code']!=$code){
            $this->ajaxReturn(array('status'=>1,'msg'=>'验证码不对'));
        }

        if($token!=md5('chuangyou')){
            $this->ajaxReturn(array('status'=>1,'msg'=>'token is error'));
        }
        if(empty($phone) || empty($passWord)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'手机和密码不能为空'));
        }
        $data['username'] = $phone;
        $data['password'] = md5($passWord);
        $data['channel'] = $channel;
        $data['regTime'] = date("Y-m-d H:i:s");
        if($user->add($data)){
            $this->isdata($date,'register',$channel); //添加数据
            //$this->data_inc('','register',$channel);
            $this->ajaxReturn(array('status'=>0,'msg'=>'ok'));
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>'添加失败'));
        }
    }

    /**
     * 快速登录
     */
    public function fastLogin(){
        $token = I("post.token");
        $token1 = MD5('chuangyou');
        if(empty($token) || $token1!=$token){
            $this->ajaxReturn(array('status'=>1,'msg'=>'token error'));
        }
         $channel = I('post.channel');
         $username = uniqid();
         $password = Md5(123456);
         $recover = uniqid();
         $data['username'] = $username;
         $data['password'] = $password;
         $data['channel'] = $channel;
         $data['recover'] = $recover;
         $date = date("Y-m-d");
         $data['regTime'] = $date;
         $result = $this->user->add($data); 
          file_put_contents("./Public/register1.txt", $this->user->getLastSql());
        if($result){
            $this->isdata($date,'register',$channel); //添加数据
            //$this->data_inc($date,'register',$channel);
            $data['username'] = $username;
            $data['password'] = $password;
            //查找用户
            $result = $this->user->where($data)->find();
            $where['username'] = $username;
            $data1['loginTime'] = $today;
            //更新表
            $this->user->where($where)->save($data1);
            if($result['logintime']!=$today){  //判断有没有登录
                $this->isdata($date,'login',$result['channel']); //添加数据
            }
            //$this->data_inc($date,'login',$channel);
            $data1['username'] = $result['username'];
            $data1['password'] = $result['password'];
            $key = "test";
            $sign = $this->mksign($data1,$key);
            $result['sign'] = $sign;
            $result['key'] = $key;
            $result['pass'] = 123456;
            $this->ajaxReturn(array('status'=>0,'msg'=>$result));
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>'添加失败'));
        }
    }

    /**
     * 临时用户找回密码
     * @param username string
     * @param recover string
     * @return status
     */

    public function findTempPass(){
         $username = I("post.username");
         $recover = I("post.recover");
         if(empty($username)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'用户名不能为空'));
         }

         if(empty($recover)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'恢复码不能为空'));
         }
         $res = $this->user->where(array('username'=>$username,'recover'=>$recover))->find();
         if($res){
            $arr['freeze'] = 1;
            $this->user->where(array('username'=>$username,'recover'=>$recover))->save($arr);
            $this->ajaxReturn(array('status'=>0,'msg'=>'ok'));
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>'用户名或恢复码错误'));
        }
    }

    /**
     * 正常用户找回密码
     * @param phone|email string
     * @return status
     */

    public function findPass(){
        $phone = I("post.phone");
        if(empty($phone)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'手机或邮箱不能为空'));
        }
        if(isphone($phone)){
            $where['phone'] = $phone;
            $type="手机号码";
        }else{
            $where['email'] = $email;
            $type="邮箱";
        }
        $res = $this->user->where($where)->find();
        if($res){
            if(isphone($phone)){
                $this->sendCode($phone); //发送手机验证码
            }else{
                $this->sendEmailCode($phone); //发送邮箱验证码
            }
            
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>$type."不存在"));
        }
    }

    /**
     * 正常用户验证验证码是否正确
     */
    public function isCode(){
        $username = I("post.username");
        $code = I("post.code");
        if(empty($username)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'用户名不能为空'));
        }
        if(empty($code) || $_SESSION['code']!=$code){
            $this->ajaxReturn(array('status'=>1,'msg'=>'验证码不正确'));
        }
        $info = $this->user->where(array('username'=>$username))->find();
        if($info){
            $data['freeze'] = 1;
            $res = $this->user->where(array('username'=>$username))->save($data);
             if($res){
                    $this->ajaxReturn(array('status'=>0,'msg'=>'ok'));
             }else{
                    $this->ajaxReturn(array('status'=>1,'msg'=>'重置密码失败'));
             }
        }
    }

    /**
     * 正常用户修改密码
     */

    public function updatePass(){
        $username = I("post.username");
        $password = I("post.password");
        $data['password'] = $password;
        $info = $this->user->where(array('username'=>$username))->find();
        if($info['freeze']!=1){
             $this->ajaxReturn(array('status'=>1,'msg'=>'先找回密码'));
        }
        $result = $this->user->where(array('username'=>$username))->save($data);
        if($result){
            $this->ajaxReturn(array('status'=>0,'msg'=>'ok'));
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>'重置密码失败'));
        }
    }


    /**
     * 临时用户修改密码
     */

    public function modPass(){
         $username = I("post.username");
         $recover = I("post.recover");
         $password = I("post.password");
         if(empty($username)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'用户名不能为空'));
         }
         if(empty($recover)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'恢复码不能为空'));
         }
        
         $data['password'] = MD5($password);
         $res = $this->user->where(array('username'=>$username,'recover'=>$recover))->save($data);
         if($res){
            $this->ajaxReturn(array('status'=>0,'msg'=>'ok'));
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>'重置密码失败'));
        }

    }

    /**
     * 激活
     */
    public function active(){
        $apparatus = M("apparatus"); 
        $deviceid = I("post.deviceid");
        $channel = I("post.channel");
        /*if(!empty($channel) && !empty($deviceid)){
            $result = $apparatus->where(array('deviceid'=>$deviceid,'channel'=>$channel))->find();
            if(empty($result)){
                $data['deviceid'] = $deviceid;
                $data['channel'] = $channel;
                $res = $apparatus->add($data);
                if($res){
                    $this->ajaxReturn(array('status'=>0,'msg'=>'ok'));
                }
                
            }else{
                $this->ajaxReturn(array('status'=>0,'msg'=>'ok'));
            }
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>'error'));
        }*/
        //初始化时调用
        if(!empty($channel) && !empty($deviceid)){
            //查询设备号是否存在
            $result = $apparatus->where(array('deviceid'=>$deviceid))->find();
            if(empty($result)){
                //不存在添加记录
                $data['deviceid'] = $deviceid;
                $data['channel'] = $channel;
                $apparatus->add($data);
                $date = date('Y-m-d');
                //同时当日新增设备号+1
                $this->isdata($date,'active',$channel,'');
            }
        }
    }

    
    public function donechannel($result,$today,$channel){
    	$userchannel = M("userchannel");
    	$data['uid'] = $result['id'];
    	$data['channel'] = $channel;
    	$channelMDL = $userchannel->where($data)->find();
    	if(!$channelMDL){
    		$data['regTime'] = $result['regtime'];
    		$data['loginTime'] = $today;
    		$data['id'] = $userchannel->add($data);    		
    		file_put_contents("./Public/freefish.txt", print_r($result,1)."-|-".$today);
    	}else{
    		$data['id']=$channelMDL['id'];
    		$data['regTime'] = $channelMDL['regtime'];
    		$data['loginTime'] = $today;	
    		
    	}   	
    	
    	
    	return $data;
    }

    /**
     * 登陆api
     */

    public function login(){
    	$userchannel = M("userchannel");
        $user = M("user");
        $username = I("post.username");
        $password = I("post.password");
        $token = I("post.token");
        $appid = I("post.appid");
        $key = I("post.key");
        $uid = I("post.uid");
        $channel = I('post.channel');
        $date = date("Y-m-d");
        $appInfo = $this->getAppInfo($appid);
        //游戏方二次验证
        if(!empty($appid)){
            if($appid!=$appInfo['appid']){
                $this->ajaxReturn(array('status'=>1,'msg'=>'Appid error'));
            }

            if($key!=$appInfo['appkey']){
                $this->ajaxReturn(array('status'=>1,'msg'=>'key error'));
            }

            if(empty($uid)){
                $this->ajaxReturn(array('status'=>1,'msg'=>'uid is null'));
            }

            $result = $user->where(array('id'=>$uid))->find();

            if($result){
                $this->ajaxReturn(array('status'=>0,'msg'=>'ok'));
            }else{
                $this->ajaxReturn(array('status'=>1,'msg'=>'user is not exists'));
            }

        //sdk验证
        }else{

            if(empty($username) || empty($password)){
                $this->ajaxReturn(array('status'=>1,'msg'=>'用户名和密码不能为空'));
            }

            $data['username'] = $username;
            $data['password'] = md5($password);
            $key = "test";
            $sign = $this->mksign($data,$key);
            $result = $user->where($data)->find();
            if($result){                       	
            	
                $today = date("Y-m-d");
                
                $this->userlogger($result['id'],$channel,1);
                
                
               $_data = $this->donechannel($result,$today,$channel);         
                if($_data){
                	$result['sign'] = $sign;
                	$result['key'] = $key;
                	if($_data['logintime']!=$today){  //判断有没有登录
                		$this->isdata($date,'login',$channel); //添加数据
                		$where['username'] = $username;
                		$data1['loginTime'] = $today;
                		$user->where($where)->save($data1);
                		$userchannel->save($_data);
                		
                		//用户的注册时间
                		$yesterday = $this->yesterday($today);
                		$sanday = $this->sanday($today);
                		$qiday = $this->qiday($today);
                		$shiwuday = $this->shiwuday($today);
                		file_put_contents("./Public/day.txt", $result['regtime']."-|-".$yesterday);
                		if($result['regtime']==$yesterday):
                		file_put_contents("./Public/day2.txt", $result['regtime']."-|-".$yesterday);
                		$this->updateciliu($result['regtime'],'ciliu',$channel);//用户昨天前注册的，今日登录的时候就是1天前存留用户
                		endif;
                		if($result['regtime']==$sanday):
                		file_put_contents("./Public/day.txt", $result['regtime']."".$sanday);
                		$this->updateciliu($result['regtime'],'sanliu',$channel);//用户3天前注册的，今日登录的时候就是3天前存留用户
                		endif;
                	
                		if($result['regtime']==$qiday):
                		file_put_contents("./Public/day.txt", $result['regtime']."".$qiday);
                		$this->updateciliu($result['regtime'],'qiliu',$channel);//用户7天前注册的，今日登录的时候就是7天前存留用户
                		endif;
                	
                		if($result['regtime']==$shiwuday):
                		file_put_contents("./Public/day.txt", $result['regtime']."".$shiwuday);
                		$this->updateciliu($result['regtime'],'shiwuliu',$channel);//用户15天前注册的，今日登录的时候就是15天前存留用户
                		endif;
                		//$this->data_inc($date,'login',$channel);
                	}   //判断有没有登录
                	$this->ajaxReturn(array('status'=>0,'msg'=>$result));
                }
                 
            }else{
                $this->ajaxReturn(array('status'=>1,'msg'=>'登陆失败,用户名和密码错误'));
            }
        }

    }

    /**
     * 拼接昨天的日期
     */
    public function yesterday($day){
        $time = strtotime($day) - 3600*24;
        return  date('Y-m-d',$time);
    }


    public function test(){
        $date = date("Y-m-d");
        echo $this->yesterday($date);
    }


    /**
     * 拼接昨天的日期
     */
    public function sanday($day){
       $time = strtotime($day) - 3600*24*3;
        return  date('Y-m-d',$time);
    }


    /**
     * 拼接昨天的日期
     */
    public function qiday($day){
        $time = strtotime($day) - 3600*24*7;
        return  date('Y-m-d',$time);
    }


    /**
     * 拼接昨天的日期
     */
    public function shiwuday($day){
        $time = strtotime($day) - 3600*24*15;
        return  date('Y-m-d',$time);
    }


    /**
     * 更新次留
     */

    /*public function updateciliu($date,$fields){
        $data[$fields] = array("exp","$fields+1");
        $this->counts->where(array('date'=>$date))->save($data);
        file_put_contents("./Public/ciliu.txt", $this->counts->getLastSql());
    }*/
    /**
     * 重写更新留存方法
     * @param $date
     * @param $fields
     */
    public function updateciliu($date,$field,$channel){
        $count_model = new CountsModel();
        $count_model->where(array('channel'=>'all','date'=>$date))->setInc($field,1);
        $count_model->where(array('channel'=>$channel,'date'=>$date))->setInc($field,1);
        file_put_contents("./Public/count_ciliu.txt", $count_model->getLastSql());
    }


    /**
     * 判断是否有数据
     */

    public function isdata($date,$fields,$channel,$orderAmount=''){
        if($date=="" || empty($date)){
            $date = date("Y-m-d");
        }
        $data1['date'] = $date;
        $data1['channel'] = $channel;
        $data1['date_time'] = time();
        if(!empty($orderAmount)){
             $data1[$fields] = $orderAmount;
        }else{
             $data1[$fields] = 1;
        }
         /**
          * 不管有没有渠道号都添加为all
          */
         if($this->counts->where(array('date'=>$date,'channel'=>'all'))->find()){
                $this->counts->where(array('channel'=>'all','date'=>$date))->setInc($fields,1);
                file_put_contents("./Public/all.txt", $this->counts->getLastSql());
          }else{
                if(!empty($orderAmount)){
                     $data3[$fields] = $orderAmount;
                }else{
                     $data3[$fields] = 1;
                }
                $data3['date'] = $date;
                $data3['channel'] = 'all';
                $data3['date_time'] = time();
                $this->counts->add($data3);
                file_put_contents("./Public/all.txt", $this->counts->getLastSql());
        }
        $res = $this->counts->where(array('date'=>$date,'channel'=>$channel))->find();
        if($res){
             if(!empty($orderAmount)){
                    $data2[$fields] = array("exp","$fields+$orderAmount");// 如果有了数据，就注册加1
             }else{
                    $data2[$fields] = array("exp","$fields+1");// 如果有了数据，就注册加1
             }
             $this->counts->where(array('date'=>$date,'channel'=>$channel))->save($data2);
             file_put_contents("./Public/data.txt", $this->counts->getLastSql());
        }else{
             $this->counts->add($data1);
             file_put_contents("./Public/data1.txt", $this->counts->getLastSql());
        }
    }

    /**
     * 数据递增方法
     * @param $date             =       日期
     * @param $field            =       递增字段
     * @param $channel          =       对应渠道
     */
    public function data_inc($date,$field,$channel){
        if($date=="" || empty($date)){
            $date = date("Y-m-d");
        }
        $count_model = new CountsModel();
        //$count_model->where(array('channel'=>'all','date'=>$date))->setInc($field,1);
        $count_model->where(array('channel'=>$channel,'date'=>$date))->setInc($field,1);
    }


    /**
     * 签名
     */

    public  function mksign($data,$key){
            ksort($data);
            $str = '';
            foreach ($data as $k => $v) {
                if($k=='sign' || $k=='sign_type'){
                    continue;
                }
                if($v===null){
                    $str.=$k.'=&';
                }else{
                    $str.=$k.'='.$v.'&';
                }
            }
            $str = trim($str,'&');
            return md5($str.$key);
    }

    /**
     * 订单api
     */

    public function order(){
        $uid = I("post.uid");
        $gameid = I("post.gameid");
        $money = I("post.money");
        $orderid = I("post.orderid");
        $Tradename = I("post.tradename");
        $paystyle = I("post.paystyle");
        if(empty($uid) || empty($gameid) || empty($money) || empty($orderid) || empty($paystyle)){
            $this->ajaxReturn(array('status'=>1,'msg'=>'所有的都不能为空'));
        }
        $_SESSION['uid'] = $uid;
        $data['uid'] = $uid;
        $data['orderh'] = build_order_no();
        $data['cp_orderh'] = $orderid;
        $data['amount'] = $money;
        $data['Tradename'] = $Tradename;
        $data['game_id'] = $gameid;
        $data['pay_type'] = $paystyle;
        $data['ordertime'] = time();
        $data['state'] = 0;
        $time = time();
        $data['orderid'] = md5($orderid.$time);
        $exist = $this->order->where(['orderid'=>$data['orderid']])->find();
        $first_order = $this->order->where(['uid'=>$data['uid']])->find();
        if(empty($first_order)){
            $data['first_pay']=1;
        }
        if ($exist) { //重复订单
        	$this->ajaxReturn(['status'=>1,'msg'=>'订单重复']);
        }
        $result = $this->order->add($data);
        if($result){
            $msg['key'] = C("key");
            $msg['order'] = $data['orderid'];
            $this->ajaxReturn(array('status'=>0,'msg'=>$msg));
        }else{
            $this->ajaxReturn(array('status'=>1,'msg'=>'添加失败'));
        }
    }


    /**
     * ordersuccess
     * 支付宝成功通知
     */

    public function ordersuccess(){
            $conf = TP_PUBLIC."alipay/alipay.config.php";
            $alipay_notify = TP_PUBLIC."alipay/lib/alipay_notify.class.php";
            require_once($conf);
            require_once($alipay_notify);
            //计算得出通知验证结果
            $alipayNotify = new \AlipayNotify($alipay_config);
            $verify_result = $alipayNotify->verifyNotify();

            if($verify_result) {//验证成功
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //请在这里加上商户的业务逻辑程序代
        
                //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
                
                //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
                
                //商户订单号

                $out_trade_no = $_POST['out_trade_no']; //唯一的订单号

                //支付宝交易号

                $trade_no = $_POST['trade_no'];   //支付宝交易号

                //交易状态
                $trade_status = $_POST['trade_status'];


                if($_POST['trade_status'] == 'TRADE_FINISHED') {

                    //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                      
                    //注意：
                    //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
                    //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的

                    //调试用，写文本函数记录程序运行情况是否正常
                    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                }else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                    
                    //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                       
                    //注意：
                    //付款完成后，支付宝系统发送该交易状态通知
                    //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的

                    //调试用，写文本函数记录程序运行情况是否正常
                    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                    $where['orderid'] = $_POST['out_trade_no'];
                    $out_trade_no = $_POST['out_trade_no'];
                    $data['state'] = 1; 
                    $data['pay_time'] = $_POST['gmt_payment']; 
                    $data['trade_no'] = $_POST['trade_no']; 
                    $this->order->where($where)->save($data);
                    $res = $this->order->where(array('orderid'=>$out_trade_no))->find();
                    /**/
                    $arr = [];
                    $arr['respCode'] = 200;
                    $respCode = $arr['respCode'];
                    $arr['respMsg'] = "交易完成";
                    $respMsg = urlencode($arr['respMsg']);
                    //$arr['signMethod'] = $signMethod;
                    $arr['tradeType'] = "01";  //类型
                    $tradeType = $arr['tradeType'];
                    $arr['tradeStatus'] = "000"; //状态
                    $tradeStatus = $arr['tradeStatus'];
                    $arr['uid'] = $res['uid']; //用户id
                    $uid = $arr['uid'];
                    $arr['orderNumber'] = $res['orderh'];  //订单号
                    $orderNumber = $arr['orderNumber'];
                    $arr['cporderNumber'] = $res['cp_orderh'];  //订单号
                    $cporderNumber = $arr['cporderNumber'];
                    $arr['orderAmount'] = $_POST['total_fee']; //金额
                    $orderAmount = $arr['orderAmount'];
                    $arr['extInfo'] = "baecaaewfedsfafdsafdfda321AZa";
                    $extInfo = $arr['extInfo'];
                    $arr['payTime'] = strtotime($_POST['gmt_payment']); //付款时间 
                    $payTime = $arr['payTime'];
                    $key = "baecaaewfedsfafdsafdfda321AZa";
                    $sign = $this->mksign($arr,$key);
                    $url = "http://120.92.133.152:8080/g04-admin/recharge/chuangyou?";
                    $url .="respCode=$respCode&respMsg=$respMsg&signMethod=md5&tradeType=$tradeType&tradeStatus=$tradeStatus&uid=$uid&orderNumber=$orderNumber&orderAmount=$orderAmount&cporderNumber=$cporderNumber&extInfo=$extInfo&payTime=$payTime&sign=$sign";
            
                    $result = file_get_contents($url);
                    $date = date("Y-m-d");
                    $this->isdata($date,'money','',$orderAmount);
                    update_first($re['uid'],$_POST['total_fee']); //更新首充或排行
                    file_put_contents("./Public/alipay_url.txt", $url);
                    file_put_contents("./Public/alipay_res.txt", $result);
                }

                //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
               
                echo "success";     //请不要修改或删除
                
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            }else{
                //验证失败13093760825
                 echo "fail";

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
        }


        /**
         * 统计登录
         */

        public function countLogin(){
            $uid = I("post.uid");
            $gameid = I("post.gameid");
            $time = I("post.time");
            $token = I("post.token");
            if(empty($uid) || empty($gameid) || empty($time)){
                $this->ajaxReturn(array('status'=>1,'msg'=>'所有的值都不能为空'));
            }
            if($token!=md5('chuangyou')){
                $this->ajaxReturn(array('status'=>1,'msg'=>'token is error'));
            }
            $data['uid'] = $uid;
            $data['game_id'] = $gameid;
            $data['login_time'] = $time;
            $result = $this->countLogin->add($data);
            if($result){
                $this->ajaxReturn(array('status'=>0,'msg'=>'添加成功'));
            }else{
                $this->ajaxReturn(array('status'=>1,'msg'=>'添加失败'));
            }
        }

        /**
         * 支付统计
         */

        public function countPay(){
            $uid = I("post.uid");
            $gameid = I("post.gameid");
            $time = I("post.time");
            $paystyle = I("post.paystyle");
            $orderid = I("post.orderid");
            $amount = I("post.amount");
            $token = I("post.token");
            $channel = I('post.channel');
            if(empty($uid) || empty($gameid) || empty($time) || empty($paystyle) || empty($orderid) || empty($amount)){
                $this->ajaxReturn(array('status'=>1,'msg'=>'所有的值都不能为空'));
            }
            if($token!=md5('chuangyou')){
                $this->ajaxReturn(array('status'=>1,'msg'=>'token is error'));
            }
            $data['uid'] = $uid;
            $data['channel'] = $channel;
            $data['game_id'] = $gameid;
            $data['login_time'] = $time;
            $data['paystyle'] = $paystyle;
            $data['orderid'] = $orderid;
            $data['amount'] = $amount;
            $result = $this->pay->add($data);
            if($result){
                $this->ajaxReturn(array('status'=>0,'msg'=>'添加成功'));
            }else{
                $this->ajaxReturn(array('status'=>1,'msg'=>'添加失败'));
            }
        }


       public function curlget($url){
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
            $output = curl_exec($ch);  
            /* 写入文件 */  
            // $fh = fopen("out.html", 'w') ;  
            // fwrite($fh, $output) ;  
            //fclose($fh) ; 
            return $output;  
        }


        /**
         * 补上首冲
         */

        public function first_change(){
            $list = M("order")->select();
            echo '<pre>';
            print_r($list);    
        }


        /**
         * 微信支付通知
         */

        public function wechat_pay(){
        	//微信支付异步回调重写
        	$notify = new PayNotifyCallBack();
        	$notify->Handle(false);
        
	        //获取通知的数据
	        /* $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
	        $trade_relult = $this->Init($xml);
	        
	        $return_code = $trade_relult['return_code'];
	
		    if($return_code=="SUCCESS"){
		        $out_trade_no = $trade_relult['out_trade_no'];
		            //查询订单
		            $order = M('order')->where(array('orderid'=>$out_trade_no))->find();
		            if($order){
		                //判断订单状态是否可支付
		                if($order['order_state'] != 0){
		                    echo "该订单无法支付，可能您已经支付过了";
		                }else{
		                    $map['id'] = $order['id'];
		                    $orderdata['pay_time'] = date('Y-m-d H:i:s');
		                    $orderdata['state'] = 1;//订单状态,待安装//待发货
		                    //$orderdata['transaction_id'] = $data['transaction_id'];//微信支付订单号
		                    $re = M('order')->where($map)->save($orderdata);
		                    file_put_contents("./Public/updateorder.txt", M("order")->getLastSql());
		                    if($re){
		                        update_first($re['uid'],$order['amount']); //更新首充或排行
		
		                        $arr['respCode'] = 200;
		                        $respCode = $arr['respCode'];
		                        $arr['respMsg'] = "交易完成";
		                        $respMsg = urlencode($arr['respMsg']);
		                        //$arr['signMethod'] = $signMethod;
		                        $arr['tradeType'] = "01";  //类型
		                        $tradeType = $arr['tradeType'];
		                        $arr['tradeStatus'] = "000"; //状态
		                        $tradeStatus = $arr['tradeStatus'];
		                        $arr['uid'] = $order['uid']; //用户id
		                        $uid = $arr['uid'];
		                        $arr['orderNumber'] = $order['orderh'];  //订单号
		                        $orderNumber = $arr['orderNumber'];
		                        $arr['cporderNumber'] = $order['cp_orderh'];  //订单号
		                        $cporderNumber = $arr['cporderNumber'];
		                        $arr['orderAmount'] = $order['amount']; //金额
		                        $orderAmount = $arr['orderAmount'];
		                        $arr['extInfo'] = "baecaaewfedsfafdsafdfda321AZa";
		                        $extInfo = $arr['extInfo'];
		                        $arr['payTime'] = strtotime($trade_relult['time_end']); //付款时间 
		                        $payTime = $arr['payTime'];
		                        $key = "baecaaewfedsfafdsafdfda321AZa";
		                        $sign = $this->mksign($arr,$key);
		                        $url = "http://120.92.133.152:8080/g04-admin/recharge/chuangyou?";
		                        $url .="respCode=$respCode&respMsg=$respMsg&signMethod=md5&tradeType=$tradeType&tradeStatus=$tradeStatus&uid=$uid&orderNumber=$orderNumber&orderAmount=$orderAmount&cporderNumber=$cporderNumber&extInfo=$extInfo&payTime=$payTime&sign=$sign";
		                    
		                        $result = file_get_contents($url);
		                        file_put_contents("./Public/wechat_res.txt", $result);
		                        file_put_contents("./Public/wechat_url.txt", $url);
		                        echo "SUCCESS";
		                    }else{
		                        echo "ERROR";
		                    }
		                }
		            }else{
		                echo "订单不存在";
		            }  
		            file_put_contents("./Public/wechat_pay.txt", $return_code);
		            file_put_contents("./Public/out_trade_no.txt", $out_trade_no);
		    }else{
		            echo "ERROR";
		    } */
    	}




        /**
         * 生成签名
         * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
         */
        public function MakeSign($input){
            //签名步骤一：按字典序排序参数
            ksort($input);
            $string = $this->ToUrlParams($input);
            //签名步骤二：在string后加入KEY
            $string = $string . "&key=".C(WEIAPPKEY);
            //签名步骤三：MD5加密
            $string = md5($string);
            //签名步骤四：所有字符转为大写
            $result = strtoupper($string);
            return $result;
        }

        /**
         * 格式化参数格式化成url参数
         */
        public function ToUrlParams($array){
            $buff = "";
            foreach ($array as $k => $v)
            {
                if($k != "sign" && $v != "" && !is_array($v)){
                    $buff .= $k . "=" . $v . "&";
                }
            }
            
            $buff = trim($buff, "&");
            return $buff;
        }
        /**
         * 
         * 产生随机字符串，不长于32位
         * @param int $length
         * @return 产生的随机字符串
         */
        public function getNonceStr($length = 32){
            $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
            $str ="";
            for ( $i = 0; $i < $length; $i++ )  {  
                $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
            } 
            return $str;
        }


        /**
         * 将xml转为array
         * @param string $xml
         * @throws WxPayException
         */
        public function Init($xml){
            $fromxml = $this->FromXml($xml);
            if($fromxml['return_code'] != 'SUCCESS'){
                 return $fromxml;
            }
            //验证签名
            $sign = $this->MakeSign($fromxml);
            if($sign != $fromxml['sign']){
                return "签名错误";
            }
            return $fromxml;
        }

        /**
         * 将xml转为array
         * @param string $xml
         * @throws WxPayException
         */
        public function FromXml($xml){
            if(!$xml){
                return "xml数据异常！";
            }
            //将XML转为array
            //禁止引用外部xml实体
            libxml_disable_entity_loader(true);
            $aa = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);      
            return $aa;
        }

}