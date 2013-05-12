<?php

namespace Mongobox\Bundle\StatisticsBundle\Statistics;

class Jukebox
{
    /**
     * Constructor
     */
    public function __construct(\Doctrine\ORM\EntityManager $dm)
    {
        $this->documentManager  = $dm;
    }

    /**
     * Retrieve the jukebox statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        $repository = $this->documentManager->getRepository('MongoboxJukeboxBundle:Videos');

        $timelineChart		= new Jukebox\Timeline($repository);
        $usersChart			= new Jukebox\Users($repository);
		$usersAvgVoteChart	= new Jukebox\UsersAvgVote($repository);

        return array(
            'timeline'      => $timelineChart->getSeries(),
            'usersRanking'  => $usersChart->getSeries(),
			'usersAvgVoteRanking'  => $usersAvgVoteChart->getSeries()
        );
    }
}
