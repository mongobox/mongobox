<?php
namespace Mongobox\Bundle\JukeboxBundle\Command;

use Mongobox\Bundle\JukeboxBundle\Entity\Videos;

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

class ImportCommand extends ContainerAwareCommand
{
    const IMPORT_DIR		= 'data';
    const FILE_EXTENSION	= 'csv';

    protected $_file;
    protected $_debug;
    protected $_logger;

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('jukebox:import')
            ->setDescription('Import new videos.')
            ->addArgument('file', InputArgument::REQUIRED, 'File (.csv) which contains the identifiers of Youtube videos.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Show logs')
        ;
    }

    /**
     * Check the given file format
     *
     * @param  string $file
     * @return bool
     */
    protected function checkImportFile($file)
    {
        $filePath = self::IMPORT_DIR . DIRECTORY_SEPARATOR . $file;
        if (!is_file($filePath)) {
            $this->_logger->addError('File does not exist.', array('filePath' => $filePath));

            return false;
        }

        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        if ($fileExtension !== self::FILE_EXTENSION) {
            $this->_logger->addError('File extension invalid.', array('given' => $fileExtension, 'expected' => self::FILE_EXTENSION));

            return false;
        }

        $this->_file = realpath($filePath);

        return true;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->debug = $input->getOption('debug');

        $this->input	= $input;
        $this->output	= $output;

        // Logger
        $this->_logger = $this->getContainer()->get('logger');
        if ($this->debug === true) {
            $this->_logger->pushProcessor(new MemoryPeakUsageProcessor());
            $this->_logger->pushProcessor(new MemoryUsageProcessor());
            $this->_logger->pushHandler(new CliHandler($this->output));
        }

        if (!$file = $input->getArgument('file')) {
            $this->_logger->addError('Argument "file" is required.');

            return $this;
        }

        if (!$this->checkImportFile($file)) {
            return $this;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->_file === null) {
            return $this;
        }

        $this->_logger->addInfo('Start videos import.');
        $em = $this->getContainer()->get('doctrine')->getManager('default');

        $newVideosCount = 0;

        if (($handle = fopen($this->_file, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                try {
                $videoId = Videos::parse_url_detail($data[0]);

                $video = new Videos();
                $video->setLien($videoId);

                $isVideo = $em
                    ->getRepository('MongoboxJukeboxBundle:Videos')
                    ->findOneby(array('lien' => $video->getLien()))
                ;
                } catch (Exception $e) {
                        $this->_logger->addWarning('Video foireuse step 1');
                        continue;

                    }

                if (!is_object($isVideo)) {
                    try {
                        $youtubeData = $video->getDataFromYoutube();

                        $video->setDate(new \Datetime())
                            ->setDone(0)
                            ->setTitle($youtubeData->title)
                            ->setAddressIp('127.0.0.1')
                            ->setDiffusion(0)
                            ->setDuration($youtubeData->duration)
                            ->setThumbnail($youtubeData->thumbnail->hqDefault)
                            ->setThumbnailHq($youtubeData->thumbnail->sqDefault)
                        ;

                        $em->persist($video);
                        $em->flush();

                        $newVideosCount++;
                        $this->_logger->addDebug('Video imported successfully.', array('videoId' => $videoId));
                    } catch (Exception $e) {
                        $this->_logger->addWarning('Video foireuse step2');
                        continue;

                    }
                }

            }
        }

        if ($newVideosCount === 0) {
            $this->_logger->addWarning('Unable to retrieve data from the file, please check the file format.');
        } else {
            $this->_logger->addInfo("$newVideosCount new videos imported successfully.");
        }

        $this->_logger->addInfo('Stop videos import.');
    }
}
