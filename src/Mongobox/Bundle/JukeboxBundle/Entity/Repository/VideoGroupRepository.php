<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * VideoGroupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VideoGroupRepository extends EntityRepository
{
	public function findLast($maxResults, $group)
	{
		$em = $this->getEntityManager();
		$qb = $em->createQueryBuilder();

		$qb->select('vg')
		->from('MongoboxJukeboxBundle:VideoGroup', 'vg')
		->where("vg.group = :group")
		->orderBy('vg.lastBroadcast', 'DESC')
		->setMaxResults($maxResults)
		->setParameters( array(
				'group' => $group
		));

		$query = $qb->getQuery();
		return $query->getResult();
	}

    public function random($group)
    {
		$em = $this->getEntityManager();
		$qb = $em->createQueryBuilder();

        $date = new \Datetime();
        $day = $date->format('w');
        if($day != 5) $vendredi = 0;
        else $vendredi = 1;

		$qb->select('vg')
		->from('MongoboxJukeboxBundle:VideoGroup', 'vg')
		->leftJoin('vg.playlist', 'p')
		->where("vg.vendredi = :vendredi")
		->andWhere("vg.group = :group")
		->andWhere("(vg.lastBroadcast < :today OR vg.lastBroadcast IS NULL)")
		->groupBy('vg.id')
		->setParameters( array(
				'group' => $group,
				'vendredi' => $vendredi,
				'today' => new \Datetime('today')
		));

		/* TODO */
		//$qb->andWhere('(DATE(vg.last_broadcast) < DATE(NOW()) OR vg.last_broadcast IS NULL')
		
		$query = $qb->getQuery();
		$results = $query->getResult();
		//var_dump($results);
        if (count($results) > 0)
		{
            $songs = array();
            foreach ($results as $song) {
                $songs[] = $song->getDiffusion() - $song->getVotes();
            }
            $max = max($songs);

            $songs = array();
            foreach ($results as $song) {
                $value = $max - ($song->getDiffusion() - $song->getVotes()) + 1;
                $songs[$song->getId()] = $value;
            }

            $rand = $this->getRandomWeightedElement($songs);
            $video = $em->getRepository('MongoboxJukeboxBundle:VideoGroup')->find($rand);

            return $video;
        } else return null;
    }

    /**
    * getRandomWeightedElement()
    * Utility function for getting random values with weighting.
    * Pass in an associative array, such as array('A'=>5, 'B'=>45, 'C'=>50)
    * An array like this means that "A" has a 5% chance of being selected, "B" 45%, and "C" 50%.
    * The return value is the array key, A, B, or C in this case.  Note that the values assigned
    * do not have to be percentages.  The values are simply relative to each other.  If one value
    * weight was 2, and the other weight of 1, the value with the weight of 2 has about a 66%
    * chance of being selected.  Also note that weights should be integers.
    *
    * @param array $weightedValues
    */
    public function getRandomWeightedElement(array $weightedValues)
    {
        $rand = mt_rand(1, (int) array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
    }
}