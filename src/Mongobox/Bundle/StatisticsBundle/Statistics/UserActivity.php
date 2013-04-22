<?php

namespace Mongobox\Bundle\StatisticsBundle\Statistics;

use Doctrine\ORM\EntityManager;
use Mongobox\Bundle\StatisticsBundle\Entity\User\Activity;
use Mongobox\Bundle\UsersBundle\Entity\User;

class UserActivity
{
    const LAST_HEARTBEAT = 1;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     * @return void
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Updates the date of last activity for the current user
     *
     * @param User $user
     * @return void
     */
    public function updateLastHeartBeat(User $user)
    {
        $userActivity = $this->em
            ->getRepository('MongoboxStatisticsBundle:User\Activity')
            ->findOneBy(array(
                'user'      => $user,
                'typeId'    => self::LAST_HEARTBEAT
            ))
        ;

        if ($userActivity === null) {
            $userActivity = new Activity();
            $userActivity->setUser($user);
            $userActivity->setTypeId(self::LAST_HEARTBEAT);
        }

        $userActivity->setDate(new \DateTime());

        $this->em->persist($userActivity);
		$this->em->flush();
    }
}
