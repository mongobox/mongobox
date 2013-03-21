<?php

namespace Mongobox\Bundle\StatisticsBundle\Statistics\Tumblr;

use Mongobox\Bundle\TumblrBundle\Entity\Repository\TumblrRepository;

class Users
{
    /**
     * Tumblr repository
     *
     * @var \Mongobox\Bundle\TumblrBundle\Entity\Repository\TumblrRepository
     */
    protected $_repository;

    /**
     * Users tags
     *
     * @var array
     */
    protected $_usersTags;

    /**
     * Constructor
     *
     * @param \Mongobox\Bundle\TumblrBundle\Entity\Repository\TumblrRepository $tumblrRepository
     * @param array $usersTags
     */
    public function __construct(TumblrRepository $tumblrRepository, array $usersTags)
    {
        $this->_repository  = $tumblrRepository;
        $this->_usersTags   = $usersTags;
    }

    /**
     * Retrieve series data
     *
     * @return array|bool
     */
    public function getSeries()
    {
        $results = $this->_repository->findAll();
        if (!$nbResults = count($results)) {
            return false;
        }

        $series = array_combine($this->_usersTags, array_pad(array(), count($this->_usersTags), 0));
        foreach ($results as $_item) {
            foreach ($this->_usersTags as $_user) {
                if (substr_count($_item->getText(), $_user)) {
                    $series[$_user]++;
                }
            }
        }

        return array(
            'xAxis' => array_keys($series),
            'data'  => array_values($series)
        );
    }
}
