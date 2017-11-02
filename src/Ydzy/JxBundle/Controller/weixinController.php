<?php

namespace Ydzy\JxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class weixinController extends Controller
{
		public function weixinAction(Request $request)   
    {
    		return new JsonResponse('is here');
    }
    
}