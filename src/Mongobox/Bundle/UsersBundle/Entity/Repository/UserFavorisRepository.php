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
}

?>
