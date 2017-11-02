<?php

namespace Ydzy\ApiBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class VpnDatebase
{
	public function connection()
   {
	//$con = mysql_connect("172.27.6.103:3306","caodong","homelink");
	$con = mysql_connect("localhost","root","ydzy2wsx");
	if (!$con)
	  {
	  die('Could not connect: ' . mysql_error());
	  }
	
	mysql_select_db('vpn', $con);
   }

}
