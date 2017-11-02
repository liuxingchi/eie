<?php

namespace Ydzy\JxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class JxController extends Controller
{
    public function indexAction()
    {
        return $this->render('YdzyJxBundle:Jx:index.html.twig');
    }
    public function rentAction()
    {
        return $this->render('YdzyJxBundle:Jx:rent.html.twig');
    }
    public function driverAction()
    {
        return $this->render('YdzyJxBundle:Jx:driver.html.twig');
    }
    public function contentAction($id)
    {
        return $this->render('YdzyJxBundle:Jx:content.html.twig',array('id'=>$id));
    }
    public function driverContentAction($id)
    {
        return $this->render('YdzyJxBundle:Jx:driver_content.html.twig',array('id'=>$id));
    }
    public function publishSaleAction()
    {
        return $this->render('YdzyJxBundle:Jx:publish_sale.html.twig');
    }
    public function publishRentAction()
    {
        return $this->render('YdzyJxBundle:Jx:publish_rent.html.twig');
    }
    public function publishDriverAction()
    {
        return $this->render('YdzyJxBundle:Jx:publish_driver.html.twig');
    }
	public function mapAction($location)
    {
        return $this->render('YdzyJxBundle:Jx:map.html.twig',array('location'=>$location));
    }
	public function myfollowAction()
    {
        return $this->render('YdzyJxBundle:Jx:myfollow.html.twig');
    }
	public function recommendAction()
    {
        return $this->render('YdzyJxBundle:Jx:recommend.html.twig');
    }
	public function infoAction($id)
    {
        return $this->render('YdzyJxBundle:Jx:info.html.twig',array('id'=>$id));
    }
	public function myinfoAction()
    {
        return $this->render('YdzyJxBundle:Jx:myinfo.html.twig');
    }
	public function loginAction()
    {
        return $this->render('YdzyJxBundle:Jx:login.html.twig');
    }
	public function registerAction()
    {
        return $this->render('YdzyJxBundle:Jx:register.html.twig');
    }
	public function repwdAction()
    {
        return $this->render('YdzyJxBundle:Jx:repwd.html.twig');
    }
	public function editSaleAction($id)
    {
        return $this->render('YdzyJxBundle:Jx:editsale.html.twig',array('id'=>$id));
    }
	public function editDriverAction($id)
    {
        return $this->render('YdzyJxBundle:Jx:editdriver.html.twig',array('id'=>$id));
    }
	public function jobAction()
    {
        return $this->render('YdzyJxBundle:Jx:job.html.twig');
    }
	public function wanjiAction()
    {
        return $this->render('YdzyJxBundle:Jx:wanji.html.twig');
    }
	public function linkusAction()
    {
        return $this->render('YdzyJxBundle:Jx:linkus.html.twig');
    }
    public function pushtmlAction($pid)
    {
        return $this->render('YdzyJxBundle:Jx:pushurl.html.twig',array("pid" => $pid));
    }
    public function xqxinwenAction()
    {
        return $this->render('YdzyJxBundle:Default:xqxinwen.html.twig');
    }
    
    public function weblunboAction($machine_id)
    {
//    		$machineId = $request->query->get('machine_id', '0');
//    		$imgIndex = $request->query->get('index', '0');
//        return $this->render('YdzyJxBundle:Default:weblunbo.html.twig', array('machine_id' => $machineId, 'img_index' => $imgIndex));
        return $this->render('YdzyJxBundle:Default:weblunbo.html.twig', array('machine_id' => $machine_id));
    }
    public function weblunbowbbAction($driver_id)
    {
        return $this->render('YdzyJxBundle:Default:weblunbowbb.html.twig', array('driver_id' => $driver_id));
    }
    

}
