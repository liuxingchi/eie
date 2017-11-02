<?php
namespace Ydzy\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;


class NewsController extends Controller
{
	
	//------------------------------------------------
	// 列表
	//------------------------------------------------
    public function retrieveByFilterAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$start = $json->get('start',0);
		$num = $json->get('num',10);
		
		$this->get('my_datebase')->connection();
		
		$sql = "select id,title,summary,image_url,creation_date from news order by id desc limit $start,$num";
		//return new Response($sql);
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		if(!$num){
			return  new Response('',400);
		}
		$array_result = array();
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
		    $row['creation_date'] = $this->get('login')->tranTime($row['creation_date']);
		    $array_result[] = $row;
		    
			}
			
		
		return new JsonResponse($array_result);
		
    }
    //------------------------------------------------
    // 推荐列表总数据
    //------------------------------------------------
    public function retrieveByFilterNumAction(Request $request)
    {
        $this->get('my_datebase')->connection();
    
        $sql = "select count(*) from news";
        //return new Response($sql);
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if(!$num){
            return  new Response(0);
        }
       
        $count = mysql_result($result,0);
        	
        return new Response($count);
    
    }
   /*
    * 新闻info
    */
    public function infoAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('news_id',0);
        $this->get('my_datebase')->connection();
        if($id){
            $result = mysql_query("select * from news where id = $id limit 1");
        }
        $array_result = array();
        
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $row['creation_date'] = date('Y-m-d',$row['creation_date']);
            $array_result = $row;
        }
        return new JsonResponse($array_result);
        
        
    }
   
    
	//------------------------------------------------
	// 发布新闻
	//------------------------------------------------
    public function addAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$content = $json->get('content','');
		$title = $json->get('title','');
		$summary = $json->get('summary','');
		$image_url = $json->get('image_url','');
		
		//return new Response(date('Y-m-d','1357401600'));
		
		$current = time();
		
		$this->get('my_datebase')->connection();
		$sql = "insert into news set title = '$title',summary='$summary',content = '$content',image_url = '$image_url', creation_date = $current,updated_date=$current";
		//return new Response($sql);
		mysql_query($sql);
		$id = mysql_insert_id();
		return new JsonResponse($id);
		
    }
    
    //------------------------------------------------
    // update新闻
    //------------------------------------------------
    public function updateAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	$id = $json->get('id','');
    	$content = $json->get('content','');
    	$title = $json->get('title','');
    	$summary = $json->get('summary','');
    	$image_url = $json->get('image_url','');
    
    	//return new Response(date('Y-m-d','1357401600'));
    
    	$current = time();
    
    	$this->get('my_datebase')->connection();
    	//mysql_query("delete from news where id = $id");
    	$sql = "update news set title = '$title',summary='$summary',content = '$content',image_url = '$image_url', creation_date = $current,updated_date=$current where id =$id";
    	//return new Response($sql);
    	mysql_query($sql);
    	$id = mysql_insert_id();
    	return new JsonResponse($id);
    
    }
    
    /*
     *删除 
     */
    public function delAction(Request $request){
        
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('news_id',-1);
        $this->get('my_datebase')->connection();
        
        mysql_query("delete from news where id = $id");
        return new JsonResponse('删除成功',200);
    }
	
	
    
}
