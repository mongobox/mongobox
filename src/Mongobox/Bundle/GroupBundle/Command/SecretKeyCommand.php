<?php

namespace Mongobox\Bundle\GroupBundle\Command;

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

class SecretKeyCommand extends ContainerAwareCommand
{
    protected $force;
    protected $debug;
    protected $logger;

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('generate:groups:secret-keys')
            ->setDescription('Generates the secret keys used with Socket.IO for the users groups')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force full regeneration')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Show logs.')
        ;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->force    = $input->getOption('force');
        $this->debug    = $input->getOption('debug');

        $this->input	= $input;
        $this->output	= $output;

        // Logger
        $this->logger = $this->getContainer()->get('logger');
        if ($this->debug === true) {
            $this->logger->pushProcessor(new MemoryPeakUsageProcessor());
            $this->logger->pushProcessor(new MemoryUsageProcessor());
            $this->logger->pushHandler(new CliHandler($this->output));
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('default');

        /** @var \Mongobox\Bundle\GroupBundle\Entity\Repository\GroupRepository $repository */
        $repository = $em->getRepository('MongoboxGroupBundle:Group');

        if ($this->force === true) {
            $groups = $repository->findAll();
        } else {
            $groups = $repository->findBy(array(
                'secretKey' => null
            ));
        }

        foreach ($groups as $_item) {
            $secretKey = $this
                ->getContainer()
                ->get('mongobox_jukebox.live_configurator')
                ->generateSecretKey()
            ;

            if ($_item->getSecretKey() === '') {
                $this->logger->addDebug('Generating a new secret key.', array(
                    'group_id'      => $_item->getId(),
                    'secret_key'    => $secretKey
                ));
            } else {
                $this->logger->addDebug('Regenerating a new secret key.', array(
                    'group_id'      => $_item->getId(),
                    'secret_key'    => $secretKey
                ));
            }

            $_item->setSecretKey($secretKey);
            $em->flush($_item);
        }
    }
}
