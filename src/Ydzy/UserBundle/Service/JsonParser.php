<?php

namespace Ydzy\UserBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class JsonParser
{
	private $json;
		
	public function parse(Request $request){
		$data = $request->getContent();
		$this->json = json_decode($data, true);
		return $this;
	}
	
	public function get($param, $default = ''){
		if ((isset($this->json[$param]))&&($this->json[$param]!='')){
			return $this->json[$param];
		}else{
			return $default;
		}
	}
}