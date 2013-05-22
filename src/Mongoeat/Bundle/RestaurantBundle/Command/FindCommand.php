<?php

namespace Mongoeat\Bundle\RestaurantBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mongoeat:restaurant:find')
            ->setDescription('Cherche via Foursquare les restaurants')
            ->addArgument('ville',InputArgument::REQUIRED,'ville de recherche')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.default_entity_manager');
        $service = $container->get('mongoeat_foursquare.service');

        $liste = $service->getRestaurant($input->getArgument('ville'));
        $progress = $this->getApplication()->getHelperSet()->get('progress');

        $progress->start($output, count($liste));
        foreach ($liste as $rest) {
            if (count($em->getRepository('MongoeatRestaurantBundle:Restaurant')->findByName($rest->getName())) === 0) {
                $em->persist($rest);
            }
            $progress->advance();
        }
        $em->flush();
        $progress->finish();

        $output->writeln("Recherche terminé");
    }
}
