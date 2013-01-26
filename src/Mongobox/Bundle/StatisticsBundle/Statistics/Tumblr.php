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
        $timeline = $this->getTimeline();

        return array(
            'timeline'  => $timeline
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
            'Roxane'
        );
    }

    protected function getTumblrRepository()
    {
        return $this->documentManager->getRepository('MongoboxTumblrBundle:Tumblr');
    }

    protected function initializeTimelineReturn(\DateTime $startDate)
    {
        $pointStart = new \DateTime($startDate->format('Y-m-d'));

        $currentDate    = new \DateTime();
        $nbDays         = $currentDate->diff($startDate)->days;

        $defaultValues = array();
        for ($i = 0; $i <= $nbDays; $i++) {
            $defaultValues[$startDate->format('d/m/Y')] = 0;
            $startDate->modify('1 day');
        }

        return array(
            'pointInterval' => 24 * 3600 * 1000,
            'pointStart'    => $pointStart,
            'data'          => $defaultValues
        );
    }

    protected function getTimeline()
    {
        $results = $this->getTumblrRepository()->findAll();
        if (!$nbResults = count($results)) {
             return false;
        }

        foreach ($results as $_item) {
            if (!isset($series)) {
                $series = $this->initializeTimelineReturn($_item->getDate());
            }

            $date = $_item->getDate()->format('d/m/Y');
            if (isset($series['data'][$date])) {
                $series['data'][$date]++;
            }
        }

        $series['data'] = array_values($series['data']);

        return $series;
    }
}
