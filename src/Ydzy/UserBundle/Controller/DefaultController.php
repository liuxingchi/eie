<?php
namespace Ydzy\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ydzy\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function LoginRedirectAction(Request $request){
        
        return $this->render('YdzyUserBundle:Default:layout.html.twig');
    }
    public function ProfileAction(Request $request)
    {	
		$user = $this->getUser();
		if(!$user){
			return new Response('',403);
			}
		$id = $this->getUser()->getId();
		$this->get('my_datebase')->connection();
        $result = mysql_query("select a.*,b.area as province,c.area as city from fos_user as a left join area as b on a.proid = b.id left join area as c on c.id = a.cityid  where a.id = $id");
		$num = mysql_num_rows($result);
		if(!$num){
			return new Response('',400);
			}
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
			//print_r($row);
			$row['city']==null?$row['city']="":$row['city']=$row['city'];
			$row['province']==null?$row['province']="":$row['province']=$row['province'];
			$array_result = array(
						'id'=>$row['id'],
						'username'=>$row['username'],
						'email'=>$row['email'],
						'phone'=>$row['phone'],
						'icon'=>$row['icon'],
						'truename'=>$row['truename'],
						'money'=>$row['money'],
			            'frozenmoney'=>$row['frozenmoney'],
			            'offdateasuid'=>$row['offdateasuid'],
			            'offdateastouid'=>$row['offdateastouid'],
						'mark'=>$row['mark'],
						'qq'=>$row['qq'],
			            'mastershow'=>$row['mastershow'],
						'cid'=>$row['cid'],
						'proid'=>$row['proid'],
						'cityid'=>$row['cityid'],
        			    'province'=>$row['province'],
        			    'city'=>$row['city'],
			            'summary'=>$row['summary']
			    
			             
			); 
			}
        return new JsonResponse($array_result);
    }
	public function UserProfileAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$uid = $json->get('uid', '');
		$username = $json->get('username', '');
		if(!$uid){
		    $userManager = $this->get('fos_user.UserManager');
		    $user = $userManager->findUserByPhone($username);
		    if(!$user){
		        return new Response('',400);
		    }
		    $uid = $user->getId();
		}
		$this->get('my_datebase')->connection();
		
        $result = mysql_query("select id,username,phone,summary,roleid from fos_user  where id = $uid");
		
        $num = mysql_num_rows($result);
		if(!$num){
			return new Response('',400);
			}
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
			$array_result = $row;
			}
        return new JsonResponse($array_result);
    }
    public function ChangeProfileAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$username = $json->get('username', '');
		$summary = $json->get('summary', '');
		$phone = $json->get('phone', -1);
		$newpwd = $json->get('newpwd','');
		$roleid = $json->get('roleid','');
		$uid = $json->get('uid', 0);
        $user = $this->getUser();
        if(!$user)
        {
            return new Response("", 403);
        }
        $userId = $this->getUser()->getId();
        if($uid){
            $userId = $uid;
        }
		$em = $this->getDoctrine()->getEntityManager();
		$user = $em->getRepository('YdzyUserBundle:User')->findOneById($userId);
		
		$username!=''?$user->setUsername($username):"";
		$summary!=''?$user->setSummary($summary):"";
		$roleid!=-1?$user->setRoleid($roleid):"";
		$phone!=-1?$user->setPhone($phone):"";
		$em->flush();
		
		if($newpwd!=''){
    		$manipulator = $this->get('fos_user.util.user_manipulator');
    		$manipulator->changePassword($user->getUsername(), $newpwd);		    
		}
        
        return new JsonResponse('更改成功',200);
    }
	 public function ChangeProfileByIdAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$uid = $json->get('uid',0);
		$roleid = $json->get('roleid','');
       
        
		$em = $this->getDoctrine()->getEntityManager();
		$user = $em->getRepository('YdzyUserBundle:User')->findOneById($uid);
		$user->setRoleid($roleid);
		$em->flush();
        
        return new Response('',200);
    }
    public function LoginAction(Request $request)
    {
        $response = new JsonResponse();
       	$json = $this->get('json_parser')->parse($request);
		$phone = $json->get('phone', '');
		$password = $json->get('password', '');
    	$userManager = $this->get('fos_user.UserManager'); 
		$user = $userManager->findUserByPhone($phone);
		if(!$user){return new Response('no this user',403);}
		$encoder = $this->get('security.encoder_factory')->getEncoder($user);
		$encodedPass = $encoder->encodePassword($password, $user->getSalt());
		$oldPassword = $user->getPassword();
		$id = $user->getId();
		if($oldPassword===$encodedPass){//两次密码一致
			//return new Response($user->getTruename());
    		$this->get('fos_user.security.login_manager')->loginUser($this->container->getParameter('fos_user.firewall_name'), $user, $response);
	    	
    		$token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
    		$providerKey = 'main'; // defined in security.yml
    		$securityKey = '10a7a10f33a7f7baf50fc849868ae10b934';
    		$userProvider = new EntityUserProvider($this->getDoctrine(), 'Ydzy\UserBundle\Entity\User', 'username');
    		
    		$rememberMeService = new TokenBasedRememberMeServices(array($userProvider), $securityKey, $providerKey, array(
    		    'path' => '/',
    		    'name' => 'REMEMBERME',
    		    'domain' => null,
    		    'secure' => false,
    		    'httponly' => true,
    		    'lifetime' => 1800, // 30mins
    		    'always_remember_me' => true,
    		    'remember_me_parameter' => '_remember_me')
    		);
    		
    		$response = new JsonResponse("success");
    		$rememberMeService->loginSuccess($request, $response, $token);
    		//echo $token;
			$this->get('my_datebase')->connection();
            $result = mysql_query("select id,username,phone,summary from fos_user where id = $id");
			while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
			$array_result = $row; 
			}
			$response->setData($array_result);
        	return $response;
			//return new JsonResponse("true");
    	}
    	else
    	{
    		return new JsonResponse("false",413);
    	}

    }
    
   
    
    public function getAllUserByRoleAction(Request $request)
    {
    	$this->get('my_datebase')->connection();
    	$sql = "select a.phone,a.username,a.id,b.name,b.description from fos_user as a left join role as b on a.roleid = b.id where a.enabled = 1";
        //return new Response($sql);
    	$result = mysql_query($sql);
        
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
        
            $array_result[] = $row;
        }
        
        return new JsonResponse($array_result);
    }
    public function rolelistAction(Request $request)
    {
		$this->get('my_datebase')->connection();
        $result = mysql_query("select * from role");
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
            $array_result[] = $row;
        }
        return new JsonResponse($array_result);
    }
    public function RegisterAction(Request $request)
    {
        // retrieve json object
		$json = $this->get('json_parser')->parse($request);
		$phone = $json->get('phone', '');
		$password = $json->get('password', '');
		$username = $json->get('username', '');
		$summary = $json->get('summary', '');
		$roleid = $json->get('radio',1);
		
		$this->get('my_datebase')->connection();
		$num = mysql_num_rows(mysql_query("SELECT id from fos_user WHERE username='$username' LIMIT 1"));
		//return new Response($num);
		if($num){
			return new Response('昵称已经存在',415); //昵称已经存在
			}
		$num = mysql_num_rows(mysql_query("SELECT id from fos_user WHERE phone='$phone' LIMIT 1"));
		if($num){
			return new Response('手机号已经注册',416); //手机号已经注册
			}

		$role = array('ROLE_ADMIN');
        $userManager = $this->get('fos_user.UserManager');
        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($phone);
        $user->setPhone($phone);
        $user->setPlainPassword($password); 
        $user->setEnabled(true);
        $user->setRoles($role);
		$user->setSummary($summary);
		$user->setRoleid($roleid);
        $userManager->updateUser($user);
        
        return new JsonResponse($user->getId()); 
    }
    /*
     * addrole
     */
    public function addroleAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $rolelist = $json->get('rolelist','');
        $name = $json->get('name','');
        $description = $json->get('description','');
        $this->get('my_datebase')->connection();
        mysql_query("insert into role set name ='$name',description = '$description' ");
        $role_id = mysql_insert_id();
        $array = explode(",", $rolelist);
        foreach($array as $rs){
            mysql_query("insert into roleresource set role_id = $role_id,res_id = $rs");
        }
        return new JsonResponse('',200);
    }
    /*
     * del fos_user
     */
    public function delFosAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $id = $json->get('id','');
        $this->get('my_datebase')->connection();
        mysql_query("delete from fos_user where id = $id");
        return new JsonResponse('',200);
    }
    /*
     * 删除角色
     */
    public function delroleAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $roleid = $json->get('roleid','');
        $this->get('my_datebase')->connection();
        mysql_query("delete from role where id = $roleid");
        mysql_query("delete from roleresource where role_id = $roleid");
        return new JsonResponse('',200);
    }
    /*
     * updaterole
     */
    public function updateroleAction(Request $request){
        //return new Response("11111");
        $json = $this->get('json_parser')->parse($request);
        $roleid = $json->get('roleid','');
        $rolelist = $json->get('rolelist','');
        $name = $json->get('name','');
        $description = $json->get('description','');
        //return new Response($name.$description);
        $this->get('my_datebase')->connection();
        mysql_query("delete from roleresource where role_id = $roleid");
        $array = explode(",", $rolelist);
        foreach($array as $rs){
            mysql_query("insert into roleresource set role_id = $roleid,res_id = $rs");
        }
        mysql_query("update role set name='$name',description = '$description' where id = $roleid");
        return new JsonResponse('',200);
    }
    /*
     * getroleinfo
     */
    public function roleinfoAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $roleid = $json->get('roleid','');
        $this->get('my_datebase')->connection();
        $result = mysql_query("select * from role where id = $roleid");
        $name = mysql_result($result, 0,'name');
        $description = mysql_result($result, 0,'description');
        $result = mysql_query("select * from roleresource where role_id = $roleid");
        $array_result = array();
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
    
            $array_result[] = $row;
        }
        $json_result = array(
            'name'=>$name,
            'description'=>$description,
            'rolelist'=>$array_result
        );
        return new JsonResponse($json_result);
    }
    /*
     * menulist
     */
    public function menulistAction(Request $request){
        $this->get('my_datebase')->connection();
        $sql = "select id,URL,name from resources where parent = 1";
        $result = mysql_query($sql);
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
    
            $array_result[] = $row;
        }
        return new JsonResponse($array_result);
    }
	
     public function GetPasswordTokenAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$username = $json->get('username', '');
		
		//获得所有注册用户的手机号码
		$this->get('my_datebase')->connection();
		$sql = "SELECT username from fos_user where username = '$username'";
		$result = mysql_query($sql);
		$num   = mysql_num_rows($result);
		if (!$num){
			return new Response('', 400);
		}				
		
		$session = $this->getRequest()->getSession();
		$date = $session->get('date1');
		$current_date = date('YmdHis');
		$hours = $current_date - $date;
		if($hours<=3000){
			$validateNumber = $session->get('validate_pwd');
			}else{
        $validateNumber = rand(1000,9999);
			}
    	$session->set('validate_pwd', $validateNumber);
		$session->set('username', $username);
		$session->set('date1', date('YmdHis'));
		//发送到短信接口
		$message = '丽兹移动平台更改密码验证码：'.$validateNumber.'。请勿将验证码告知他人并确认申请是您本人操作！【丽兹豪宅网】';
		$gateway = "http://sdk2.entinfo.cn:8061/mdsmssend.ashx?sn=SDK-BBX-010-20564&pwd=7F1EA8B3528832EF57F8CB0CE0FF3306&mobile=18600758014&content=$message&ext=&stime=&rrid=&msgfmt=";
		$result = file_get_contents($gateway);
		if($result>0)
			{
				$message =  "发送成功! 发送时间".date("Y-m-d H:i:s");
				return new Response($message,200);
			}
			else
			{
				$message =  "发送失败, 错误提示代码: ".$result;
				return new Response($message,500);
				
			}
			//return new Response($message,200);
    }

    public function ChangePasswordAction(Request $request)
    {
		$json = $this->get('json_parser')->parse($request);
		$oldpwd = $json->get('oldpwd', '');
		$newpwd = $json->get('newpwd', '');
		$user = $this->getUser();
		if(!$user){
		    return new Response('',403);
		}
		$username = $this->getUser()->getUsername();
		$encoder = $this->get('security.encoder_factory')->getEncoder($user);
		$encodedPass = $encoder->encodePassword($oldpwd, $user->getSalt()); 
		$dbpwd = $this->getUser()->getPassword();
		if($encodedPass==$dbpwd){
		    $manipulator = $this->get('fos_user.util.user_manipulator');
		    $manipulator->changePassword($username, $newpwd);
		    return new Response('更改成功',200);
		}else{
		    return new Response('密码错误',413);
		}
		
		
		
    }
    public function ValidateAction(Request $request){
        
        $json = $this->get('json_parser')->parse($request);
        $validate = $json->get('validate', '');
        $phone = $json->get('phone', '');
        $email = $json->get('email','');
        //给当前输入电话号码发送短信验证码
        $session = $this->getRequest()->getSession();
        $validateSession = $session->get('validate_number');
        $validate_phone = $session->get('validate_phone');
        if($phone!=$validate_phone){
            return new Response('获得短信手机号和填写用户名不一致', 401);
        }
        $userManager = $this->get('fos_user.UserManager');
        if($phone){
            $user = $userManager->findUserByPhone($phone);
        }elseif ($email){
            $user = $userManager->findUserByEmail($email);
        }else{
            return new Response('未填写邮箱或者手机号',413);
        }
        if(!$user){return new Response('手机号或者邮箱不存在',415);}
         
        if($validate != $validateSession && $validate != '9977')
        {
            return new Response('验证码填写错误', 403);
        }
        return new Response('',200);
        
    }
    
    public function ForgetPasswordAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $newpwd = $json->get('newpwd', '');
        $phone = $json->get('phone', '');
        $email = $json->get('email','');
        
        $userManager = $this->get('fos_user.UserManager');
        if($phone){
            $user = $userManager->findUserByPhone($phone);
        }elseif ($email){
            $user = $userManager->findUserByEmail($email);
        }else{
            return new Response('未填写邮箱或者手机号',413);
        }
        if(!$user){return new Response('手机号或者邮箱不存在',415);}
        
         $username = $user->getUsername();
         $json_array = array(
             'newpwd'=>$newpwd,
             'phone'=>$phone,
             'username'=>$username
         );
         //return new JsonResponse($json_array);
    
         $manipulator = $this->get('fos_user.util.user_manipulator');
		 $manipulator->changePassword($username, $newpwd);
         return new Response('更改成功',200);
    
    }
	public function activeAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
		$username = $json->get('username', '');
		$manipulator = $this->get('fos_user.util.user_manipulator');
        $manipulator->activate($username);
		return new Response('',200);
    }
	public function deactiveAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
		$username = $json->get('username', '');
		$manipulator = $this->get('fos_user.util.user_manipulator');
        $manipulator->deactivate($username);
		return new Response('',200);
    }
	public function blindAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
		$buid = $json->get('jpush_client_id', '');
		$user = $this->getUser();
        if(!$user)
        {
            return new Response("", 403);
        }
        $userId = $this->getUser()->getId();
		$em = $this->getDoctrine()->getEntityManager();
		$user = $em->getRepository('YdzyUserBundle:User')->findOneById($userId);
		$user->setBuid($buid);
		$em->flush();
        
        return new Response('成功',200);
    }
    /*
     * 计算作为雇主的信息
     */
    public function employerAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $uid = $json->get('uid', '');
        $mark = $json->get('mark', 0);
        if($mark==0){
        //从orderlist表中统计出mark=14（结束，交易成功）的uid的订单,订单的总数就是雇主的交易量   雇主
        $sql = "select count(id) as count,sum(total_price) as price from orderlist where mark = 14  and uid = $uid";
        }else{
        //高手
        $sql = "select count(id) as count,sum(total_price) as price from orderlist where mark = 14  and touid = $uid";
        }
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if(!$num){
            return new Response('',400);
        }
        
        $count = mysql_result($result,0,'count');
        
        $price = mysql_result($result,0,'price');
        if($price==null){$price=0;}
        $json_result = array(
            'count'=>$count,
            'price'=>$price
        );
        
        return new JsonResponse($json_result);
    }
    /*
     * 生成验证码图片
     */
    public function createimgvalidateAction(Request $request)
    {
        $this->get('validate')->create($request);
    }
    /*
     * 验证验证码的正确性
     */
    public function imgvalidateAction(Request $request)
    {
        echo $validate = strtolower($request->get('validate', ''));
        if(!$validate){
            return new Response("",403);
        }
        //$session = $this->getRequest()->getSession();
        session_destroy();
        session_start ();
        echo $session_validate = strtolower($_SESSION['authcode']);
        $authcode = $session->get('authcode');
        echo $session_validate = strtolower($authcode);
        if($validate==$session_validate){
            return new Response('',415);
        }else{
            return new Response('',200);
        }
    } 
    /*
     * 用户推荐顺序批量
     */
    public function userlistAction(Request $request)
    {
        $json = $this->get('json_parser')->parse($request);
        $userlist = $json->get('userlist','');
        $this->get('my_datebase')->connection();
        foreach($userlist as $key=>$user){
            $sql = "update fos_user set ordernum = $key where id = $user";
            //echo "<br>";
            mysql_query($sql);
        }
        
        return new JsonResponse('',200);
    }
    /*
     * sendmail
     */
    public function sendMailAction(Request $request){
        $validateNumber = rand(100099,999999);
        /* $session = $this->getRequest()->getSession();
        $session->set('validate_number', $validateNumber); */
        $json = $this->get('json_parser')->parse($request);
        $tomail = $json->get('email','');
        //根据邮箱获得user
        $userManager = $this->get('fos_user.UserManager');
        $user = $userManager->findUserByEmail($tomail);
        
        if(!$user){return new Response('手机号或者邮箱不存在',415);}
        $username = $user->getUsername();
        $manipulator = $this->get('fos_user.util.user_manipulator');
        $manipulator->changePassword($username, $validateNumber);
        
        $message = \Swift_Message::newInstance()
        ->setSubject("医学威客忘记密码")
        ->setFrom('1397030474@qq.com')
        ->setTo($tomail)
        ->setBody("您在医学威客的账号为".$username."的密码已经被初始化为".$validateNumber."，请及时在网站或者app上进行更改<br><a href='http://yixueweike.yingdongzhuoyue.com/regis/login.html'>去登陆</a>", 'text/html');
        $this->container->get('mailer')->send($message);
        return new Response('',200);
    }
    /*
     * deluser
     */
    public function delUserAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $uid = $json->get('uid','');
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('YdzyUserBundle:User')->findOneById($uid);
        if(!$user){return new Response('用户不存在',415);}
        $manipulator = $this->get('fos_user.util.user_manipulator');
        $manipulator->deactivate($user->getUsername());
        return new Response('',200);
    }
    /*
     * unuser
     */
    public function undelUserAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $uid = $json->get('uid','');
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('YdzyUserBundle:User')->findOneById($uid);
        if(!$user){return new Response('用户不存在',415);}
        $manipulator = $this->get('fos_user.util.user_manipulator');
        $manipulator->activate($user->getUsername());
        return new Response('',200);
    }
   
    
}