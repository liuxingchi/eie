<?php

namespace Mirror\CardBundle\Service;

use Mirror\CardBundle\Entity\Users;
use Mirror\CardBundle\Entity\Sessions;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SessionManager
{
	private $duration = 'PT3H';

	public function check(Controller $controller, $allowRoleTypes = array('SYS_ADMIN', 'SYS_OPER', 'MER_OPER', 'MER_RECEPTION', 'USER'))
	{
		// get php session
		$session = $controller->getRequest()->getSession();
		if (!$session || !$session->get('login')){
			return 401;
		}

		$roleType = $session->get('roleType');
		if (!in_array($roleType, $allowRoleTypes)){
			return 403;
		}

		$sessionId = $session->getId();
		$session = $this->get($controller, $session);

		if (!$session || $session->getExpireDate()->getTimestamp()<time()){
			$this->debug("the second 401", array());
			$this->destroy($controller);
			return 401;
		}

		if ($session->getSessionId()!=$sessionId){
			return 409;
		}

		// update db session
		$ipAddr = $this->getClientIp($controller);
		$this->update($controller, $session, $ipAddr);
		return 200;
	}

	public function create(Controller $controller, $user, $role, $app, $merchantId, $apnsToken)
	{
		// get & create php session
		$session = $controller->getRequest()->getSession();

		$userId = $user->getId();
		$username = $user->getRealname();
		$roleType = $role->getConstName();
		$appId = $app->getId();

		$sessionId = $session->getId();
		$ipAddr = $this->getClientIp($controller);

		$session->set('login', true);
		$session->set('userId', $userId);
		$session->set('username', $username);
		$session->set('roleType', $roleType);
		$session->set('appId', $appId);
		$session->set('merchantId', $merchantId);
		$session->set('apnsToken', $apnsToken);
		$session->set('ipAddr', $ipAddr);

		// create or update db session
		$session = $this->get($controller, $session);
		if ($session){
			return $this->update($controller, $session, $ipAddr, $sessionId);
		}

		$em = $controller->getDoctrine()->getEntityManager();

		$session = new Sessions();
		$session->setUser($user);
		$session->setApp($app);
		$session->setMerchantId($merchantId);
		$session->setApnsToken($apnsToken);
		$session->setSessionId($sessionId);
		$session->setIpAddr($ipAddr);
		$datetime = new \DateTime("now");
		$datetime->add(new \DateInterval($this->duration));
		$session->setExpireDate($datetime);
		$session->setCreationDate(new \DateTime("now"));
		$session->setStatus(0);
		$em->persist($session);
		$em->flush();

		return $session;
	}

	public function update(Controller $controller, $session, $ipAddr = null, $sessionId = null)
	{
		$em = $controller->getDoctrine()->getEntityManager();

		$datetime = new \DateTime("now");
		$datetime->add(new \DateInterval($this->duration));
		$session->setExpireDate($datetime);
		if ($ipAddr){
			$session->setIpAddr($ipAddr);
		}
		if ($sessionId){
			$session->setSessionId($sessionId);
			$session->setCreationDate(new \DateTime("now"));
		}
		
		$em->flush();

		return $session;
	}
	
	public function apnsUpdate(Controller $controller, $apnsToken)
	{
		$session = $controller->getRequest()->getSession();
		if (!$session || !$session->get('login')){
			return false;
		}
		
		$session->set('apnsToken', $apnsToken);
		$sessionId = $session->getId();
		
		$em = $controller->getDoctrine()->getEntityManager();
		$session = $em->getRepository('MirrorCardBundle:Sessions')
					->findOneBySessionId($sessionId);
					
		$session->setApnsToken($apnsToken);
		$em->flush();
		
		return true;
	}

	public function destroy(Controller $controller)
	{
		$session = $controller->getRequest()->getSession();

		if (!$session){
			return true;
		}

		$sessionId = $session->getId();

		$session->set('login', false);
		$session->invalidate();

		$em = $controller->getDoctrine()->getEntityManager();
		$session = $em->getRepository('MirrorCardBundle:Sessions')
					->findOneBySessionId($sessionId);

		if ($session){
			$em->remove($session);
			$em->flush();
		}

		return true;
	}

	public function getId(Controller $controller, $prefix = '')
	{
		$session = $controller->getRequest()
					->getSession();
		if (!($session && $session->get('login'))){
			return null;
		}

		$sessionId = $session->getId();
		return $prefix.$sessionId;
	}

	private function get(Controller $controller, $session)
	{
		$userId = $session->get('userId');
		$appId = $session->get('appId');

		$em = $controller->getDoctrine()->getEntityManager();
		$repository = $em->getRepository('MirrorCardBundle:Sessions');
		$query = $repository->createQueryBuilder('s')
					->join('s.user', 'u')
					->join('s.app', 'a')
					->where('u.id = :uid')
					->andWhere('a.id = :aid')
					->setParameter('uid', $userId)
					->setParameter('aid', $appId)
					->getQuery();

		$this->debug("count = %d", array(count($query->getResult())));
		return count($query->getResult())==1?$query->getSingleResult():null;
	}

	public function getClientIp(Controller $controller){
		$ipAddr = $controller->getRequest()->server->get('HTTP_X_REAL_IP');
		if (!$ipAddr){
			$ipAddr = $controller->getRequest()->server->get('HTTP_X_FORWARDED_FOR');
		}
		if (!$ipAddr){
			$ipAddr = $controller->getRequest()->server->get('REMOTE_ADDR');
		}
		return $ipAddr;
	}

	public function debug($format, $args = array()){
		return; 
		
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
		$logStr = vsprintf($format, $args);
		
		$fp = fopen('/tmp/mycard.log', 'a');
		fprintf($fp, "%s%s\n", $debugStr, $logStr);
		fclose($fp);
	}

}
