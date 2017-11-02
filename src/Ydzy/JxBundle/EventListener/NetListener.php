<?php

namespace Ydzy\JxBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class NetListener
{
    private $dbconnection;

    public function __construct($dbconnection)
    {
        	$this->dbconnection = $dbconnection;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
		//echo $event->getRequest()->getPathInfo();
        //$param = $this->getPageInfo($event->getRequest()->getPathInfo());
        $session = $event->getRequest()->getSession();
        $this->dbconnection->connection();
        $sql = " select * from system";
        $result = mysql_query($sql);
        while($rs = mysql_fetch_array($result,MYSQL_ASSOC)){
            //$_SESSION['quality']=$rs['value'];
            $session->set("{$rs['name']}", $rs['value']);
        } 
       //echo $quality = $session->get('allow_normal_publish');//$_SESSION['quality'];
        
    }
	
	protected function getPageInfo($path){
		//echo $path;
	    $this->dbconnection->connection();
	    $sql = " select * from setting";
	    $result = mysql_query($sql);
	    $session = $this->getRequest()->getSession();
	    while($rs = mysql_fetch_array($result,MYSQL_ASSOC)){
	        //$_SESSION['quality']=$rs['value'];
	        $session->set($rs[key], $rs[value]);
	    }
		$quality = $session->set('quality');//$_SESSION['quality'];
		
	}
		
		
	protected function savelog($cid,$fid){
		$current = date('Y-m-d H:i:s',time());
		$this->dbconnection->connection();
		$sql = " insert into Net set fid=$fid,cid=$cid,creation_date='$current'";
		mysql_query($sql);
	}	
		
}
