<?php
namespace Mongobox\Bundle\JukeboxBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// logger
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;

// Handler
use Mongobox\Bundle\JukeboxBundle\Command\Monolog\Handler\CliHandler;

// Google API
use Google_Client;
use Google_Service_YouTube;
use Symfony\Component\Security\Acl\Exception\Exception;

use Mongobox\Bundle\JukeboxBundle\Entity\VideoTag;

/**
 * Class UpdateYoutubeDataCommand
 *
 * @category    Mongobox
 * @package     Mongobox\Bundle\JukeboxBundle\Command
 */
class UpdateYoutubeDataCommand extends ContainerAwareCommand
{
    const YOUTUBE_STATUS_PUBLIC = 'public';
    const YOUTUBE_STATUS_UNLISTED = 'unlisted';

    const UPDATE_LIMIT = 50;

    protected $debug;

    private $em;

    private $videos;
    private $nbVideos;
    private $updatedVideos = 0;
    private $updatedVideosStatus = 0;
    private $tagReplace;

    private $client;
    private $youtube;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('jukebox:updateDataYoutube')
            ->setDescription('Udpate data from Youtube.')
            ->setHelp('');

        $this->getDefinition()->addOptions(
            array(
                new InputOption('debug', null, InputOption::VALUE_NONE, 'Show Message'),
            )
        );
    }

    /**
     * Call before execute
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->debug = $input->getOption('debug');

        $this->googleAppName = $this->getContainer()->getParameter('google_app_name');
        $this->googleDeveloperKey = $this->getContainer()->getParameter('google_developer_key');

        $this->input = $input;
        $this->output = $output;

        // Logger
        $this->logger = $this->getContainer()->get('logger');
        if ($this->debug === true) {
            $this->logger->pushProcessor(new MemoryPeakUsageProcessor());
            $this->logger->pushProcessor(new MemoryUsageProcessor());
            $this->logger->pushHandler(new CliHandler($this->output));
        }

        $this->em = $this->getContainer()->get('doctrine')->getManager('default');
    }

    private function getVideos()
    {

        $this->videos = $this->em->getRepository('MongoboxJukeboxBundle:Videos')->findAll();
        $this->nbVideos = count($this->videos);

        $this->output->writeln('Get videos list: ' . $this->nbVideos);

        $this->tagReplace = $this->em->getRepository('MongoboxJukeboxBundle:VideoTag')
            ->findOneBy(array('system_name' => VideoTag::VIDEO_TAG_REPLACE));
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output->writeln('<bg=cyan;fg=red>Start traitement </>');

        $this->getVideos();

        $this->client = new Google_Client();
        $this->client->setApplicationName($this->googleAppName);
        $this->client->setDeveloperKey($this->googleDeveloperKey);

        $this->youtube = new Google_Service_YouTube($this->client);

        while ($this->updatedVideos < $this->nbVideos) {

            $start = $this->updatedVideos;
            $end = $start + self::UPDATE_LIMIT;
            $this->output->writeln("Videos $start to $end");
            $videos = array_slice($this->videos, $this->updatedVideos, self::UPDATE_LIMIT);
            $this->updateVideos($videos);
        }

        $this->logger->addInfo("Updated videos: {$this->updatedVideos}");
        $this->logger->addInfo("Updated videos status: {$this->updatedVideosStatus}");

        $output->writeln('<bg=cyan;fg=red>Fin du traitement</>');
    }

    private function updateVideos($listVideos = array())
    {
        $ids = array();

        foreach ($listVideos as $video) {
            $ids[] = $video->getLien();
        }

        $youtubeData = $this->getYoutubeData($ids);

        foreach ($listVideos as $video) {

            $youtubeVideo = $youtubeData[$video->getLien()];

            if (empty($youtubeVideo)) {
                $video->addTag($this->tagReplace);

                $this->output->writeln(
                    "Update Video to replace: <fg=yellow>(id= {$video->getId()}) {$video->getTitle()} </>"
                );

                $this->updatedVideosStatus++;
            } else {
                $video
                    ->setTitle($youtubeVideo['title'])
                    ->setDuration($youtubeVideo['duration'])
                    ->setThumbnail($youtubeVideo['thumbnail'])
                    ->setThumbnailHq($youtubeVideo['thumbnailHq']);

                $this->output->writeln(
                    "Update Video: <fg=green>(id={$video->getId()}) {$video->getTitle()}</>"
                );
            }

            $this->em->persist($video);

            $this->updatedVideos++;
        }

        $this->em->flush();
    }

    private function getYoutubeData($videoIds)
    {
        $videosYoutube = array_fill_keys($videoIds, null);

        $videoIds = implode(',', $videoIds);

        try {
            $response = $this->youtube->videos->listVideos("snippet,status,contentDetails", array('id' => $videoIds));
            $items = $response->getItems();

            foreach ($items as $youtubeVideo) {

                switch ($youtubeVideo->getStatus()->getPrivacyStatus()) {
                    case self::YOUTUBE_STATUS_PUBLIC:
                    case self::YOUTUBE_STATUS_UNLISTED:

                        $snippet = $youtubeVideo->getSnippet();

                        // Duration
                        $duration = $youtubeVideo->getContentDetails()->getDuration();
                        $interval = new \DateInterval($duration);
                        $duration = $this->toSeconds($interval);

                        // Thumbnails
                        $thumbnails = $snippet->getThumbnails();

                        $videosYoutube[$youtubeVideo->getId()] = array(
                            'title'       => $snippet->getTitle(),
                            'duration'    => $duration,
                            'thumbnail'   => $thumbnails->getDefault()->getUrl(),
                            'thumbnailHq' => $thumbnails->getHigh()->getUrl()
                        );

                        break;
                    default:
                        $this->output->writeln($youtubeVideo);
                        break;
                }
            }
        } catch (Exception $e) {
            $this->output->writeln('<bg=red;fg=white>Error: ' . $e->getMessage() . '</>');
        }

        return $videosYoutube;
    }

    /**
     * Convert Date Interval into total seconds
     *
     * @param \DateInterval $delta
     *
     * @return int
     */
    private function toSeconds(\DateInterval $delta)
    {
        $seconds = ($delta->s)
            + ($delta->i * 60)
            + ($delta->h * 60 * 60)
            + ($delta->d * 60 * 60 * 24)
            + ($delta->m * 60 * 60 * 24 * 30)
            + ($delta->y * 60 * 60 * 24 * 365);

        return (int) $seconds;
    }
}
