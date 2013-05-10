<?php

namespace Mongobox\Bundle\StatisticsBundle\Statistics;

class Tumblr
{
    /**
     * Constructor
     */
    public function __construct(\Doctrine\ORM\EntityManager $dm)
    {
        $this->documentManager  = $dm;
    }

    public function getStatistics()
    {
        $usersTags          = $this->getUsersTags();
        $tumblrRepository   = $this->getTumblrRepository();

        $timelineChart      = new Tumblr\Timeline($tumblrRepository);
        $usersRankingChart  = new Tumblr\Users($tumblrRepository, $usersTags);

        return array(
            'timeline'      => $timelineChart->getSeries(),
            'usersRanking'  => $usersRankingChart->getSeries()
        );
    }

    protected function getUsersTags()
    {
        return array(
            'Alex',
            'Aurélien',
            'Charly',
            'Cédric',
            'David',
            'Florian',
            'Jean',
            'Marion',
            'Pierre',
            'Roxane',
			'Jérôme'
        );
    }

    protected function getTumblrRepository()
    {
        return $this->documentManager->getRepository('MongoboxTumblrBundle:Tumblr');
    }
}
