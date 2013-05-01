<?php

namespace Mongobox\Bundle\UsersBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Description of UserFavorisRepository
 *
 * @author pierremarseille
 */
class UserFavorisRepository extends EntityRepository
{
	/**
	 * Fonction pour connaitre si l'utilisateur a déjà ajouté cette vidéo à ses favoris
	 */
	public function checkUserFavorite($id_video, $user)
	{
		$params = array(
			'user' => $user,
			'video' => $this->getEntityManager()->getReference('MongoboxJukeboxBundle:Videos', $id_video)
		);
		$query = $this->getEntityManager()->createQueryBuilder()
				->select('count(uf.id)')
				->from('MongoboxUsersBundle:UserFavoris', 'uf')
				->where('uf.user = :user')
				->andWhere('uf.video = :video')
				->setParameters($params)
				->getQuery()
		;

		// Catch l'exception pour gérer si l'utilisateur n'a pas de contenu
		try
		{
			$result = ( $query->getSingleScalarResult() > 0 ) ? true: false;
		}catch( \Doctrine\Orm\NoResultException $e )
		{
			$result = false;
		}
		return $result;
	}

	/**
	 * Fonction pour récupérer un tableau des vidéos favorites de l'utilisateur
	 * @param Mongobox\Bundle\UsersBundle\Entity\User $user
	 * @param int $page
	 * @return arry
	 */
	public function getUniqueFavorisUser($user, $page, $limitation)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb
			->select('v')
			->from('MongoboxJukeboxBundle:Videos', 'v')
			->innerJoin('v.favoris', 'uf')
			->where('uf.user = :user')
			->setParameters(array(
				'user' => $user
			))
			->groupBy('v.id')
			->setMaxResults($limitation+1)
		;

		if( $page > 0)
		{
			$qb->setFirstResult($page * $limitation);
		}
		return $qb->getQuery()->getArrayResult();
	}

	/**
	 * Fonction pour récupérer la date d'ajout en favoris d'une vidéo à une liste
	 * @param Mongobox\Bundle\UsersBundle\Entity\User $user $user
	 * @param int $id_video
	 * @param int $id_liste
	 * @return array
	 */
	public function getDateAddToList($user, $id_video, $id_liste)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb
			->select('uf.date_favoris')
			->from('MongoboxUsersBundle:UserFavoris', 'uf')
			->where('uf.user = :user')
			->andWhere('uf.liste = :liste')
			->andWhere('uf.video = :video')
			->setParameters(array(
				'user' => $user,
				'video' => $this->getEntityManager()->getReference('MongoboxJukeboxBundle:Videos', $id_video),
				'liste' => $this->getEntityManager()->getReference('MongoboxUsersBundle:ListeFavoris', $id_liste)
			))
		;
		return $qb->getQuery()->getSingleResult();
	}

	/**
	 * Fonction pour récupérer la date d'ajout aux favoris
	 * @param Mongobox\Bundle\UsersBundle\Entity\User $user
	 * @param int $id_video
	 */
	public function getDateAddToFavorite($user, $id_video)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb
			->select('uf.date_favoris')
			->from('MongoboxUsersBundle:UserFavoris', 'uf')
			->where('uf.user = :user')
			->andWhere('uf.liste IS NULL')
			->andWhere('uf.video = :video')
			->setParameters(array(
				'user' => $user,
				'video' => $this->getEntityManager()->getReference('MongoboxJukeboxBundle:Videos', $id_video),
			))
		;
		return $qb->getQuery()->getSingleResult();
	}

	/**
	 * Fonction pour supprimer une vidéo des favoris
	 * @param Mongobox\Bundle\UsersBundle\Entity\User $user
	 * @param int $id_video
	 * @return boolean
	 */
	public function removeVideoFromBookmark($user, $id_video)
	{
		$em = $this->getEntityManager();
		$favoris = $this->findBy(array(
			"user" => $user,
			"video" => $em->getReference("MongoboxJukeboxBundle:Videos", $id_video)
		));
		if( !is_array($favoris) )
			return false;
		foreach($favoris as $fav)
			$em->remove($fav);
		$em->flush();
		return true;
	}

	/**
	 * Fonction pour récupérer le nombre de favoris d'un utilisateur
	 * @param Mongobox\Bundle\UsersBundle\Entity\User $user
	 * @return int
	 */
	public function getNombreFavoris($user)
	{
		$em = $this->getEntityManager();
		$qb = $em->createQueryBuilder();
		$qb
			->select('COUNT(DISTINCT uf.video )')
			->from('MongoboxUsersBundle:UserFavoris', 'uf')
			->where('uf.user = :user')
			->setParameters(array(
				"user" => $user
			))
		;
		return $qb->getQuery()->getSingleScalarResult();
	}

	/**
	 * Fonction pour récupérer le nombre de listes d'un utilisateur
	 * @param Mongobox\Bundle\UsersBundle\Entity\User $user
	 * @return int
	 */
	public function getNombreListes($user)
	{
		$em = $this->getEntityManager();
		$qb = $em->createQueryBuilder();
		$qb
			->select('COUNT(DISTINCT uf.liste )')
			->from('MongoboxUsersBundle:UserFavoris', 'uf')
			->where('uf.user = :user')
			->setParameters(array(
				"user" => $user
			))
		;
		return $qb->getQuery()->getSingleScalarResult();
	}
}

?>
