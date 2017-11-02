<?php
namespace Ydzy\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ydzy\ApiBundle\Entity\pages;
use Symfony\Component\HttpFoundation\JsonResponse;
//require_once "lib/Channel.class.php";
class PushController extends Controller
{   	
	//定义错误信息
	function error_output ( $str ) 
	{
		echo "\033[1;40;31m" . $str ."\033[0m" . "\n";
	}

	public function TagsAction(){
		//error_reporting(E_ALL ^ E_NOTICE);
		$apikey=$this->container->getParameter('apikey');
	    $secretkey=$this->container->getParameter('secretkey');  
	    $channel = new \Channel($apikey, $secretkey);
	    $ret=$channel->fetchTag();//循环标签
	    //检查返回值
	    if ( false === $ret )
	    {
	        // echo ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!'.'<br>' );
	        // echo ( 'ERROR NUMBER: ' . $channel->errno ( ) . '<br>' );
	        // echo ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) . '<br>' );
	        // echo ( 'REQUEST ID: ' . $channel->getRequestId ( ) . '<br>' );
	        return new Response('服务器错误，请重试');
	    }
	    
	    return $this->render('YdzyAdminBundle:Push:push.html.twig',array('tags' =>$ret['response_params']['tags']));
	}
	
	//推送给所有人
	public function PushAll($messages)
	{
		$apikey=$this->container->getParameter('apikey');
		$secretkey=$this->container->getParameter('secretkey');
		$channel = new \Channel ( $apikey, $secretkey ) ;
		$push_type = 3; //推送单播消息
		//指定消息类型为通知
		$optional[\Channel::MESSAGE_TYPE] = 1;
		$message_key = "msg_key";
		$ret = $channel->pushMessage ( $push_type, $messages, $message_key, $optional ) ;
		if ( false === $ret )
		{
			error_output ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!!' ) ;
			error_output ( 'ERROR NUMBER: ' . $channel->errno ( ) ) ;
			error_output ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) ) ;
			error_output ( 'REQUEST ID: ' . $channel->getRequestId ( ) );
		}
		 return $ret;
	}
	
	//推送安卓
	public function PushAndroid($messages,$tag_name){
		$apikey=$this->container->getParameter('apikey');
	    $secretkey=$this->container->getParameter('secretkey');  
	    $channel = new \Channel($apikey, $secretkey);
	    $push_type = 2;//推送给一群人
	    $message_keys = 'msg_key';
	    //推送通知，必须指定MESSAGE_TYPE为1
	    $optional[\Channel::MESSAGE_TYPE] = 1;
	    //推送类型是群组
	    $optional[\Channel::TAG_NAME] = $tag_name;
	    $ret = $channel->pushMessage($push_type, $messages, $message_keys,$optional);
	    if ( false === $ret )
	    {
	        echo ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!'.'<br>' );
	        echo ( 'ERROR NUMBER: ' . $channel->errno ( ) . '<br>' );
	        echo ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) . '<br>' );
	        echo ( 'REQUEST ID: ' . $channel->getRequestId ( ) . '<br>' );
	       
	    }
	    return $ret;	

	} 
	//推送ios
	public function PushIos($messages,$tag_name)
	{
		$apikey=$this->container->getParameter('apikey');
	    $secretkey=$this->container->getParameter('secretkey'); 
		$channel = new \Channel($apikey, $secretkey);
		$push_type = 2;//推送给一群人
		//推送类型是群组
	    $optional[\Channel::TAG_NAME] = $tag_name;
	    $message_keys = 'msg_key';
		//指定发到ios设备
		$optional[Channel::DEVICE_TYPE] = 4;
		//指定消息类型为通知
		$optional[Channel::MESSAGE_TYPE] = 1;
		//如果ios应用当前部署状态为开发状态，指定DEPLOY_STATUS为1，默认是生产状态，值为2.
		//旧版本曾采用不同的域名区分部署状态，仍然支持。
		$optional[Channel::DEPLOY_STATUS] = 1;
		//通知类型的内容必须按指定内容发送，示例如下：
		//$message = '{ 
//			"aps":{
//				"alert":"msg from baidu push",
//				"sound":"",
//				"badge":0
//			}
//		}';
		$ret = $channel->pushMessage ( $push_type, $messages, $message_key, $optional ) ;
		if ( false === $ret )
		{
			echo ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!!' ) ;
			echo ( 'ERROR NUMBER: ' . $channel->errno ( ) ) ;
			echo ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) ) ;
			echo ( 'REQUEST ID: ' . $channel->getRequestId ( ) );
		}
		else
		{
			 return $ret;
		}
}	
	
//推送主方法
	public function PushAction(Request $request){
	    //接受数据
	    $json = $this->get('json_parser')->parse($request);
	    $tag_name=$json->get('select','');
	    $type=$json->get('type','');//推送类型
	    $title=$json->get('title','');//标题
	    $content=$json->get('content','');//内容
		$range=$json->get('range','');//范围
		$pageid=$json->get('pageid','');//江涛跳转的页面的ID
	    if ($title==null) {
	    	return new Response("标题不能为空");
	    }
	    if (strlen($title) > 20) {
	    	return new Response("标题不能超过20个字符");
	    }
	    if ($content==null) {
	    	return new Response("内容不能为空");
	    }if (strlen($content) > 40) {
	    	return new Response("内容不能超过40个字符");
	    }else{ 
	    $message = array(
				'title'=>$title,
				'description'=>$content,
				'open_type'=>'2',
				'pkg_content'=>"intent:#Intent;action=android.intent.action.MAIN;category=android.intent.category.LAUNCHER;launchFlags=0x10000000;component=com.ydzy.lzhang/.MessageActivity;end"
			);
	    	$messages = json_encode($message);
		}
	    
		//判断推送范围
		if($range=="all"){
			if(PushController::PushAll($messages)==false){
					return new Response('推送失败');
				}else{
					PushController::insert($title,$content,$pageid);
					return new Response('推送成功');
				}
		}else{
			//判断推送的类型
			if ($type==3) {//推送安卓
				if(PushController::PushAndroid($messages,$tag_name)==false){
					return new Response('安卓推送失败');
				}else{
					PushController::insert($title,$content,$pageid);
					return new Response('安卓推送成功');
				}
			}
			if ($type==4) {//推送ios
				if(PushController::PushIos($messages,$tag_name)==false){
					return new Response('Ios失败');
				}else{return new Response('Ios成功');}
			}
			if($type==34 || $type==null){
				if(PushController::PushAndroid($messages,$tag_name)==false && PushController::PushIos($messages,$tag_name)==false){
					return new Response('推送失败');
				}
				if(PushController::PushAndroid($messages,$tag_name)==false){
					return new Response('安卓推送失败');
				}
				if(PushController::PushIos($messages,$tag_name)==false){
					return new Response('Ios推送失败');
				}
				else{
					PushController::insert($title,$content,$pageid);
					return new Response('全部推送成功');
				}
			} 
	  }
}
	
	public function insert($title,$content,$pageid)
	{
		$this->get('my_datebase')->connection();
		$time=date('Y-m-d H:i:s',time());
		$sql = "insert into User_log set nickname='$title', content='$content' ,pid='$pageid' ,creation_date='$time',updated_date='$time',status=1";
		if(mysql_query($sql)){
			return new Response('', 200);
		}else{return new Response('', 500);}
	}
	
	public function picAction()
    {
        return $this->render('YdzyAdminBundle:Push:pic.html.twig');
    }  
   public function saveAction(Request $request)   //推送的时候存储页面
    {
// retrieve json object
		$json = $this->get('json_parser')->parse($request);
		
		$pagename = $json->get('pagename');
		$pageTitle = $json->get('pagetitle', 'title');
		$html = $json->get('pagecontent');
		
		$em = $this->getDoctrine()->getManager();
		$page = new pages();
		$page->setName("hehe");
		$page->setTitle($pageTitle);
		$page->setContent($html);
		$page->setcreateDate(new \DateTime("now"));
		$page->setupdateDate(new \DateTime("now"));
		$page->setStatus(0);
		$em->persist($page);
		$em->flush();
		
        //return new Response("page saved");
		$response = new JsonResponse();
		$response->setData(array(
						'Pid' => $page->getId(),
    				'pagetitle' => $page->getTitle(),
						'pagecontent' => $page->getContent()
					));

		return $response;
    }     
}

