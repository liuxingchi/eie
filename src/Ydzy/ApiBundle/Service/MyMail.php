<?php
namespace Ydzy\ApiBundle\Service;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\SwiftmailerBundle;
class MyMail
{
  public function sendEmail($subject,$emails_to,$body)
    {
        $message = \Swift_Message::newInstance()
        ->setSubject($subject)
        ->setFrom('1397030474@qq.com')
        ->setTo($emails_to)
        ->setBody($body, 'text/html');
        $this->mailer->send($message);
        //$this->container->get('mailer')->send($message); 
    }
    
}