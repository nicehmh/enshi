<?php
namespace Home\Controller;
use Think\Controller;

class TestController extends Controller {
	 public function index(){
		  // echo $_SERVER['PHP_SELF'];exit;
		  //第一步：用户同意授权，获取code
	       $code=I('get.code');
		  // 第二步：通过code换取网页授权access_token
		   $url_acc="https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx772ddaa0a2959c4f&secret=ede692b3f2b15cc0593d98c4e07f15a7&code=".$code."&grant_type=authorization_code";
           $str_arr=$this->https_post($url_acc);
		  // dump($url);
		  // dump($str_arr);
		   $php_arr=json_decode($str_arr,true);
		  // dump($php_arr['refresh_token']);
		   //第三步：刷新access_token
		   $url_info="https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=wx772ddaa0a2959c4f&grant_type=refresh_token&refresh_token=".$php_arr['refresh_token'];
		   //dump($url_info);
		   $info_arr=$this->https_post($url_info);
		   //dump($info_arr);
		   // 第四步：拉取用户信息
		   $acc_arr=json_decode($str_arr,true);
		   //dump($acc_arr);
		   $url_user="https://api.weixin.qq.com/sns/userinfo?access_token=".$acc_arr['access_token']."&openid=".$acc_arr['openid']."&lang=zh_CN";
		   $user_arr=$this->https_post($url_user);
		   $user_info=json_decode($user_arr,true);
		  // dump($user_info);exit;
		  // session_start();
		  $_SESSION['uid']=$user_info['openid'];
		  $_SESSION['name']=$user_info['nickname'];
		  $_SESSION['img']=$user_info['headimgurl'];
		  $_SESSION['num']=1;
		  //获取signature
		  //dump($signature);
	      $this->display();
	 }
	
	 //填聚会页面
	 public function form(){
		$this->display();
	 }
	 //以curl方式模拟提交
	public function https_post($url,$data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
             return 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }
	//记录集会时间
	public function record(){
		$_SESSION['num']++;
		if($_SESSION['num']==2){
			$map['title']=$_GET['title'];
	    	$map['time']=$_GET['start_time']."@".$_GET['end_time'];
		    $str=$this->account_user();
	     	$map['uid']=$str['uid'];
		    $map['name']=$str['name'];
		    $map['pid']=$this->create_mate($str['uid']);
		    $m=M('count');
		    if($m->add($map)){
		    	$this->redirect('result');
		    }else{
		    	$this->error('提交失败','index');
		    }
		}else{
			$this->redirect('result');
		}
		//dump($_SESSION);exit;
	}
	//创造匹配的pid
	public function create_mate($uid){
		//dump($uid);
		$map['uid']=$uid;
		$map['datatime']=time();
		$m=M('mate');
		//dump($map);
		if($m->add($map)){
			$str=$m->where($map)->find();
			if($str){
				$_SESSION['pid']=$str['pid'];
				return $str['pid'];
			}else{
				$this->error('提交失败','index');
			}
		}else{
			$this->error('提交失败','index');
		}
	}
	//统计用户
	public function account_user(){
		$m=M('user');
		$map['uid']=$_SESSION['uid'];
		$map['name']=$_SESSION['name'];
		$map['img']=$_SESSION['img'];
		$str=$m->where($map)->find();
		if($str){
			return $str;
		}else{
			if($m->add($map)){
				$str_1=$m->where($map)->find();
				if($str_1){
					return $str_1;
				}else{
					$this->error('提交失败','index');
				}
			}else{
			    $this->error('提交失败','index');
		    }
		}
	}
	//显示结果页面
	public function result(){
		 $signature=A("Jssdk")->GetSignPackage();
		  $this->assign("signature",$signature);
		  
		  $this->assign('pid',$_SESSION['pid']);
		  $where['pid']=$_SESSION['pid'];
		  //dump($_SESSION);
		  $m=M('count');
		  $str=$m->where($where)->find();
		  $this->assign('title',$str['title']);
		  
		  $d=M('result');
		  $arr=$d->where($where)->select();
		  $this->assign('list',$arr);
	   $this->display(); 
	}
	//选择页面
	public function select(){
		
		  $code=I('get.code');
		  if($code){
		   // 第二步：通过code换取网页授权access_token
		   $url_acc="https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx772ddaa0a2959c4f&secret=ede692b3f2b15cc0593d98c4e07f15a7&code=".$code."&grant_type=authorization_code";
           $str_arr=$this->https_post($url_acc);
		  // dump($url);
		  // dump($str_arr);
		   $php_arr=json_decode($str_arr,true);
		  // dump($php_arr['refresh_token']);
		   //第三步：刷新access_token
		   $url_info="https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=wx772ddaa0a2959c4f&grant_type=refresh_token&refresh_token=".$php_arr['refresh_token'];
		   //dump($url_info);
		   $info_arr=$this->https_post($url_info);
		   //dump($info_arr);
		   // 第四步：拉取用户信息
		   $acc_arr=json_decode($str_arr,true);
		   //dump($acc_arr);
		   $url_user="https://api.weixin.qq.com/sns/userinfo?access_token=".$acc_arr['access_token']."&openid=".$acc_arr['openid']."&lang=zh_CN";
		   $user_arr=$this->https_post($url_user);
		   $user_info=json_decode($user_arr,true);
		  // dump($user_info);exit;
		  // session_start();
		  $_SESSION['uid']=$user_info['openid'];
		  $_SESSION['name']=$user_info['nickname'];
		  $_SESSION['img']=$user_info['headimgurl'];
		  $_SESSION['num']=1;
		  
		  $_SESSION['pid']=I('get.state');
       $this->assign('pid',I('get.state'));
	   
	     $where['pid']=$_SESSION['pid'];
		  }else{
			  
		  }
		  //dump($_SESSION);
		  $m=M('count');
		  $str=$m->where($where)->find();
		  $this->assign('title',$str['title']);
		$this->display();
	}
	//
    public function rc_select(){
	    $map['pid']=I('get.pid');
		$map['rtime']=I('get.start_time');
		if($map['pid']&&$map['rtime']){
		   $m=M('result');	
		   $str=$m->where($map)->find();
		   if($str){
			   $where['number']=$str['number']+1;
			   $m->where($map)->save($where);
		   }else{
			   $map['number']=1;
			   $m->add($map);
		   }
		}
		$this->redirect('result');
    }
	
}
?>
