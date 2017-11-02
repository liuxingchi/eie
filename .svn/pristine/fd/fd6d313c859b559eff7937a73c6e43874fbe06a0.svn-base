<?php
namespace Acme\DemoBundle\Security\Authorization\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
class Voter implements VoterInterface
{
    const VIEW = "view";
    const Edit = "edit";
    const DELETE = "delete";
	/* (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\Authorization\Voter\VoterInterface::supportsAttribute()
     */
    public function supportsAttribute ($attribute)
    {
        return in_array($attribute, array(self::VIEW, self::Edit, self::DELETE));
    }

	/* (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\Authorization\Voter\VoterInterface::supportsClass()
     */
    public function supportsClass ($class)
    {
        $supportedClass = "";
    }

	/* (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\Authorization\Voter\VoterInterface::vote()
     */
    public function vote (
            \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token, 
            $object, array $attributes)
    {
        // TODO Auto-generated method stub
        
    }

    
}