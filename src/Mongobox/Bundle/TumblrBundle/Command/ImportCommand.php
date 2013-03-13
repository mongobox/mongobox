<?php

namespace Mongobox\Bundle\TumblrBundle\Command;

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

class ImportCommand extends ContainerAwareCommand
{
    protected $_baseUrl;
    protected $_debug;
    protected $_logger;
    protected $_progressBar;

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
            ->addOption('progress', null, InputOption::VALUE_NONE, 'Show progress bar.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Show debug logs.')
        ;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->_baseUrl     = $input->getArgument('baseUrl');
        $this->_progressBar = $input->getOption('progress');
        $this->_debug       = $input->getOption('debug');

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
        $this->_logger->addInfo('Tumblr images import - START');

        $em = $this->getContainer()->get('doctrine')->getManager('default');
        $tumblrImages   = $em->getRepository('MongoboxTumblrBundle:Tumblr')->findAll();
        $nbTumblrImages = count($tumblrImages);

        $basePath = realpath($this->getContainer()->getParameter('kernel.root_dir')
            . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web');

        // Check tumblr base path
        $tumblrPath = $basePath . DIRECTORY_SEPARATOR . 'tumblr';
        if (!is_dir($tumblrPath)) {
            mkdir($tumblrPath);
        }

        // Progress bar setup
        if ($this->_progressBar === true) {
            $app = new Application();

            $progress = $app->getHelperSet()->get('progress');
            $progress->start($output, $nbTumblrImages);
        }

        // Initialize reporting
        $reports = array(
            'total'     => $nbTumblrImages,
            'success'   => 0,
            'skipped'   => 0
        );

        $i = 0;
        foreach ($tumblrImages as $_item) {
            $i++;

            // Check image in the local directory
            $localImagePath = $_item->getLocalPath();
            if (!is_null($localImagePath)) {
                if (is_file($basePath . str_replace($this->_baseUrl, null, $localImagePath))) {
                    $this->_logger->addDebug(
                        'Recovery of the remote image skipped : already imported.',
                        array('tumblr_id' => $_item->getId())
                    );

                    // Advance the progress bar
                    if ($this->_progressBar === true) {
                        $progress->advance();

                        if ($this->_debug === true && $i < $nbTumblrImages) {
                            $output->writeln("\r");
                        }
                    }

                    $reports['skipped']++;

                    continue;
                }
            }

            // Check tumblr local directory
            $postDate   = $_item->getDate()->format('Y-m-d');
            $imagePath  = $tumblrPath . DIRECTORY_SEPARATOR . $postDate;
            if (!is_dir($imagePath)) {
                mkdir($imagePath);
            }

            // Retrieve remote image
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

                    $reports['success']++;
                }
            }

            // Advance the progress bar
            if ($this->_progressBar === true) {
                $progress->advance();

                if ($this->_debug === true && $i < $nbTumblrImages) {
                    $output->writeln("\r");
                }
            }
        }

        // Finish the progress bar
        if ($this->_progressBar === true) {
            $progress->finish();
        }

        $reports['failed'] = $reports['total'] - $reports['success'] - $reports['skipped'];
        $this->_logger->addInfo('Tumblr images import - END', $reports);
    }

    /**
     * Retrieve a remote image data by a given url
     *
     * @param $url
     * @return mixed
     */
    protected function getRemoteImage($url)
    {
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $tmp    = curl_exec($ch);
            $infos  = curl_getinfo($ch);

            curl_close($ch);
        } catch (\Exception $e) {
            $this->_logger->addError($e->getMessage(), array('url' => $url));

            return false;
        }

        if (!in_array($infos['content_type'], array('image/jpeg', 'image/png', 'image/gif'))) {
            $this->_logger->addError('Wrong file content type !', array('url' => $url));

            return false;
        }

        return $tmp;
    }
}
