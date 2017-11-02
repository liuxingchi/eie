<?php

namespace Ydzy\ApiBundle\Service;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class Logger
{
	/* log level:
		0 - off
		1 - warning 
		2 - info
		3 - debug
	*/
	
	public function log(Controller $controller, $level, $format, $args = array()){
		$em = $controller->getDoctrine()
				->getEntityManager();

		$logLevelSettings = $em->getRepository('MirrorCardBundle:SystemSettings')
									->findOneByName('LOG_LEVEL');
		
		$logLevel = $logLevelSettings?$logLevelSettings->getValue():0;
		
		if ($level>$logLevel){
			return false;
		}
		
		$logStr = vsprintf($format, $args);
		
		$log = new Logs();
		$log->setLevel($level);
		$log->setContent($logStr);
		$log->setCreationDate(new \DateTime("now"));
		$log->setStatus(0);
		$em->persist($log);
		$em->flush();

		// $fp = fopen('/tmp/mycard.log', 'a');
		// fprintf($fp, "%s%s\n", $debugStr, $logStr);
		// fclose($fp);
		
		return true;
	}
	
	public function warning(Controller $controller, $format, $args = array()){
		$this->log($controller, 1, $format, $args);
	}
	
	public function info(Controller $controller, $format, $args = array()){
		$this->log($controller, 2, $format, $args);
	}
	
	public function debug(Controller $controller, $format, $args = array()){
		$stacks = debug_backtrace();
		$file = '';
		$line = 0;
		$function = '';
		foreach ($stacks as $key => $stack){
			if ($key==0){
				$file = $stack['file'];
				$line = $stack['line'];
			}else if ($key==1){
				$function = $stack['function'];
			}
		}
		
		$debugStr = sprintf("[%s@%s:%d] ", $function, $file, $line);
		$this->log($controller, 3, $debugStr.$format, $args);
	}
}