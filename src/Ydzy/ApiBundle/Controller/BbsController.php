<?php
namespace Ydzy\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Symfony\Component\HttpFoundation;

require_once "wxpay/lib/WxPay.Api.php";

class BbsController extends Controller
{
    public function indexAction(Request $request)
    {
        return new Response("bbs");
    }
    
    /*
     * 微信 统一下单
     */
    public function createWeixinPayAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	$mark = $json->get('mark',1);//1三星 2五星 3三星升五星
    	$session = $this->getRequest()->getSession();
    	$vip3_money = $session->get('vip3_money');
    	$vip5_money = $session->get('vip5_money');
    	$vip3_vip5 = $session->get('vip3_vip5');
    	$current = time();
    	//uid 判断
    	$user_id = $this->get('login')->checkLogin($request);
    	if(!$user_id){
    		return new Response('传token啊,魂淡！！',403);
    	}
    	
    	$grade = $this->get("login")->returnGrade($request);
    	
    	if($grade==1&&$mark==1){
    		return new Response('你已经是三星会员，无需购买',401);
    	}
    	if($grade==2&&$mark==2){
    		return new Response('你已经是五星会员，无需购买',402);
    	}
    	
    	$out_trade_no = time().rand(0000, 9999);
    	
    	if($mark==1){
    		$money = $vip3_money;
    		$body = "充值成为三星会员";
    	}else if ($mark==2){
    		$money = $vip5_money;
    		$body = "充值成为五星会员";
    	}else if ($mark==3){
    		$money = $vip3_vip5;
    		$body = "充值成为五星会员";
    	}else{
    		$money = 0;
    	}
    	
    	
    	$input = new \WxPayUnifiedOrder();
    	$input->SetBody($body);
    	$input->SetAttach("");
    	$input->SetOut_trade_no($out_trade_no);
    	$input->SetTotal_fee($money*100);
    	$input->SetTime_start(date("YmdHis"));
    	$input->SetTime_expire(date("YmdHis", time() + 600));
    	$input->SetGoods_tag("会员充值");
    	$input->SetNotify_url("http://eie.ren/wxpay/example/notify.php");
    	$input->SetTrade_type("APP");
    	$order = \WxPayApi::unifiedOrder($input);
    	$order = array_merge($order,array('out_trade_no'=>$out_trade_no));
    	$this->get('my_datebase')->connection();
    	mysql_query("insert into nodify set payway = 1,out_trade_no = '$out_trade_no',mark=$mark,status=0,user_id = $user_id,creation_date = $current");
    	return new JsonResponse($order);
    }
	
    /*
     * 获得支付的价格，和唯一的out_trade_no
     */
    public function createPayAction(Request $request){
        
        $priKey = file_get_contents("alipay/key/rsa_private_key.txt");
        $pubKey = file_get_contents("alipay/key/alipay_public_key.txt");
        //return new Response($pubKey);
        $json = $this->get('json_parser')->parse($request);
        $mark = $json->get('mark',1);//1三星 2五星 3三星升五星
        $session = $this->getRequest()->getSession();
        $vip3_money = $session->get('vip3_money');
        $vip5_money = $session->get('vip5_money');
        $vip3_vip5 = $session->get('vip3_vip5');
        $current = time();
        //uid 判断
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('传token啊,魂淡！！',403);
        }
        
        $grade = $this->get("login")->returnGrade($request);
        
        if($grade==1&&$mark==1){
            return new Response('你已经是三星会员，无需购买',401);
        }
        if($grade==2&&$mark==2){
            return new Response('你已经是五星会员，无需购买',402);
        } 
        
        $out_trade_no = time().rand(0000, 9999);
        
        if($mark==1){
            $money = $vip3_money;
            $body = "充值成为三星会员";
        }else if ($mark==2){
            $money = $vip5_money;
            $body = "充值成为五星会员";
        }else if ($mark==3){
            $money = $vip3_vip5;
            $body = "充值成为五星会员";
        }else{
            $money = 0;
        }
        $this->get('my_datebase')->connection();
        mysql_query("insert into nodify set out_trade_no = '$out_trade_no',mark=$mark,status=0,user_id = $user_id,creation_date = $current");
        
        
        
        $partner = "2088911579871271";
        $seller_id = "ghg2233@163.com";
        $_input_charset = "utf-8";
        $sign_type = "RSA";
        $subject = "会员充值";
        $total_fee = $money;
        $nodify_url = "http://eie.ren/alipay/notify_url.php";
        
        $info = array(
            'partner'=>$partner,
            'seller_id'=>$seller_id,
            '_input_charset'=>$_input_charset,
            'sign_type'=>$sign_type,
        	'out_trade_no'=>$out_trade_no,
            'subject'=>$subject,
            'body'=>$body,
            'total_fee'=>$total_fee,
            'nodify_url'=>$nodify_url,
            'pubKey'=>$pubKey,
            'priKey'=>$priKey
        );
        
        return new JsonResponse($info);
    }
    function log($str)
    {
    
    	$open=fopen("wxpay/logs/log.txt","a" );
    	fwrite($open,$str);
    	fclose($open);
    }
    
    //------------------------------------------------
    // 支付宝（微信）服务器响应处理(主要操作，更改个人用户表会员开通时间，还有会员等级)
    //------------------------------------------------
    public function notifyAction(Request $request){
                      
            $trade_no = $request->get('out_trade_no');
        	$this->log($trade_no."\r\n");
        	$current = time();
            $this->get('my_datebase')->connection();
            $sql = "select user_id,mark from nodify where status = 0 and out_trade_no = '$trade_no'";
            $this->log("sql:".$sql."\r\n");
            $result = mysql_query($sql);
            $num  = mysql_num_rows($result);
            //$this->log("num:".$num."\r\n");
            if($num){
                //根据订单的id来获得uid和mark
                $uid = mysql_result($result, 0 ,'user_id');
                $mark = mysql_result($result, 0, 'mark');
                $this->log("uid:".$uid."mark:".$mark."\r\n");
                //$money = $money*100;
                //$this->log("money2:".$money."\r\n");
                
                //更新user表，主要是更新buy_Date and grade
                
                if($mark == 3){
                	mysql_query("update user set grade  = 2 where id = '$uid");
                }else{
                	
	                mysql_query("update user set buy_date = $current,grade  =$mark where id = '$uid'");
                }
                $this->log("update nodify set status = 1 where out_trade_no = '$trade_no'");
                mysql_query("update nodify set status = 1 where out_trade_no = '$trade_no'");
            }else{
                //$this->log("error\r\n");
                return new Response('订单号错误',403);
            }
            
            
         return new Response('',200);   
    }
    
    
    
	
	//------------------------------------------------
	// 列表
	//------------------------------------------------
    public function retrieveByFilterAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$mark = $json->get('mark',0);
		$start = $json->get('start',0);
		$num = $json->get('num',10);
		
		 
		//分页
		$limit = (($start==-1&&$num==-1)||($start==-1))?"":" limit $start,$num ";
		
		$this->get('my_datebase')->connection();
		
		$session = $this->getRequest()->getSession();
		$allow_normal_vip3 = $session->get('allow_normal_vip3');
		$allow_normal_vip5 = $session->get('allow_normal_vip5');
		
		$user_id = $this->get('login')->checkLogin($request);
		//$user_id = 1;
		if(!$user_id){
			return new Response('你必须要登陆才能看里面的内容！',403);
		}
		//$grade = 2;
		$grade = $this->get("login")->returnGrade($request);
		if($grade==0&&$allow_normal_vip3==0&&$allow_normal_vip5==0){
			return new Response('你什么也不是，并且超管也没有开启普通会员查看权限',401);
		}
		if($grade==0&&$allow_normal_vip5==0&&$mark==1){
			return new Response('超管没开放vip5的普通会员查看权限',402);
		}
		if($grade==0&&$allow_normal_vip3==0&&$mark==0){
			return new Response('超管没开放vip3的普通会员查看权限',405);
		}
		if($grade==1&&$allow_normal_vip5==0&&$mark==1){
			return new Response('你是vip3，不能查看vip5的东西',406);
		}
		
		$sql = "select a.*,b.username,b.icon from bbs as a left join user as b on a.user_id = b.id where a.mark=$mark order by id desc".$limit;
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
    
   /*
    * info
    */
    public function infoAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('id',0);
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('传token啊,魂淡！！',403);
        }
        $this->get('my_datebase')->connection();
        if($id){
            $result = mysql_query("select a.*,b.username,b.icon from bbs as a left join user as b on a.user_id = b.id where a.id = $id");
        }
        $array_result = array();
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
        	$row['creation_date'] = $this->get('login')->tranTime($row['creation_date']);
        	$array_result = $row;
        }
        
        $image_result = mysql_query("select b.url from bbs_image as a left join images as b on a.image_id = b.id where a.bbs_id = $id");
        $image_array = array();
        while ($row = mysql_fetch_array($image_result, MYSQL_ASSOC)) {
        
            $image_array[] = $row;
        }
        $num = mysql_result(mysql_query("select count(id) from bbs_collection where bbs_id = $id and user_id = $user_id"), 0);
        $array_result['collection'] = $num;
        $array_result['image_urls'] = $image_array;
        return new JsonResponse($array_result);
        
        
    }
    /*
     * 评价帖子
     */
    public function commentAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('id',0);
        $content = $json->get('content','');
        $current = time();
        //uid 判断
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('传token啊,魂淡！！',403);
        }  
        if(!$content){
            return new Response('content不能为空',405);
        }
        $this->get('my_datebase')->connection();
        $sql = "select floor from bbs_comment where bbs_id = $id order by id desc limit 1";
        $result = mysql_query($sql);
        if(mysql_num_rows($result)){
	        $floor = mysql_result($result,0);
        }else{
        	$floor = 0;
        }
        mysql_query("insert into bbs_comment set floor=$floor+1,bbs_id = $id ,user_id=$user_id,creation_date=$current,content='$content'");
        mysql_query("update bbs set comment_num = comment_num+1 where id = $id");
        return new Response('哈哈哈，成功了！！插入成功了！！！',200);
    }
    /*
     * commentList
     */
    public function commentListAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('id',0);
        $mark = $json->get('mark',0);//0获取最新的 1获取历史
        $start = $json->get('start',0);
        $num = $json->get('num',10);
        
        $mark_sql = ($mark==0?" order by a.id desc":" order by a.id asc");
         
        $this->get('my_datebase')->connection();
        $sql = "select a.*,b.icon,b.username,b.phone from bbs_comment as a left join user as b on a.user_id = b.id where a.bbs_id = $id ".$mark_sql." limit $start,$num";
        $result = mysql_query($sql);
        /* $num = mysql_num_rows($result);
        if($num){
            return new Response('没有记录',401);
        } */
        $array_result = array();
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        	$row['creation_date'] = $this->get('login')->tranTime($row['creation_date']);
            $array_result[] = $row;
        }
        return new JsonResponse($array_result);
      
        
    }
    
	//------------------------------------------------
	// 发布帖子
	//------------------------------------------------
    public function addAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$content = $json->get('content','');
		$title = $json->get('title','');
		$mark = $json->get('mark',0);
		$image_ids = $json->get('image_ids','');
		
		$session = $this->getRequest()->getSession();
		$allow_normal_publish_vip3 = $session->get('allow_normal_publish_vip3');
		$allow_normal_publish_vip5 = $session->get('allow_normal_publish_vip5');
		
		
		//return new Response(date('Y-m-d','1357401600'));
		$current = time();
		
		//uid 判断
		$user_id = $this->get('login')->checkLogin($request);
		if(!$user_id){
			return new Response('传token啊,魂淡！！',403);
		}
		
		$grade = $this->get('login')->returnGrade($request);
		
		/* if($grade==0){
			return new JsonResponse('没有特权',402);
		}else if($grade==1&&$mark==1){
			return new JsonResponse('你越权了知道吗，去三星论坛发布！！！',405);
		} */
		
		if($grade==0&&$allow_normal_publish_vip3==0&&$allow_normal_publish_vip5==0){
		    return new Response('你什么也不是，并且超管也没有开启普通会员发布权限',401);
		}
		if($grade==0&&$allow_normal_publish_vip5==0&&$mark==1){
		    return new Response('超管没开放vip5的普通会员发布权限',402);
		}
		if($grade==0&&$allow_normal_publish_vip3==0&&$mark==0){
		    return new Response('超管没开放vip3的普通会员发布权限',405);
		}
		if($grade==1&&$allow_normal_publish_vip5==0&&$mark==1){
		    return new Response('你是vip3，不能在vip5发布帖子',406);
		}
		
		
		//$session = $request->getSession();
		//$allow_normal_publish = $session->get("allow_normal_publish");
		//return new Response($grade."-------------".$allow_normal_publish);
		$this->get('my_datebase')->connection();
		$sql = "insert into bbs set user_id=$user_id,mark = $mark,title = '$title',content = '$content', creation_date = $current,updated_date=$current";
		//return new Response($sql);
		mysql_query($sql);
		$id = mysql_insert_id();
		
						
		if($image_ids){
			if(strstr($image_ids,",")){
			   $image_array = explode(',', $image_ids);
					//echo "1111";
			   
				while ($image_one = current($image_array)) {
					//printf("%s <br />", $step_one);
					$image_sql = "insert into bbs_image set bbs_id =$id ,image_id = $image_one";
					mysql_query($image_sql);
					next($image_array);
					} 
		}else{
			$image_sql = "insert into bbs_image set bbs_id =$id ,image_id = $image_ids";
			mysql_query($image_sql);
			}
		}
		
		//insert into bbs_collection
		mysql_query("insert into bbs_collection set user_id = $user_id,bbs_id = $id");
		
		return new JsonResponse($id);
		
    }
    /*
     *收藏
     */
    public function collectionAction(Request $request){
    
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('id',0);
        $mark = $json->get('mark',0); //0=del  1=add
        
        
        //uid 判断
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('传token啊,魂淡！！',403);
        } 
        //$user_id = 1;
        
        $this->get('my_datebase')->connection();
        $num = mysql_result(mysql_query("select count(id) from bbs_collection where bbs_id = $id and user_id = $user_id"), 0);
        
        if($mark==1&&$num==0){
            mysql_query("insert into bbs_collection set bbs_id = $id,user_id = $user_id");
        }else if($mark==0&&$num==1){
            mysql_query("delete from bbs_collection where bbs_id = $id and user_id = $user_id");
        }
        return new Response('成功了',200);
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
        return new Response('删除成功',200);
    }
	
	//------------------------------------------------
	// collectionList
	//------------------------------------------------
    public function collectionListAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $start = $json->get('start',0);
        $num = $json->get('num',10);
		$this->get('my_datebase')->connection();
		//判断uid
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('传token啊,魂淡！！',403);
        }
		
		$result = mysql_query("select b.*,c.username,c.icon from bbs_collection as a left join bbs as b on a.bbs_id = b.id left join user as c on b.user_id = c.id where a.user_id = $user_id order by id desc limit $start,$num");
		
		$array_result = array();
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
			$row['creation_date'] = $this->get('login')->tranTime($row['creation_date']);
		    $array_result[] = $row;
		}
		
		return new JsonResponse($array_result);
		
    }
    
    /*
     * 删除帖子
     */
    
    public function delmyAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $bbs_id = $json->get('bbs_id',-1);
        $this->get('my_datebase')->connection();
        
		//判断uid
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('传token啊,魂淡！！',403);
        }
        
        mysql_query("delete from bbs where id = $bbs_id and user_id = $user_id");
        mysql_query("delete from bbs_collection where bbs_id = $bbs_id");
        return new Response('success',200);
    
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
		    $row['price']==null?$row['price']="":$row['price']=$row['price'];
		    $row['summary']==null?$row['summary']="":$row['summary']=$row['summary'];
			$array_result[] = $row;
			}
		
		return new JsonResponse($array_result);
		
    }
	
	
	
	
	//------------------------------------------------
	// 订单(草稿)的状态改变
	//------------------------------------------------
    public function updateAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$id = $json->get('id',-1);
		$mark = $json->get('mark',-1);
		$flag = $json->get('flag',-1);
		$draft = $json->get('draft',-1);
		$touid = $json->get('touid',-1);
		$cid = $json->get('cid',-1);
		$title = $json->get('title','');
		$top = $json->get('top',-1);
		$end_date = $json->get('end_date','');
		$workdescription = $json->get('workdescription','');
		$original_price = $json->get('original_price','');
		$total_price = $json->get('total_price','');
		$true_price = $json->get('true_price','');
		$discount = $json->get('discount','');
		$message = $json->get('message','');
		$endreason = $json->get('endreason','');
		$uidreport = $json->get('uidreport','');
		$touidreport = $json->get('touidreport','');
		$attr = $json->get('attr','');
		$step = $json->get('step','');
		$current = time();
		$this->get('my_datebase')->connection();
		//首先获得此订单的发单人
		$uid = mysql_result(mysql_query("select uid from orderlist where id =$id "),0);
		if($mark==6){
		  //更改他的offdateasuid加一
		  mysql_query("update fos_user set `offdateasuid` = `offdateasuid` + 1 where id = $uid"); 
		}
		if($mark==11){
		    //更改uploadresult  上传成果的时间
		    mysql_query("update orderlist set `uploadresult` = $current where id = $id");
		}
		$mark!=-1?$mark_sql = " mark = $mark,":$mark_sql = "";
		$flag!=-1?$flag_sql = " flag = $flag,":$flag_sql = "";
		$top!=-1?$top_sql = " top = $top,":$top_sql = "";
		$draft!=-1?$draft_sql = " draft = $draft,":$draft_sql = "";
		$touid!=-1?$touid_sql = " touid = $touid,":$touid_sql = "";
		$cid!=-1?$cid_sql = " cid = $cid,":$cid_sql = "";
		$title!=''?$title_sql = " title = '$title',":$title_sql = "";
		$end_date!=''?$end_date_sql = " end_date = $end_date,":$end_date_sql = "";
		$workdescription!=''?$workdescription_sql = " workdescription = '$workdescription',":$workdescription_sql = "";
		$total_price!=''?$total_price_sql = " total_price = $total_price,":$total_price_sql = "";
		$original_price!=''?$original_price_sql = " original_price = $original_price,":$original_price_sql = "";
		$true_price!=''?$true_price_sql = " true_price = $true_price,":$true_price_sql = "";
		$discount!=''?$discount_sql = " discount = $discount,":$discount_sql = "";
		$message!=''?$message_sql = " message = '$message',":$message_sql = "";
		$endreason!=''?$endreason_sql = " endreason = '$endreason',":$endreason_sql = "";
		$uidreport!=''?$uidreport_sql = " uidreport = '$uidreport',":$uidreport_sql = "";
		$touidreport!=''?$touidreport_sql = " touidreport = '$touidreport',":$touidreport_sql = "";
		if($uidreport){
		      //首先获得到endreason
		      $report = $uidreport;
		}elseif($touidreport){
		    $report = $touidreport;
		}else{
		    $report= $endreason;
		}
		
		$dbendreason = mysql_result(mysql_query("select endreason from orderlist where id = $id"), 0);
		if($dbendreason==''||$dbendreason==null){
		    $endreason_sql = "endreason = '$report',";
		}
		
		//$recommend!=-1?$recommend_sql = " recommend = '$recommend'":$recommend_sql = "";
		//$torecommend!=-1?$torecommend_sql = " torecommend = '$torecommend'":$torecommend_sql = "";
		
		
		
		$sql = "update orderlist set ".$mark_sql.$top_sql.$flag_sql.$draft_sql.$touid_sql.$cid_sql.$title_sql.$end_date_sql.$workdescription_sql.$original_price_sql.$total_price_sql.$true_price_sql.$discount_sql.$endreason_sql.$message_sql.$uidreport_sql.$touidreport_sql."updated_date = $current where id = $id ";
		//return new Response($sql);
		mysql_query($sql);
		if($attr!=""){
			//先删除所有的数据
			mysql_query("delete from orderattr where oid = $id");
			$attr_array = json_decode($attr,true);
			while (list($key, $val) = each($attr_array)){
			$attr_sql = "insert into orderattr set oid =$id ,aid = $key,num = $val";
			mysql_query($attr_sql);
			}
		}
		if($step!=""){
			mysql_query("delete from orderstep where oid = $id");
			$step_array = explode(',',$step);
			while ($step_one = current($step_array)) {
			$step_sql = "insert into orderstep set oid=$id,sid = $step_one";
			mysql_query($step_sql);
			next($step_array);
			}

			}
			if(mysql_query($sql)){
			    return new JsonResponse('更改成功',200);
			}else{
			    return new Response('',500);
			}   
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
