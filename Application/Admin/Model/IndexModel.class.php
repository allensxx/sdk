<?php
namespace Admin\Model;
use Think\Model;
class IndexModel extends Model{
	 public function regnumber(){
	 	//$model = new Model();
	 	$sql="select count(username) from sp_user inner join sp_downl where sp_user.id=sp_downl.uid and sp_user.regtime=sp_downl.downtime";
	 	return $this->query($sql);
	 }
	 public function activation(){
	 	$sql="select count(username) from sp_user inner join sp_downl where sp_user.id=sp_downl.uid and sp_user.regtime=sp_downl.downtime";
	 	return $this->query($sql);
	   }
	public function logins(){
		//$where['is_login']='是';
		return M("user")->where($where)->count();
	}
	 public function fdl(){
	 	$sql="select count(username) from sp_user where is_pay='是'";
	 	return $this->query($sql);
	 }
	 public function firsts(){
	 	$sql="select pay_time from sp_order where is_topup ='是'";
		return $this->query($sql);
	 }
	 public function topups(){
	 	$sql="select sum(amount) from sp_order ";
	 	return $this->query($sql);
	 }
	 public function crlcs(){
	 	$sql="select count(username) from sp_user where regtime=curdate()-1 and dltime=curdate()";
	 	return $this->query($sql);
	 }
	 public function srlcs(){
	 	$sql="select count(username) from sp_user where regtime=curdate()-3 and dltime=curdate()";
	 	return $this->query($sql);
	 }
	 public function trlcs(){
	 	$sql="select count(username) from sp_user where regtime=curdate()-7 and dltime=curdate()";
	 	return $this->query($sql);
	 }
	 public function swrlcs(){
	 	$sql="select count(username) from sp_user where regtime=curdate()-15 and dltime=curdate()";
	 	return $this->query($sql);
	 }
	 public function downls(){
	 	$sql="select sum(number) from sp_downl";
	 	return $this->query($sql);
	 }
	 public function scczs(){
	 	/**
	 	$sql="select sp_order.pay_time ,sp_user.username ,sp_order.id  from sp_order inner join sp_user where  
	 	   sp_order.uid=sp_user.id and is_topup='是'";
	 	   ***/
	 	 $sql="select pay_time, amount, id from sp_order where is_topup='是'";
	 	   return $this->query($sql);
	 }
	 public function games(){
	 	$sql="select sp_games.id,sp_games.game_name,sp_games.game_url,sp_games.game_img,sp_games.intro,sp_game_cat.cat_name from sp_games inner join sp_game_cat where sp_games.cat_id=sp_game_cat.id";
	 	return $this->query($sql);
	 }
}
