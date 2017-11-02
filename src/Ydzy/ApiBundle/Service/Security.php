<?php

namespace Mirror\CardBundle\Service;

use Mirror\CardBundle\Entity\Users;
use Mirror\CardBundle\Entity\Applicatoins;
use Mirror\CardBundle\Entity\Logins;
use Mirror\CardBundle\Entity\LoginFences;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Security
{
	private $logDuration = 'P6M';
	private $frequentLocationNum = 3;
	private $blockingDuration = 'PT15M';
	private $maxLoginAttempts = 5;
	
	public function createLoginRecords(Controller $controller, 
						$user, $app, $ipAddr, $region, $address)
	{
		$em = $controller->getDoctrine()->getEntityManager();
		
		$login = new Logins();
		$login->setUser($user);
		$login->setApp($app);
		$login->setIpAddr($ipAddr);
		$login->setRegion($region);
		$login->setAddress($address);
		$login->setCreationDate(new \DateTime('now'));
		$login->setStatus(0);
		$em->persist($login);
		$em->flush();
	}
	
	public function getLoginRecords(Controller $controller, $user)
	{
		$em = $controller->getDoctrine()->getEntityManager();
		
		$threshold = new \Datetime('now');
		$threshold->sub(new \DateInterval($this->logDuration));
		
		$repository = $em->getRepository('MirrorCardBundle:Logins');
		$query = $repository->createQueryBuilder('l')
			->where('l.creationDate > :threshold')
			->setParameter('threshold', $threshold);
		
		if ($user){
			$query = $query->andWhere('l.user = :user')
							->setParameter('user', $user);
		}
		
		$query = $query->orderBy('l.id', 'DESC')
						->getQuery();

		$logins = $query->getResult();
		
		return $logins;
	}
	
	public function getFrequentLocations(Controller $controller, $logins)
	{
		$total = count($logins);
		
		$loginCounts = array();
		foreach ($logins as $login){
			$region = $login->getRegion();
			if (isset($loginCounts[$region])){
				$loginCounts[$region]++;
			}else{
				$loginCounts[$region] = 1;
			}
		}
		
		$frequentLocations = array();
		foreach ($loginCounts as $location => $loginCount){
			if ($loginCount*100/$total>5){
				$frequentLocations[] = $location;
			}
		}
		
		/*arsort($loginCounts);
		$count = 0;
		foreach ($loginCounts as $location => $loginCount){
			if ($count++<$this->frequentLocationNum){
				$frequentLocations[] = $location;
			}else{
				break;
			}
		}*/
		
		return $frequentLocations;
	}
	
	// 从数据库获得超时信息
	// 根据session中的ip地址与登录账号进行判断。
	private function getLoginFence(Controller $controller, $credential, $ipAddr)
	{
		$em = $controller->getDoctrine()->getEntityManager();
		
		$repository = $em->getRepository('MirrorCardBundle:LoginFences');
		$query = $repository->createQueryBuilder('lf')
					->where('lf.credential = :credential')
					->andWhere('lf.ipAddr = :ipAddr')
					->setParameter('credential', $credential)
					->setParameter('ipAddr', $ipAddr)
					->getQuery();
		return count($query->getResult())==1?$query->getSingleResult():null;
	}
	
	// 根据预先设置的尝试次数，如果次数超过预先设定的次数(5次)，并且未超过预先设定的时间(15分钟)
	// 那么返回false，验证失败
	public function checkLoginFence(Controller $controller, $credential, $ipAddr)
	{
		
		$em = $controller->getDoctrine()->getEntityManager();
		$loginFence = $this->getLoginFence($controller, $credential, $ipAddr);
		
		if ($loginFence){
			$expireDate = $loginFence->getExpireDate();
			$attempts = $loginFence->getAttempts();
			if ($expireDate->getTimestamp()>time() && $attempts<=0){
				return false;
			}
		}
		
		return true;
	}
	
	public function updateLoginFence(Controller $controller, $credential, $ipAddr)
	{
		$em = $controller->getDoctrine()->getEntityManager();
		$loginFence = $this->getLoginFence($controller, $credential, $ipAddr);
		
		$expireDate = new \Datetime('now');
		$expireDate->add(new \DateInterval($this->blockingDuration));
		
		if ($loginFence){
			if ($loginFence->getExpireDate()->getTimestamp()>time()){
				$loginFence->setAttempts($loginFence->getAttempts()-1);
			}else{
				$loginFence->setExpireDate($expireDate);
				$loginFence->setAttempts(0);
			}
		}else{
			$loginFence = new LoginFences();
			$loginFence->setCredential($credential);
			$loginFence->setIpAddr($ipAddr);
			$loginFence->setAttempts($this->maxLoginAttempts-1);
			$loginFence->setExpireDate($expireDate);
			$loginFence->setCreationDate(new \DateTime('now'));
			$loginFence->setStatus(0);
			$em->persist($loginFence);
		}
		$em->flush();
	}
	
	public function clearLoginFence(Controller $controller, $credential, $ipAddr)
	{
		$em = $controller->getDoctrine()->getEntityManager();
		$loginFence = $this->getLoginFence($controller, $credential, $ipAddr);
		
		if ($loginFence){
			$em->remove($loginFence);
			$em->flush();
		}
	}
}
