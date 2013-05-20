<?php

namespace Mongobox\Bundle\StatisticsBundle\Statistics\Jukebox;

use Mongobox\Bundle\JukeboxBundle\Entity\Repository\VideosRepository;

class UsersAvgVote
{
    /**
     * @var \Mongobox\Bundle\JukeboxBundle\Entity\Repository\JukeboxRepository
     */
    protected $_repository;

    /**
     * Constructor
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Repository\VideosRepository $repository
     */
    public function __construct(VideosRepository $repository)
    {
        $this->_repository = $repository;
    }

    /**
     * Retrieve series data
     *
     * @return array
     */
    public function getSeries()
    {
        $series = array();

        if ($results = $this->_repository->getMaxAvgVotePerUser()) {
            foreach ($results as $_item) {
                $series[$_item['firstname'] . ' ' . $_item['lastname']] = (float)number_format($_item['avg_votes'], 2, '.', '');
            }
        }

        return array(
            'xAxis' => array_keys($series),
            'data'  => array_values($series)
        );
    }
}
