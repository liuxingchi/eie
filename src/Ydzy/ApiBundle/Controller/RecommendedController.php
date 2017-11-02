<?php
namespace Ydzy\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;


class RecommendedController extends Controller
{
    public function indexAction(Request $request)
    {
        return new Response("recommended");
    }
	
	
	//------------------------------------------------
	// 推荐列表
	//------------------------------------------------
    public function retrieveByFilterAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$start = $json->get('start',0);
		$num = $json->get('num',10);
		$keyword = $json->get('keyword','');
		
		//分页
		$limit = (($start==-1&&$num==-1)||($start==-1))?"":" limit $start,$num ";
		
		$this->get('my_datebase')->connection();
		
		$sql = "select a.*,b.name as category from recommended as a left join category as b on a.cid = b.id where a.status = 1 and a.content like '%$keyword%' order by a.id desc".$limit;
		//return new Response($sql);
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		if(!$num){
			return  new Response('没有值返回啊',400);
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
    	$json = $this->get('json_parser')->parse($request);
    	$keyword = $json->get('keyword','');
        $this->get('my_datebase')->connection();
    
        $sql = "select count(*) from recommended where status = 1 and content like '%$keyword%'";
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
    * 推荐info
    */
    public function infoAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('recommended_id',0);
        $this->get('my_datebase')->connection();
        if($id){
            $result = mysql_query("select * from recommended where id = $id limit 1");
        }
        $array_result = array();
        //$array_result = mysql_fetch_array($result,MYSQL_ASSOC);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        	$row['creation_date'] = $this->get('login')->tranTime($row['creation_date']);
        	$array_result = $row;
        }
        
        $image_result = mysql_query("select b.original_image as url,b.id from recommended_image as a left join images as b on a.image_id = b.id where a.recommended_id = $id");
        //return new Response("select b.url from recommended_image as a left join images as b on a.image_id = b.id where a.recommended_id = $id");
        $image_array = array();
        while ($row = mysql_fetch_array($image_result, MYSQL_ASSOC)) {
        	
            $image_array[] = $row;
        }
        $array_result['image_urls'] = $image_array;
        return new JsonResponse($array_result);        
        
    }
    /*
     * buy or not
     */
    public function BuyOrNotAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $recommended_id = $json->get('recommended_id',0);
        //uid 判断
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('传token啊,魂淡！！',403);
        }
    
        $this->get('my_datebase')->connection();
        $buy = mysql_result(mysql_query("select count(*) from user_recommended where user_id = $user_id and recommended_id = $recommended_id limit 1"),0);
        $unlike = mysql_result(mysql_query("select count(*) from user_unlike where user_id = $user_id and recommended_id = $recommended_id limit 1"),0);
        
        return new JsonResponse(array('buy'=>$buy,'unlike' => $unlike));
    }
    /*
     * buy
     */
    public function BuyAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $recommended_id = $json->get('recommended_id',0);
        //uid 判断
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('传token啊,魂淡！！',403);
        }
        
         
        $this->get('my_datebase')->connection();
        $sql = "select id from user_recommended where user_id = $user_id and recommended_id = $recommended_id limit 1";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if($num){
            //return new Response('已经购买过了啊，你还想买啊！',401);
            mysql_query("delete from user_recommended where user_id = $user_id and recommended_id = $recommended_id");
            return new Response('uninterested',200);
        }else{
        //insert into user_recommended
        mysql_query("insert into user_recommended set user_id = $user_id , recommended_id = $recommended_id");
        }
        return new Response('interested',200);
        
    }
    /*
     * unlike
     */
    public function UnlikeAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $recommended_id = $json->get('recommended_id',0);
        //uid 判断
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('传token啊,魂淡！！',403);
        }
    
         
        $this->get('my_datebase')->connection();
        $sql = "select id from user_unlike where user_id = $user_id and recommended_id = $recommended_id limit 1";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if($num){
            //return new Response('已经不感兴趣了，就不要再点了！',401);
            mysql_query("delete from user_unlike where user_id = $user_id and recommended_id = $recommended_id");
            return new Response('已经取消不感兴趣了状态了',200);
        }else{
        //insert into user_recommended
        mysql_query("insert into user_unlike set user_id = $user_id , recommended_id = $recommended_id");
        return new Response('好吧，已经放到你的不感兴趣列表了',200);
        }
    
    }
    /*
     * 想购买的人的列表
     */
    public function retrieveWantBuyListAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $recommended_id = $json->get('recommended_id',0);
        $start = $json->get('start',0);
        $num = $json->get('num',10);
    
    
        
        $this->get('my_datebase')->connection();
    
        $sql = "select b.id,b.icon,b.username,b.phone from user_recommended as a left join user as b on a.user_id = b.id where a.recommended_id = $recommended_id limit $start,$num";
        //return new Response($sql);
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if (! $num) {
            return new Response('', 400);
        }
        $array_result = array();
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    		$row['count'] = mysql_result(mysql_query("select count(*) from orderlist where user_id = $row[id] and status != 3 and status != 4 and publish_id = $recommended_id"),0);
    		$array_result[] = $row;
        }
        return new JsonResponse($array_result);
    
    }
    
	//------------------------------------------------
	// 发布推荐
	//------------------------------------------------
    public function addAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$content = $json->get('content','');
		$title = $json->get('title','');
		$cid = $json->get('cid',0);
		$price = $json->get('price',0);
		$image_url = $json->get('image_url','');
		$image_ids = $json->get('image_ids','');
		
		//return new Response(date('Y-m-d','1357401600'));
		if(!$image_ids){
		    return new Response('得有图片啊大哥！',401);
		}
		$current = time();
		
		
		//uid 判断
		$user_id = $this->get('login')->checkLogin($request);
		if(!$user_id){
		    //return new Response('传token啊,魂淡！！',403);
		    $user_id = 0;
		}
		
		
		
		$this->get('my_datebase')->connection();
		$sql = "insert into recommended set title='$title',user_id = $user_id,cid = $cid,price = $price,content = '$content',image_url = '$image_url', creation_date = $current,updated_date=$current";
		//return new Response($sql);
		mysql_query($sql);
		$id = mysql_insert_id();
		
		
		
		$image_array = explode(',', $image_ids);
		while ($image_one = current($image_array)) {
			//printf("%s <br />", $step_one);
			$image_sql = "insert into recommended_image set recommended_id =$id ,image_id = $image_one";
			mysql_query($image_sql);
			next($image_array);
			}
		
		return new JsonResponse($id);
		
    }
    /*
     *类别列表
     */
    public function categoryListAction(Request $request){
    
        $this->get('my_datebase')->connection();
        $sql = "select * from category";
        //return new Response($sql);
        $result = mysql_query($sql);
        $array_result = array();
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
        
            $array_result[] = $row;
        }
        	
        
        return new JsonResponse($array_result);
    }
    /*
     *updatecategory
     */
    public function updatecategoryAction(Request $request){
    	
    	$json = $this->get('json_parser')->parse($request);
    	$id = $json->get('id',-1);
    	$category = $json->get('category','');
    	$this->get('my_datebase')->connection();
    	$sql = "update category set `name`='$category' where id = $id ";
    	//return new Response($sql);
    	$result = mysql_query($sql);
    	
      return new JsonResponse('',200);
    }
    /*
     *addcategory
     */
    public function addcategoryAction(Request $request){
    	 
    	$json = $this->get('json_parser')->parse($request);
    	$category = $json->get('category','');
    	$this->get('my_datebase')->connection();
    	$sql = "insert into category set `name`='$category'";
    	//return new Response($sql);
    	$result = mysql_query($sql);
    	 
    	return new JsonResponse('',200);
    }
    /*
     *delcategory
     */
    public function delcategoryAction(Request $request){
    
    	$json = $this->get('json_parser')->parse($request);
    	$id = $json->get('id','');
    	$this->get('my_datebase')->connection();
    	//删除分类
    	mysql_query("delete from category where id = $id");
    	return new JsonResponse('',200);
    }
    /*
     *删除 
     */
    public function delAction(Request $request){
        
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('recommended_id',-1);
        $this->get('my_datebase')->connection();
        
        mysql_query("delete from recommended where id = $id");
        mysql_query("delete from recommended_image where recommended_id = $id");
        mysql_query("delete from user_recommended where recommended_id = $id");
        mysql_query("delete from orderlist where publish_id = $id");
        return new JsonResponse('删除成功',200);
    }
	
	//------------------------------------------------
	// 接订单
	//------------------------------------------------
    public function acceptAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$oid = $json->get('oid',-1);
		$this->get('my_datebase')->connection();
		//uid判断
		$user = $this->getUser();
		if(!$user){
			return new Response('',403);
			}
		$touid = $user->getId();
		//判断uid
		$result = mysql_query("select a.uid,b.phone,b.username from orderlist as a left join fos_user as b on a.uid = b.id where a.id = $oid limit 1");
		$num = mysql_num_rows($result);
		if(!$num){
			return new Response('',415);
			}
		$uid = mysql_result($result,0,'uid');
		$phone = mysql_result($result,0,'phone');
		$username = mysql_result($result,0,'username');
		
		$current = time();
		
		$this->get('my_datebase')->connection();
		
		//更改orderlist表的mark和showlinker字段  将mark变成1表示此订单变成待指派类型的
		mysql_query("update orderlist set showlinker=1 ,mark=1,flag=0 where id = $oid");
		
		$sql = "insert into userorder set uid = $uid,touid = $touid,status = 1,oid = $oid,creation_date = $current,updated_date=$current";
		$result = mysql_query($sql);
		//$id = mysql_insert_id();
		$json_result = array(
				
				'username'=>$username,
				'phone'=>$phone,
				'uid'=>$uid
		);
		
		
		
		return new JsonResponse($json_result);
		
    }
    
    /*
     * 取消接单（跟接单的功能相反）
     */
    
    public function cancelacceptAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $oid = $json->get('oid',-1);
        $this->get('my_datebase')->connection();
        //uid判断
        $user = $this->getUser();
        if(!$user){
            return new Response('',403);
        }
        //当前登陆者--接单者id
        $touid = $user->getId();
        //判断是否存在该订单
        $result = mysql_query("select a.uid,b.phone,b.username from orderlist as a left join fos_user as b on a.uid = b.id where a.id = $oid limit 1");
        $num = mysql_num_rows($result);
        if(!$num){
            return new Response('',415);
        }
        $uid = mysql_result($result,0,'uid');
        $this->get('my_datebase')->connection();
    
        //更改orderlist表的mark和showlinker字段  将mark变成1表示此订单变成待指派类型的（前提是判断是否有其他的人接过此单）
        $result = mysql_query("select * from userorder where uid = $uid and touid!=$touid and status = 1 and oid = $oid");
        $num = mysql_num_rows($result);
        if(!$num){//表示没有其他的高手接单，所以将mark=0，showlinker=0
            //return new Response("delete from userorder where uid = $uid and touid = $touid and oid = $oid");
            mysql_query("update orderlist set showlinker=0 ,mark=0 ,flag = 0 where id = $oid");
            mysql_query("delete from userorder where uid = $uid and touid = $touid and oid = $oid");
            return new Response('成功',200);
        }else{//直接删除userorder里的关系
            //return new Response("delete from userorder where uid = $uid and touid = $touid and oid = $oid");
            mysql_query("delete from userorder where uid = $uid and touid = $touid and oid = $oid");
            return new Response('成功',200);
        }
        
        
    
    }
	//------------------------------------------------
	// 订单的所有接单人信息
	//------------------------------------------------
    public function touidlistAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$oid = $json->get('oid',-1);
		
		$this->get('my_datebase')->connection();
		//uid判断
		$user = $this->getUser();
		if(!$user){
			return new Response('',403);
			}
		
        $sql = "select a.*,b.icon,b.phone,b.username,b.email,b.qq,c.area as city,b.summary,b.cid,b.offdateastouid,b.speed,b.attitude,b.quality from userorder as a left join fos_user as b on a.touid = b.id left join area as c on b.cityid = c.id where a.oid = $oid";
		$result = mysql_query($sql);
		if(!mysql_num_rows($result)){
			return new Response('没数据',400);
			}
	    $array_result = array();
	    $session = $this->getRequest()->getSession();
	    $quality_session = $session->get('quality');
	    $speed_session = $session->get('speed');
	    $attitude_session = $session->get('attitude');
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
		    $row['quality_ave'] = $quality_session;
		    $row['speed_ave']=$speed_session;
		    $row['attitude_ave']=$attitude_session;
		    $result1 = mysql_query("select count(id) as count,sum(total_price) as price from orderlist where mark = 14  and touid = $row[touid]");
		    $row['count']=mysql_result($result1,0,'count');
		    $row['price']=mysql_result($result1,0,'price');
		    $row['city']==null?$row['city']="":$row['city']=$row['city'];
		    $row['summary']==null?$row['summary']="":$row['summary']=$row['summary'];
			$array_result[] = $row;
			}
		
		return new JsonResponse($array_result);
		
    }
	
	
	//------------------------------------------------
	// 发单者和接单者对order的评价
	//------------------------------------------------
    public function commentAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$oid = $json->get('oid',-1);
		$content = $json->get('content','');
		$rate = $json->get('rate',-1);
		$quality = $json->get('quality',-1);
		$speed = $json->get('speed',-1);
		$attitude = $json->get('attitude',-1);
		$mark = $json->get('mark',-1);
		$current = time();
		
		$this->get('my_datebase')->connection();
		
		$result = mysql_query("select uid,touid from orderlist where id = $oid");
		$uid = mysql_result($result,0,'uid');
		$touid = mysql_result($result,0,'touid');
		if($mark==1){
			$sql="insert into comment set oid=$oid,uid=$uid,touid=$touid,rate=$rate,content='$content',mark=1,creation_date=$current";
			}else if($mark==0){
				$sql = "insert into comment set oid=$oid,uid=$uid,touid=$touid,quality=$quality,speed=$speed,attitude=$attitude,content='$content',mark=0,creation_date=$current";
				}
		$result = mysql_query($sql);
		$id = mysql_insert_id();
		return new JsonResponse($id);    
    }
	
	
	//------------------------------------------------
	// 订单(草稿)的状态改变
	//------------------------------------------------
    public function updateAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$content = $json->get('content','');
		$title = $json->get('title','');
		$cid = $json->get('cid',0);
		$id = $json->get('id',0);
		$price = $json->get('price',0);
		$image_url = $json->get('image_url','');
		$image_ids = $json->get('image_ids','');
		
		//return new Response(date('Y-m-d','1357401600'));
		if(!$image_ids){
		    return new Response('得有图片啊大哥！',401);
		}
		$current = time();
		
		
		//uid 判断
		$user_id = $this->get('login')->checkLogin($request);
		if(!$user_id){
		    //return new Response('传token啊,魂淡！！',403);
		    $user_id = 0;
		}
		
		
		
		$this->get('my_datebase')->connection();
		$sql = "update recommended set title='$title',user_id = $user_id,cid = $cid,price = $price,content = '$content',image_url = '$image_url',updated_date=$current where id=$id";
		//return new Response($sql);
		mysql_query($sql);
		//$id = mysql_insert_id();
		
		mysql_query("delete from recommended_image where recommended_id = $id");
		
		
		$image_array = explode(',', $image_ids);
		while ($image_one = current($image_array)) {
			//printf("%s <br />", $step_one);
			$image_sql = "insert into recommended_image set recommended_id =$id ,image_id = $image_one";
			mysql_query($image_sql);
			next($image_array);
			}
		
		return new JsonResponse($id);
		  
    }
	//------------------------------------------------
	// 查看所有的折扣信息
	//------------------------------------------------
    public function discountlistAction(Request $request)
    {
		
		$this->get('my_datebase')->connection();
		
		$sql = "select * from discount";
		$result = mysql_query($sql);
		$array_result = array();
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
			$array_result[] = $row; 
			}
		return new JsonResponse($array_result);
		
    }
	
	//------------------------------------------------
	// 工作阶段
	//------------------------------------------------
    public function stepAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$end_date = $json->get('end_date',-1);
		$flag = $json->get('flag',-1);
		$workcontent = $json->get('workcontent','');
		$percent = $json->get('percent','');
		
		$this->get('my_datebase')->connection();
		
		$sql = "insert into step set flag = '$flag',percent = $percent, workcontent='$workcontent',end_date =$end_date";
		mysql_query($sql);
		$id = mysql_insert_id();
		return new JsonResponse($id);    
    }
    
    //------------------------------------------------
    // 工作阶段的更改
    //------------------------------------------------
    public function stepupdateAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('id',0);
        $end_date = $json->get('end_date',-1);
        $flag = $json->get('flag',-1);
        $workcontent = $json->get('workcontent','');
        $percent = $json->get('percent','');
        
        $end_date!=-1?$end_date_sql = " end_date = $end_date,":$end_date_sql = "";
        $flag!=-1?$flag_sql = "flag = '$flag',":$flag_sql = "";
        $workcontent!=''?$workcontent_sql = " workcontent = '$workcontent',":$workcontent_sql = "";
        $percent!=''?$percent_sql = " percent = $percent,":$percent_sql = "";
    
        $this->get('my_datebase')->connection();
    
        $sql = "update step set ".$end_date_sql.$flag_sql.$workcontent_sql.$percent_sql." status = 1 where id = $id";
        mysql_query($sql);
        return new JsonResponse($id);
    }
    //------------------------------------------------
    // 工作阶段的删除
    //------------------------------------------------
    public function stepdelAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('id',0);
    
        
    
        $this->get('my_datebase')->connection();
    
        $sql = "delete from step where id = $id";
        mysql_query($sql);
        return new JsonResponse('删除成功',200);
    }

	//------------------------------------------------
	// 折扣的修改和发布
	//------------------------------------------------
    public function discountAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$discount = $json->get('discount','');
		
		$this->get('my_datebase')->connection();
		
		$sql = "insert into discount set discount = '$discount'";
		mysql_query($sql);
		$id = mysql_insert_id();
		return new JsonResponse($id);    
    }

	//------------------------------------------------
	// 接单者进度安排
	//------------------------------------------------
    public function rateAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$rate = $json->get('rate','');
		$oid = $json->get('oid','');
		$vids = $json->get('vids','');
		$content = $json->get('content','');
		$current = time();
		$this->get('my_datebase')->connection();
		//首先先看有没有100%
		$num = mysql_num_rows(mysql_query("select id from orderrate where oid = $oid and rate = 100 limit 1"));
		if($num){
		    return new Response('此订单已经完成到100%，不能再添加',415);
		}
		$sql = "insert into orderrate set rate = $rate,oid=$oid,content='$content',creation_date=$current";
		mysql_query($sql);
		$id = mysql_insert_id();
		if($vids!=""){
		    $vids_array = explode(',',$vids);
		    
		    for($index=0;$index<count($vids_array);$index++){
		        //echo $vids_array[$index];
		        mysql_query("insert into orderversion set oid = $oid ,rid = $id,vid=$vids_array[$index],creation_date=$current,updated_date=$current");
		    }
		}
		return new JsonResponse($id);    
    }
    
    //------------------------------------------------
    // 进度安排的列表页
    //------------------------------------------------
    public function retrieverateAction(Request $request)
    {
        $oid = $request->get('oid',0);
        $start = $request->get('start',0);
        $num = $request->get('num',10);
    
        $this->get('my_datebase')->connection();
    
        $sql = "select * from orderrate where oid = $oid order by id desc limit $start,$num";
        $result = mysql_query($sql);
        $array_result= array();
       
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
		    //$row['filepath'] == null?$row['filepath']="":$row['filepath']=$row['filepath'];
		    //echo $row['id'];
		    $sql1 = "select b.* from orderversion as a left join Version as b on a.vid = b.id where rid = $row[id] order by a.id desc";
		    $result1 = mysql_query($sql1);
		    $array_result1= array();
		    while($rs = mysql_fetch_array($result1,MYSQL_ASSOC)){
		        $array_result1[] = array(
		            'id'=>$rs['id'],
		            'name'=>$rs['name'],
		            'filepath'=>$rs['filepath'] 
		        );
		    }
		    
		    $row['creation_date'] = date('Y-m-d H:i:s',$row['creation_date']);
			$array_result[] = array(
			    'id'=>$row['id'],
			    'oid'=>$row['oid'],
			    'rate'=>$row['rate'],
			    'content'=>$row['content'],
			    'recommend'=>$row['recommend'],
			    'creation_date'=>$row['creation_date'],
			    'file'=>$array_result1
			);
			//print_r($array_result1);
			//$array_result2[] = array_merge_recursive($array_result,array('file'=>$array_result1));
		}
			
		return new JsonResponse($array_result);
    }
    
	//------------------------------------------------
	// 发单者对接单者进度安排的评价
	//------------------------------------------------
    public function raterecommendAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$rid = $json->get('rid','');
		$recommend = $json->get('recommend',0);
		
		$this->get('my_datebase')->connection();
		
		$sql = "update orderrate set recommend = $recommend where id = $rid";
		mysql_query($sql);
		return new JsonResponse($rid);    
    }

	//------------------------------------------------
	// 完成者和订单的关系
	//------------------------------------------------
    public function compaleterateAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$oid = $json->get('oid','');
		$vids = $json->get('vids','');
		$usernamerate = $json->get('usernamerate','');
		$content = $json->get('content','');
		$current = time();
        $attr_array = json_decode($usernamerate,true);
		
		while (list($key, $val) = each($attr_array)){
			$sql = "insert into compaleterate set rate = $val,oid=$oid,username='$key'";
			mysql_query($sql);
			}
		mysql_query("insert into orderrate set rate = 100,oid=$oid,content='$content',creation_date=$current");
		$id = mysql_insert_id();
		//将mark值变成10，flag变成3
		mysql_query("update orderlist set mark = 11, flag = 3,updated_date = $current,`uploadresult` = $current where id = $oid");
		if($vids!=""){
		    $vids_array = explode(',',$vids);
		
		    for($index=0;$index<count($vids_array);$index++){
		        //echo $vids_array[$index];
		        mysql_query("insert into orderversion set oid = $oid ,rid = $id,vid=$vids_array[$index],creation_date=$current,updated_date=$current");
		    }
		}
		return new JsonResponse('成功',200);     
    }
    //------------------------------------------------
    // 订单的完成者列表
    //------------------------------------------------
    public function compaleteratelistAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $oid = $json->get('oid','');
        $this->get('my_datebase')->connection();
        
    
        $sql = "select * from compaleterate where oid=$oid";
        $result = mysql_query($sql);
        $array_result = array();
        while($rs = mysql_fetch_array($result,MYSQL_ASSOC)){
            
            $array_result[] = $rs;
        
        }
        $sql1 = "select * from orderrate where oid=$oid  order by id desc limit 1";
        $result1 = mysql_query($sql1);
        $array_result1 = array();
        while($rs = mysql_fetch_array($result1,MYSQL_ASSOC)){
            $rs['creation_date'] = date('Y-m-d H:i:s',$rs['creation_date']);
            $array_result1 = $rs;
        
        }
        $json_result = array(
            'rate'=>$array_result1,
            'compalete'=>$array_result
            
        );
        return new JsonResponse($json_result);
    }
    
    //------------------------------------------------
    // 附件增加说明
    //------------------------------------------------
    public function versiondesAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	$vid = $json->get('vid',0);
    	$description = $json->get('description',0);
    
    	$this->get('my_datebase')->connection();
    
    	mysql_query("update Version set description = '$description' where id=$vid");
    	return new Response($vid);
    }
    //------------------------------------------------
    // 订单和附件的关系表
    //------------------------------------------------
    public function orderversionAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	$oid = $json->get('oid',0);
    	$vid = $json->get('vid',0);
    	$rid = $json->get('rid',0);
    	$current = time();
    
    	$this->get('my_datebase')->connection();
    
    	mysql_query("insert into orderversion set rid=$rid,oid = $oid,vid=$vid,creation_date='$current',updated_date='$current',status=1");
    	$id = mysql_insert_id();
    	return new JsonResponse($id);
    }
    //------------------------------------------------
    // 查看附件的详情
    //------------------------------------------------
    public function versioninfoAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	$vid = $json->get('vid',0);
    
    	$this->get('my_datebase')->connection();
    
    	$result = mysql_query("select * from Version where id = $vid");
    	$num = mysql_num_rows($result);
		if(!$num){
			return  new Response('',400);
		}
		$array_result = array();
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
			$array_result[] = $row;
		}
    	return new JsonResponse($array_result);
    }
    /*
     * 根据id来更改ordernum
     */
    public function ordernumAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $orderlist = $json->get('orderlist','');
        $this->get('my_datebase')->connection();
        foreach($orderlist as $key=>$order){
            $sql = "update orderlist set ordernum = $key where id = $order";
            mysql_query($sql);
        }
        
        return new JsonResponse('',200);
    }
    
}
