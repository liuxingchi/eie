<?php

namespace Ydzy\UserBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class CreateUser
{
	public function create($username, $password, $email, $phone, $active, $superadmin, $Nickname)
    {
        $userManager = $this->container->get('fos_user.util.user_manipulator');
        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setPlainPassword($password);
        $user->setEnabled((Boolean) $active);
        $user->setSuperAdmin((Boolean) $superadmin);
        $user->setNickname($Nickname);
        $userManager->updateUser($user);
        return $user;
    }
}


