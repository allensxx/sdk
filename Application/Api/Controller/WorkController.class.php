<?php
namespace Api\Controller;

#define('THINK_MODE', 'cli');
use Think\Controller;

class WorkController extends Controller {
	private $counts;
	private $logger;
	private $objchannel;
	public function _initialize(){
		$this->counts = M("counts1");  //正式改成counts
		$this->logger = M("logger");
		
		$this->objchannel = M("channel");
	}
	
	public function index(){
		$g_date = I("get.date");
		$time = time() - 3600*24;
		$date = date('Y-m-d',$time);
		
		
		if($g_date && strtotime($g_date)<strtotime($date)){
			$date = $g_date;
		}
		
		
		
		$c_list = $this->objchannel->select();
		foreach($c_list as $val){
			
			//echo $this->getcount(1,$date,$val['bundelid']).'<br>';
			
			$data['channel'] = $val['bundelid'];
			$data['date'] = $date;

			$this->done($data);
			
		}
		
		
		//file_put_contents("./Public/fish.txt", 'hello1');
	}
	
	public function done($data){
		
		//计算当天注册
		$data['islogin']='0';
		$regcount = $this->logger->where($data)->count();
		
		$data['islogin']='1';
		$logincount = $this->logger->where($data)->count();
		
		$data['islogin']='2';  //下载
		$downcount = $this->logger->where($data)->count();
		
		$data['islogin']='3';  //激活
		$activecount = $this->logger->where($data)->count();
		
		$list = $this->counts->where($data)->find();
		if($list)return true;
		
		$tmp['date_time']=time();
		$tmp['down']=$downcount;
		$tmp['active']=$activecount;
		$tmp['vipLogin']=0;
		$tmp['money']=0;
		
		$tmp['ciliu']=$this->getcount(1,$data['date'],$data['channel']);
		$tmp['sanliu']=$this->getcount(3,$data['date'],$data['channel']);
		$tmp['qiliu']=$this->getcount(7,$data['date'],$data['channel']);
		$tmp['shiwuliu']=$this->getcount(15,$data['date'],$data['channel']);
		
		$tmp['date']=$data['date'];
		$tmp['channel']=$data['channel'];
		$tmp['register']=$regcount;
		$tmp['login']=$logincount;
		
		$this->counts->add($tmp);
		
		//当天登录
		
	}
	
	function getcount($day,$date,$channel){
		$regtime = strtotime($date) - 3600*24*$day;
		$count=0;
		$where = sprintf(' date="%s" and channel="%s"',date('Y-m-d',$regtime),$channel);
		$countdata = $this->counts->where($where)->find();
		if($countdata)$count=$countdata['register'];
		
		$where = sprintf(' islogin=1 and datediff(logindate,regdate)=%d and date="%s" and channel="%s"',$day,$date,$channel);
		$logincount = $this->logger->where($where)->count();
		return $count==0 ? 0 : $logincount/$count;
	}
	
}