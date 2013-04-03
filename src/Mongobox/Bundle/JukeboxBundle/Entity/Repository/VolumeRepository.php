<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity\Repository;
use Doctrine\ORM\EntityRepository;

class VolumeRepository extends EntityRepository
{
    public function wipe($playlist)
    {
        $em = $this->getEntityManager();
        $query = $em
            ->createQuery('DELETE FROM MongoboxJukeboxBundle:Volume v WHERE v.playlist = :playlist' )
            ->setParameter('playlist', $playlist)
        ;

        return $query->getResult();
    }
}
