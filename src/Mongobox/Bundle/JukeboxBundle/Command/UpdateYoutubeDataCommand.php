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

    protected $debug;

    private $em;

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

        $this->input = $input;
        $this->output = $output;

        // Logger
        $this->logger = $this->getContainer()->get('logger');
        if ($this->debug === true) {
            $this->logger->pushProcessor(new MemoryPeakUsageProcessor());
            $this->logger->pushProcessor(new MemoryUsageProcessor());
            $this->logger ->pushHandler(new CliHandler($this->output));
        }

        $this->em = $this->getContainer()->get('doctrine')->getManager('default');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('<bg=cyan;fg=red>Start traitement </>');

        $client = new Google_Client();
        $client->setApplicationName("Mongobox");
        $client->setDeveloperKey("AIzaSyATMXQ_V-Ie8KaJEN0BAzK5bSSK2qvEukQ");
        $youtube = new Google_Service_YouTube($client);


        $em = $this->getContainer()->get('doctrine')->getManager('default');
        $videos = $em->getRepository('MongoboxJukeboxBundle:Videos')->findAll();

        $compteurVideo = $compteurDelatedVideo = 0;
        foreach ($videos as $key => $video) {

            try {

                $response =
                    $youtube->videos->listVideos("snippet,status,contentDetails", array('id' => $video->getLien()));

                $items = $response->getItems();
                if (empty($items)) {

                    $output->writeln('Error on video : <fg=red>' . $video->getLien() . '</>');

                    $this->deleteVideo($video);

                    $this->output->writeln('Delete video: ' . $video->getLien());
                    $compteurDelatedVideo++;

                    continue;
                }

                foreach ($response->getItems() as $youtubeVideo) {

                    $output->writeln('Youtube ID: ' . $youtubeVideo->getId());

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

                            $video
                                ->setTitle($snippet->getTitle())
                                ->setDuration($duration)
                                ->setThumbnail($thumbnails->getDefault()->getUrl())
                                ->setThumbnailHq($thumbnails->getHigh()->getUrl());
                            $em->persist($video);
                            $em->flush();

                            $output->writeln(
                                'Update Video: <fg=green>(id=' . $video->getId() . ') ' . $snippet->getTitle() . '</>'
                            );
                            $compteurVideo++;
                            break;
                        default:
                            $output->writeln('<bg=red;fg=white>############# New case #############</>');
                            $output->writeln($youtubeVideo);
                            break;
                    }
                }
            } catch (Exception $e) {
                $output->writeln('<bg=red;fg=white>Error: ' . $e->getMessage() . '</>');
                continue;
            }
        }

        $this->logger->addInfo("Updated videos: {$compteurVideo}");
        $this->logger->addInfo("Delated videos: {$compteurDelatedVideo}");

        $output->writeln('<bg=cyan;fg=red>Fin du traitement</>');
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

    /**
     * Delete video
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Videos $video
     */
    private function deleteVideo(\Mongobox\Bundle\JukeboxBundle\Entity\Videos $video)
    {

        #### Videos Group
        $videosGroup = $this->em->getRepository('MongoboxJukeboxBundle:VideoGroup')->findBy(
            array('video' => $video)
        );

        foreach ($videosGroup as $videoGr) {

            #### Playlist
            $videosPlaylist = $this->em->getRepository('MongoboxJukeboxBundle:Playlist')->findBy(
                array('video_group' => $videoGr)
            );

            foreach ($videosPlaylist as $videoPlaylist) {
                $this->em->remove($videoPlaylist);
            }

            $this->em->remove($videoGr);
        }


        #### Users Favoris
        $usersFavorites = $this->em->getRepository('MongoboxBookmarkBundle:UserFavoris')->findBy(
            array('video' => $video)
        );

        foreach ($usersFavorites as $videoFavorite) {
            $this->em->remove($videoFavorite);
        }

        #### Video
        $this->em->remove($video);

        $this->em->flush();
    }
}
