<?php 
	
namespace Admin\Model;
use Think\Model;
class UserModel extends Model{
   protected $_validate = array(
	     array('username','require','用户名不能为空'), //默认情况下用正则进行验证
	     array('password','require','密码不能为空'), // 自定义函数验证密码格式
	   );

    protected $_auto = array (  
            array('password','md5',3,'function') , // 对password字段在新增和编辑的时候使md5函数处理         
            array('username','111'), // 对name字段在新增和编辑的时候回调getName方法          
            array('login_time','2015-10-7'), // 对name字段在新增和编辑的时候回调getName方法          
            array('login_ip','192.168.1.1'), // 对name字段在新增和编辑的时候回调getName方法          
            );
}



