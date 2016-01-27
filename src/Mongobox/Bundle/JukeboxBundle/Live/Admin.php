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
        $this->container = $container;
        $this->em = $entityManager;
    }

    /**
     * Check if the user is currently the administrator of the live,
     * but if no administrator is defined then this user will get the administrator rights.
     *
     * @return boolean
     */
    public function isCurrentAdmin()
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('ROLE_USER')) {
            $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();
        } else {
            return false;
        }

        $session = $this->container->get('session');

        $groupRepository = $this->em->getRepository('MongoboxGroupBundle:Group');
        $groupEntity = $groupRepository->find($session->get('id_group'));

        if ($groupEntity->getLiveCurrentAdmin() === null) {
            $groupEntity->setLiveCurrentAdmin($currentUser);
            $this->em->flush($groupEntity);
        }

        return ($groupEntity->getLiveCurrentAdmin() === $currentUser);
    }

    /**
     * Retrieve the live current administrator
     *
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function getCurrentAdmin()
    {
        $session = $this->container->get('session');

        $groupRepository = $this->em->getRepository('MongoboxGroupBundle:Group');
        $groupEntity = $groupRepository->find($session->get('id_group'));

        return $groupEntity->getLiveCurrentAdmin();
    }

    /**
     * Initialize the jukebox playlist
     *
     * @param Group $group
     *
     * @return Playlist object
     */
    public function initializePlaylist(Group $group)
    {
        $this->em->getRepository('MongoboxJukeboxBundle:Playlist')->generate($group);

        $currentPlaylist = $this->em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(
            array(
                'group'   => $group->getId(),
                'current' => 1
            )
        );

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

    /**
     * Switch or not (depending of the given status) the administrator of the live
     *
     * @param integer $userId
     * @param boolean $status
     *
     * @return boolean
     */
    public function switchAdmin($userId, $status)
    {
        try {
            $session = $this->container->get('session');

            $userRepository = $this->em->getRepository('MongoboxUsersBundle:User');
            if (!$userEntity = $userRepository->find($userId)) {
                return false;
            }

            $groupRepository = $this->em->getRepository('MongoboxGroupBundle:Group');
            if (!$groupEntity = $groupRepository->find($session->get('id_group'))) {
                return false;
            }

            if ($status === true) {
                $groupEntity->setLiveCurrentAdmin($userEntity);
                $this->em->flush($groupEntity);
            }

            $conditions = array(
                'group'    => $groupEntity,
                'user'     => $userEntity,
                'response' => null
            );

            $putschRepository = $this->em->getRepository('MongoboxJukeboxBundle:Putsch');
            if (!$putschEntity = $putschRepository->findOneBy($conditions)) {
                return false;
            }

            $putschEntity->setResponse($status);
            $this->em->flush($putschEntity);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
