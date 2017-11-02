<?php

namespace Ydzy\JxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function infoAction($id,$category)
    {		
        return $this->render('YdzyJxBundle:Default:info.html.twig', array('id' => $id,'category'=>$category));
    }
}
