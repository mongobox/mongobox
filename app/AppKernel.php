<?php
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
	/**
	 * (non-PHPdoc)
	 * @see \Symfony\Component\HttpKernel\KernelInterface::registerBundles()
	 */
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
			new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(),
            new DMS\Bundle\FilterBundle\DMSFilterBundle(),
			new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new PUGX\AutocompleterBundle\PUGXAutocompleterBundle(),
			// Mongobox
            new Mongobox\Bundle\TumblrBundle\MongoboxTumblrBundle(),
        	new Mongobox\Bundle\JukeboxBundle\MongoboxJukeboxBundle(),
			new Mongobox\Bundle\UsersBundle\MongoboxUsersBundle(),
			new Mongobox\Bundle\GroupBundle\MongoboxGroupBundle(),
			new Mongobox\Bundle\StatisticsBundle\MongoboxStatisticsBundle(),
            new Mongobox\Bundle\CoreBundle\MongoboxCoreBundle(),
            new Mongoeat\Bundle\FoursquareBundle\MongoeatFoursquareBundle(),
            new Mongoeat\Bundle\RestaurantBundle\MongoeatRestaurantBundle(),
            new Mongoeat\Bundle\VoteBundle\MongoeatVoteBundle(),
            new Mongobox\Bundle\BookmarkBundle\MongoboxBookmarkBundle()
	);

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
			$bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\KernelInterface::registerContainerConfiguration()
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
