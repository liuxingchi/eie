<?php

namespace Ydzy\ApiBundle\Service;

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
			/*if ($this->json[$param]=='' && $this->json[$param]!=$default){
				return $default;
			}else{
				if (is_array($this->json[$param])){
					return $this->json[$param];
				}else{
					return stripslashes($this->json[$param]);
				}
			}*/
			return $this->json[$param];
		}else{
			return $default;
		}
	}

}