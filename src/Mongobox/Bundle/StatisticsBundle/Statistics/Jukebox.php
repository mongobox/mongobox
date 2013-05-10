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

        $timelineChart = new Jukebox\Timeline($repository);

        return array(
            'timeline' => $timelineChart->getSeries()
        );
    }
}
