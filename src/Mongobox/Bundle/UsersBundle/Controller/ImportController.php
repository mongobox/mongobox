<?php

namespace Mongobox\Bundle\UsersBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ImportController extends Controller
{
	/**
	 * Fonction d'affichage de l'import
	 * @Route("/favoris/import", name="import_favoris_index_action")
	 * @Template()
	 */
	public function indexAction()
	{
		return array();
	}
}
