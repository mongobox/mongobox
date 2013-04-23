<?php
namespace Mongobox\Bundle\JukeboxBundle\Command;

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

// Web sockets
use Mongobox\Bundle\JukeboxBundle\Live\Stream;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class SocketCommand extends ContainerAwareCommand
{
    protected $_debug;
    protected $_logger;

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('jukebox:socket')
            ->setDescription('Manage the web sockets server.')
            ->addArgument('action', InputArgument::REQUIRED, 'Action to be performed on the server')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Show logs')
        ;
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
        $action = $input->getArgument('action');

        if ($action === 'start') {
            $this->logger->addInfo('Launch of the server in progress...');

            $server = IoServer::factory(
                new WsServer(new Stream()),
                8001
            );

            $server->run();
        }
    }
}
