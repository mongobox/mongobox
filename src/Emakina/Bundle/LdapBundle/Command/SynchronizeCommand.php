<?php

namespace Emakina\Bundle\LdapBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Emakina\Bundle\LdapBundle\Entity\User;

class SynchronizeCommand extends ContainerAwareCommand
{
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Console\Command.Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('emakina:ldap:synchronize')
            ->setDescription('Mise à jour des données de la DB depuis le LDAP')
        ;
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Console\Command.Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Compteur d'insertion
        $newUserCpt = $updtUserCpt = 0;
        // Récupération du container pour appeler le LDAP
        $container = $this->getContainer();
        $ldapManager = $container->get('emakina_ldap.ldapmanager');

        $em = $container->get('doctrine.orm.default_entity_manager');
		$result= $ldapManager->search('(objectclass=User)', 'OU=groupeReflect,DC=groupereflect,DC=net', \Zend\Ldap\Ldap::SEARCH_SCOPE_SUB);
		
        foreach ($result as $user) {
            $userDb = $em
                    ->getRepository('EmakinaLdapBundle:User')
                    ->findOneByTrigramme($user->get('trigramme'));

            if (is_null($userDb)) {
                $userDb = new User();
                $userDb->setIsEnabled(1);
					
                $newUserCpt++;
            }else $updtUserCpt++;

			$userDb->transform($user);
			
            $em->persist($userDb);
            $em->flush();
        }

        $output->writeln('Synchronisation terminée');
        $output->writeln($newUserCpt .' nouveaux utilisateurs');
        $output->writeln($updtUserCpt .' mise à jour');

    }
}
