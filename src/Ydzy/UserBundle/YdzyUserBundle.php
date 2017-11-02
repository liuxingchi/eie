<?php

namespace Ydzy\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class YdzyUserBundle extends Bundle
{
	public function getParent()
    {
        return 'FOSUserBundle';
    }
}
