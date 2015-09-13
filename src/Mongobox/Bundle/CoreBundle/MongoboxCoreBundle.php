<?php

namespace Mongobox\Bundle\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MongoboxCoreBundle extends Bundle
{
    public function getParent()
    {
        return 'SonataAdminBundle';
    }
}
