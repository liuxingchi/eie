<?php
namespace Ydzy\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;


class LeavewordController extends Controller
{
	/*
	 *retrieve
	 */
    public function retrieveAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
    
        $this->get('my_datebase')->connection();
        $sql = "select * from leaveword order by id desc";
        //return new Response($sql);
        $result = mysql_query($sql);
        $json_array = array();
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
            
            //
            $json_array[] = $row;
        }
        return new JsonResponse($json_array);
		
    }
    
    /*
     *insert 
     */
    public function insertAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $content = $json->get('content','');
        $current = time();
        $this->get('my_datebase')->connection();
        $sql = "insert into leaveword set content='$content',creation_date=$current";
        //return new Response($sql);
        if(mysql_query($sql)){
            return new JsonResponse('插入成功了',200);
        }else{
            return new Response('',500);
        }
        
    
    }
    
    /*
     *del word
     */
    public function delWordAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('id','');
    
        if($id!=0){
            $this->get('my_datebase')->connection();
            $sql = "DELETE FROM `leaveword` WHERE (`id`=$id)";
            if(mysql_query($sql)){
                return new JsonResponse('success',200);
            }else{
                return new Response('',500);
            }
        }else{
            return new Response('',403);
        }
    
    
    }
   
    
}
