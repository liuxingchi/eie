<?php

namespace Ydzy\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        //$grade = $this->get('login')->returnGrade($request);
        return new Response("EIE API");
    }
    public function updateSystemAction(Request $request){
        $json = $this->get('json_parser')->parse($request);
        $list = $json->get('list','');
        $list2 = $json->get('list2','');
        
        $this->get('my_datebase')->connection();
        
        $array = explode(",", $list);
        mysql_query("update system set value=0");
        foreach($array as $rs){
            mysql_query("update system set value=1 where id = $rs");
        }
        
        $array2 = json_decode($list2);
        foreach($array2 as $key=>$rs){
            mysql_query("update system set value=$rs where id =$key");
        }
        return new Response('',200);
        
    }
    public function systemAction()
    {
        $this->get('my_datebase')->connection();
        $sql = "select * from system";
        $result = mysql_query($sql);
        while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
    
            $arrayList[] = $row;
        }
        return new JsonResponse($arrayList);
    }
}
