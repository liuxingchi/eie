<?php
namespace Ydzy\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;


class PreshowController extends Controller
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
		
		$sql = "select * from preshow order by id desc limit $start,$num";
		//return new Response($sql);
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		if(!$num){
			return  new Response('',400);
		}
		$array_result = array();
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
			$row['creation_date'] = $this->get('login')->tranTime($row['creation_date']);
			$row['showtime'] = date('Y-m-d H:i:s',$row['showtime']);
        	$row['auctiontime'] = date('Y-m-d H:i:s',$row['auctiontime']);
        	$row['endtime'] = date('Y-m-d H:i:s',$row['endtime']);
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
    
        $sql = "select count(*) from preshow";
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
    * 预展info
    */
    public function infoAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('preshow_id',0);
        $this->get('my_datebase')->connection();
        if($id){
            $result = mysql_query("select * from preshow where id = $id limit 1");
        }
    	$array_result = array();
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
        	$row['showtime'] = date('Y-m-d H:i:s',$row['showtime']);
        	$row['auctiontime'] = date('Y-m-d H:i:s',$row['auctiontime']);
        	$row['endtime'] = date('Y-m-d H:i:s',$row['endtime']);
        	$array_result = $row;
        }
    	
        $sql = "select b.original_image as url,b.id from preshow_image as a left join images as b on a.image_id = b.id where a.preshow_id = $id";
		//return new Response($sql);
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		$array_images = array();
		if($num){
			
			while($row1 = mysql_fetch_array($result,MYSQL_ASSOC)){
			    $array_images[] = $row1;
				}
				
		}
		$array_result['images'] = $array_images;
        return new JsonResponse($array_result);
        
        
    }
   
    
	//------------------------------------------------
	// 发布
	//------------------------------------------------
    public function addAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$title = $json->get('title','');
		$description = $json->get('description','');
		$content = $json->get('content','');
		$showtime = $json->get('showtime',time());
		$endtime = $json->get('endtime',time());
		$showaddress = $json->get('showaddress','');
		$auctiontime = $json->get('auctiontime',time());
		$auctionaddress = $json->get('auctionaddress','');
		$image_ids = $json->get('image_ids','');
		
		/* if(!$image_ids){
			return new Response('得有图片啊大哥！',401);
		} */
		//return new Response(date('Y-m-d','1357401600'));
		
		$current = time();
		
		$this->get('my_datebase')->connection();
		$sql = "insert into preshow set title = '$title',description = '$description',content = '$content',showtime = $showtime,showaddress='$showaddress',auctiontime=$auctiontime,endtime=$endtime,auctionaddress='$auctionaddress', creation_date = $current,updated_date=$current";
		//return new Response($sql);
		mysql_query($sql);
		$id = mysql_insert_id();
		
		
		
		if($image_ids){
			$image_ids = ltrim($image_ids, ",");
			if(strstr($image_ids,",")){
				$image_array = explode(',', $image_ids);
				while ($image_one = current($image_array)) {
					//printf("%s <br />", $step_one);
					$image_sql = "insert into preshow_image set preshow_id =$id ,image_id = $image_one";
					mysql_query($image_sql);
					next($image_array);
				}
			}else{
				mysql_query("insert into preshow_image set preshow_id =$id ,image_id = $image_ids");
			}
		}
		
		return new JsonResponse($id);
		
    }
    
    //------------------------------------------------
    // update
    //------------------------------------------------
    public function updateAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	$id=$json->get('id',0);
    	$title = $json->get('title','');
    	$description = $json->get('description','');
    	$content = $json->get('content','');
    	$showtime = $json->get('showtime',0);
    	$endtime = $json->get('endtime',0);
    	$showaddress = $json->get('showaddress','');
    	$auctiontime = $json->get('auctiontime',0);
    	$auctionaddress = $json->get('auctionaddress','');
    	$image_ids = $json->get('image_ids','');
    
    	/* if(!$image_ids){
    		return new Response('得有图片啊大哥！',401);
    	} */
    	//return new Response(date('Y-m-d','1357401600'));
    
    	$current = time();
    
    	$this->get('my_datebase')->connection();
    	$sql = "update preshow set title = '$title',description = '$description',content = '$content',showtime = $showtime,showaddress='$showaddress',auctiontime=$auctiontime,endtime=$endtime,auctionaddress='$auctionaddress',updated_date=$current where id = $id";
    	//return new Response($sql);
    	mysql_query($sql);
    	//$id = mysql_insert_id();
    	mysql_query("delete from preshow_image where preshow_id = $id");
    	$image_array = explode(',', $image_ids);
    	while ($image_one = current($image_array)) {
    		//printf("%s <br />", $step_one);
    		$image_sql = "insert into preshow_image set preshow_id =$id ,image_id = $image_one";
    		mysql_query($image_sql);
    		next($image_array);
    	}
    
    	return new JsonResponse($id);
    
    }
    
    /*
     *删除 
     */
    public function delAction(Request $request){
        
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('preshow_id',-1);
        $this->get('my_datebase')->connection();
        
        mysql_query("delete from preshow where id = $id");
        return new JsonResponse('删除成功',200);
    }
	
	
    
}
