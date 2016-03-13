<?php

namespace Mongobox\Bundle\CoreBundle\Command;

use Mongobox\Bundle\CoreBundle\Entity\FeedItem;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

// Logger
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;

// Handler
use Mongobox\Bundle\JukeboxBundle\Command\Monolog\Handler\CliHandler;

class RssImportCommand extends ContainerAwareCommand
{
    const TUMBLR_DIR = 'archives';

    protected $_baseUrl;
    protected $_debug;
    protected $_logger;

    protected $doctine;
    protected $entityManager;

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('core:rss:import')
            ->setDescription('Import RSS Feed')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Show debug logs.');
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->_debug = $input->getOption('debug');

        $this->input = $input;
        $this->output = $output;

        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->entityManager = $this->doctrine->getManager('default');

        // Logger
        $this->_logger = $this->getContainer()->get('logger');
        if ($this->_debug === true) {
            $this->_logger->pushProcessor(new MemoryPeakUsageProcessor());
            $this->_logger->pushProcessor(new MemoryUsageProcessor());
            $this->_logger->pushHandler(new CliHandler($this->output));
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_logger->addInfo('RSS Feed import - START');

        $rssFeeds = $this->entityManager->getRepository('MongoboxCoreBundle:Feed')->findAll();
        $nbFeeds = count($rssFeeds);

        // Initialize reporting
        $reports = array(
            'total'   => $nbFeeds,
            'success' => 0,
            'skipped' => 0
        );

        $i = 0;
        foreach ($rssFeeds as $feed) {
            $i++;

            $feedItems = $this->getFeedData(
                $feed->getUrl(),
                $feed->getMaxItems()
            );
            if (empty($feedItems)) {
                $this->_logger->addDebug(
                    'Empty RSS Feed ',
                    array('id_feed' => $feed->getId(), 'url' => $feed->getUrl())
                );

                $reports['skipped']++;

                continue;
            } else {
                $this->importFeedItems($feed, $feedItems);

                $this->_logger->addDebug(
                    'RSS Feed successfully imported.',
                    array('id_feed' => $feed->getId(), 'url' => $feed->getUrl())
                );

                $reports['success']++;
            }

            $this->cleanFeedItems($feed);
        }

        $reports['failed'] = $reports['total'] - $reports['success'] - $reports['skipped'];

        $this->_logger->addInfo('RSS Feed import - END', $reports);
    }

    private function importFeedItems($feed, $feedItems)
    {
        foreach ($feedItems as $item) {

            $title = (string) $item->title;
            $link = (string) $item->link;
            $datePub = new \DateTime((string) $item->pubDate);

            $feedItem = $this->entityManager->getRepository('MongoboxCoreBundle:FeedItem')->findOneBy(
                array('pubDate' => $datePub, 'feed' => $feed)
            );

            if (empty($feedItem)) {
                $feedItem = new FeedItem();
            }

            $feedItem
                ->setTitle($title)
                ->setLink($link)
                ->setFeed($feed)
                ->setPubDate($datePub);

            if ($description = (string) $item->description) {
                $feedItem->setDescription($description);
            }

            $this->entityManager->persist($feedItem);
        }
        $this->entityManager->flush();
    }

    private function cleanFeedItems($feed)
    {
        $repository = $this->entityManager->getRepository('MongoboxCoreBundle:FeedItem');
        $repository->cleanFeed($feed);
    }

    /**
     * Parse RSS feed
     *
     * @param $url
     * @param bool $filter_type
     * @param bool $filter
     * @param int $limit
     *
     * @return array
     */
    private function getFeedData($url, $limit = 6)
    {
        $feed = simplexml_load_file($url);
        $results = array();
        $i = 0;

        if (false !== $feed) {
            foreach ($feed->channel->item as $article) {
                $results[] = $article;

                $i++;
                if ($i == $limit) {
                    break;
                }
            }
        }

        return $results;
    }
}
