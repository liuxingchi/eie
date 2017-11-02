<?php

namespace Ydzy\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class PushController extends Controller
{
    public function indexAction(Request $request)
    {
        return new Response("jx push");
    }

	//------------------------------------------------
	// 服务器图片
	//------------------------------------------------
    public function picAction(Request $request)
    {
		
		$this->get('my_datebase')->connection();
		$sql = "select b.thumbnail from Local_pic as a left join images as b on a.image_id=b.id limit 1";
		$pic_url = mysql_result(mysql_query($sql),0);
		$json_result=array('url'=>$pic_url);
		$response = new Response(json_encode($json_result));	
		return $response;
    }
	//------------------------------------------------
	// 更改服务器图片
	//------------------------------------------------
    public function changepicAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$image_id = $json->get('image_id','');
		$this->get('my_datebase')->connection();
		$sql = "update Local_pic set image_id =$image_id where id = 1";
		if(mysql_query($sql)){
			return new Response('', 200);
			}else{
				
			return new Response('', 500);
			
			}
    }
		
	//获得所有的推送信息
	public function getAllAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$start = $json->get('start',-1);
		$num = $json->get('num',-1);
		//分页
		$limit = (($start==-1&&$num==-1)||($start==-1))?"":" limit $start,$num ";
		
		
		$this->get('my_datebase')->connection();
		$sql = "select * from User_log where uid = 0 and status=1 ".$limit;
		$result = mysql_query($sql);
		$num   = mysql_num_rows($result);
		if (!$num){
			return new Response('', 400);
		}
		while($row = mysql_fetch_array($result)){
			   $json_result[]=array(
			   	  'id'=>$row['id'],
			      'title' => $row['nickname'],
				  'uid'=>$row['uid'],
				  'pid'=>$row['pid'],
				  'content' =>$row['content'],
		          'creation_date' => $row['creation_date']
		  		  );
		}
		
		$response = new Response(json_encode($json_result));
			
		return $response;
    }
	//删除推送信息
	public function delAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$id = $json->get('id','');
		$this->get('my_datebase')->connection();
		$sql = "update User_log set status = 0 where id = $id";
		$result = mysql_query($sql);
		if(mysql_query($sql)){
			return new Response('', 200);
			}else{
				
			return new Response('', 500);
		}
		
		
    }
	
    public function onepushtmlAction(Request $request)
    {
    	  
        $json = $this->get('json_parser')->parse($request);
				$pid = $json->get('pid','');
				$em = $this->getDoctrine()->getManager();
				$pushhtml = $em->getRepository('YdzyApiBundle:pages')->findOneById($pid);
    		$content = $pushhtml->getContent();
    		$title = $pushhtml->getTitle();
    		
    		$json_r = array(
    				'content' => $content,
    				'ptitle' => $title
    		);
    		return new JsonResponse($json_r);
    }

}
