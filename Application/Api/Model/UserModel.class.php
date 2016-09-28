<?php
namespace Api\Model;
use Think\Model;
class UserModel extends Model
{
	
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('userName', 'require', '用户名称不能为空！'),
			array('passWord', 'require', '密码不能为空！'),
	);


}

