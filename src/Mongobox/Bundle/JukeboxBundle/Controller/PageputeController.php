<?php
namespace Mongobox\Bundle\JukeboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Page pute controller.
 *
 * @Route("/page-pute")
 */
class PageputeController extends Controller
{

    protected $feedUrl = 'http://www.brain-magazine.com/rss.php';

    protected function _getFeedData()
    {
        $data = simplexml_load_file( $this->feedUrl );

        //var_dump('<pre>', $data );exit;
        return $data;
    }

    /**
     * Get feed from http://www.brain-magazine.com/.
     *
     * @Route("/", name="page_pute")
     * @Template()
     */
    public function indexAction()
    {
        $results = array();
        $xml = $this->_getFeedData();

        foreach ($xml->channel->item as $article) {
            if ($article->category == 'Page Pute') {
                $results[] = $article;
            }
        }

        return array(
            'title' => $xml->channel->title,
            'entities' => $results,
        );
    }
}
