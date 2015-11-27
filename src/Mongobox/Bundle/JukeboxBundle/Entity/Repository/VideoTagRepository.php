<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TagsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VideoTagRepository extends EntityRepository
{
	
	/**
	 * Function pour récupérer les mots clés pour l'autocomplétion
	 * @param string $value
	 * @return array
	 */
	public function getTags($value)
	{
		$em = $this->getEntityManager();
		$qb = $em->createQueryBuilder();
		$qb->select('vt')
			->from('MongoboxJukeboxBundle:VideoTag', 'vt')
			->where("vt.name LIKE :tag")
			->orderBy('vt.name', 'ASC')
			->setParameter('tag', $value.'%')
            ->setMaxResults(10)
		;
			
		$query = $qb->getQuery();
		$tags = $query->getResult();
			
		$json = array();
		foreach ($tags as $mot) {
			$json[] = array(
					'id' => $mot->getId(),
					'label' => $mot->getName(),
					'value' => $mot->getName()
			);
		}
	
		return $json;
	}

	/**
	 * Function pour récupérer les mots clés pour une vidéo
	 * @param string $value
	 * @return array
	 */
	public function getVideoTags($video)
	{
		$em = $this->getEntityManager();
		$qb = $em->createQueryBuilder();
		$qb->select('vt')
			->from('MongoboxJukeboxBundle:VideoTag', 'vt')
			->leftJoin('vt.videos', 'v')
			->where("v = :video")
			->setParameter('video', $video)
		;
			
		$query = $qb->getQuery();
		$result = $query->getResult();

		return $result;
	}

	/**
     * Funtion to load TumblrTab by name
     *
     * @param string $tag
     * @return boolean
     */
    public function loadOneTagByName($tag)
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT vt.id, vt.name FROM MongoboxJukeboxBundle:VideoTag vt WHERE vt.name LIKE :tag')
            ->setParameter('tag', $tag);

        try
        {
            $result = $query->getSingleResult();
            return $result;

        } catch (\Doctrine\ORM\NoResultException $e)
        {
            return false;
        }

    }
	
    /**
     * Funtion to load all tags used on videos for a group
     *
     * @param string $group
     * @return tag
     */
	public function getTagsForGroup($group)
	{
		$em = $this->getEntityManager();
		$query = $em->createQuery('SELECT vt, COUNT(vt.id) as total
			FROM MongoboxJukeboxBundle:VideoTag vt
			LEFT JOIN vt.videos v
			LEFT JOIN v.video_groups vg
			WHERE vg.group = :group
			GROUP BY vt.id
			ORDER BY total DESC')
			->setParameters(array('group' => $group))
		;
			
		return $query->getResult();
	}
}
