<?php
namespace Ydzy\AdminBundle\Security\Authorization\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Httpful\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
class AccessVoter extends Controller implements VoterInterface 
{
    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }
    
    public function supportsAttribute ($attribute)
    {
       return true;
    }

    public function supportsClass ($class)
    {
        return TRUE;
    }

    public function vote(TokenInterface $token, $object, array $attributes) {
        //echo "dddd";
        $url = $_SERVER['REQUEST_URI'];
        if(strpos($url, "admin")){
            $curUri = explode("admin", $url);
        }else{
            $curUri[1] = '';
        }
        if (strpos($curUri[1], "?")) {
            $curUris = explode("?", $curUri[1]);
            $needUri = $curUris[0];
        }else{
            $needUri = $curUri[1];
        }
        //echo $needUri;
        $noAuthArr = array("","/","/menu","/welcome","/noaccess");
        if (in_array($needUri, $noAuthArr)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }
        $user = $token->getUser();
        $roleId = $user->getRoleid();
        $this->dbConnection->connection();
        $sql = "select b.URL as url,b.URL2 as url2,b.URL3 as url3,URL4 as url4 from roleresource as a left join resources as b on a.res_id = b.id where a.role_id = $roleId";
        $result = mysql_query($sql);
        $array_result = array();
        while($rs = mysql_fetch_array($result,MYSQL_ASSOC)){
            //echo $rs['url'];
            $array_result[] = $rs['url'];
            $array_result[]= $rs['url2'];
            $array_result[]= $rs['url3'];
            $array_result[]= $rs['url4'];
            //array_push($array_result, );
        }
        //echo "<pre>";
        $array_result=array_filter($array_result);
        //print_r($array_result);
        
       //echo $needUri;
        $needUri_arr = explode("/", $needUri);
        if($needUri_arr[1]){
            $needUri = "/".$needUri_arr[1];
        }else{
            $needUri = "/";
        } 
        //echo $needUri;
        if (!in_array(substr($needUri,1), $array_result)) {
            //echo "notin";
            //return $this->render('YdzyAdminBundle:Default:welcome.html.twig');
            header("Location: /app_dev.php/admin/noaccess");
            //return new RedirectResponse($this->generateUrl('ydzy_admin_welcome'));
            //return VoterInterface::ACCESS_DENIED;
        }
        return VoterInterface::ACCESS_ABSTAIN;
       
    } 
}