<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller {
   
    function _initialize(){
       	if(!($_SESSION['islogin']==true && !empty($_SESSION['user']))){
       		$this->redirect("Admin/login/login");
       	}	
    }
   
}