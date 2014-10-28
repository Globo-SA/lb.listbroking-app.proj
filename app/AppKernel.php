<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // Extra Symfony Bundles
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),

            // User Bundle
            new FOS\UserBundle\FOSUserBundle(),
            new Adclick\UserBundle\AdclickUserBundle(),

            // Exposes routing to the client-side
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),

            // Extraction Help
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),

            // API Bundle
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),

            // Core Bundles
            new Adclick\CacheBundle\AdclickCacheBundle(),
            new Adclick\AdvancedConfigurationBundle\AdclickAdvancedConfigurationBundle(),
            new Adclick\DoctrineBehaviorBundle\AdclickDoctrineBehaviorBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            // ListBroking Bundles
            new ListBroking\CoreBundle\ListBrokingCoreBundle(),
            new ListBroking\UIBundle\ListBrokingUIBundle(),
            new ListBroking\AdvancedConfigurationBundle\ListBrokingAdvancedConfigurationBundle(),
            new ListBroking\DoctrineBundle\ListBrokingDoctrineBundle(),
            new ListBroking\ExceptionHandlerBundle\ListBrokingExceptionHandlerBundle(),
            new ListBroking\ClientBundle\ListBrokingClientBundle(),
            new ListBroking\ExtractionBundle\ListBrokingExtractionBundle(),
            new ListBroking\LeadBundle\ListBrokingLeadBundle(),
            new ListBroking\LockBundle\ListBrokingLockBundle(),

            //API Bundle
            new ListBroking\APIBundle\ListBrokingAPIBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new RaulFraile\Bundle\LadybugBundle\RaulFraileLadybugBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
