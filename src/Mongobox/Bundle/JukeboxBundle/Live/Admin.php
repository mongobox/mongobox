<?php

namespace Mongobox\Bundle\JukeboxBundle\Live;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Admin
{
    private $container;
    private $em;

    /**
     * Constructor
     */
    public function __construct(ContainerInterface $container, EntityManager $entityManager)
    {
        $this->container    = $container;
        $this->em           = $entityManager;
    }

    /**
     * Check if the user is currently the administrator of the live,
     * but if no administrator is defined then this user will get the administrator rights.
     *
     * @return boolean
     */
    public function isCurrentAdmin()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $currentUser = $securityContext->getToken()->getUser();
        } else {
            return false;
        }

        $session = $this->container->get('session');

        $groupRepository    = $this->em->getRepository('MongoboxGroupBundle:Group');
        $groupEntity        = $groupRepository->find($session->get('id_group'));

        if ($groupEntity->getLiveCurrentAdmin() === null) {
            $groupEntity->setLiveCurrentAdmin($currentUser);
            $this->em->flush($groupEntity);
        }

        return ($groupEntity->getLiveCurrentAdmin() === $currentUser);
    }
}
