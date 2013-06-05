<?php

namespace Mongobox\Bundle\BookmarkBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Mongobox\Bundle\BookmarkBundle\Entity\ListeFavoris;
use Mongobox\Bundle\UsersBundle\Entity\User;

/**
 * Description of ListeFavorisRepository
 *
 * @author pierremarseille
 */
class ListeFavorisRepository extends EntityRepository
{
	/**
	 * Fonction pour récupérer les listes de favoris pour une vidéo d'un user
	 * @param User $user
	 * @param int $video
	 */
	public function getListesUserForOneVideo($user, $id_video)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb
			->select('l')
			->from('MongoboxBookmarkBundle:ListeFavoris', 'l')
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

	/**
	 * Fonction pour récupérer des listes de favoris en JSON
	 * @param string $value
	 * @return array
	 */
	public function findList($user, $value)
	{
		$em = $this->getEntityManager();
		$qb = $em->createQueryBuilder();

		$qb->select('l')
			->from('MongoboxBookmarkBundle:ListeFavoris', 'l')
			->where("l.name LIKE :value")
			->andWhere('l.user = :user')
			->orderBy('l.name', 'ASC')
			->setMaxResults(10)
			->setParameters( array(
				'value' => '%'.$value.'%',
				'user' => $user
			));

		$query = $qb->getQuery();
		return $query->getResult();
	}

	/**
	 * Fonction pour récupérer la liste des vidéos en favoris dans une liste
	 * @param ListeFavoris $list
	 * @param User $user
	 * @return array
	 */
	public function getBookmarkFromList(ListeFavoris $list, User $user)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb
			->select('v as video', 'uf.date_favoris as date')
			->from('MongoboxJukeboxBundle:Videos', 'v')
			->innerJoin('v.favoris', 'uf')
			->where('uf.liste = :list')
			->andWhere('uf.user = :user')
			->andWhere('uf.video IS NOT NULL')
			->setParameters(array(
				'list' => $list,
				'user' => $user
			))
		;
		return $qb->getQuery()->getResult();
	}

	/**
	 * Fonction pur récupérer les listes avec les vidéos en favoris
	 * @param User $user
	 */
	public function getListsAndVideos(User $user)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb
			->select('l as object, f as user_favoris, v as video')
			->from('MongoboxBookmarkBundle:ListeFavoris', 'l')
			->innerJoin('l.favoris', 'f')
			->innerJoin('f.video', 'v')
			->where('l.user = :user')
			->setParameters(array(
				"user" => $user
			))
		;

		return $qb->getQuery()->getResult();
	}
}

?>
