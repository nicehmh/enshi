<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
            define("TOKEN", "weixintry1");
            if (!isset($_GET['echostr'])) {
				$this->responseMsg();
            }else{
                 $this->valid();
            }
	 }
	 public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
	private function checkSignature()
	{
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	public $appid = "wx772ddaa0a2959c4f";
	public $appsecret = "ede692b3f2b15cc0593d98c4e07f15a7";
	public function access_token() {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}";
		$cont = file_get_contents($url);
		return json_decode($cont, 1);
	}
	function app_menu() {
        $data = ' {
		     "button":[
		     {	
		         "name":"我的计划",
		         "sub_button":[
		            {
		                "type":"view",
			            "name":"学习",
			            "url":"http://1.weixintry1.sinaapp.com/index.php/home/Weixin/"
		            },
		            {
		               "type":"click",
		               "name":"健康",
		               "key":"A2"
		            }]
		      },
		      {
		           "name":"学习助手",
		           "sub_button":[
		            {
		               "type":"click",
		               "name":"热门书籍",
		               "key":"B1"
		            },
		            {
		               "type":"click",
		               "name":"课程",
		               "key":"B2"
		            }]
		      },
		      {
		           "name":"我的生活",
		           "sub_button":[
		            {
		               "type":"click",
		               "name":"发起投票",
		               "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx772ddaa0a2959c4f&redirect_uri=http://121.42.14.98/time_count/index.php/home/Test/index.html&response_type=code&scope=snsapi_userinfo&state=creator#wechat_redirect"
		            },
		            {
		               "type":"click",
		               "name":"免费与优惠",
		               "key":"C2"
		            }]
		       }]
		 	}';
		$access_token = $this -> access_token();
		$ch = curl_init('https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token['access_token']);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
		$data = curl_exec($ch);
	}
	
	public function responseMsg()
    {
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
				$msgType=trim($postObj->MsgType);
                $time = time();
				switch ($msgType) {
					
					case 'text':
					  //  $this->sendtext();          
			           if(!empty( $keyword ))
                       {
						   switch($keyword)
						   {
							   case 新闻:
							        $this -> send_news($fromUsername,$toUsername,$time);
							   case d:
							       $this->receiveText($postObj);
							   break;
							   case f:
							       $this->send_all_text();
							   break;
							   case 测试:
							       $contentStr = "<a href='https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx772ddaa0a2959c4f&redirect_uri=http://sunhuaqi.cn/time_count/index.php/home/Test/index.html&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect'>测试</a>";
	                               $this -> send_text($fromUsername,$toUsername,$time,$contentStr);
							   break;
							   case 创建:
							       $contentStr = "<a href='https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx772ddaa0a2959c4f&redirect_uri=http://sunhuaqi.cn/time_count/index.php/home/Test/index.html&response_type=code&scope=snsapi_userinfo&state=form#wechat_redirect'>创建</a>";
	                               $this -> send_text($fromUsername,$toUsername,$time,$contentStr);
							   break;
							   case g:
							       $contentStr = "<a href='http://121.42.14.98/time_count/index.php/home/Test/'>发起投票</a>";
	                               $this -> send_text($fromUsername,$toUsername,$time,$contentStr);
							   break;
							   
							   default:
							       $contentStr = "暂无此项功能";
				                   $this -> send_text($fromUsername,$toUsername,$time,$contentStr);     
						   }
                       }else{
                         	echo "Input something...";
                       }
						break;
						
			        case 'image':
					         //上传图片到sae
					   	     $media_id=$postObj->MediaId;
							 $this->upload($fromUsername,$toUsername,$time,$media_id);
                             //$this->receiveImage($media_id);								   
		 			         break;
					   
					case 'event':
					    $Event = $postObj->Event;
					    $EventKey=$postObj->EventKey;
						if($Event=="CLICK")
						{
							//return $EventKey;
							//$this -> send_news($fromUsername,$toUsername,$time);
				            if(!empty($EventKey)){
                                  $this -> getEventKey($fromUsername,$toUsername,$time,$EventKey);
								//return $EventKey;
								}
                                 else{$this -> send_news($fromUsername,$toUsername,$time);}							
						}
 			            else{$this->getEvent($fromUsername,$toUsername,$time,$Event,$EventKey);}	 
					    break;
				    case 'shortvideo':
					     $contentStr = "暂无此项功能";
				         $this -> send_text($fromUsername,$toUsername,$time,$contentStr);
				    default:
					    $contentStr = "sorry";
					    $this->send_text($fromUsername,$toUsername,$time,$contentStr);
						break;
				}
        }else {
        	echo "sorry";
        	exit;
        }
	}
	
	
	
	
	//发送文本消息
	public function send_text($fromUsername,$toUsername,$time,$contentStr)
	{
		  $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";   
              		         $msgType = "text";
                        	 $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                             echo $resultStr;
	}
	
	//发送新闻消息
	public function send_news($fromUsername,$toUsername,$time)
	{
		   $textTpl = "<xml>
							    <ToUserName><![CDATA[%s]]></ToUserName>
							    <FromUserName><![CDATA[%s]]></FromUserName>
							    <CreateTime>%s</CreateTime>
							    <MsgType><![CDATA[%s]]></MsgType>
							    <ArticleCount>1</ArticleCount>
                                <Articles>
                                <item>
						     	<Title><![CDATA[我的个人微信]]></Title> 
                                <Description><![CDATA[hello my friend]]></Description>
                                <PicUrl><![CDATA[http://www.php100.com/cms/php100/2013/php3r.jpg]]></PicUrl>
                                <Url><![CDATA[http://1.weixintry1.sinaapp.com/index.php/home/Index/A1_send]]></Url>
                                </item>
							    </Articles>
							    </xml>";   
								$msgType = "news";
								$contentStr = "Welcome to wechat world!";
								$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
								echo $resultStr;
	}
	//回复客服消息
	private function receiveText($postObj){
			//获取文本消息的内容
			$access_token = $this -> access_token();
			$content = $postObj->Content;
			$url ="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token['access_token'];
			//根据用户回复不同的关键字来发送不同类型的客服消息
			$touser=$postObj->fromUsername;
			$content="hello world";
			switch($content)
			{
				case 'd':
				    $data = '{
                         "touser":"o3RDfv80hYs6H8DfPUbHfJm0m39s",
                         "msgtype":"text",
                         "text":
                         {
                             "content":"hello world"
                         }
                    }';
		            $result=$this->https_post($url,$data);
				    $final = json_decode($result);
                    return $final;
					break;
			    default:
			        $contentStr = urlencode("不欢迎不欢迎");//转码
					$post_text_arr = array(
							"touser"=>"{$postObj->FromUserName}",
							"msgtype"=>"text",
							"text"=>array("content"=>"{$contentStr}")
						);
					$post_text_json = json_encode($post_text_arr);
					$post_text_json = urldecode($post_text_json);//解码
					$this->https_request($url,$post_text_json);
					break;    
		    }
	}
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
	
	//群发消息
	//public function 
	//关注事件
	public function getEvent($fromUsername,$toUsername,$time,$Event,$EventKey){
		            switch($Event){
 				           //关注事件
 				           case "subscribe":
						        $this->record($fromUsername);
 				          	    break;
 				           //取消关注事件
 				           case "unsubscribe":
						       $contentStr = "再见！朋友！";
				               $this -> send_text($fromUsername,$toUsername,$time,$contentStr); 
  				            	break;
							case "VIEW":
                               $contentStr = "欢迎关注我们的微信公众帐号平台。";
				               $this -> send_text($fromUsername,$toUsername,$time,$contentStr);
 				           //扫描二维码用户已关注事件推送
						   //case "CLICK":
						      //$this -> getEventKey($fromUsername,$toUsername,$time,$EventKey);
					           break;
				           default:
							  $this -> send_news($fromUsername,$toUsername,$time);
							  break;
					}
	}
	
	//相应点击事件
	public function getEventKey($fromUsername,$toUsername,$time,$EventKey)
	{
		            switch($EventKey){
						case "A1":
						    //$Url = "http://www.baidu.com";
				     //        $this -> EventKeyUrl($fromUsername,$toUsername,$time,$Url);
							   $textTpl = "<xml>
                                            <ToUserName><![CDATA[%s]]></ToUserName>
					                       <FromUserName><![CDATA[%s]]></FromUserName>
					                       <CreateTime>%s</CreateTime>
                                           <MsgType><![CDATA[event]]></MsgType>
                                           <Event><![CDATA[VIEW]]></Event>
                                           <EventKey><![CDATA[http://www.baidu.com]]></EventKey>
                                          </xml>";   
								$contentStr = "Welcome to wechat world!";
								$EventKey="http://www.baidu.com";
								$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time,$EventKey,$contentStr);
								echo $resultStr; 
							break;
						case "A2":
						    $this-> send_text($fromUsername,$toUsername,$time,$EventKey);
							break;
						default:
						    $this-> send_text($fromUsername,$toUsername,$time,$EventKey);
							break;	    
					}
	}
	
	public function EventKeyUrl($fromUsername,$toUsername,$time,$Url)
	{
		     $textTpl = "<xml>
                         <ToUserName><![CDATA[%s]]></ToUserName>
					     <FromUserName><![CDATA[%s]]></FromUserName>
					     <CreateTime>%s</CreateTime>
                         <MsgType><![CDATA[event]]></MsgType>
                         <Event><![CDATA[VIEW]]></Event>
                         <EventKey><![CDATA[http://www.baidu.com]]></EventKey>
                         </xml>";   
								$contentStr = "Welcome to wechat world!";
								$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time);
								echo $resultStr; 
	}
	
	//微信图片上传并插入数据库
	public function upload($fromUsername,$toUsername,$time,$media_id){
	 
	//通过CURL get请求来实现下载多媒体文件
	$access_token = $this -> access_token();
	$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token['access_token']."&media_id={$media_id}";
	//1、开始一个CURL会话
	$ch = curl_init();

	//2、设置CURL会话的选项
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	
	//3、执行一个CURL会话
	$optout = curl_exec($ch);
	//$this->assign('list',$optout);
	
    
	//通过SAE中的storage来上传调用下载多媒体文件接口下载下来的文件资源，以图片的形式保存
	$storage = new \SaeStorage();//初始化
	$domain = 'image';  //指定存储文件的目录名
	$fileName = $media_id.'.jpg';//指定创建的文件名
	$content = $optout;  //写入的内容
	$result = $storage->write($domain,$fileName, $content);
	//4、关闭一个CURL会话
	curl_close($ch);
	$m=D('images');
	$openid = $fromUsername;
				$access_token = $this -> access_token();
	            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token['access_token']."&openid={$openid}&lang=zh_CN";
		        $userInfo = $this->https_request($url);
	$data['imageurl']=$storage->getUrl($domain,$fileName);//读取某储存文件的URL
	$data['openid']=$userInfo['openid'];
	$m->add($data);
    $contentStr = "<a href='http://1.weixintry1.sinaapp.com/index.php/home/Image/'>查看图片</a>";
	$this -> send_text($fromUsername,$toUsername,$time,$contentStr);
   }
         //记录关注用户信息
        public function record($fromUsername){
                $m=D('userinfo');
		     	$openid = $fromUsername;
				$access_token = $this -> access_token();
	            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token['access_token']."&openid={$openid}&lang=zh_CN";
		        $userInfo = $this->https_request($url);
	            $headimgurl = $userInfo['headimgurl'];//获取用户头像
		      	//换成小头像
			    // $smallimgurl = substr($headimgurl,0,-1)."132";
		       //将图片保存到自己的服务器上
		        $file = file_get_contents($headimgurl);

			   //保存到SAE上
		       $storage = new \SaeStorage();//初始化
		       $domain = 'image';  //指定存储文件的目录名
			   $imgName = $openid.".jpg";
	           $storage->write($domain,$imgName, $file);
			   $data['openid']=$userInfo['openid'];
		       $data['nickname'] = $userInfo['nickname'];//获取昵称
			   $data['sex'] = $userInfo['sex'];	//获取性别
		       //$city=$userInfo['city'];
			   $data['city'] = $userInfo['city'];//获取用户所在城市
		       $data['headimgurl'] = $storage->getUrl($domain,$imgName);//读取某储存文件的URL
		       $data['subscribe_time'] = $userInfo['subscribe_time'];//获取用户关注的时间	
			   $count=$m->add($data);
				
			   if(empty($openid))
			   { $contentStr = "欢迎关注我们的微信公众帐号平台。";
	             $this -> send_text($fromUsername,$toUsername,$time,$contentStr);
				}else{								 
				  $this -> send_text($fromUsername,$toUsername,$time,$data['openid']); 
    			   }
		}
	//群发文本消息
	public function send_all_text()
	{
		$access_token = $this -> access_token();
		$url="https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=".$access_token['access_token'];
		$data='{
                 "touser":[
                 "o3RDfv80hYs6H8DfPUbHfJm0m39s",
                 "o3RDfv9y2qsca9eVgAwSL1zaBwrU"
                 ],
                 "msgtype": "text",
                 "text": { "content": "hello from boxer."}
               }';
	     $this->https_request($url,$data);
	}
   	protected function https_request($url,$data = null)
	{
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			if(!empty($data))
			{
				curl_setopt($ch,CURLOPT_POST,1);//模拟POST
				curl_setopt($ch,CURLOPT_POSTFIELDS,$data);//POST内容
			}
			$outopt = curl_exec($ch);
			curl_close($ch);
			$outoptArr = json_decode($outopt,true);
			if(is_array($outoptArr))
			{
				return $outoptArr;
			}
			else
			{
				return $outopt;
			}
	}
   
}
        
