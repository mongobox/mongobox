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

class UpdateYoutubeDataCommand extends ContainerAwareCommand
{
    protected $debug;
    private $feedUrl;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
        ->setName('jukebox:updateDataYoutube')
        ->setDescription('Udpate data from Youtube.')
        ->setHelp('')
        ;

        $this->getDefinition()->addOptions(
                array(
                        new InputOption('debug',null,InputOption::VALUE_NONE,'Show Message'),
                )
        );
    }

    /**
     * Call before execute
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<bg=cyan;fg=white>Start traitement </>');

        $em = $this->getContainer()->get('doctrine')->getEntityManager('default');
        $videos = $em->getRepository('MongoboxJukeboxBundle:Videos')->findBy(array('duration' => 0));

        $compteurVideo = 0;
        foreach ($videos as $key => $video) {

            try {

                $this->feedUrl = 'http://gdata.youtube.com/feeds/api/videos/' . $video->getLien();
                $data = $this->_getFeedData();

                    if ($data) {

                        $output->writeln('Titre : <fg=green>(id=' . $video->getId() .') ' .  $data->title . '</>');

                        $video
                            ->setTitle( $data->title )
                            ->setDuration( $data->duration )
                            ->setThumbnail( $data->thumbnail->hqDefault )
                            ->setThumbnailHq( $data->thumbnail->sqDefault )
                        ;

                        $em->persist($video);
                        $em->flush();

                        $compteurVideo++;
                    } else {
                        $output->writeln('Error on video : <fg=red>' .  $video->getLien() . '</>');
                    }

            } catch (Exception $e) {
                continue;
            }

        }

        $this->logger->addInfo("Nombre de vidéos modifiées : {$compteurVideo}");

        $output->writeln('<bg=cyan;fg=white>Fin du traitement</>');

    }

    private function _getFeedData()
    {
        $feed = $this->feedUrl . '?v=2&alt=jsonc';

        $json = file_get_contents($feed);
        $data = json_decode($json);

        return $data->data;
    }

}
