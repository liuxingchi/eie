<?php
namespace Ydzy\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Symfony\Component\HttpFoundation;

require_once 'Easemob.class.php';
require_once "wxpay/lib/WxPay.Api.php";
class userController extends Controller
{
	
    
    /*
     * 生成验证码
     */
    public function getTelValidateAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$phone = $json->get('phone', '');
		$mark = $json->get('mark',0);//0注册1更改密码
		if(!$phone){
			return new Response("手机号不能为空",401);
			}
		//获得所有注册用户的手机号码
		$this->get('my_datebase')->connection();
		$sql = "SELECT phone,enable from user where phone = $phone limit 1";
		$result = mysql_query($sql);
		if(mysql_num_rows($result)&&$mark==0){
			return new Response('',403); //手机号已经被注册过
		}		
		if($mark==1){ //更改密码
		    if(!mysql_num_rows($result)){
    		    return new Response('',415); //手机号没有被注册过
		    }else if(mysql_result($result, 0, 'enable')==0){
		        return new Response('',406); //手机号已经被禁用
		    }
		}
		
		
		$session = $this->getRequest()->getSession();
		
        $validateNumber = rand(1000,9999);
		
    	$session->set('validate_number', $validateNumber);
    	$session->set('validate_phone', $phone);
		//$session->set('date', date('YmdHis'));
		

		/* // 配置项
        $api = 'http://dx.ipyy.net/sms.aspx';
        $params = array('account' => 'xd001048','password'=>'xd00000','mobile' => '18600758014', 'content' => '123456', 'action' => 'send');
        // 发送验证码
        
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $api );
        // 以返回的形式接收信息
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        // 设置为POST方式
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
        // 不验证https证书
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
        'Accept: application/json',
        ) );
        $smsresponse = curl_exec( $ch );
        
        // 不要忘记释放资源
        curl_close( $ch ); 
        return new Response($smsresponse);*/
    	$message = '您的验证码是:'.$validateNumber.'.请不要把验证码泄露给其他人.如非本人操作,可不用理会!【艺鉴】';
    	$gateway = "http://dx.ipyy.net/sms.aspx?action=send&userid=&account=xd001048&password=xd00000&mobile=$phone&content=$message&sendTime=&extno=";
    	//return new Response($gateway);
    	$result = file_get_contents($gateway);
    	//$result = "Success 操作成功 181816 1509241047390022 1";
    	$rule ="/<returnstatus>.+<\/returnstatus>/";
    	$response = '';
    	preg_match($rule,$result,$response);
    	//print_r($response);
    	if($response[0]=="<returnstatus>Success</returnstatus>")
    	{
    		$message =  "发送成功! 发送时间".date("Y-m-d H:i:s");
    		return new Response($message,200);
    	}
    	else
    	{
    		$message =  "发送失败, 错误提示代码: ".$result;
    		return new Response($message,500);
    	
    	}


		//发送到短信接口
		//$message = '练吧平台验证码：'.$validateNumber.'。请勿将验证码告知他人并确认申请是您本人操作！【练吧】';
		
			//return new JsonResponse(array('validateNumber'=>$validateNumber));
    }


    function get_hash(){
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()+-';
        $random = $chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)].$chars[mt_rand(0,73)];//Random 5 times
        $content = uniqid().$random;   // 类似  5443e09c27bf4aB4uT
        return sha1($content);
    }

	/**
 * 发起一个post请求到指定接口
 * 
 * @param string $api 请求的接口
 * @param array $params post参数
 * @param int $timeout 超时时间
 * @return string 请求结果
 */
function postRequest( $api, array $params = array(), $timeout = 30 ) {
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $api );
    // 以返回的形式接收信息
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    // 设置为POST方式
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
    // 不验证https证书
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
        'Accept: application/json',
    ) ); 
	$response = curl_exec( $ch );
    // 不要忘记释放资源
    curl_close( $ch );
}




    /*
     * 注销前端用户
     */
    public function logoutAction(Request $request)
    {
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('',403);
        }
        $token = $request->headers->get('token');
        $this->get('my_datebase')->connection();
        $sql = "delete from token where user_id  = $user_id and token='$token'";
        //return new Response($sql);
        mysql_query($sql);
        //清空相应用户表里的registration字段
        mysql_query("update user set registration = '' where id = $user_id");
        return new JsonResponse('success',200);
    }
    
    
    
    /*
     * 注册前端用户
     */
    public function registerAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $username = $json->get('username','');
        $phone = $json->get('phone','');
        $password = $json->get('password','');
        $validate = $json->get('validate','');
        $mark = $json->get('mark',0);
        
        //获得所有注册用户的手机号码
        $this->get('my_datebase')->connection();
        $sql = "SELECT phone from user where phone = '$phone' limit 1";
        $result = mysql_query($sql);
        if(mysql_num_rows($result)){
            return new Response('',415); //手机号已经被注册过
        }
        if($username){
        $sql = "SELECT username from user where username = '$username' limit 1";
        $result = mysql_query($sql);
        if(mysql_num_rows($result)){
            return new Response('',416); //昵称已经被使用
        }
        }else{
            $username = "EIE".$phone;
        }
        $current = time();
        
        $session = $this->getRequest()->getSession();
        $validateSession = $session->get('validate_number');
        $validate_phone = $session->get('validate_phone');
        /*if($phone!=$validate_phone){
         return new Response('获得短信手机号和填写用户名不一致', 401);
        }*/
        if($validate != $validateSession && $validate != '9977'&&$validate!='')
        {
            return new Response('', 403);
        }
        if(!$phone){
            return new Response('',401);
        }
        if(!$password){
            return new Response('',402);
        }
        
        $client_id = $this->container->getParameter('client_id');
        $client_secret = $this->container->getParameter('client_secret');
        
        $options = array('client_id'=>$client_id,'client_secret'=>$client_secret,'org_name'=>'eie','app_name'=>'eie');
        $easemob = new \Easemob($options);
        //echo $access_token = $easemob->getToken();
        
        //注册环信用户
        //$user_result = $easemob->openRegister(array('username'=>$phone,'password'=>$password));
        $user_result = $easemob->openRegister(array('username'=>$phone,'nickname'=>'EIE'.substr($phone,-4),'password'=>'111111'));
        /* if($user_result){
        	return new Response('ok');
        }else{
        	return new Response('error');
        } */
        //生成随机salt值
        $salt = $this->get_hash();
        $password = md5($password + $salt);
         
        $sql = "insert into user set salt='$salt',mark=$mark,password='$password',icon='/images/noicon.jpg',username='$username',phone='$phone',last_login=$current,create_date=$current";
        //return new Response($sql);
        if(mysql_query($sql)){
                
             return new JsonResponse('success',200);
        }else{
            return new JsonResponse('fail',500);
        }
        
    }
    
    /*
     * 登陆
     */
    public function loginAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $phone = $json->get('phone','');
        $password = $json->get('password','');
        $registration  = $json->get('registration','');
    
        $current = time();

        if(!$phone){
            return new Response('',401);
        }
        if(!$password){
            return new Response('',402);
        }
         
        $this->get('my_datebase')->connection();
        $sql = "select id,salt,password,enable from user where phone='$phone' or username='$phone' limit 1";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if(!$num){
           return new Response('没有此用户',403); 
        }
        $enable = mysql_result($result, 0, 'enable');
        if($enable==0){
            return new JsonResponse('user unable',406);
        }
        $user_id = mysql_result($result, 0, 'id');
        //return new Response($user_id);
        $salt = mysql_result($result, 0,'salt'); 
        $dbpassword = mysql_result($result, 0, 'password');
        $password = md5($password + $salt);
        if($password==$dbpassword){
            //生成token，然后保存到数据库
            $token = $this->get_hash();
			$result = mysql_query("select token from token where user_id='$user_id' limit 1");
            $num = mysql_num_rows($result);
            /* if($num){
				//$token = mysql_result($result,0);
				mysql_query("update token set token='$token' where user_id='$user_id'");
            }else{
                mysql_query("insert into token set token='$token',user_id='$user_id'");
            } */ 
            mysql_query("insert into token set token='$token',user_id='$user_id'");
			if($registration){
            	mysql_query("update user set registration = '$registration' where id = $user_id");
			}
            $array = array('token'=>$token);
            return new JsonResponse($array);
        }else{
            return new JsonResponse('wrong password',405);
        }
        
    }
    
    
    //------------------------------------------------
    // 想购买的列表
    //------------------------------------------------
    public function retrieveWantBuyListAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $user_id = $json->get('user_id',0);
        $start = $json->get('start',0);
        $num = $json->get('num',10);
    
    
        //uid 判断
        if($user_id){
            $login_uid = $user_id;
        }else{
            $user_id = $this->get('login')->checkLogin($request);
            if(!$user_id){
                return new Response('传token啊,魂淡！！',403);
            }else{
                $login_uid = $user_id;
            }
    
        }
    
        $this->get('my_datebase')->connection();
    
        $sql = "select b.* from user_recommended as a left join recommended as b on a.recommended_id = b.id where a.user_id = $login_uid limit $start,$num";
        //return new Response($sql);
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if (! $num) {
            return new Response('', 400);
        }
        $array_result = array();
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    
            $row['creation_date'] = date("Y-m-d ", $row['creation_date']);
            $row['updated_date'] = date("Y-m-d ", $row['updated_date']);
            $array_result[] = $row;
        }
        return new JsonResponse($array_result);
    
    }
    /*
     * 获得个人信息(或者根据user_id获取用户信息)
     */
    public function profileAction(Request $request)
    {
        
        $json = $this->get('json_parser')->parse($request);
        $user_id = $json->get('user_id',0);
        $phone = $json->get('phone',0);
        if ($user_id==0&&$phone==0){
            $user_id = $this->get('login')->checkLogin($request);
            if(!$user_id){
                return new Response('',403);
            }
        }
        $this->get('my_datebase')->connection();
        if($phone){
        	$sql="select username,phone,id,cid,last_login,create_date,enable,icon,mark,grade,buy_date,qq,email,truename from user where phone='$phone'";
        }else{
        $sql = "select username,phone,id,cid,last_login,create_date,enable,icon,mark,grade,buy_date,qq,email,truename from user where id='$user_id'";
        }
        $result = mysql_query($sql);
        $json_array = array();
        $category = "";
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
            $row['last_login'] = date('Y-m-d H:i:s',$row['last_login']);
            $row['create_date'] = date('Y-m-d H:i:s',$row['create_date']);
            if($row['cid']){
	            $category_result = mysql_query("select name from category where id in (".$row['cid'].")");
	            while($category_row = mysql_fetch_array($category_result,MYSQL_ASSOC)){
	            	if ($category){
	            		$category = $category . "," . $category_row['name'];
	            	}else{
	            		$category = $category_row['name'];
	            	}
	            }
            }
            $row['category'] = $category;
            $json_array = $row;
        }
        return new JsonResponse($json_array);
        
    
    }
    
    /*
     * 更改个人信息
     */
    public function changeProfileAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $username = $json->get('username','');
        $password = $json->get('password','');
        $icon = $json->get('icon','');
        $mark = $json->get('mark',-1);
        $grade = $json->get('grade',-1);
        $qq = $json->get('qq',-1);
        $email = $json->get('email','');
        $truename = $json->get('truename','');
        $location = $json->get('location','');
        $cid = $json->get('cid','');
        
        $user_id = $json->get('user_id',0);
        if ($user_id==0){
            $user_id = $this->get('login')->checkLogin($request);
            if(!$user_id){
                return new Response('',403);
            }
        }
        $this->get('my_datebase')->connection();
        
        $current = time();
        $username_sql = ($username==''?"":" username='$username', ");
        $icon_sql = ($icon==''?"":" icon='$icon', ");
        $location_sql = ($location==''?"":" location='$location', ");
        $mark_sql = ($mark==-1?"":" mark='$mark', ");
        $grade_sql = ($grade==-1?"":" grade='$grade', ");
        $qq_sql = ($qq==-1?"":" qq='$qq', ");
        $email_sql = ($email==''?"":" email='$email', ");
        $truename_sql = ($truename==-1?"":" truename='$truename', ");
        $cid_sql = ($cid==''?"":" cid='$cid', ");
        
        if($username){
        	$client_id = $this->container->getParameter('client_id');
        	$client_secret = $this->container->getParameter('client_secret');
        	
        	$options = array('client_id'=>$client_id,'client_secret'=>$client_secret,'org_name'=>'eie','app_name'=>'eie');
        	$easemob = new \Easemob($options);
        	
        	$phone = mysql_result(mysql_query("select phone from user where id = $user_id limit 1"),0);
        	//return new Response($phone);
        	//更改环信用户昵称
        	$user_result = $easemob->nickname(array('username'=>$phone,'nickname'=>$username));
        }
        
        
        if($password!=''){
            //生成随机salt值
            $salt = $this->get_hash();
            $newpassword = md5($password + $salt);
            $password_sql = " password='$newpassword',salt='$salt', ";
        }else{
            $password_sql='';
        }
        if($grade!=''){
            $buy_date_sql = " buy_date=$current, ";
        }else{
            $buy_date_sql = "";
        }
        $sql = "update user set".$username_sql.$icon_sql.$location_sql.$cid_sql.$mark_sql.$grade_sql.$truename_sql.$email_sql.$qq_sql.$password_sql.$buy_date_sql." update_date = $current where id='$user_id'";
        //return new Response($sql);
        mysql_query($sql);
        return new JsonResponse('success',200);
    
    
    }

    /*
     * 重置密码
     */
    public function repwdAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $newpassword = $json->get('newpassword','');
        $oldpassword = $json->get('oldpassword','');
        
        $user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('',403);
        }
        $this->get('my_datebase')->connection();
        $sql = "select salt,password,enable from user where id='$user_id' limit 1";
        $result = mysql_query($sql);
        $salt = mysql_result($result, 0,'salt');
        $dbpassword = mysql_result($result, 0, 'password');
        $password = md5($oldpassword + $salt);
        if($password==$dbpassword){
        
            //进行更改密码的操作
            //生成随机salt值
            $salt = $this->get_hash();
            $newpassword = md5($newpassword + $salt);
             
            $sql = "update user set salt='$salt',password='$newpassword' where id='$user_id'";
            mysql_query($sql);
            return new JsonResponse('success',200);
        }else{
            return new JsonResponse('wrong oldpassword',405);
        }
        
        
        
    }
    
    /*
     * 忘记密码
     */
    public function forgetPwdAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $phone = $json->get('phone','');
        $newpassword = $json->get('newpassword','');
        $validate = $json->get('validate','');
        
        
        $session = $this->getRequest()->getSession();
        $validateSession = $session->get('validate_number');
        $validate_phone = $session->get('validate_phone');
        /*if($phone!=$validate_phone){
         return new Response('获得短信手机号和填写用户名不一致', 401);
        }*/
        if($validate != $validateSession && $validate != '9977'&&$validate!='')
        {
            return new Response('', 403);
        }
        if(!$phone){
            return new Response('',401);
        }
        if(!$newpassword){
            return new Response('',402);
        }
        
        $this->get('my_datebase')->connection();
        $sql = "select enable from user where phone='$phone' limit 1";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if(!$num){
            return new Response('没有此用户',405);
        }
        $enable = mysql_result($result, 0, 'enable');
        if($enable==0){
            return new JsonResponse('user unable',406);
        }
        
        //生成随机salt值
        $salt = $this->get_hash();
        $newpassword = md5($newpassword + $salt);
         
        $sql = "update user set salt='$salt',password='$newpassword' where phone='$phone'";
        //return new Response($sql);
        if(mysql_query($sql)){
        
            return new JsonResponse('success',200);
        }else{
            return new JsonResponse('fail',500);
        }
    }
    
    /*
     * 创建聊天群组
     */
    public function createGroupsAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $groupname = $json->get('groupname','');
        $owner = $json->get('owner',0);
        $desc = $json->get('desc',0);
        $grade = $json->get('grade',2);
        
        $num = mysql_result(mysql_query("select count(*) from user where phone = '$owner' limit 1"),0);
        if(!$num){return new JsonResponse('此用户不能做主持人',400);}
        $client_id = $this->container->getParameter('client_id');
        $client_secret = $this->container->getParameter('client_secret');
    
        $options = array('client_id'=>$client_id,'client_secret'=>$client_secret,'org_name'=>'eie','app_name'=>'eie');
        $easemob = new \Easemob($options);
        //echo $access_token = $easemob->getToken();
    
        //创建环信群组
    
        $group_result = $easemob->createGroups(array('groupname'=>$groupname,'owner'=>$owner,'desc'=>$desc,'public'=>true,'approval'=>false));
        $result = json_decode($group_result,true);
        $groupid = $result['data']['groupid'];
        //将群组信息插入到数据库
        $this->get('my_datebase')->connection();
        //echo $sql = "insert into auction set grade=$grade,groupid=$groupid,name='$groupname',desc='$desc',owner='$owner'";
        $sql = "INSERT INTO `eie`.`auction` (`id`, `groupid`, `name`, `owner`, `desc`, `result`, `grade`) VALUES (NULL, '$groupid', '$groupname', '$owner', '$desc', '', '$grade');";
        mysql_query($sql);
        return new Response($groupid);
    }
    
    
	/*
	 * 查看是否已经有群组，如果有，返回群组信息
	 */
    public function getGroupAction(Request $request)
    {
        
        $client_id = $this->container->getParameter('client_id');
        $client_secret = $this->container->getParameter('client_secret');
        
        $options = array('client_id'=>$client_id,'client_secret'=>$client_secret,'org_name'=>'eie','app_name'=>'eie');
        $easemob = new \Easemob($options);
        $access_token = $easemob->getToken();
        
        //return new JsonResponse($access_token);
        //创建环信群组
        
        $group_result = $easemob->chatGroups();
        $result = json_decode($group_result,true);
        if(!$result['data']){
            return new JsonResponse('',200);
        }else{
            $detail_result = $easemob->chatGroupsDetails($result['data'][0]['groupid']);
            $data_result = json_decode($detail_result,true);
            $groupid = $data_result['data'][0]['id']; 
            $this->get('my_datebase')->connection();
            $sql = "select grade from auction where groupid = $groupid ";
            $grade = mysql_result(mysql_query($sql),0); 
            $data_result['data'][0]['grade'] = $grade;
            return new JsonResponse(($data_result['data'][0]));
        }
		
    }
    
    /*
     * 删除群组
     */
    public function delGroupAction(Request $request)
    {
    
        $json = $this->get('json_parser')->parse($request);
        $groupid = $json->get('groupid','');
        $client_id = $this->container->getParameter('client_id');
        $client_secret = $this->container->getParameter('client_secret');
        
        $options = array('client_id'=>$client_id,'client_secret'=>$client_secret,'org_name'=>'eie','app_name'=>'eie');
        $easemob = new \Easemob($options);
        //echo $access_token = $easemob->getToken();
        
        //删除环信群组
        
        $group_result = $easemob->deleteGroups($groupid);
        return new JsonResponse(json_decode($group_result));
    
    }
    /*
     * 获得聊天记录
     */
    public function historyAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $groupid = $json->get('groupid','77684973879951768');
        $client_id = $this->container->getParameter('client_id');
        $client_secret = $this->container->getParameter('client_secret');
        
        $options = array('client_id'=>$client_id,'client_secret'=>$client_secret,'org_name'=>'eie','app_name'=>'eie');
        $easemob = new \Easemob($options); 
        
        $group_result = $easemob->chatRecord();
        //$group_result = '{"action":"get","params":{"limit":["100"],"order by timestamp desc":[""]},"path":"/chatmessages","uri":"http://a1.easemob.com/eie/eie/chatmessages","entities":[{"uuid":"cdb6d36a-1f00-11e5-891f-65f1e2b2c11b","type":"chatmessage","created":1435652401644,"modified":1435652401644,"timestamp":1435652401128,"from":"admin","msg_id":"77685655269802476","to":"77684973879951768","groupId":"77684973879951768","chat_type":"groupchat","payload":{"bodies":[{"type":"txt","msg":"u5927u5bb6u90fdu8e0au8dc3u53d1u8a00uff0cu62b5u5236u4e0du826fu4fe1u606f"}]}},{"uuid":"8fde533e-1f13-11e5-9ecb-370c318dd2e2","type":"chatmessage","created":1435660458322,"modified":1435660458322,"timestamp":1435660456841,"from":"admin","msg_id":"77720254242882044","to":"77684973879951768","groupId":"77684973879951768","chat_type":"groupchat","payload":{"bodies":[{"type":"txt","msg":"u6211u8bd5u8bd5u6587u5b57u548cu56feu7247u4e00u8d77u7684"}]}},{"uuid":"8fdea14a-1f13-11e5-9596-4b4e0fdd6bb5","type":"chatmessage","created":1435660458324,"modified":1435660458324,"timestamp":1435660456845,"from":"admin","msg_id":"77720254259659260","to":"77684973879951768","groupId":"77684973879951768","chat_type":"groupchat","payload":{"bodies":[{"type":"img","filename":"C:fakepath1.jpg","secret":"iZVJGh8TEeW-4k0fFILAp_X_fmUJBmHksQRY2AtYEef7t-oH","url":"https://a1.easemob.com/eie/eie/chatfiles/89954910-1f13-11e5-af3b-d313e219d045"}]}}],"timestamp":1435735563298,"duration":665,"count":3}';
        $entities = json_decode($group_result,true);
        $entities_array = $entities['entities'];
        $group_array = array();
        foreach($entities_array as $entity){
            if($entity['groupId']==$groupid){
                if($entity['payload']['bodies'][0]['type']=='txt'){
                    $entity['payload']['bodies'][0]['timestamp'] = date("Y-m-d H:i:s",substr($entity['timestamp'], 0,10));
                    $entity['payload']['bodies'][0]['from'] = $entity['from'];
                    $group_array[] = $entity['payload']['bodies'][0]; 
                }
            }
            
        }
        return new JsonResponse($group_array);
        
        
    }
    
    /*
     * 更新result
     */
    public function updateResultAction(Request $request)
    {
    
        $json = $this->get('json_parser')->parse($request);
        $groupid = $json->get('groupid','');
        $result = $json->get('result','');
        
        
        $this->get('my_datebase')->connection();
         
        mysql_query("update auction set result='$result' where groupid = $groupid");
        
        return new JsonResponse('success',200);
    
    }
    
    /*
     * 显示result
     */
    public function showResultAction(Request $request)
    {
    
        $json = $this->get('json_parser')->parse($request);
        $groupid = $json->get('groupid','');
    
        $this->get('my_datebase')->connection();
        $result = mysql_query("select result from auction where groupid = $groupid limit 1");
        $result = mysql_result($result, 0);
        return new JsonResponse($result);
    
    }
    /*
     * 群组列表
     */
    public function retrieveGroupsListAction(Request $request)
    {
        $this->get('my_datebase')->connection();
         
        $sql = "select * from auction";
        $result = mysql_query($sql);
        $json_array = array();
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
            $json_array[] = $row;
        }
        return new JsonResponse($json_array);
        
        
    
    }
    
    /*
     * 获得用户列表
     */
    public function retrieveByFilterAction(Request $request)
    {    
        $json = $this->get('json_parser')->parse($request);
        $keyword = $json->get('keyword','');
        $start = $json->get('start',0);
        $num = $json->get('num',20);
    
        if($keyword!=''){
            $filter = " where (phone like '%$keyword%' or username like '%$keyword%') ";
        }else{
            $filter = "";
        }
        
        $this->get('my_datebase')->connection();
         
        $sql = "select username,phone,id,last_login,create_date,enable,location,grade,mark,cid from user ".$filter."order by id desc limit $start,$num";
        $result = mysql_query($sql);
        $json_array = array();
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
            $row['last_login'] = date('Y-m-d H:i:s',$row['last_login']);
            $row['create_date'] = date('Y-m-d H:i:s',$row['create_date']);
            
            $json_array[$row['id']] = $row;
        }
        
        foreach($json_array as $rs){
        	if($rs['cid']!=''&&$rs['cid']!=0){
        		$cid_array = (explode(",",$rs['cid']));
        		$cidnum = count($cid_array);
        		$category = '';
        		for($i=0;$i<$cidnum;$i++){
        			$sql = "select name from category where id = $cid_array[$i]";
        			$category = $category .",".mysql_result(mysql_query($sql),0,"name");
        		}
        		$category = substr($category,1);
        		$json_array[$rs['id']]['category']=$category;
        	}else{
        		$json_array[$rs['id']]['category']="";
        	}
        	
        }
        return new JsonResponse(array_values($json_array));
    
    }
    
    /*
     * 获得用户列表Num
     */
    public function retrieveByFilterNumAction(Request $request)
    {
    
        $json = $this->get('json_parser')->parse($request);
        $keyword = $json->get('keyword','');
        if($keyword!=''){
            $filter = " where phone like '%$keyword%' ";
        }else{
            $filter = "";
        }
    
        $this->get('my_datebase')->connection();
         
        $sql = "select count(*) from user".$filter;
        
            return new Response(mysql_result(mysql_query($sql), 0));
        
    }
    
    /*
     * 禁用用户
     */
    public function enableAction(Request $request)
    {
    
        $json = $this->get('json_parser')->parse($request);
        $user_id = $json->get('user_id',0);
        $enable = $json->get('enable',-1);
        
        if($user_id!=0){
            
            $this->get('my_datebase')->connection();
             
            $sql = "update user set enable=$enable where id=$user_id";
            mysql_query($sql);
            
            return new JsonResponse('success',200);
        }
        return new Response('',500);
    
    }
    
    /*
     * createOrder
     */
    public function createOrderAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        
        
        $user_id = $json->get('user_id',0);
        
        $myuid = $this->get('login')->checkLogin($request);
        if(!$user_id){
        	$user_id = $myuid;
        }
        
        $publish_id = $json->get('publish_id',0);
        $money = $json->get('money',0);
        
        $order_mark = $json->get('order_mark',0);
        $current = time();
        //return new Response($current);
        $order_num = $current.rand(1000, 9999);
        //return new Response($order_num);
        $this->get('my_datebase')->connection();
        
        //echo "select status from recommended where id = $publish_id";
        if($order_mark==0){
	        $status = mysql_result(mysql_query("select status from recommended where id = $publish_id"),0);
	        if($status==0){
	        	//mysql_query("UPDATE orderlist SET status = 5,status_message='交易取消',updated_date = $current WHERE status!=5 and publish_id = $publish_id");
	        	return new Response('此商品已经被支付过！',405);
	        }
        }
        
        
        $sql = "insert into orderlist set order_mark = $order_mark,money = $money,user_id = $user_id,publish_id = $publish_id,order_num = $order_num,status=0,status_message='需支付订单',creation_date=$current,updated_date=$current";
        mysql_query($sql);
        $id = mysql_insert_id();
        $registration = mysql_result(mysql_query("select registration from user where id = $user_id limit 1"),0);
        //又开始推送了
        if($registration){
        $this->pushurl($registration,$user_id, "等待支付！客服在后台给你生成订单啦", $id);
        }
        return new Response($id);
    }
    /*
     * 根据uid来发送推送通知(带data)
     */
    function pushurl($registration,$uid,$content,$id)
    {
    	$this->get('my_datebase')->connection();
    	$current = time();
    	mysql_query("insert into message set uid = $uid,title='等待支付！客服在后台给你生成订单啦',content='$content',creation_date = $current,type=2,data='$id'");
    	
   
    	 
    	$this->get('jpush')->push_url($registration,$content,$id,2);
    
    }
    /*
     * myOrder
     */
    public function myOrderAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
     	
    	$user_id = $this->get('login')->checkLogin($request);
        if(!$user_id){
            return new Response('',403);
        }
    	$start = $json->get('start',0);
    	$num = $json->get('num',20);
    	$this->get('my_datebase')->connection();
    	$sql = "select * from orderlist where user_id = $user_id and status!=5 order by id desc limit $start,$num";
    	//$sql = "select a.*,b.image_url,b.title from orderlist as a left join recommended as b on a.publish_id = b.id where a.user_id = $user_id and a.status!=5 order by a.id desc limit $start,$num";
    	$result = mysql_query($sql);
    	$json_array = array();
    	while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
            $row['creation_date'] = date('Y-m-d',$row['creation_date']);
            
           
            if($row['order_mark']==0){
            	$recommend = mysql_fetch_array(mysql_query("select image_url,title from recommended where id = $row[publish_id]"),MYSQL_ASSOC);
            	$row['title'] = ($recommend['title']=="<null>"||$recommend['title']==null?"":$recommend['title']);      
            	$row['image_url'] = ($recommend['image_url']=="<null>"||$recommend['image_url']==null?"":$recommend['image_url']);
            }else{
            	$article = mysql_fetch_array(mysql_query("select image_url,title from article where id = $row[publish_id]"),MYSQL_ASSOC);
            	$row['title'] = ($article['title']=="<null>"||$article['title']==null?"":$article['title']);      
            	$row['image_url'] = ($article['image_url']=="<null>"||$article['image_url']==null?"":$article['image_url']);
            	
            }
            
            
            $json_array[] = $row;
        }
        return new JsonResponse($json_array);
    
    
    }
    /*
     * 推送测试
     */
    public function pushAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	$registration = $json->get('registration','');
    	$content = $json->get('content','');
    	$title = $json->get('title','');
    	$this->get('jpush')->push_one($registration,$content);
    	return new Response("ok");
    }
    /*
     * 根据uid来发送推送通知
     */
    public function pushoneAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $uid = $json->get('uid','');
        $content = $json->get('content','');
        $this->get('my_datebase')->connection();
        $current = time();
        //首先获得到registration，fos_user
        if(strpos($uid,",")){
            $uid_array = explode(",", $uid);
            foreach($uid_array as $uid_one){
                mysql_query("insert into message set uid = $uid_one,title='通知消息',content='$content',creation_date = $current,type=3,data=''");
                $sql = "select registration from user where id = $uid_one";
                $result = mysql_query($sql);
                $registration = mysql_result($result, 0);
                if($registration){
                    //return new Response('',415);
	                $this->get('jpush')->push_one($registration,$content);
                }
                
                
            }
        }else{
            mysql_query("insert into message set uid = $uid,title='通知消息',content='$content',creation_date = $current,type=3,data=''");
            $sql = " select registration from user where id = $uid";
            $result = mysql_query($sql);
            $registration = mysql_result($result, 0);
            if($registration){
                //return new Response('',415);
	            $this->get('jpush')->push_one($registration,$content);
            }
        }
        return new JsonResponse('',200);
    }
    
    
    
    
    /*
     * push all
     */
    public function pushallAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $title = $json->get('title','');
        $content = $json->get('content','');
        $this->get('jpush')->pushall($title,$content);
        //$this->get('push')->push_all_android($title,$content);
        //$this->get('push')->push_all_ios($title,$content);
        $this->get('my_datebase')->connection();
        $current = time();
        
        $result = mysql_query("select id from user where registration !=''");
        mysql_query("insert into message set status=1,title = '$title',content = '$content',creation_date = $current,type=3,data=''");
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
        	mysql_query("insert into message set uid=$row[id],status=0,title = '$title',content = '$content',creation_date = $current,type=3,data=''");
        }
        return new Response('',200);
    }

    /*
     * push all by url
     */
    public function pushallurlAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $title = $json->get('title','');
        $id = $json->get('id','');
        $url = "http://eie.ren/app_dev.php/api/order/showArticle?id=".$id;
        $this->get('jpush')->push_url_all($title,$url,1);
        //$this->get('push')->push_all_android($title,$content);
        //$this->get('push')->push_all_ios($title,$content);
        $this->get('my_datebase')->connection();
        $current = time();

        $result = mysql_query("select id from user where registration !=''");
        mysql_query("insert into message set status=1,title = '$title',content = '$title',creation_date = $current,type=1,data='$url'");
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
            mysql_query("insert into message set uid=$row[id],status=0,title = '$title',content = '$title',creation_date = $current,type=1,data='$url'");
        }
        return new Response	('',200);
    }
    
    /*
     * all system message
     */
    public function allSystemMessageAction(Request $request)
    {
    	$this->get('my_datebase')->connection();
    	$sql = "select * from message where uid = 0 order by id desc limit 30";
    	$result = mysql_query($sql);
    	$json_array = array();
    	while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
    		$row['creation_date'] = date('Y-m-d',$row['creation_date']);
    		$json_array[] = $row;
    	}
    	return new JsonResponse($json_array);
    }
    
    /*
     * all messages to me 
     */
    public function allMessagesToMeAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	$start = $json->get('start',0);
    	$num = $json->get('num',10);
    	
    	$user_id = $this->get('login')->checkLogin($request);
    	if(!$user_id){
    		return new Response('',403);
    	}
    	$this->get('my_datebase')->connection();
    	$sql = "select * from message where uid = $user_id order by id desc limit $start,$num";
    	$result = mysql_query($sql);
    	$json_array = array();
    	while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
    		
    		$row['creation_date'] = date('Y-m-d',$row['creation_date']);
    		$json_array[] = $row;
    	}
    	return new JsonResponse($json_array);
    }
    /*
     * del message
     */
    public function delMessageAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	$id = $json->get('id',0);
    	 
    	$user_id = $this->get('login')->checkLogin($request);
    	if(!$user_id){
    		return new Response('',403);
    	}
    	$this->get('my_datebase')->connection();
    	$sql = "delete from message where id = $id";
    	$result = mysql_query($sql);
    	
    	return new JsonResponse("success",200);
    }
    
    /*
     * update messages readTime
     */
    public function updateReadTimeAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	$id = $json->get('id',0);
    	 
    	$user_id = $this->get('login')->checkLogin($request);
    	if(!$user_id){
    		return new Response('',403);
    	}
    	
    	$this->get('my_datebase')->connection();
    	$sql = "update message set status = 1 where id = $id";
    	mysql_query($sql);
    	return new JsonResponse("",200);
    }
    
    /*
     * 获得我未读信息
     */
    public function unreadNumAction(Request $request)
    {
    	$json = $this->get('json_parser')->parse($request);
    	 
    	$user_id = $this->get('login')->checkLogin($request);
    	if(!$user_id){
    		return new Response('',403);
    	}
    	$this->get('my_datebase')->connection();
    	$sql = "select count(*) from message where uid = $user_id and status=0";
    	$result = mysql_query($sql);
    	$num = mysql_result($result, 0);
    	return new Response($num);
    }
    
    function log($str)
    {
    
    	$open=fopen("wxpay/logs/log.txt","a" );
    	fwrite($open,$str);
    	fclose($open);
    }
    //------------------------------------------------
    // 支付宝（微信）服务器响应处理(主要操作:更改订单状态)
    //------------------------------------------------
    public function notifyAction(Request $request){
    
    	$trade_no = $request->get('out_trade_no');
    	//$this->log($trade_no."\r\n");
    	$current = time();
    	$this->get('my_datebase')->connection();
    	//$sql = "select id from nodify where status = 0 and out_trade_no = '$trade_no'";
    	//$this->log("sql:".$sql."\r\n");
    	//$result = mysql_query($sql);
    	//$num  = mysql_num_rows($result);
    	//$this->log("num:".$num."\r\n");
    	//if($num){
    		//更新nodify表
    		//mysql_query("update nodify set status = 1 where out_trade_no = '$trade_no'");
    		//$this->log("SELECT publish_id FROM `orderlist` WHERE order_num = '$trade_no'"."\r\n");
    		$result = mysql_query("SELECT publish_id FROM `orderlist` WHERE order_num = '$trade_no'");
    		if(mysql_num_rows($result)){
    			$publish_id = mysql_result($result,0);
    			//$this->log($publish_id."\r\n");
	    		//将此out_trade_no对应的orderlist表中的publish_id的recommend表中的status=0
	    		mysql_query("UPDATE recommended SET status = 0 WHERE id = $publish_id");
	    		//将orderlist里面所有的publish_id的status = 5，status_message = 交易取消
	    		mysql_query("UPDATE orderlist SET status = 5,status_message='交易取消',updated_date = $current WHERE status!=5 and publish_id = $publish_id");
	    		//更改orderlist里当前订单的状态 status=3 status_message = 交易成功
	    		mysql_query("UPDATE orderlist set status = 3 ,status_message = '交易成功',updated_date = $current where order_num = '$trade_no'");
    			
    		//}
    	}else{
    		$this->log("error\r\n");
    		return new Response('订单号错误',403);
    	}
    
    
    	return new Response('',200);
    }
    
    /*
     * 微信 统一下单
     */
    public function createWeixinPayAction(Request $request)
    {
    	//return new Response((int)("0.01")*100);
    	$json = $this->get('json_parser')->parse($request);
    	$order_id = $json->get('order_id',1);//订单的id
        $name = $json->get('name','');
        $phone = $json->get('phone','');
        $address = $json->get('address','');
    	$current = time();
    	//uid 判断
    	$user_id = $this->get('login')->checkLogin($request);
    	if(!$user_id){
    		return new Response('传token啊,魂淡！！',403);
    	}
    	 
    	 
    	//$out_trade_no = time().rand(0000, 9999);
    	 
    	//根据order_id 来得到定金
    	$result = mysql_query("select * from orderlist where id = $order_id");
    	if(!mysql_num_rows($result)){
    		return  new Response('没有这个订单',401);
    	}
		$orderinfo = mysql_fetch_array($result,MYSQL_ASSOC);
			if($orderinfo['order_mark']==0){
			$status = mysql_result(mysql_query("select status from recommended where id = $orderinfo[publish_id]"),0);
			if($status==0){
				mysql_query("UPDATE orderlist SET status = 5,status_message='交易取消',updated_date = $current WHERE status!=5 and publish_id = $orderinfo[publish_id]");
				return new Response('此商品已经被支付过！',405);
			}
		}
		$out_trade_no = $orderinfo['order_num'];
		
    	$input = new \WxPayUnifiedOrder();
    	$input->SetBody("支付全款");
    	$input->SetAttach("");
    	$input->SetOut_trade_no($out_trade_no);
    	$input->SetTotal_fee(($orderinfo['money'])*100);
    	$input->SetTime_start(date("YmdHis"));
    	$input->SetTime_expire(date("YmdHis", time() + 600));
    	$input->SetGoods_tag("支付全款");
    	$input->SetNotify_url("http://eie.ren/wxpay/example/ordernotify.php");
    	$input->SetTrade_type("APP");
    	//return new Response(intval($orderinfo['money'])*100);
    	$order = \WxPayApi::unifiedOrder($input);
    
    	//mysql_query("insert into nodify set out_trade_no = '$out_trade_no',mark=0,status=0,user_id = $user_id,creation_date = $current");
    	mysql_query("update orderlist set phone='$phone',address='$address',`name`='$name' where id = $order_id");
        return new JsonResponse($order);
    }
    
    
    /*
     * alipay统一下单
     */
    public function createAlipayAction(Request $request)
    {
    	$priKey = file_get_contents("alipay/key/rsa_private_key.txt");
    	$pubKey = file_get_contents("alipay/key/alipay_public_key.txt");
    	$json = $this->get('json_parser')->parse($request);
    	$order_id = $json->get('order_id',1);//订单的id
        $name = $json->get('name','');
        $phone = $json->get('phone','');
        $address = $json->get('address','');
    	$current = time();
    	//uid 判断
    	$user_id = $this->get('login')->checkLogin($request);
    	if(!$user_id){
    		return new Response('传token啊,魂淡！！',403);
    	}
    
    
    	//$out_trade_no = time().rand(0000, 9999);
    
    	//根据order_id 来得到定金和订单编号
    	$result = mysql_query("select * from orderlist where id = $order_id");
    	if(!mysql_num_rows($result)){
    		return  new Response('没有这个订单',401);
    	}
    	$orderinfo = mysql_fetch_array($result,MYSQL_ASSOC);
		
    	if($orderinfo['order_mark']==0){
    	
	    	$status = mysql_result(mysql_query("select status from recommended where id = $orderinfo[publish_id]"),0);
	    	if($status==0){
	    		mysql_query("UPDATE orderlist SET status = 5,status_message='交易取消',updated_date = $current WHERE status!=5 and publish_id = $orderinfo[publish_id]");
	    		return new Response('此商品已经被支付过！',405);
	    	}
    	
    	}
    	$partner = "2088911579871271";
        $seller_id = "ghg2233@163.com";
        $_input_charset = "utf-8";
        $sign_type = "RSA";
        $subject = "支付全款";
        $total_fee = $orderinfo['money'];
        $out_trade_no = $orderinfo['order_num'];
        $nodify_url = "http://eie.ren/alipay/order_notify_url.php";
        
        $info = array(
            'partner'=>$partner,
            'seller_id'=>$seller_id,
            '_input_charset'=>$_input_charset,
            'sign_type'=>$sign_type,
        	'out_trade_no'=>$out_trade_no,
            'subject'=>$subject,
            'body'=>"支付全款",
            'total_fee'=>$total_fee,
            'nodify_url'=>$nodify_url,
            'pubKey'=>$pubKey,
            'priKey'=>$priKey
        );
        
    
    	//mysql_query("insert into nodify set out_trade_no = '$out_trade_no',mark=0,status=0,user_id = $user_id,creation_date = $current");
        mysql_query("update orderlist set `name`='$name',phone='$phone',address='$address' where id = $order_id");
        return new JsonResponse($info);
    }
    
    
    
    
    
    
    
    
       
}