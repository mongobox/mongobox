<?php
namespace Emk\Bundle\JukeboxBundle\Command\Monolog\Handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Symfony\Component\Console\Output\OutputInterface;

class CliHandler extends AbstractProcessingHandler
{
    private $initialized = false;
    private $output;
    private $statement;

    public function __construct(OutputInterface $output, $level = Logger::DEBUG, $bubble = false)
    {
        $this->output = $output;

        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        $this->output->write( (string) $record['formatted'] );
    }
}
