<?php

namespace Mongobox\Bundle\CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Mongobox\Bundle\CoreBundle\Entity\Feed;

/**
 * FeedItemRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FeedItemRepository extends EntityRepository
{
    /**
     * Delete old items
     *
     * @param Feed $feed
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function cleanFeed(Feed $feed)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $limit = $feed->getMaxItems();

        // Get Last Items
        $qb->select('f.id')
            ->from('MongoboxCoreBundle:FeedItem', 'f')
            ->where('f.feed = :feed')
            ->orderBy('f.pubDate', 'DESC')
            ->setMaxResults($limit)
            ->setParameters(array('feed' => $feed))
            ->getQuery();

        $query = $qb->getQuery();

        if ($limit == 1) {
            $result = $query->getOneOrNullResult();
        } else {
            $result = $query->getResult();
        }

        // Delete others items
        $qb = $em->createQueryBuilder();
        $qb
            ->delete()
            ->from('MongoboxCoreBundle:FeedItem', 'f')
            ->where('f.feed = :feed')
            ->andWhere('f.id NOT IN (:items)')
            ->setParameters(
                array(
                    'feed'  => $feed,
                    'items' => $result
                )
            );

        $query = $qb->getQuery();

        return $query->getResult();
    }

}
