<?php

namespace Mongobox\Bundle\JukeboxBundle\Live;

use Doctrine\ORM\EntityManager;
use Mongobox\Bundle\GroupBundle\Entity\Group;
use Mongobox\Bundle\JukeboxBundle\Entity\Playlist;
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

    /**
     * Initialize the jukebox playlist
     *
     * @param Group $group
     * @return Playlist object
     */
    public function initializePlaylist(Group $group)
    {
        $this->em->getRepository('MongoboxJukeboxBundle:Playlist')->generate($group);

        $currentPlaylist = $this->em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(array(
            'group'     => $group->getId(),
            'current'   => 1
        ));

        if (!is_null($currentPlaylist)) {
            $votes = $this->em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($currentPlaylist);

            $currentPlaylist->getVideoGroup()->setVotes($currentPlaylist->getVideoGroup()->getVotes() + $votes);
            $currentPlaylist->getVideoGroup()->setDiffusion($currentPlaylist->getVideoGroup()->getDiffusion() + 1);

            $this->em->getRepository('MongoboxJukeboxBundle:Vote')->wipe($currentPlaylist);
            $this->em->getRepository('MongoboxJukeboxBundle:Volume')->wipe($currentPlaylist);
            $this->em->remove($currentPlaylist);
        }

        $nextInPlaylist = $this->em->getRepository('MongoboxJukeboxBundle:Playlist')->next(1, $group);

        if (is_object($nextInPlaylist)) {
            $nextInPlaylist->setCurrent(1);
            $nextInPlaylist->getVideoGroup()->setLastBroadcast(new \Datetime());
        }

        $this->em->flush();

        return $nextInPlaylist;
    }
}
