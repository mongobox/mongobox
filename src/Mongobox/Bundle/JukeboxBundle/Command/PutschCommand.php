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

class PutschCommand extends ContainerAwareCommand
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
            ->setName('jukebox:putsch:clean')
            ->setDescription('Perform a check to reset all the putsches attempts that have failed.')
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
        $this->logger->addInfo('Cleaning of the failed putsches attempts - START');

        $defaultWaiting = $this->getContainer()->getParameter('next_putsch_waiting');

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('default');

        $groupsList = $em->getRepository('MongoboxGroupBundle:Group')->findAll();
        if (!empty($groupsList)) {
            foreach ($groupsList as $_item) {
                if ($_item->getNextPutschWaiting() !== null) {
                    $groupWaiting = (int) $_item->getNextPutschWaiting();
                } else {
                    $groupWaiting = $defaultWaiting;
                }

                $date = new \DateTime();
                $date->modify("- $groupWaiting minutes");

                $qb = $em->createQueryBuilder();

                $qb
                    ->update('MongoboxJukeboxBundle:Putsch', 'p')
                    ->set('p.response', $qb->expr()->literal(-1))
                    ->where('p.group = :group')
                    ->andWhere('p.date < :date')
                    ->andWhere('p.response is null')
                    ->setParameters(array(
                        'group' => $_item,
                        'date'  => $date
                    ))
                ;

                if ($result = $qb->getQuery()->execute()) {
                    $this->logger->addInfo(
                        'Resetting a putsch attempt done successfully !',
                        array('group' => $_item->getId())
                    );
                }
            }
        }

        $this->logger->addInfo('Cleaning of the failed putsches attempts - END');
    }
}
