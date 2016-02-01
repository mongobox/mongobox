<?php

namespace Mongobox\Bundle\UsersBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MongoboxUsersBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
