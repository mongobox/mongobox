<?php

namespace Mongobox\Bundle\TumblrBundle\Command;

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
    protected $_baseUrl;
    protected $_debug;
    protected $_logger;

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('tumblr:import')
            ->setDescription('Import tumblr images on the local server')
            ->addArgument('baseUrl', InputArgument::REQUIRED, 'Base URL of the application.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Show debug logs.')
        ;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->_baseUrl  = $input->getArgument('baseUrl');
        $this->_debug    = $input->getOption('debug');

        $this->input	= $input;
        $this->output	= $output;

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
        $this->_logger->addInfo('Start tumblr images import.');

        $em = $this->getContainer()->get('doctrine')->getManager('default');
        $tumblrImages = $em->getRepository('MongoboxTumblrBundle:Tumblr')->findAll();

        $basePath = realpath($this->getContainer()->getParameter('kernel.root_dir')
            . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web');

        $tumblrPath = $basePath . DIRECTORY_SEPARATOR . 'tumblr';
        if (!is_dir($tumblrPath)) {
            mkdir($tumblrPath);
        }

        foreach ($tumblrImages as $_item) {
            $localImagePath = $_item->getLocalPath();
            if (!is_null($localImagePath)) {
                if (is_file($basePath . str_replace($this->_baseUrl, null, $localImagePath))) {
                    $this->_logger->addDebug(
                        'Recovery of the remote image skipped : already retrieved.',
                        array('tumblr_id' => $_item->getId())
                    );

                    continue;
                }
            }

            $postDate   = $_item->getDate()->format('Y-m-d');
            $imagePath  = $tumblrPath . DIRECTORY_SEPARATOR . $postDate;
            if (!is_dir($imagePath)) {
                mkdir($imagePath);
            }

            $imageUrl = $_item->getImage();
            if ($tmp = $this->getRemoteImage($imageUrl)) {
                $localImageName = md5(uniqid(rand(), true));
                $imageExtension = pathinfo($imageUrl, PATHINFO_EXTENSION);

                $localImagePath = $imagePath . DIRECTORY_SEPARATOR . $localImageName . '.' . $imageExtension;
                if (file_put_contents($localImagePath, $tmp)) {
                    $localImageUrl = $this->_baseUrl . '/tumblr/' . $postDate . '/' . $localImageName . '.' . $imageExtension;
                    $_item->setLocalPath($localImageUrl);

                    $em->persist($_item);
                    $em->flush();

                    $this->_logger->addDebug(
                        'Recovery of the remote image done successfully.',
                        array('tumblr_id' => $_item->getId())
                    );
                }
            }
        }

        $this->_logger->addInfo('Stop tumblr images import.');
    }

    /**
     * Retrieve a remote image data by a given url
     *
     * @param $url
     * @return mixed
     */
    protected function getRemoteImage($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $tmp    = curl_exec($ch);
        $infos  = curl_getinfo($ch);

        curl_close($ch);

        if (!in_array($infos['content_type'], array('image/jpeg', 'image/png', 'image/gif'))) {
            return false;
        }

        return $tmp;
    }
}
