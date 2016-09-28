<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model{
     protected $insertFields = array('username','password','is_deny','verifyCode');
     protected $updateFields = array('id','username','password','is_deny');
	// 添加和修改管理员时使用的规则
     protected $_validate = array(
		array('username', 'require', '账号不能为空！', 1, 'regex', 3),
		array('username', '1,30', '账号的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 1),
		array('password', '1,32', '密码的值最长不能超过 32 个字符！', 1, 'length', 1),
		array('password', '1,32', '密码的值最长不能超过 32 个字符！', 2, 'length', 2),
		array('is_deny', '是,否', "是否禁用的值只能是在 '是,否' 中的一个值！", 2, 'in', 3),
		array('username', '', '账号已经存在！', 1, 'unique', 3),
	);
     public $_login_validate = array(
		array('verifyCode', 'require', '验证码不能为空！', 1, 'regex', 3),
		array('username', 'require', '账号不能为空！', 1, 'regex', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 3),
		
         );
     public function login(){
         //从模型中取得用户提交的用户名和密码
         $username=$this->username;
         $password=$this->password;
         $user=$this->where(array('username'=>array('eq',$username),))->find();
         if($user)
		{
			// 账号是否被禁用了
			if($user['is_deny'] == '是')
			{
				$this->error = '账号已经被禁用！';
				return FALSE;
			}
			// 判断密码
			if($user['password'] == $password )
			{
				// 把信息存到session中
				session('id', $user['id']);
				session('username', $user['username']);
				return TRUE;
			}
			else 
			{
				$this->error = '密码不正确！';
				return FALSE;
			}
		}
		else 
		{
			$this->error = '账号不存在！';
			return FALSE;
		}
	}
     }
?>
