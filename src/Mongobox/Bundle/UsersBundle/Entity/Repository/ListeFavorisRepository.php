<?php

namespace Mongobox\Bundle\UsersBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Description of ListeFavorisRepository
 *
 * @author pierremarseille
 */
class ListeFavorisRepository extends EntityRepository
{
	/**
	 * Fonction pour récupérer les listes de favoris pour une vidéo d'un user
	 * @param Mongobox\Bundle\UsersBundle\Entity\User $user
	 * @param int $video
	 */
	public function getListesUserForOneVideo($user, $id_video)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb
			->select('l')
			->from('MongoboxUsersBundle:ListeFavoris', 'l')
			->innerJoin('l.favoris', 'uf')
			->where('l.user = :user')
			->andWhere('uf.video = :video')
			->setParameters(array(
				'user' => $user,
				'video' => $this->getEntityManager()->getReference('MongoboxJukeboxBundle:Videos', $id_video)
			))
		;
		return $qb->getQuery()->getArrayResult();
	}
}

?>
