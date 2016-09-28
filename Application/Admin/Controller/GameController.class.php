<?php
namespace Admin\Controller;
use Think\Controller;
class GameController extends Controller{
    public function showlist(){
        $this->display('game-list');
    }
    public function qudao(){
        $this->display();
    }
}
