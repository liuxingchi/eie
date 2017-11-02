<?php

namespace Ydzy\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Ydzy\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('YdzyAdminBundle:Default:main.html.twig');
    }
	public function welcomeAction()
    {
        return $this->render('YdzyAdminBundle:Default:welcome.html.twig');
    }
    public function noaccessAction()
    {
        return $this->render('YdzyAdminBundle:Default:noaccess.html.twig');
    }
	public function registerAction()
    {
        return $this->render('YdzyAdminBundle:Default:register.html.twig');
    }
	public function mainAction()
    {
        return $this->render('YdzyAdminBundle:Default:main.html.twig');
    }
    public function okorderAction($id)
    {
        return $this->render('YdzyAdminBundle:Default:okorder.html.twig',array('id'=>$id));
    }
    public function roleAction()
    {
        return $this->render('YdzyAdminBundle:Default:role.html.twig');
    }
    public function addroleAction()
    {
        return $this->render('YdzyAdminBundle:Default:addrole.html.twig');
    }
    public function updateroleAction($id)
    {
        return $this->render('YdzyAdminBundle:Default:updaterole.html.twig',array('id'=>$id));
    }
    public function userroleAction()
    {
        return $this->render('YdzyAdminBundle:Default:userrole.html.twig');
    }
    public function manageAction()
    {
        return $this->render('YdzyAdminBundle:Default:admin.html.twig');
    }
    public function addmanageAction()
    {
        return $this->render('YdzyAdminBundle:Default:addadmin.html.twig');
    }
    public function adduserAction()
    {
        return $this->render('YdzyAdminBundle:Default:adduser.html.twig');
    }
    public function updatemanageAction($id)
    {
        return $this->render('YdzyAdminBundle:Default:updatemanage.html.twig',array('id'=>$id));
    }
    public function memberAction()
    {
        return $this->render('YdzyAdminBundle:Default:member.html.twig');
    }
    public function memberinfoAction($id)
    {
        return $this->render('YdzyAdminBundle:Default:memberinfo.html.twig',array("id"=>$id));
    }
    public function menuAction()
    {
		$user = $this->getUser();
        if(!$user)
        {
            return new Response("", 403);   
        }
        //从数据库获得列表
        $this->get('my_datebase')->connection();
        $sql = "select id,URL,name from resources where parent = 1";
        $result = mysql_query($sql);
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
        
            $arrayList[] = $row; 
        }
        
        
		$role = $this->getUser()->getRoles();
		$uid =  $this->getUser()->getId();
		$username =  $this->getUser()->getUsername();
		return $this->render('YdzyAdminBundle:Default:menu.html.twig',array('role'=>$role,'uid'=>$uid,'username'=>$username,'menuList'=>$arrayList));
    }
	
	public function preshowAction()
    {
        return $this->render('YdzyAdminBundle:Default:preshow.html.twig');
    }
	public function newsAction()
    {
        return $this->render('YdzyAdminBundle:Default:news.html.twig');
    }
	public function usertopAction($id,$day)
    {
        return $this->render('YdzyAdminBundle:Default:usertop.html.twig',array('plan_id'=>$id,'day'=>$day));
    }
	public function ordertopAction()
    {
        return $this->render('YdzyAdminBundle:Default:ordertop.html.twig');
    }
	public function publishAction()
    {
        return $this->render('YdzyAdminBundle:Default:publish.html.twig');
    }
    public function retrieveCommentAction($id)
    {
        return $this->render('YdzyAdminBundle:Default:comment.html.twig',array('id'=>$id));
    }
	public function pushAction()
    {
        return $this->render('YdzyAdminBundle:Default:push.html.twig');
    }
    public function pusharticleAction()
    {
        return $this->render('YdzyAdminBundle:Default:pusharticle.html.twig');
    }
	public function marketAction()
    {
        return $this->render('YdzyAdminBundle:Default:market.html.twig');
    }
	public function rechargeAction($id,$day)
    {
        return $this->render('YdzyAdminBundle:Default:recharge.html.twig',array('plan_id'=>$id,'day'=>$day));
    }
	public function appealAction()
    {
        return $this->render('YdzyAdminBundle:Default:appeal.html.twig');
    }
	public function addgoodsAction()
    {
		//从数据库获得列表
        $this->get('my_datebase')->connection();
        $sql = "select id,name from category";
        $result = mysql_query($sql);
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
        
            $arrayList[] = $row; 
        }
		return $this->render('YdzyAdminBundle:Default:addgoods.html.twig',array('categoryList'=>$arrayList));
    }
    public function updategoodsAction($id)
    {
    	//从数据库获得列表
    	$this->get('my_datebase')->connection();
    	$sql = "select id,name from category";
    	$result = mysql_query($sql);
    	while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
    
    		$arrayList[] = $row;
    	}
    	return $this->render('YdzyAdminBundle:Default:updategoods.html.twig',array('categoryList'=>$arrayList,'id'=>$id));
    }
	public function goodsdetailAction($id)
    {
        return $this->render('YdzyAdminBundle:Default:goodsdetail.html.twig',array('id'=>$id));
    }
	public function addnewsAction()
    {
        return $this->render('YdzyAdminBundle:Default:addnews.html.twig');
    }
    public function updatenewsAction($id)
    {
        return $this->render('YdzyAdminBundle:Default:updatenews.html.twig',array('id'=>$id));
    }
	public function addmarketAction()
	{
		return $this->render('YdzyAdminBundle:Default:addmarket.html.twig');
	}
	public function updatemarketAction($id)
	{
		return $this->render('YdzyAdminBundle:Default:updatemarket.html.twig',array('id'=>$id));
	}
	public function addpreshowAction()
	{
		return $this->render('YdzyAdminBundle:Default:addpreshow.html.twig');
	}
	public function updatepreshowAction($id)
	{
		return $this->render('YdzyAdminBundle:Default:updatepreshow.html.twig',array('id'=>$id));
	}
	public function auctionAction()
	{
	    return $this->render('YdzyAdminBundle:Default:auction.html.twig');
	}
	public function auctionResultAction()
	{
	    return $this->render('YdzyAdminBundle:Default:auctionResult.html.twig');
	}
	public function orderAction()
	{
	    return $this->render('YdzyAdminBundle:Default:order.html.twig');
	}
	public function createorderAction($id)
	{
		return $this->render('YdzyAdminBundle:Default:createorder.html.twig',array('id'=>$id));
	}
	public function categoryAction()
	{
		return $this->render('YdzyAdminBundle:Default:category.html.twig');
	}
	public function orderdetailAction($id)
	{
		//从数据库获得列表
		$this->get('my_datebase')->connection();
		$order_mark = mysql_result(mysql_query("select order_mark from orderlist where id = $id"),0);
		
		if($order_mark==1){
			//后台生成订单，关联article
			$sql = "select a.*,b.username,b.phone as buy_phone,c.* from orderlist as a left join user as b on a.user_id=b.id left join article as c on a.publish_id=c.id where a.id = $id";
		}else{
			$sql = "select a.*,b.username,b.phone as buy_phone,c.* from orderlist as a left join user as b on a.user_id=b.id left join recommended as c on a.publish_id=c.id where a.id = $id";
			
		}
		$result = mysql_query($sql);
		$arrayList = mysql_fetch_array($result,MYSQL_ASSOC);
		$arrayList['creation_date'] = date('Y-m-d H:i:s',$arrayList['creation_date']);
		//return new JsonResponse($arrayList);
		return $this->render('YdzyAdminBundle:Default:orderdetail.html.twig',array('id'=>$id,'array'=>$arrayList));
	}
	
	
	
	
	
	
	
	
	
	
	
	public function kefuAction()
	{
		header("location:https://kefu.easemob.com/mo/agent");
		return new Response("");
	}
	
	
	
	
	
	public function systemAction()
	{
		$this->get('my_datebase')->connection();
		$sql = "select * from system";
		$result = mysql_query($sql);
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
		
			$arrayList[] = $row;
		}
		return $this->render('YdzyAdminBundle:Default:system.html.twig',array('list'=>$arrayList));
	}
}
