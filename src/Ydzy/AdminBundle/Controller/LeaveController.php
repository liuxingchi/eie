<?php

namespace Ydzy\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LeaveController extends Controller
{
	
	public function leavewordAction()
    {
        return $this->render('YdzyAdminBundle:Leave:manage.html.twig');
    }
	public function addAction()
    {
        return $this->render('YdzyAdminBundle:Brand:add.html.twig');
    }
	public function modifyAction()
    {
	   return $this->render('YdzyAdminBundle:Brand:modify.html.twig',array('brand_id'=>$_GET['brand_id']));
    }

}
