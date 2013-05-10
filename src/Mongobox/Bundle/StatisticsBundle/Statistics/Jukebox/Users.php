<?php

namespace Mongobox\Bundle\StatisticsBundle\Statistics\Jukebox;

use Mongobox\Bundle\JukeboxBundle\Entity\Repository\VideosRepository;

class Users
{
    /**
     * @var \Mongobox\Bundle\TumblrBundle\Entity\Repository\TumblrRepository
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

        if ($results = $this->_repository->getMaxCountPerUser()) {
            foreach ($results as $_item) {
                $series[$_item['firstname'] . ' ' . $_item['lastname']] = (int) $_item['nb_videos'];
            }
        }

        return array(
            'xAxis' => array_keys($series),
            'data'  => array_values($series)
        );
    }
}
