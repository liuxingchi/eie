<?php
namespace Ydzy\ApiBundle\Service;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
require_once "lib/Channel.class.php";
class Push
{
	
	//用户下线推送ios
	public function ios_push($user_id,$channel_id,$uid,$touid,$content,$icon){
		$apikey="YV0XuQGoluM7TW7V0toAYUAL";
		$secretkey = "uCXqtErbUVmIRfDxNWqmKVqAq5FQH0jg";
		$channel = new \Channel ( $apikey, $secretkey ) ;
	
	$push_type = 1; //推送单播消息
	$optional[\Channel::USER_ID] = $user_id; //如果推送单播消息，需要指定user
	$optional[\Channel::CHANNEL_ID] = $channel_id;

	//指定发到ios设备
	$optional[\Channel::DEVICE_TYPE] = 4;
	//指定消息类型为通知
	$optional[\Channel::MESSAGE_TYPE] = 1;
	//如果ios应用当前部署状态为开发状态，指定DEPLOY_STATUS为1，默认是生产状态，值为2.
	//旧版本曾采用不同的域名区分部署状态，仍然支持。
	$optional[\Channel::DEPLOY_STATUS] = 1;
	//通知类型的内容必须按指定内容发送，示例如下：
	$mymessage = '{
			"icon": "'.$icon.'",
			"uid": "'.$uid.'",
			"content":"'.$content.'",
			"touid": "'.$touid.'"
 		}';
	$message = '{ 
		"aps":{
			"alert":"'.$content.'",
			"sound":"",
			"badge":0
		},
        "icon": "'.$icon.'",
		"uid": "'.$uid.'",
		"touid": "'.$touid.'"
 	}';
	
	$message_key = "msg_key";
    $ret = $channel->pushMessage ( $push_type, $message, $message_key, $optional ) ;
    return new Response('',200);
    if ( false === $ret )
    {
        echo ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!!' ) ;
        echo ( 'ERROR NUMBER: ' . $channel->errno ( ) ) ;
        echo ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) ) ;
        echo ( 'REQUEST ID: ' . $channel->getRequestId ( ) );
    }
    else
    {
        echo ( 'SUCC, ' . __FUNCTION__ . ' OK!!!!!' ) ;
        echo ( 'result: ' . print_r ( $ret, true ) ) ;
    }
}
	//推送安卓下线通知
	public function android_push($user_id,$channel_id,$uid,$touid,$content,$icon){
		$apikey="YV0XuQGoluM7TW7V0toAYUAL";
		$secretkey = "uCXqtErbUVmIRfDxNWqmKVqAq5FQH0jg";
	    $channel = new \Channel($apikey, $secretkey);
	    $push_type = 1;//单个设备的推送
	    $message_keys = 'msg_key';
	    //消息透传
	    //$optional[\Channel::MESSAGE_TYPE] = 0;
		$optional[\Channel::USER_ID] = $user_id; //如果推送单播消息，需要指定user
		$optional[\Channel::CHANNEL_ID] = $channel_id; 
		$message = '{
			"icon": "'.$icon.'",
			"uid": "'.$uid.'",
			"content":"'.$content.'",
			"touid": "'.$touid.'"
 		}';
		
	    $ret = $channel->pushMessage($push_type, $message, $message_keys,$optional);
		return new Response('',200);
	    if ( false === $ret )
		{
			echo ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!!' ) ;
			echo ( 'ERROR NUMBER: ' . $channel->errno ( ) ) ;
			echo ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) ) ;
			echo ( 'REQUEST ID: ' . $channel->getRequestId ( ) );
		}
		else
		{
			echo ( 'SUCC, ' . __FUNCTION__ . ' OK!!!!!' ) ;
			echo ( 'result: ' . print_r ( $ret, true ) ) ;
		} 

	} 	
	
 public function android_push_one($user_id,$channel_id,$content){
        $apikey="YV0XuQGoluM7TW7V0toAYUAL";
		$secretkey = "uCXqtErbUVmIRfDxNWqmKVqAq5FQH0jg";
	    $channel = new \Channel($apikey, $secretkey);
	    $push_type = 1;//单个设备的推送
	    $message_keys = 'msg_key';
		$optional[\Channel::USER_ID] = $user_id; //如果推送单播消息，需要指定user
		$optional[\Channel::CHANNEL_ID] = $channel_id; 
		$optional[\Channel::MESSAGE_TYPE] = 1; //0透传 1消息
		
		
		$message = '{ 
			"title": "系统通知",
			"description": "'.$content.'",
			"notification_basic_style":7,
			"open_type":1,
			"url":"http://www.baidu.com"
 		}';
		
	    $ret = $channel->pushMessage($push_type, $message, $message_keys,$optional);
	    return new Response('',200);
	    if ( false === $ret )
		{
			echo ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!!' ) ;
			echo ( 'ERROR NUMBER: ' . $channel->errno ( ) ) ;
			echo ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) ) ;
			echo ( 'REQUEST ID: ' . $channel->getRequestId ( ) );
			//return new Response('推送失败',406);
		}
		else
		{
			echo ( 'SUCC, ' . __FUNCTION__ . ' OK!!!!!' ) ;
			echo ( 'result: ' . print_r ( $ret, true ) ) ;
			//return  new Response('推送成功',200);
		}
			
 }
 public function push_all_ios($title,$content){
     $apikey="YV0XuQGoluM7TW7V0toAYUAL";
     $secretkey = "uCXqtErbUVmIRfDxNWqmKVqAq5FQH0jg";
     $channel = new \Channel($apikey, $secretkey);
     $push_type = 3;//所有的设备推送
     $message_keys = 'msg_key';
     //指定发到ios设备
	$optional[\Channel::DEVICE_TYPE] = 4;
	//指定消息类型为通知
	$optional[\Channel::MESSAGE_TYPE] = 1;
	//如果ios应用当前部署状态为开发状态，指定DEPLOY_STATUS为1，默认是生产状态，值为2.
	//旧版本曾采用不同的域名区分部署状态，仍然支持。
	$optional[\Channel::DEPLOY_STATUS] = 1;
	//通知类型的内容必须按指定内容发送，示例如下：
	$message = '{ 
		"aps":{
			"alert":"'.$content.'",
			"sound":"",
			"badge":0
		}
 	}';
	
	$message_key = "msg_key";
    $ret = $channel->pushMessage ( $push_type, $message, $message_key, $optional ) ;
 
 
     /* $message = '{
			"title": "'.$title.'",
			"description": "'.$content.'",
			"notification_basic_style":7,
			"open_type":1,
			"url":"http://www.baidu.com"
 		}'; */
     /* $message = '{
                "title": "'.$title.'",
			    "description": "'.$content.'",
                "aps": {
                	"alert":"'.$title.'",
                	"Sound":"",
                	"Badge":0
                	}
        }';
 
     $ret = $channel->pushMessage($push_type, $message, $message_keys,$optional); */
     return new Response('',200);
     if ( false === $ret )
     {
         echo ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!!' ) ;
         echo ( 'ERROR NUMBER: ' . $channel->errno ( ) ) ;
         echo ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) ) ;
         echo ( 'REQUEST ID: ' . $channel->getRequestId ( ) );
         //return new Response('推送失败',406);
     }
     else
     {
         echo ( 'SUCC, ' . __FUNCTION__ . ' OK!!!!!' ) ;
         echo ( 'result: ' . print_r ( $ret, true ) ) ;
         //return  new Response('推送成功',200);
     }
     	
 }
 
 public function push_all_android($title,$content){
     $apikey="YV0XuQGoluM7TW7V0toAYUAL";
     $secretkey = "uCXqtErbUVmIRfDxNWqmKVqAq5FQH0jg";
     $channel = new \Channel($apikey, $secretkey);
     $push_type = 3;//所有的设备推送
     $message_keys = 'msg_key';
      
     //指定消息类型为通知
     $optional[\Channel::MESSAGE_TYPE] = 1;
 
     $message = '{
      "title": "'.$title.'",
      "description": "'.$content.'"
 
      }';
 
     $ret = $channel->pushMessage($push_type, $message, $message_keys,$optional);
     return new Response('',200);
     if ( false === $ret )
     {
         echo ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!!' ) ;
         echo ( 'ERROR NUMBER: ' . $channel->errno ( ) ) ;
         echo ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) ) ;
         echo ( 'REQUEST ID: ' . $channel->getRequestId ( ) );
         //return new Response('推送失败',406);
     }
     else
     {
         echo ( 'SUCC, ' . __FUNCTION__ . ' OK!!!!!' ) ;
         echo ( 'result: ' . print_r ( $ret, true ) ) ;
         //return  new Response('推送成功',200);
     }
 
 }
		
	
}