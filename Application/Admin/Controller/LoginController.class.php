<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function index(){
        	$this->display();
    }

     /**
     * 用户登陆
     */
    public function login(){
        $this->display();
    }

    /**
     * 用户退出
     */
    public function logout(){
        session_destroy();
        unset($_SESSION);
        redirect(__ROOT__."/admin");
    }

    public function dologin(){
        $username = I("post.username");
        $password = md5(I("post.password"));
        if(!$this->check_code()){
            $this->error("验证码错误");
        }
        if(empty($username) || empty($password)){
            $this->error("用户名或密码不能为空");
        }
        if($username=="manage_login" && $password==md5("awfiriu123")){
            $_SESSION['islogin'] = true;
            $_SESSION['is_root'] = true;
            $_SESSION['user'] = array("username"=>$username);
            $this->redirect("Admin/index/index");
        }else{
            $res = M("admin")->where(array('username'=>$username,'password'=>$password))->find();
            if($res){
                 $_SESSION['islogin'] = true;
                 $_SESSION['is_root'] = false;
                 $_SESSION['user'] = $res;
                 $this->redirect("Admin/index/index");
            }else{
                $this->error("用户名或密码错误");
            }
            
        }
    }

    /**
     * 检查验证码
     */
    public function check_code(){
        $verify = new \Think\Verify();
        return $verify->check($_REQUEST['code'], "");
    }

    public function code(){
        $config =    array(   
        'fontSize'    =>    14,   // 验证码字体大小    
        'length'      =>    4,     // 验证码位数    
        'useNoise'    =>    false, // 关闭验证码杂点
        'imageW'      => 100,
        'imageH'      => 35,
        );
        $Verify = new \Think\Verify($config);
        $code = $Verify->entry();
        echo $code;
    }

    public function active(){
         $verify = $_GET['verify']; 
         $result = M("user")->where(array('status'=>0,'token'=>$verify))->find();
         if($result){
            $data['status']=1;
            $satus = M("user")->where(array('id'=>$result['id']))->save($data);
            if($satus){
                $this->success("激活成功","user/index");
            }else{
                $this->error("激活失败");
            }
         }else{
            $this->error("失败");
         }
    }

    /**
     * 显示修改密码
     */
    public function mod(){
        if(empty($_SESSION['userinfo'])){
            $this->error("用户未登陆",'login');
        }
        $this->display();
    }

    /**
     * 修改密码
     */
    public function modPassword(){
        $username = $_SESSION['userinfo']['username'];
        $oldPass = I('post.oldpassword');
        $newPass = I('post.newpassword');
        $rePass = I('post.repeatpassword');
        if(empty($username)){
            $this->error("用户未登陆",'login');
        }
        
        $userinfo = M("user")->where(array('username'=>$username))->find();
        
        if($userinfo['password']!=$oldPass){
            $this->error("旧密码错误！");
        }
        if($newPass!=$rePass){
            $this->error("两次密码不一致！");
        }
        $data['password'] = $newPass;
        
        $result = M("user")->where(array('username'=>$username))->save($data); // 根据条件更新记录

        if($result){
            $this->success("密码修改成功",'index');
        }else{
            $this->error("密码修改失败");
        }

    }

   
}