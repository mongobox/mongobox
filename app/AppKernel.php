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
			// Mongobox
			new Emakina\Bundle\LdapBundle\EmakinaLdapBundle(),
            new Mongobox\Bundle\AuthenticationBundle\MongoboxAuthenticationBundle(),
            new Mongobox\Bundle\TumblrBundle\MongoboxTumblrBundle(),
        	new Mongobox\Bundle\JukeboxBundle\MongoboxJukeboxBundle(),
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
