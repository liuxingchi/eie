<?php
namespace Ydzy\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;


class MoneyController extends Controller
{
    public function indexAction(Request $request)
    {
        return new Response("medicine money");
    }
    
    
    //------------------------------------------------
    // 我的账单列表get
    //------------------------------------------------
    public function retrieveMyBillAction(Request $request)
    {
    	
    	$start = $request->get('start',0);
    	$num = $request->get('num',10);
    	$mark = $request->get('mark',0);
    
    
    	//uid判断
    	$user = $this->getUser();
    	if($user){
    		$login_uid = $user->getId();
    	}else{
    		$login_uid = 0;
    		return new Response('没有登陆',403);
    	}   
        
        $this->get('my_datebase')->connection();
        //查找的是交易成功的order的true_price
        if($mark==0){
            $sql = "select a.id,a.uid,a.touid,a.true_price,a.updated_date,a.order_code,a.title,b.name as category,a.creation_date,a.workdescription from orderlist as a left join category as b on a.cid = b.id where (a.uid = $login_uid or a.touid = $login_uid ) and a.mark = 14 and a.true_price!=0 order by a.updated_date desc limit $start,$num";
        }else if($mark==1){
            $sql = "select a.id,a.uid,a.touid,a.true_price,a.updated_date,a.order_code,a.title,b.name as category,a.creation_date,a.workdescription from orderlist as a left join category as b on a.cid = b.id where a.touid = $login_uid and a.mark = 14 and a.true_price!=0 order by a.updated_date desc limit $start,$num";
        }else{
            $sql = "select a.id,a.uid,a.touid,a.true_price,a.updated_date,a.order_code,a.title,b.name as category,a.creation_date,a.workdescription from orderlist as a left join category as b on a.cid = b.id where a.uid = $login_uid and a.mark = 14 and a.true_price!=0 order by a.updated_date desc limit $start,$num";
        }
        
        //return new Response($sql);
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if (! $num) {
            return new Response('', 400);
        }
        $array_result = array();
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            if($login_uid == $row['uid']){
                //表示是作为发单者，所以是付款
                $row['status']=0;
            }else{
                $row['status']=1;
            }
            $row['creation_date'] = date('Y-m-d H:i:s',$row['creation_date']);
            $row['updated_date'] = date('Y-m-d H:i:s',$row['updated_date']);
            $array_result[] = $row;
        }
        return new JsonResponse($array_result);
    
    }
   
	//------------------------------------------------
	// 添加充值记录
	//------------------------------------------------
    public function addAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$money = $json->get('money',0);
		$way = $json->get('way','');//充值方式，支付宝或者银行卡
		$mark = $json->get('mark',0);
		
		//uid判断
		$user = $this->getUser();
		if(!$user){
			return new Response('',403);
			}
		$uid = $user->getId();
		$truename = $user->getUsername();
		$current = time();
		$payid = time()*1000+rand(1000,9999);
		
		$this->get('my_datebase')->connection();
		
		$sql = "insert into moneyrecord set payid = $payid,uid = $uid,truename = '$truename',money = $money,way = '$way',mark = $mark,status=0,creation_date = $current,updated_date=$current";
		if(mysql_query($sql)){
		    //if success, do alipay,return orderid
		    if($way=='支付宝'){
		        $json_result = array(
		            'out_trade_no'=>$payid,
		            'subject'=>'支付宝充值到医学威客app',
		            'seller_id'=>'alipay-test09@alipay.com',
		            'total_fee'=>$money,
		            'body'=>'医学威客',
		            'notify_url'=>''
		        );
		     return new JsonResponse($json_result);
		    }else{
		     return new Response('',200);
		    }
		    
		    
		}
		
		
		return new Response('',500);
		
    }
    function log($str)
    {
    
        $open=fopen("alipay/log.txt","a" );
        fwrite($open,$str);
        fclose($open);
    }
    //------------------------------------------------
    // 支付宝服务器相应地址(主要操作，更改充值者账号的money值，将充值记录的status变成1)
    //------------------------------------------------
    public function notifyAction(Request $request){
                      
            $trade_no = $request->get('out_trade_no');
        	//$this->log($trade_no."\r\n");
            $this->get('my_datebase')->connection();
            $sql = "select uid,money from moneyrecord where status = 0 and payid = '$trade_no'";
            //$this->log("sql:".$sql."\r\n");
            $result = mysql_query($sql);
            $num  = mysql_num_rows($result);
            //$this->log("num:".$num."\r\n");
            if($num){
                //根据订单的id来获得uid和money
                $uid = mysql_result($result, 0 ,'uid');
                $money = mysql_result($result, 0, 'money');
                //$this->log("uid:".$uid."money:".$money."\r\n");
                //$money = $money*100;
                //$this->log("money2:".$money."\r\n");
                $em = $this->getDoctrine()->getEntityManager();
                $user = $em->getRepository('YdzyUserBundle:User')->findOneById($uid);
                $oldmoney = $user->getMoney();
                //$this->log("moneyold:".$oldmoney."\r\n");
                $user->setMoney($money+$oldmoney);
                $em->flush();
                //将充值记录变成已经审核（成功）状态
                mysql_query("update moneyrecord set status = 1 where payid = '$trade_no'");
            }else{
                //$this->log("error\r\n");
                return new Response('订单号错误',403);
            }
            
            
         return new Response('',200);   
    }
    
    //------------------------------------------------
    // 添加提现记录
    //------------------------------------------------
    public function addWithdrawAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $money = $json->get('money',0);
        $way = $json->get('way','');//银行名称或者支付宝
        $card = $json->get('card','');//银行卡号或者支付宝账号
        $card2 = $json->get('card2','');//银行开户支行
        $login_pwd = $json->get('login_pwd','');//登陆账号
        $truename = $json->get('truename','');//真实姓名
        $mark = $json->get('mark',1);//提现
        $current = time();
        $this->get('my_datebase')->connection();
        //uid判断 
        $user = $this->getUser();
        if(!$user){
            return new Response('',403);
        } 
        $uid = $user->getId();
        $username = $user->getUsername();
        if($way=="支付宝"){ 
            $truename = $username;
        }
        //money can use
        $useablemoney = $user->getMoney()-$user->getFrozenmoney();
        
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $encodedPass = $encoder->encodePassword($login_pwd, $user->getSalt());
        $dbpwd = $this->getUser()->getPassword();
        if($encodedPass==$dbpwd){
            //pwd same,then do sth
            $sql = "insert into moneyrecord set uid = $uid,status=1,money = $money,card = '$card',card2 = '$card2',truename = '$truename',way = '$way',mark = $mark,creation_date = $current,updated_date=$current";
            //return new Response($sql);
            mysql_query($sql);
            $id = mysql_insert_id();
            mysql_query("update `fos_user` set `money` = `money` - $money where `id` = $uid");
            return new JsonResponse($id);
            
        }else{
            return new Response('密码错误',413);
        }
        
        if($useablemoney<$money){
            return new Response('钱不够啊',415);
        }
        //如果是支付宝，直接将钱打到他指定的支付宝
        
        
        
    
        
    
        
    
    }
	
	//------------------------------------------------
	// 更改金额和更改mark值的操作
	//------------------------------------------------
    public function changeMoneyAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$oid = $json->get('oid',-1);
		//$money = $json->get('money',0);//进行操作的钱，操作1和2默认都是oid的trueprice,当action=0的时候要填写数值
		$action = $json->get('action',0);//要进行的操作，0赔偿1确认成果2确认打款
		$current = time();
		//uid判断
		$user = $this->getUser();
		if(!$user){
			return new Response('',403);
			}
		$login_uid = $user->getId();
		//判断uid
		$this->get('my_datebase')->connection();
		$result = mysql_query("select uid,touid,true_price from orderlist where id = $oid limit 1");
		$num = mysql_num_rows($result);
		if(!$num){
			return new Response('',415);
			}
		$uid = mysql_result($result,0,'uid');
		$touid = mysql_result($result,0,'touid');
		$true_price = mysql_result($result,0,'true_price');
		
		if($uid!=$login_uid){
		    return new Response('',405);//当前登陆者和此订单的uid（发单者）不一致
		}
		
		//根据不同的action才进行不同的操作
		if($action==1){
		    //确认成果，将订单的钱从uid的frozenmoney中转入到touid的money中
		    mysql_query("update fos_user set `frozenmoney` = `frozenmoney` - $true_price,`money` = `money`-$true_price where `id`  = $uid");
		    mysql_query("update fos_user set `money` = `money` + $true_price where `id`  = $touid");
		    mysql_query("update orderlist set mark = 12,flag = 4,updated_date = $current where id = $oid");
		    //首先先查是不是改联系人已经存在
		    $result = mysql_query("select id from contacts where uid = $uid and touid = $touid limit 1");
		    $num = mysql_num_rows($result);
		    if(!$num){
		        mysql_query("insert into contacts set `uid`='$uid' ,`touid`=$touid, `creation_date`='$current',status=1");
		    }
		    //更新confirm，用当前的时间-uploadresult的时间
		    mysql_query("update orderlist set `confirm` = $current-`uploadresult` where id = $oid");
		    return new Response('success',200);
		}
		if($action==2){
		    //确认打款，将uid的money-truemoney 到frozenmoney
		    mysql_query("update fos_user set `frozenmoney` = `frozenmoney` + $true_price where `id`  = $uid");
		    mysql_query("update orderlist set mark = 9,flag = 2,updated_date = $current where id = $oid");
		    return new Response('success',200);
		}
		if($action==0){
		    //赔偿
		    mysql_query("update fos_user set `frozenmoney` = `frozenmoney` - $true_price,`money` = `money`-$true_price where `id`  = $uid");
		    mysql_query("update fos_user set `money` = `money` + $true_price where `id`  = $touid");
		    mysql_query("update orderlist set mark = 6,flag = 6,updated_date = $current where id = $oid");
		    return new Response('success',200);
		}
		
		
    }
	
    //------------------------------------------------
    // 我的充值记录和提现记录列表get
    //------------------------------------------------
    public function retrieveMyMoneyAction(Request $request)
    {
         
        $start = $request->get('start',0);
        $num = $request->get('num',10);
        $mark = $request->get('mark',0);
    
    
        //uid判断
        $user = $this->getUser();
        if($user){
            $login_uid = $user->getId();
        }else{
            $login_uid = 0;
            return new Response('没有登陆',403);
        }
    
        $this->get('my_datebase')->connection();
        //mark=0充值记录
        if($mark == 0){
            $sql = "select * from moneyrecord where mark = 0 and status = 1 and uid = $login_uid order by id desc limit $start,$num";
        }else{
            $sql = "select * from moneyrecord where mark = 1 and status = 1 and uid = $login_uid order by id desc limit $start,$num";
        }
        //return new Response($sql);
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if (! $num) {
            return new Response('', 400);
        }
        $array_result = array();
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $row['creation_date'] = date('Y-m-d H:i:s',$row['creation_date']);
            $row['updated_date'] = date('Y-m-d H:i:s',$row['updated_date']);
            $array_result[] = $row;
        }
        return new JsonResponse($array_result);
    
    }
    /*
     * 根据uid来更改money
     */
    public function moneyAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $uid = $json->get('uid',-1);
        $touid = $json->get('touid',-1);
        $money = $json->get('money',-1);//最后确认赔偿的价格
        $total_price = $json->get('total_price',-1);//订单的实际价格
        $this->get('my_datebase')->connection();
        mysql_query("update fos_user set `frozenmoney` = `frozenmoney` - $total_price,`money`=`money`-$money where id = $uid");
        mysql_query("update fos_user set `money` = `money` + $money where id = $touid");
        return new Response('',200);
        
    }
    /*
     * retrieveByFilter
     */
    public function retrieveByFilterAction(Request $request){
        
        $mark = $request->get('mark',-1);  //0充值 1提现
        $start = $request->get('start',0);
        $pay = $request->get('pay',-1);
        $way = $request->get('way',0); 
        $num = $request->get('num',10);
        $status = $request->get('status',0);
        $this->get('my_datebase')->connection();
        if($status==2){
            $status_sql = "";
        }else{
            $status_sql = " status = $status and ";
        }
        if($pay!=-1){
            $pay_sql = " and pay = $pay ";
        }else{
            $pay_sql="";
        }
        if($way==1){
            $way_sql = " and way = '支付宝' ";
        }else if($way==2){
            $way_sql = " and way!='支付宝'";
        }else{
            $way_sql = "";
        } 
        $result = mysql_query("select * from moneyrecord where money!=0 and ".$status_sql." mark = $mark ".$pay_sql.$way_sql."order by id desc limit $start,$num");
        $array_result = array();
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $row['creation_date'] = date('Y-m-d H:i:s',$row['creation_date']);
            $row['updated_date'] = date('Y-m-d H:i:s',$row['updated_date']);
            $array_result[] = $row;
        }
        return new JsonResponse($array_result);
        
    }
    /*
     * retrieveByFilterNum
     */
    public function retrieveChargeByFilterNumAction(Request $request){
        
        $json = $this->get('json_parser')->parse($request);
    
        $mark = $json->get('mark',-1);  //0充值 1提现
        $pay = $json->get('pay',-1);
        if($pay!=-1){
            $pay_sql = " and pay = $pay ";
        }else{
            $pay_sql="";
        }
        $this->get('my_datebase')->connection();
        $sql = "select count(id) as num,sum(money) as money from moneyrecord where money!=0 and status = 1 and mark = $mark".$pay_sql;
        //return new Response($sql);
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if(!$num){
            return  new Response(0);
        }
         
        $count = mysql_result($result,0,'num');
        $money = mysql_result($result,0,'money');
        
        
        
        return new JsonResponse(array('count'=>$count,'money'=>$money));
    
    }
    /*
     * 审核提现记录
     */
    public function statusAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('id',-1);  //提现记录的id
        $this->get('my_datebase')->connection();
        $current = time();
        //$result = mysql_query("select * from moneyrecord where id = $id ");
        //$money = mysql_result($result,0,'money');
        //$uid = mysql_result($result, 0,'uid');
        //mysql_query("update `fos_user` set `money` = `money` - $money where `id` = $uid");
        mysql_query("update moneyrecord set pay = 1,updated_date = $current where id = $id ");
        return new Response('',200);
    }
    
}
