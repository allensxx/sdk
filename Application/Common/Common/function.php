<?php 
        
/**
 * 下载文件
 * @param string $file
 *               被下载文件的路径
 * @param string $name
 *               用户看到的文件名
 */

 function download($file,$name=''){
    $fileName = $name ? $name : pathinfo($file,PATHINFO_FILENAME);
    $filePath = realpath($file);
    
    $fp = fopen($filePath,'rb');
    
    if(!$filePath || !$fp){
        header('HTTP/1.1 404 Not Found');
        echo "Error: 404 Not Found.(server file path error)<!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding -->";
        exit;
    }
    
    $fileName = $fileName .'.'. pathinfo($filePath,PATHINFO_EXTENSION);
    $encoded_filename = urlencode($fileName);
    $encoded_filename = str_replace("+", "%20", $encoded_filename);
    
    header('HTTP/1.1 200 OK');
    header( "Pragma: public" );
    header( "Expires: 0" );
    header("Content-type: application/octet-stream");
    header("Content-Length: ".filesize($filePath));
    header("Accept-Ranges: bytes");
    header("Accept-Length: ".filesize($filePath));
    
    $ua = $_SERVER["HTTP_USER_AGENT"];
    if (preg_match("/MSIE/", $ua)) {
        header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
    } else if (preg_match("/Firefox/", $ua)) {
        header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '"');
    } else {
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
    }
    
    // ob_end_clean(); <--有些情况可能需要调用此函数
    // 输出文件内容
    fpassthru($fp);
    exit;
 }

        /**
         * 递归树
         */
      
       function &array_to_tree($arr, $pIdname='parentid', $parentId = 0, $lv = 0 ) {
            $lv++; $tree = array(); 
            foreach ($arr as $row) {
                if ($row[$pIdname] == $parentId ) {
                    $row['level'] = $lv - 1;
                    if($row[$pIdname]!=0){
                        $row['sty']   = str_repeat('　', $row['level']);
                    }
                    if ( $children = array_to_tree($arr, $pIdname, $row['id'], $lv)) {
                        if ($row[$pIdname]!=0){
                            $row['sty'] .= '├ ';
                        }
                        $tree[] = $row;
                        $tree = array_merge($tree, $children); 
                    }else {
                        if ($row[$pIdname]!=0){
                            $row['sty'] .= '└ ';
                        }
                        $tree[] = $row;
                    }
                } 
            } 
        return $tree; 
        }

        /**
         * 通过父类查找所有子类
         */
        function getChild($data,$pid=0,$level=0){
                static $arr = array();
                foreach ($data as $key) {
                       if($key['pid']==$pid){ 
                            $arr[]=$key;
                       }
                }
                return $arr;
        }

/**
 * 发送邮件
 * @param string $address
 * @param string $title
 * @param string $message
 * @return array<br>
 * 返回格式：<br>
 * array(<br>
 *  "error"=>0|1,//0代表出错<br>
 *  "message"=> "出错信息"<br>
 * );
 */
function send_email($address,$title,$message){
    $smtpserver = C("MAIL_SMTP");//SMTP服务器
    $smtpserverport =25;//SMTP服务器端口
    $smtpusermail = C("MAIL_ADDRESS");//SMTP服务器的用户邮箱
    $smtpemailto = $address;//发送给谁
    $smtpuser = C("MAIL_LOGINNAME");//SMTP服务器的用户帐号
    $smtppass = C("MAIL_PASSWORD");//SMTP服务器的用户密码
    $mailsubject = $title;//邮件主题
    $mailbody = $message;//邮件内容
    $mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
    ##########################################
    $smtp = new \Org\Util\Email($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
    $smtp->debug = FALSE;//是否显示发送的调试信息
    $result = $smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);  // 发送邮件。

    if(!$result) {
        return array("error"=>1,"message"=>"发送失败");
    }else{
        return array("error"=>0,"message"=>"success");
    }
}


    /**
     * 生成唯一的订单号
     */

   function build_order_no(){
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }


function uponeday($day){
    $time = strtotime($day) - 3600*24;
    return  date('Y-m-d',$time);
}


function upthreeday($day){
    $time = strtotime($day) - 3600*24*3;
    return  date('Y-m-d',$time);
}



function upsevenday($day){
    $time = strtotime($day) - 3600*24*7;
    return  date('Y-m-d',$time);
}



function upsiwuday($day){
    $time = strtotime($day) - 3600*24*15;
    return  date('Y-m-d',$time);
}





/**
 * 获取次留
 * 2016-06-07
 */
    
   function getciliu($date){
       
        $date1 = uponeday($date);
        $fday = M("counts")->field('id,register,date')->where(array('date'=>$date1))->find(); //昨天的注册用户数
        //file_put_contents("./Public/f.txt", M("counts")->getLastSql());
        $sday = M("counts")->field('id,login,date,ciliu')->where(array('date'=>$date))->find(); //当天的次留用户数
        //file_put_contents("./Public/f1.txt", $sday['login']);
       return $sday['ciliu']/$fday['register']*100;
   }


/**
 * 获取三留
 * 2016-06-07
 */
    
   function getsanliu($date){
        $date1 = upthreeday($date);
        $fday = M("counts")->field('id,register,date')->where(array('date'=>$date1))->find(); //三天前的注册用户数
        file_put_contents("./Public/f.txt", M("counts")->getLastSql());
        $sday = M("counts")->field('id,login,date,sanliu')->where(array('date'=>$date))->find(); //当天的登录用户数
        file_put_contents("./Public/f1.txt", $sday['login']);
        return $sday['sanliu']/$fday['register']*100;
   }


/**
 * 获取七留
 * 2016-06-07
 */
    
   function getqiliu($date){
       
        $date1 = upsevenday($date);
        $fday = M("counts")->field('id,register,date')->where(array('date'=>$date2))->find(); //昨天的注册用户数
        file_put_contents("./Public/f.txt", M("counts")->getLastSql());
        $sday = M("counts")->field('id,login,date,qiliu')->where(array('date'=>$date))->find(); //当天的登录用户数
        file_put_contents("./Public/f1.txt", $sday['login']);
        return $sday['qiliu']/$fday['register']*100;
   }



/**
 * 获取15留
 * 2016-06-07
 */
    
   function getsiwuliu($date){
        $date1 = upsiwuday($date);
        $fday = M("counts")->field('id,register,date')->where(array('date'=>$date1))->find(); //昨天的注册用户数
        file_put_contents("./Public/f.txt", M("counts")->getLastSql());
        $sday = M("counts")->field('id,login,date,shiwuliu')->where(array('date'=>$date))->find(); //当天的登录用户数
        file_put_contents("./Public/f1.txt", $sday['login']);
        return $sday['shiwuliu']/$fday['register']*100;
   }

   /**
    * 判断手机号码
    */

   function isphone(){
    if(preg_match("/^1[34578]{1}\d{9}$/",$phonenumber)){  
        return true;
    }else{  
        return false;
    }  
   }

   /**
    * 发送邮件
    */

    function sendEmail($address, $subject, $body){
        
        //标识pid
        $pid=37664;
        //任务标题
        $title=$subject;
        //获取13位毫秒数
        $timestamp=getTimestamp(13);
        //密钥
        $key="c1841d4c3c594d089b293fdeec1db5ef";
        //组成sign
        $sign=md5($pid.$timestamp.$key);

        /**
           *进行邮件发送
           */
         //任务编号
        $taskId=680365;
        //邮件标题
        $emailtitle=$subject;
        //邮件内容
        $emailcontent=$body;
        //邮件回复地址(可选)
        $replay="service@mail.dataea.com";
        //发件人（可选）
        $displayName="创游工场网络";
        //发件人邮箱
        $fromEmail="chuangyou@bobgame.cn";
        //收件人邮箱
        $recipient=$address;
        //组成邮件sign
        $emailsign=md5($pid.$taskId.$timestamp.$key);

        /**
           *执行发送邮件操作
           *@param1 int $pid 唯一标识
           *@param2 char $title 任务标题
           *@param3 int $taskId 任务编号
           *@param4 char $replay 邮件回复地址（可选）
           *@param5 char $displayName 发件人（可选）
           *@param6 char $fromEmail 发件人邮箱
           *@param7 char $recipient 收件人邮箱
           *@param8 int $timestamp 13位毫秒
           *@param9 char $emailsign 邮件sign
           */
        $curlPost = array(
                'pid'=>$pid,
                'title'=>$title,
                'taskId'=>$taskId,
                'replay'=>$replay,
                'displayName'=>$displayName,
                'fromEmail'=>$fromEmail,
                'content'=>$emailcontent,
                'recipient'=>$recipient,
                'timestamp'=>$timestamp,
                'sign'=>$emailsign
            );

        $url = "http://api.emailfire.cn/api/sendMail.action";
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
        $send=curl_exec($ch);
        $send=json_decode($send,true);
        curl_close($ch);


        //判断状态
        if($send['ERROR_CODE']=='ILLEGAL_ARGUMENT'){
                return array("error"=>1,"message"=>"参数不正确");
            }elseif($send['ERROR_CODE']=='ILLEGAL_REPLAY'){
                return array("error"=>1,"message"=>"回复邮箱无效");
            }elseif($send['ERROR_CODE']=='ILLEGAL_RECIPIENT'){
                return array("error"=>1,"message"=>"收件人邮箱无效");
            }elseif($send['ERROR_CODE']=='ILLEGAL_FROM_MAIL'){
                return array("error"=>1,"message"=>"发件人邮箱无效");
            }elseif($send['ERROR_CODE']=='LLEGAL_ANTI_PHISHING_KEY'){
                return array("error"=>1,"message"=>"非法时间戳参数");
            }elseif($send['ERROR_CODE']=='ILLEGAL_PID'){
                return array("error"=>1,"message"=>"pid无效");
            }elseif($send['ERROR_CODE']=='LLEGAL_ZERO'){
                return array("error"=>1,"message"=>"API 投递量不足");
            }elseif($send['ERROR_CODE']=='LLEGAL_SIGN'){
                return array("error"=>1,"message"=>"签名不正确");
            }elseif($send['ERROR_CODE']=='SUCCESS'){
                return array("error"=>0,"message"=>"success");
            }
     } 

     /**
      * 首次充值
      */
    function update_first($uid,$money){
        if(!empty($uid)){
            $find = M("user")->where(array('id'=>$uid))->find();
            if($find['first_recharge']==0){
                 M("user")->where(array('id'=>$uid))->setInc('first_recharge',$money);
            }
            M("user")->where(array('id'=>$uid))->setInc('money_top',$money);
        }
    }


    /**
     * 渠道转成中文
     */
    function change_channel($channel){
        $result = M("channel")->field('id,bundelid,name')->where(array('bundelid'=>$channel))->find();
        $result['name']=empty($result)?'all':$result['name'];//author by fanxiaochange 渠道转换非空判断
        return $result['name'];
    }

    /**
     * 渠道转成中文
     */
    function get_channel($channel_id){
        $result = M("channel")->field('id,bundelid,name')->where(array('id'=>$channel_id))->find();
        return $result['name'];
    }
