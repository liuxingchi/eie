<?php

namespace Mirror\CardBundle\Service;

use Mirror\CardBundle\Entity\SystemSettings;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ConfigManager
{
	public function get(Controller $controller, $name, $default){
		$em = $controller->getDoctrine()
				->getEntityManager();
		
		$systemSettings = $em->getRepository('MirrorCardBundle:SystemSettings')
									->findOneByName($name);
									
		return $systemSettings?$systemSettings->getValue():$default;
	}
	
	public function set(Controller $controller, $name, $value, $description = ''){
		$em = $controller->getDoctrine()
				->getEntityManager();
		
		$systemSettings = $em->getRepository('MirrorCardBundle:SystemSettings')
									->findOneByName($name);
		
		if ($systemSettings){
			$systemSettings->setValue($value);
			$systemSettings->setDescription($description);
			$em->flush();
			return true;
		}
		return false;
	}
	
	public function getAll(Controller $controller){
		$em = $controller->getDoctrine()
				->getEntityManager();
				
		$systemSettings = $em->getRepository('MirrorCardBundle:SystemSettings')
									->findAll();
									
		$results = array();
		foreach ($systemSettings as $systemSetting){
			$name = $systemSetting->getName();
			$value = $systemSetting->getValue();
			$description = $systemSetting->getDescription();
			
			$results[] = array(
							'name' => $name,
							'value' => $value,
							'description' => $description
							);
		}
		
		return $results;
	}
	
	public function ObjTrans(Controller $controller, $objname){
		$em = $controller->getDoctrine()
				->getEntityManager();
		
		$root_url = $em->getRepository('MirrorCardBundle:SystemSettings')
									->findOneByName('SYS_ROOT_URL');
									
		$root_url = $root_url?$root_url->getValue():'';
		$host_url = 'http://'.$_SERVER['HTTP_HOST'];
		
		if ($root_url==''){
			$root_url = $host_url;
		}
		
		if ($objname==''){
			return '';
		}else if (substr($objname, 0, 7)=='http://'){
			return $objname;
		}else{
			return $root_url.$objname;
		}
	}
}