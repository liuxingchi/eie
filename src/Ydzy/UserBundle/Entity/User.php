<?php
// src/Ydzy/UserBundle/Entity/User.php

namespace Ydzy\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\IntegerType;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var integer $roleid
	 *
	 * @ORM\Column(name="roleid", type="integer")
	 */
	protected $roleid;
	
	
	/**
	 * @var string $summary
	 *
	 * @ORM\Column(name="summary", type="string",nullable=true,length=255)
	 */
	protected $summary;


	public function __construct()
	{
		parent::__construct();
		// your own logic
	}

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
   

    /**
     * Set summary
     *
     * @param string $summary
     * @return User
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    
        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }
    
    
    /**
     * Get roleid
     *
     * @return integer
     */
    public function getRoleid()
    {
        return $this->roleid;
    }
    
    /**
     * Set roleid
     *
     * @param integer $roleid
     * @return User
     */
    public function setRoleid($roleid)
    {
        $this->roleid = $roleid;
    
        return $this;
    }
}